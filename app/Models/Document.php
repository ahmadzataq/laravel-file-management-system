<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    /** Storage disk for uploaded files. "local" is private: files are served only through controller routes. */
    public const DISK = 'local';

    /** File types that may be uploaded (shared by validation and the upload form). */
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png',
    ];

    /** Maximum upload size in kilobytes (10 MB). */
    public const MAX_SIZE_KB = 10240;

    /** Types the browser can show inline. */
    public const PREVIEWABLE_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    protected $fillable = [
        'folder_id',
        'department_id',
        'uploaded_by',
        'title',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isPreviewable(): bool
    {
        return in_array($this->mime_type, self::PREVIEWABLE_MIME_TYPES, true);
    }

    /**
     * Search by title, original file name or department name (case-insensitive).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = "%{$term}%";

        return $query->where(function (Builder $query) use ($like) {
            $query->whereLike('title', $like)
                ->orWhereLike('original_name', $like)
                ->orWhereHas('department', fn (Builder $department) => $department->whereLike('name', $like));
        });
    }
}
