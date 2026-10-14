<?php

namespace App\Policies;

use App\Models\ContentSection;
use App\Models\User;

class ContentSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.manage');
    }

    public function update(User $user, ContentSection $section): bool
    {
        return $user->hasPermission('content.manage');
    }
}
