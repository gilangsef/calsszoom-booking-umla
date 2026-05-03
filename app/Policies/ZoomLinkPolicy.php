<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ZoomLink;
use Illuminate\Auth\Access\HandlesAuthorization;

class ZoomLinkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ZoomLink');
    }

    public function view(AuthUser $authUser, ZoomLink $zoomLink): bool
    {
        return $authUser->can('View:ZoomLink');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ZoomLink');
    }

    public function update(AuthUser $authUser, ZoomLink $zoomLink): bool
    {
        return $authUser->can('Update:ZoomLink');
    }

    public function delete(AuthUser $authUser, ZoomLink $zoomLink): bool
    {
        return $authUser->can('Delete:ZoomLink');
    }

    public function restore(AuthUser $authUser, ZoomLink $zoomLink): bool
    {
        return $authUser->can('Restore:ZoomLink');
    }

    public function forceDelete(AuthUser $authUser, ZoomLink $zoomLink): bool
    {
        return $authUser->can('ForceDelete:ZoomLink');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ZoomLink');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ZoomLink');
    }

    public function replicate(AuthUser $authUser, ZoomLink $zoomLink): bool
    {
        return $authUser->can('Replicate:ZoomLink');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ZoomLink');
    }

}