<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ZoomBooking;
use Illuminate\Auth\Access\HandlesAuthorization;

class ZoomBookingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ZoomBooking');
    }

    public function view(AuthUser $authUser, ZoomBooking $zoomBooking): bool
    {
        return $authUser->can('View:ZoomBooking');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ZoomBooking');
    }

    public function update(AuthUser $authUser, ZoomBooking $zoomBooking): bool
    {
        return $authUser->can('Update:ZoomBooking');
    }

    public function delete(AuthUser $authUser, ZoomBooking $zoomBooking): bool
    {
        return $authUser->can('Delete:ZoomBooking');
    }

    public function restore(AuthUser $authUser, ZoomBooking $zoomBooking): bool
    {
        return $authUser->can('Restore:ZoomBooking');
    }

    public function forceDelete(AuthUser $authUser, ZoomBooking $zoomBooking): bool
    {
        return $authUser->can('ForceDelete:ZoomBooking');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ZoomBooking');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ZoomBooking');
    }

    public function replicate(AuthUser $authUser, ZoomBooking $zoomBooking): bool
    {
        return $authUser->can('Replicate:ZoomBooking');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ZoomBooking');
    }

}