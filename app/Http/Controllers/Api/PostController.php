<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Post\StorePostRequest;
use App\Http\Requests\Api\Post\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Get query parameters with defaults
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $order = $request->input('order', 'desc');

        // Validate sort_by to prevent SQL injection
        $allowedSortFields = ['id', 'title', 'slug', 'created_at', 'updated_at'];
        if (! in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        // Validate order
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'desc';

        $posts = Post::with('author:id,name,email')
            // Filter by author_id
            ->when($request->author_id, function ($query, $authorId) {
                return $query->where('author_id', $authorId);
            })
            // Search by title or body
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%');
                });
            })
            // Filter by slug
            ->when($request->slug, function ($query, $slug) {
                return $query->where('slug', $slug);
            })
            // Sorting
            ->orderBy($sortBy, $order)
            ->paginate($perPage);

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'body' => $request->body,
            'author_id' => $request->user()->id,
        ]);

        $post->load('author:id,name,email');

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load('author:id,name,email');

        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $post->update($request->validated());

        $post->load('author:id,name,email');

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        // Check if user is the author
        if ($post->author_id !== request()->user()->id) {
            return response()->json([
                'message' => 'Forbidden. You can only delete your own posts.',
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}
