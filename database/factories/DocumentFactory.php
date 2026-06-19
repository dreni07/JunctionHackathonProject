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
        $title = $this->faker->sentence(4);

        return [
            'title' => rtrim($title, '.'),
            'original_filename' => $this->faker->slug(3).'.pdf',
            'source_type' => $this->faker->randomElement(['image', 'pdf']),
            'page_count' => $this->faker->numberBetween(1, 12),
            'full_text' => $this->faker->paragraphs(3, true),
        ];
    }
}
