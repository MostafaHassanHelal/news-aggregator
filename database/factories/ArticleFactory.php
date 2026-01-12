<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'external_id' => $this->faker->uuid(),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name(),
            'url' => $this->faker->url(),
            'image_url' => $this->faker->imageUrl(800, 600, 'news'),
            'category' => $this->faker->randomElement([
                'Technology', 'Business', 'Sports', 'Entertainment',
                'Politics', 'Science', 'Health', 'World',
            ]),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
