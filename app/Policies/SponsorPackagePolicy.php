<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SponsorPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class SponsorPackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_sponsor::package');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SponsorPackage $sponsorPackage): bool
    {
        return $user->can('view_sponsor::package');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sponsor::package');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SponsorPackage $sponsorPackage): bool
    {
        return $user->can('update_sponsor::package');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SponsorPackage $sponsorPackage): bool
    {
        return $user->can('delete_sponsor::package');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_sponsor::package');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, SponsorPackage $sponsorPackage): bool
    {
        return $user->can('force_delete_sponsor::package');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_sponsor::package');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, SponsorPackage $sponsorPackage): bool
    {
        return $user->can('restore_sponsor::package');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_sponsor::package');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, SponsorPackage $sponsorPackage): bool
    {
        return $user->can('replicate_sponsor::package');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_sponsor::package');
    }
}
