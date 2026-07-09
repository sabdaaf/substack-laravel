<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'id' => fake()->uuid(),
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'body' => fake()->paragraphs(3, true),
            'author_id' => \App\Models\User::factory(),
        ];
    }
}
