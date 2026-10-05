<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_administrator_can_upload_a_file(): void
    {
        $admin = User::factory()->administrator()->create();
        $folder = Folder::factory()->create();
        $department = Department::factory()->create();

        $this->actingAs($admin)
            ->post(route('documents.store'), [
                'folder_id' => $folder->id,
                'department_id' => $department->id,
                'title' => 'Employee Handbook',
                'file' => UploadedFile::fake()->create('handbook.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('folders.show', $folder));

        $document = Document::firstOrFail();

        $this->assertSame('Employee Handbook', $document->title);
        $this->assertSame('handbook.pdf', $document->original_name);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame($admin->id, $document->uploaded_by);
        $this->assertSame($folder->id, $document->folder_id);
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_upload_requires_a_title_department_and_file(): void
    {
        $folder = Folder::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('documents.store'), ['folder_id' => $folder->id])
            ->assertSessionHasErrors(['title', 'department_id', 'file']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_upload_rejects_disallowed_types_and_oversized_files(): void
    {
        $folder = Folder::factory()->create();
        $department = Department::factory()->create();

        $this->actingAs(User::factory()->administrator()->create());

        $payload = fn (UploadedFile $file) => [
            'folder_id' => $folder->id,
            'department_id' => $department->id,
            'title' => 'Some file',
            'file' => $file,
        ];

        $this->post(route('documents.store'), $payload(UploadedFile::fake()->create('virus.exe', 10)))
            ->assertSessionHasErrors('file');

        $this->post(route('documents.store'), $payload(UploadedFile::fake()->create('big.pdf', Document::MAX_SIZE_KB + 1)))
            ->assertSessionHasErrors('file');

        // Looks like plain text, but is named like a program: the extension must be allowed as well.
        $this->post(route('documents.store'), $payload(UploadedFile::fake()->create('notes.exe', 10, 'text/plain')))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_viewers_cannot_upload_files(): void
    {
        $folder = Folder::factory()->create();
        $department = Department::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        $this->get(route('documents.create', ['folder' => $folder->id]))->assertForbidden();

        $this->post(route('documents.store'), [
            'folder_id' => $folder->id,
            'department_id' => $department->id,
            'title' => 'Nope',
            'file' => UploadedFile::fake()->create('nope.pdf', 10),
        ])->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_viewers_can_see_file_details_and_download_the_file(): void
    {
        Storage::disk('local')->put('documents/sample.txt', 'hello');
        $document = Document::factory()->create([
            'title' => 'Sample',
            'path' => 'documents/sample.txt',
            'original_name' => 'sample.txt',
            'mime_type' => 'text/plain',
        ]);

        $this->actingAs(User::factory()->viewer()->create());

        $this->get(route('documents.show', $document))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('document.title', 'Sample')
                ->where('document.file_name', 'sample.txt')
                ->has('document.folder.name')
                ->has('document.department.name')
                ->has('document.uploaded_by')
                ->has('document.created_at')
            );

        $this->get(route('documents.download', $document))
            ->assertOk()
            ->assertDownload('sample.txt');
    }

    public function test_download_returns_404_when_the_stored_file_is_missing(): void
    {
        $document = Document::factory()->create(['path' => 'documents/missing.pdf']);

        $this->actingAs(User::factory()->viewer()->create())
            ->get(route('documents.download', $document))
            ->assertNotFound();
    }

    public function test_only_pdf_and_images_can_be_previewed(): void
    {
        Storage::disk('local')->put('documents/a.pdf', '%PDF-1.4');
        Storage::disk('local')->put('documents/a.txt', 'text');

        $pdf = Document::factory()->create([
            'path' => 'documents/a.pdf',
            'original_name' => 'a.pdf',
            'mime_type' => 'application/pdf',
        ]);
        $text = Document::factory()->create([
            'path' => 'documents/a.txt',
            'original_name' => 'a.txt',
            'mime_type' => 'text/plain',
        ]);

        $this->actingAs(User::factory()->viewer()->create());

        $response = $this->get(route('documents.preview', $pdf))->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));

        $this->get(route('documents.preview', $text))->assertNotFound();
    }

    public function test_administrator_can_edit_file_information(): void
    {
        $document = Document::factory()->create(['title' => 'Old title']);
        $department = Department::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('documents.update', $document), [
                'title' => 'New title',
                'department_id' => $department->id,
            ])
            ->assertRedirect(route('documents.show', $document));

        $document->refresh();
        $this->assertSame('New title', $document->title);
        $this->assertSame($department->id, $document->department_id);
    }

    public function test_administrator_can_delete_a_file_and_its_stored_content(): void
    {
        Storage::disk('local')->put('documents/a.pdf', 'content');
        $document = Document::factory()->create(['path' => 'documents/a.pdf']);

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('documents.destroy', $document))
            ->assertRedirect(route('folders.show', $document->folder_id));

        $this->assertModelMissing($document);
        Storage::disk('local')->assertMissing('documents/a.pdf');
    }

    public function test_viewers_cannot_edit_or_delete_files(): void
    {
        $document = Document::factory()->create(['title' => 'Original']);
        $department = Department::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        $this->get(route('documents.edit', $document))->assertForbidden();
        $this->put(route('documents.update', $document), [
            'title' => 'Hacked',
            'department_id' => $department->id,
        ])->assertForbidden();
        $this->delete(route('documents.destroy', $document))->assertForbidden();

        $this->assertSame('Original', $document->fresh()->title);
    }
}
