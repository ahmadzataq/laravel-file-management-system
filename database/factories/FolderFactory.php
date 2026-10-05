<?php

namespace Database\Factories;

use App\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Folder>
 */
class FolderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
        ];
    }

    public function childOf(Folder $parent): static
    {
        return $this->state(fn (array $attributes) => ['parent_id' => $parent->id]);
    }
}
