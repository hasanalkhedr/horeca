<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EffAdsPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class EffAdsPackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_eff::ads::package');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EffAdsPackage $effAdsPackage): bool
    {
        return $user->can('view_eff::ads::package');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_eff::ads::package');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EffAdsPackage $effAdsPackage): bool
    {
        return $user->can('update_eff::ads::package');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EffAdsPackage $effAdsPackage): bool
    {
        return $user->can('delete_eff::ads::package');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_eff::ads::package');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, EffAdsPackage $effAdsPackage): bool
    {
        return $user->can('force_delete_eff::ads::package');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_eff::ads::package');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, EffAdsPackage $effAdsPackage): bool
    {
        return $user->can('restore_eff::ads::package');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_eff::ads::package');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, EffAdsPackage $effAdsPackage): bool
    {
        return $user->can('replicate_eff::ads::package');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_eff::ads::package');
    }
}
