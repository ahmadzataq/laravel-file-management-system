<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /** Viewers may look at, preview and download files. */
    public function view(User $user, Document $document): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, Document $document): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->isAdministrator();
    }
}
