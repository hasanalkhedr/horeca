<?php

namespace App\Observers;

use App\Models\Contract;
use App\Models\UserTarget;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $this->updateUserTargets($contract);
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        $this->updateUserTargets($contract);
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        $this->updateUserTargets($contract);
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        $this->updateUserTargets($contract);
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        $this->updateUserTargets($contract);
    }

    /**
     * Update user targets for the contract's seller and event.
     */
    private function updateUserTargets(Contract $contract): void
    {
        try {
            // Find the user target for this seller and event
            $userTarget = UserTarget::where('user_id', $contract->seller)
                ->where('event_id', $contract->event_id)
                ->first();

            if ($userTarget) {
                $userTarget->updateAchievedValues();
            }
        } catch (\Exception $e) {
            // Log error but don't break the contract operation
            \Log::error('Failed to update user targets: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'seller' => $contract->seller,
                'event_id' => $contract->event_id,
            ]);
        }
    }
}
