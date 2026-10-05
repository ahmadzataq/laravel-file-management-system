<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FolderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_a_root_folder(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('folders.store'), ['name' => 'Finance'])
            ->assertRedirect(route('folders.index'));

        $this->assertDatabaseHas('folders', ['name' => 'Finance', 'parent_id' => null]);
    }

    public function test_administrator_can_create_a_nested_folder(): void
    {
        $parent = Folder::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('folders.store'), ['name' => '2026', 'parent_id' => $parent->id])
            ->assertRedirect(route('folders.show', $parent));

        $this->assertDatabaseHas('folders', ['name' => '2026', 'parent_id' => $parent->id]);
    }

    public function test_folder_names_must_be_unique_among_siblings(): void
    {
        $parent = Folder::factory()->create();
        $otherParent = Folder::factory()->create();
        Folder::factory()->childOf($parent)->create(['name' => 'Reports']);

        $this->actingAs(User::factory()->administrator()->create());

        $this->post(route('folders.store'), ['name' => 'Reports', 'parent_id' => $parent->id])
            ->assertSessionHasErrors('name');

        // The same name under a different parent is fine.
        $this->post(route('folders.store'), ['name' => 'Reports', 'parent_id' => $otherParent->id])
            ->assertSessionHasNoErrors();
    }

    public function test_root_folder_names_must_be_unique(): void
    {
        Folder::factory()->create(['name' => 'Finance']);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('folders.store'), ['name' => 'Finance'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Folder::where('name', 'Finance')->count());
    }

    public function test_administrator_can_rename_a_folder(): void
    {
        $folder = Folder::factory()->create(['name' => 'Old name']);

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('folders.update', $folder), ['name' => 'New name'])
            ->assertRedirect(route('folders.index'));

        $this->assertSame('New name', $folder->fresh()->name);
    }

    public function test_a_folder_can_keep_its_own_name_when_renamed(): void
    {
        $folder = Folder::factory()->create(['name' => 'Same']);

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('folders.update', $folder), ['name' => 'Same'])
            ->assertSessionHasNoErrors();
    }

    public function test_deleting_a_folder_removes_subfolders_files_and_stored_contents(): void
    {
        Storage::fake('local');

        $root = Folder::factory()->create();
        $child = Folder::factory()->childOf($root)->create();
        Storage::disk('local')->put('documents/a.pdf', 'content');
        $document = Document::factory()->create(['folder_id' => $child->id, 'path' => 'documents/a.pdf']);

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('folders.destroy', $root))
            ->assertRedirect(route('folders.index'));

        $this->assertModelMissing($root);
        $this->assertModelMissing($child);
        $this->assertModelMissing($document);
        Storage::disk('local')->assertMissing('documents/a.pdf');
    }

    public function test_viewers_cannot_create_rename_or_delete_folders(): void
    {
        $folder = Folder::factory()->create(['name' => 'Original']);

        $this->actingAs(User::factory()->viewer()->create());

        $this->post(route('folders.store'), ['name' => 'Hacked'])->assertForbidden();
        $this->put(route('folders.update', $folder), ['name' => 'Hacked'])->assertForbidden();
        $this->delete(route('folders.destroy', $folder))->assertForbidden();

        $this->assertDatabaseMissing('folders', ['name' => 'Hacked']);
        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'name' => 'Original']);
    }
}
