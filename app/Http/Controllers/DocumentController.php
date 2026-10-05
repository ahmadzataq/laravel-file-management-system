<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Services\DocumentService;
use App\Services\FolderService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
        private readonly FolderService $folders,
    ) {}

    /** Upload form. The target folder comes from the query string: /documents/create?folder=ID */
    public function create(Request $request): Response
    {
        $this->authorize('create', Document::class);

        $folder = Folder::findOrFail($request->integer('folder'));

        return Inertia::render('Documents/Create', [
            'folder' => ['id' => $folder->id, 'name' => $folder->name],
            'breadcrumbs' => $this->folders->breadcrumbs($folder),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'allowedExtensions' => Document::ALLOWED_EXTENSIONS,
            'maxSizeKb' => Document::MAX_SIZE_KB,
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = $this->documents->store(
            $request->validated(),
            $request->file('file'),
            $request->user(),
        );

        return redirect()
            ->route('folders.show', $document->folder_id)
            ->with('success', 'File uploaded.');
    }

    /** File detail page. */
    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        $document->load(['folder', 'department', 'uploader']);

        return Inertia::render('Documents/Show', [
            'document' => new DocumentResource($document),
            'breadcrumbs' => $this->folders->breadcrumbs($document->folder),
        ]);
    }

    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        $document->load(['folder', 'department']);

        return Inertia::render('Documents/Edit', [
            'document' => new DocumentResource($document),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $document->update($request->validated());

        return redirect()->route('documents.show', $document)->with('success', 'File updated.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $folderId = $document->folder_id;

        $this->documents->delete($document);

        return redirect()->route('folders.show', $folderId)->with('success', 'File deleted.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        return $this->diskFor($document)->download($document->path, $document->original_name);
    }

    /** Streams the file for inline display (PDF and images only). */
    public function preview(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless($document->isPreviewable(), 404);

        return $this->diskFor($document)->response(
            $document->path,
            $document->original_name,
            ['X-Content-Type-Options' => 'nosniff'],
        );
    }

    /** The storage disk, after making sure the physical file still exists. */
    private function diskFor(Document $document): FilesystemAdapter
    {
        $disk = Storage::disk(Document::DISK);

        abort_unless($disk->exists($document->path), 404, 'The file is missing from storage.');

        return $disk;
    }
}
