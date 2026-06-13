<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => rtrim($title, '.'),
            'original_filename' => fake()->slug(3).'.pdf',
            'source_type' => fake()->randomElement(['image', 'pdf']),
            'page_count' => fake()->numberBetween(1, 12),
            'full_text' => fake()->paragraphs(3, true),
        ];
    }
}
