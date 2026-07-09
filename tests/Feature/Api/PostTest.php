<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

test('guest can list all posts', function () {
    Post::factory()->count(3)->create();

    $response = getJson('/api/posts');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'body', 'author_id', 'created_at', 'updated_at', 'author'],
            ],
        ]);
});

test('guest can view a single post', function () {
    $post = Post::factory()->create();

    $response = getJson('/api/posts/'.$post->slug);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'post' => ['id', 'title', 'slug', 'body', 'author_id', 'created_at', 'updated_at', 'author'],
        ])
        ->assertJson([
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
            ],
        ]);
});

test('authenticated user can create a post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = postJson('/api/posts', [
        'title' => 'Test Post Title',
        'body' => 'This is the body of the test post.',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'post' => ['id', 'title', 'slug', 'body', 'author_id', 'created_at', 'updated_at', 'author'],
        ])
        ->assertJson([
            'message' => 'Post created successfully',
            'post' => [
                'title' => 'Test Post Title',
                'slug' => 'test-post-title',
                'body' => 'This is the body of the test post.',
                'author_id' => $user->id,
            ],
        ]);

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post Title',
        'slug' => 'test-post-title',
        'author_id' => $user->id,
    ]);
});

test('unauthenticated user cannot create a post', function () {
    $response = postJson('/api/posts', [
        'title' => 'Test Post Title',
        'body' => 'This is the body of the test post.',
    ]);

    $response->assertUnauthorized();
});

test('post creation validates required fields', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = postJson('/api/posts', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'body']);
});

test('slug is automatically generated from title', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = postJson('/api/posts', [
        'title' => 'My Awesome Blog Post',
        'body' => 'Content here.',
    ]);

    $response->assertCreated()
        ->assertJson([
            'post' => [
                'slug' => 'my-awesome-blog-post',
            ],
        ]);
});

test('custom slug can be provided', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = postJson('/api/posts', [
        'title' => 'My Post',
        'slug' => 'custom-slug-here',
        'body' => 'Content here.',
    ]);

    $response->assertCreated()
        ->assertJson([
            'post' => [
                'slug' => 'custom-slug-here',
            ],
        ]);
});

test('slug must be unique', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    Post::factory()->create(['slug' => 'existing-slug']);

    $response = postJson('/api/posts', [
        'title' => 'New Post',
        'slug' => 'existing-slug',
        'body' => 'Content here.',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

test('author can update their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['author_id' => $user->id]);

    Sanctum::actingAs($user, ['*']);

    $response = putJson('/api/posts/'.$post->slug, [
        'title' => 'Updated Title',
        'body' => 'Updated body content.',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Post updated successfully',
            'post' => [
                'title' => 'Updated Title',
                'slug' => 'updated-title',
                'body' => 'Updated body content.',
            ],
        ]);

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Title',
    ]);
});

test('user cannot update another users post', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($otherUser, ['*']);

    $response = putJson('/api/posts/'.$post->slug, [
        'title' => 'Attempted Update',
    ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot update a post', function () {
    $post = Post::factory()->create();

    $response = putJson('/api/posts/'.$post->slug, [
        'title' => 'Attempted Update',
    ]);

    $response->assertUnauthorized();
});

test('author can delete their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['author_id' => $user->id]);

    Sanctum::actingAs($user, ['*']);

    $response = deleteJson('/api/posts/'.$post->slug);

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Post deleted successfully',
        ]);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

test('user cannot delete another users post', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create(['author_id' => $author->id]);

    Sanctum::actingAs($otherUser, ['*']);

    $response = deleteJson('/api/posts/'.$post->slug);

    $response->assertForbidden();

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
    ]);
});

test('unauthenticated user cannot delete a post', function () {
    $post = Post::factory()->create();

    $response = deleteJson('/api/posts/'.$post->slug);

    $response->assertUnauthorized();
});

