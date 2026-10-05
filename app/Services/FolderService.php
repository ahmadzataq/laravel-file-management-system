<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FolderService
{
    /**
     * Folders from the root down to (and including) the given folder.
     *
     * @return list<array{id: int, name: string}>
     */
    public function breadcrumbs(Folder $folder): array
    {
        $trail = [];

        for ($current = $folder; $current !== null; $current = $current->parent) {
            array_unshift($trail, ['id' => $current->id, 'name' => $current->name]);
        }

        return $trail;
    }

    /**
     * Delete a folder together with all of its sub-folders, files and stored file contents.
     */
    public function delete(Folder $folder): void
    {
        $folderIds = $this->descendantIds($folder)->push($folder->id);

        $paths = Document::whereIn('folder_id', $folderIds)->pluck('path');

        // Sub-folders and document rows are removed by the ON DELETE CASCADE foreign keys.
        $folder->delete();

        Storage::disk(Document::DISK)->delete($paths->all());
    }

    /**
     * IDs of every folder below the given one, collected level by level.
     *
     * @return Collection<int, int>
     */
    private function descendantIds(Folder $folder): Collection
    {
        $descendants = collect();
        $level = collect([$folder->id]);

        while ($level->isNotEmpty()) {
            $level = Folder::whereIn('parent_id', $level)->pluck('id');
            $descendants = $descendants->merge($level);
        }

        return $descendants;
    }
}
