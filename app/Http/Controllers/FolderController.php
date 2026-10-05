<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\FolderResource;
use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FolderController extends Controller
{
    public function __construct(private readonly FolderService $folders) {}

    /** Top level of the explorer: every root folder. */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Folder::class);

        return $this->browse($request, null);
    }

    /** Contents of one folder: its sub-folders and its files. */
    public function show(Request $request, Folder $folder): Response
    {
        $this->authorize('view', $folder);

        return $this->browse($request, $folder);
    }

    public function store(StoreFolderRequest $request): RedirectResponse
    {
        $folder = Folder::create($request->validated());

        return $this->redirectToFolder($folder->parent_id)->with('success', 'Folder created.');
    }

    public function update(UpdateFolderRequest $request, Folder $folder): RedirectResponse
    {
        $folder->update($request->validated());

        return $this->redirectToFolder($folder->parent_id)->with('success', 'Folder renamed.');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        $parentId = $folder->parent_id;

        $this->folders->delete($folder);

        return $this->redirectToFolder($parentId)->with('success', 'Folder and everything inside it were deleted.');
    }

    /**
     * Build the explorer page for the given folder (null = top level).
     *
     * When a search term or department filter is active, the file list switches to
     * "search results" and covers the files of ALL folders.
     */
    private function browse(Request $request, ?Folder $folder): Response
    {
        $term = trim((string) $request->query('q', ''));
        $departmentId = $request->integer('department') ?: null;
        $searching = $term !== '' || $departmentId !== null;

        $documents = Document::query()
            ->with(['folder:id,name', 'department:id,name', 'uploader:id,name'])
            ->when(
                $searching,
                fn ($query) => $query
                    ->search($term)
                    ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId)),
                fn ($query) => $query->where('folder_id', $folder?->id),
            )
            ->latest()
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $folders = $searching
            ? collect()
            : Folder::query()
                ->where('parent_id', $folder?->id)
                ->withCount(['children', 'documents'])
                ->orderBy('name')
                ->get();

        return Inertia::render('Folders/Index', [
            'folder' => $folder ? new FolderResource($folder) : null,
            'breadcrumbs' => $folder ? $this->folders->breadcrumbs($folder) : [],
            'folders' => FolderResource::collection($folders),
            'documents' => DocumentResource::collection($documents),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'filters' => ['q' => $term, 'department' => $departmentId],
            'searching' => $searching,
        ]);
    }

    private function redirectToFolder(?int $folderId): RedirectResponse
    {
        return $folderId
            ? redirect()->route('folders.show', $folderId)
            : redirect()->route('folders.index');
    }
}