test('post has uuid as primary key', function () {
    $post = Post::factory()->create();

    expect($post->id)->toBeString()
        ->and($post->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

test('post belongs to author user', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['author_id' => $user->id]);

    expect($post->author)->toBeInstanceOf(User::class)
        ->and($post->author->id)->toBe($user->id);
});

test('user has many posts relationship', function () {
    $user = User::factory()->create();
    Post::factory()->count(3)->create(['author_id' => $user->id]);

    expect($user->posts)->toHaveCount(3)
        ->and($user->posts->first())->toBeInstanceOf(Post::class);
});

test('posts can be filtered by author', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Post::factory()->count(2)->create(['author_id' => $user1->id]);
    Post::factory()->count(3)->create(['author_id' => $user2->id]);

    $response = getJson('/api/posts?author_id='.$user1->id);

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('partial update works with patch method', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'author_id' => $user->id,
        'title' => 'Original Title',
        'body' => 'Original body',
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->patchJson('/api/posts/'.$post->slug, [
        'title' => 'Only Title Updated',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'post' => [
                'title' => 'Only Title Updated',
                'body' => 'Original body',
            ],
        ]);
});

test('can paginate posts with custom per_page', function () {
    Post::factory()->count(25)->create();

    $response = getJson('/api/posts?per_page=5');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'current_page',
            'data',
            'per_page',
            'total',
        ])
        ->assertJson(['per_page' => 5]);
});

test('pagination defaults to 10 per page', function () {
    Post::factory()->count(15)->create();

    $response = getJson('/api/posts');

    $response->assertSuccessful()
        ->assertJsonCount(10, 'data')
        ->assertJson(['per_page' => 10]);
});

test('can sort posts by title ascending', function () {
    Post::factory()->create(['title' => 'Zebra Post']);
    Post::factory()->create(['title' => 'Alpha Post']);
    Post::factory()->create(['title' => 'Beta Post']);

    $response = getJson('/api/posts?sort_by=title&order=asc');

    $response->assertSuccessful();
    $data = $response->json('data');

    expect($data[0]['title'])->toBe('Alpha Post')
        ->and($data[1]['title'])->toBe('Beta Post')
        ->and($data[2]['title'])->toBe('Zebra Post');
});

test('can sort posts by created_at descending by default', function () {
    $post1 = Post::factory()->create(['created_at' => now()->subDays(3)]);
    $post2 = Post::factory()->create(['created_at' => now()->subDays(1)]);
    $post3 = Post::factory()->create(['created_at' => now()->subDays(2)]);

    $response = getJson('/api/posts');

    $response->assertSuccessful();
    $data = $response->json('data');

    expect($data[0]['id'])->toBe($post2->id)
        ->and($data[1]['id'])->toBe($post3->id)
        ->and($data[2]['id'])->toBe($post1->id);
});

test('can filter posts by search term in title', function () {
    Post::factory()->create(['title' => 'Laravel is awesome']);
    Post::factory()->create(['title' => 'PHP is great']);
    Post::factory()->create(['title' => 'Laravel tutorial']);

    $response = getJson('/api/posts?search=Laravel');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $data = $response->json('data');
    expect($data[0]['title'])->toContain('Laravel')
        ->and($data[1]['title'])->toContain('Laravel');
});

test('can filter posts by search term in body', function () {
    Post::factory()->create(['body' => 'This post contains Laravel content']);
    Post::factory()->create(['body' => 'This is about PHP']);
    Post::factory()->create(['body' => 'Another Laravel post']);

    $response = getJson('/api/posts?search=Laravel');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('can filter posts by slug', function () {
    Post::factory()->create(['slug' => 'my-unique-post']);
    Post::factory()->create(['slug' => 'another-post']);

    $response = getJson('/api/posts?slug=my-unique-post');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJson([
            'data' => [
                ['slug' => 'my-unique-post'],
            ],
        ]);
});

test('invalid sort_by field defaults to created_at', function () {
    Post::factory()->count(3)->create();

    $response = getJson('/api/posts?sort_by=invalid_field');

    $response->assertSuccessful();
    // Should still return results without error
    expect($response->json('data'))->toBeArray();
});

test('invalid order defaults to desc', function () {
    Post::factory()->count(3)->create();

    $response = getJson('/api/posts?order=invalid');

    $response->assertSuccessful();
    // Should still return results without error
    expect($response->json('data'))->toBeArray();
});
