<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTarget extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'target_space',
        'target_space_amount',
        'target_sponsor_amount',
        'achieved_space',
        'achieved_space_amount',
        'achieved_sponsor_amount',
        'contracts_count',
        'completion_percentage',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_space' => 'decimal:2',
        'target_space_amount' => 'decimal:2',
        'target_sponsor_amount' => 'decimal:2',
        'achieved_space' => 'decimal:2',
        'achieved_space_amount' => 'decimal:2',
        'achieved_sponsor_amount' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
    ];

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Update achieved values when creating a new target
        static::created(function ($userTarget) {
            $userTarget->updateAchievedValues();
        });

        // Update achieved values when updating an existing target
        static::updated(function ($userTarget) {
            // Check if any target fields were changed
            $changedFields = $userTarget->getDirty();
            $targetFields = ['target_space', 'target_space_amount', 'target_sponsor_amount', 'user_id', 'event_id'];

            $shouldUpdate = false;
            foreach ($targetFields as $field) {
                if (array_key_exists($field, $changedFields)) {
                    $shouldUpdate = true;
                    break;
                }
            }

            if ($shouldUpdate) {
                $userTarget->updateAchievedValues();
            }
        });
    }

    /**
     * Get the user that owns the target.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that owns the target.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the contracts for this user and event.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'seller', 'user_id')
            ->where('event_id', $this->event_id);
    }

    /**
     * Update achieved values based on current contracts.
     */
    public function updateAchievedValues(): void
    {
        $contracts = $this->contracts()->get();

        // Calculate achieved space from Stand relationship
        $this->achieved_space = $contracts->sum(function ($contract) {
            return $contract->Stand ? $contract->Stand->space : 0;
        });

        // Calculate achieved space amount from space_net field with USD conversion
        $this->achieved_space_amount = $contracts->sum(function ($contract) {
            $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
            return ($contract->space_net ?? 0) * $rateToUSD;
        });

        // Calculate achieved sponsor amount from sponsor_net field with USD conversion
        $this->achieved_sponsor_amount = $contracts->sum(function ($contract) {
            $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
            return ($contract->sponsor_net ?? 0) * $rateToUSD;
        });

        $this->contracts_count = $contracts->count();

        // Calculate completion percentage based on space target
        if ($this->target_space > 0) {
            $this->attributes['completion_percentage'] = round(($this->achieved_space / $this->target_space) * 100, 2);
        } else {
            $this->attributes['completion_percentage'] = 0.0;
        }

        // Update status if target is achieved
        $completionPercentage = (float) $this->attributes['completion_percentage'];
        if ($completionPercentage >= 100 && $this->status === 'active') {
            $this->status = 'completed';
        }

        //$this->save();
        $this->saveQuietly();
    }

    /**
     * Update all targets for a specific event.
     */
    public static function updateTargetsForEvent(int $eventId): void
    {
        self::where('event_id', $eventId)->get()->each(function ($target) {
            $target->updateAchievedValues();
        });
    }

    /**
     * Update all targets for a specific user.
     */
    public static function updateTargetsForUser(int $userId): void
    {
        self::where('user_id', $userId)->get()->each(function ($target) {
            $target->updateAchievedValues();
        });
    }

    /**
     * Get status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'primary',
            'completed' => 'success',
            'expired' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get completion status color based on percentage.
     */
    public function getCompletionColor(): string
    {
        if ($this->completion_percentage >= 100) {
            return 'success';
        } elseif ($this->completion_percentage >= 75) {
            return 'warning';
        } elseif ($this->completion_percentage >= 50) {
            return 'info';
        } else {
            return 'danger';
        }
    }
}
