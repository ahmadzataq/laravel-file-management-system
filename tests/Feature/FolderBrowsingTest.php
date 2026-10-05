<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FolderBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_browse_folders(): void
    {
        $this->get(route('folders.index'))->assertRedirect(route('login'));
    }

    public function test_viewers_can_browse_folders_with_a_breadcrumb_trail(): void
    {
        $root = Folder::factory()->create(['name' => 'Finance']);
        $child = Folder::factory()->childOf($root)->create(['name' => '2026']);
        Document::factory()->create(['folder_id' => $child->id, 'title' => 'Budget']);

        $this->actingAs(User::factory()->viewer()->create());

        $this->get(route('folders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Folders/Index')
                ->has('folders', 1)
                ->where('folders.0.name', 'Finance')
                ->where('folders.0.children_count', 1)
                ->has('documents.data', 0)
            );

        $this->get(route('folders.show', $child))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('breadcrumbs', [
                    ['id' => $root->id, 'name' => 'Finance'],
                    ['id' => $child->id, 'name' => '2026'],
                ])
                ->has('documents.data', 1)
                ->where('documents.data.0.title', 'Budget')
            );
    }
}
