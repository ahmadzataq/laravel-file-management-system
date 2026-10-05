<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'folder_id' => Folder::factory(),
            'department_id' => Department::factory(),
            'uploaded_by' => User::factory()->administrator(),
            'title' => fake()->catchPhrase(),
            'original_name' => fake()->slug(2).'.pdf',
            'path' => 'documents/'.Str::random(40).'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(10_000, 5_000_000),
        ];
    }
}
