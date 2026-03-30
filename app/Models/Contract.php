<?php

namespace App\Models;

use App\Models\Settings\Category;
use App\Models\Settings\Price;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Contract extends Model
{
    // ==================== STATUS CONSTANTS ====================
    const STATUS_INTERESTED = 'INT';          // Interested (Default)
    const STATUS_SIGNED_NOT_PAID = 'S&NP';    // Signed and Not Paid
    const STATUS_SIGNED_PAID = 'S&P';         // Signed and Paid
    const STATUS_CLOSED = 'CLOSED';           // Closed
    const STATUS_FREE_FROM_HS = 'Free From HS'; // Free From HS
    const STATUS_PAID_TROC = 'Paid Troc';      // Paid Troc
    const STATUS_ON_HOLD = 'On Hold';         // On Hold
    const STATUS_ON_SITE_FREE = 'On Site Free'; // On Site Free
    const STATUS_ANIMATION = 'Animation';      // Animation
    const STATUS_SPONSOR = 'Sponsor';         // Sponsor

    /**
     * Get all available statuses with display names
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_INTERESTED => 'Interested (INT)',
            self::STATUS_SIGNED_NOT_PAID => 'Signed & Not Paid (S&NP)',
            self::STATUS_SIGNED_PAID => 'Signed & Paid (S&P)',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_FREE_FROM_HS => 'Free From HS',
            self::STATUS_PAID_TROC => 'Paid Troc',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_ON_SITE_FREE => 'On Site Free',
            self::STATUS_ANIMATION => 'Animation',
            self::STATUS_SPONSOR => 'Sponsor',
        ];
    }

    /**
     * Get simplified status display names (without codes)
     */
    public static function getSimpleStatuses(): array
    {
        return [
            self::STATUS_INTERESTED => 'Interested',
            self::STATUS_SIGNED_NOT_PAID => 'Signed (Not Paid)',
            self::STATUS_SIGNED_PAID => 'Signed (Paid)',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_FREE_FROM_HS => 'Free From HS',
            self::STATUS_PAID_TROC => 'Paid Troc',
            self::STATUS_ON_HOLD => 'On Hold',
            self::STATUS_ON_SITE_FREE => 'On Site Free',
            self::STATUS_ANIMATION => 'Animation',
            self::STATUS_SPONSOR => 'Sponsor',
        ];
    }

    /**
     * Get status color for badges
     */
    public static function getStatusColor($status): string
    {
        return match ($status) {
            self::STATUS_INTERESTED => 'info',
            self::STATUS_SIGNED_NOT_PAID => 'warning',
            self::STATUS_SIGNED_PAID => 'success',
            self::STATUS_CLOSED => 'danger',
            self::STATUS_FREE_FROM_HS => 'primary',
            self::STATUS_PAID_TROC => 'success',
            self::STATUS_ON_HOLD => 'warning',
            self::STATUS_ON_SITE_FREE => 'info',
            self::STATUS_ANIMATION => 'purple',
            self::STATUS_SPONSOR => 'success',
            default => 'gray',
        };
    }

    /**
     * Get status icon
     */
    public static function getStatusIcon($status): string
    {
        return match ($status) {
            self::STATUS_INTERESTED => 'heroicon-o-eye',
            self::STATUS_SIGNED_NOT_PAID => 'heroicon-o-document-check',
            self::STATUS_SIGNED_PAID => 'heroicon-o-check-circle',
            self::STATUS_CLOSED => 'heroicon-o-x-circle',
            self::STATUS_FREE_FROM_HS => 'heroicon-o-gift',
            self::STATUS_PAID_TROC => 'heroicon-o-banknotes',
            self::STATUS_ON_HOLD => 'heroicon-o-pause-circle',
            self::STATUS_ON_SITE_FREE => 'heroicon-o-home',
            self::STATUS_ANIMATION => 'heroicon-o-sparkles',
            self::STATUS_SPONSOR => 'heroicon-o-star',
            default => 'heroicon-o-document',
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        $statuses = self::getSimpleStatuses();
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status full display name (with code)
     */
    public function getStatusFullDisplayAttribute(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get status color for this contract
     */
    public function getStatusColorAttribute(): string
    {
        return self::getStatusColor($this->status);
    }

    /**
     * Get status icon for this contract
     */
    public function getStatusIconAttribute(): string
    {
        return self::getStatusIcon($this->status);
    }

    /**
     * Check if contract is in specific status
     */
    public function isStatus($status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if contract shows interest
     */
    public function isInterested(): bool
    {
        return $this->status === self::STATUS_INTERESTED;
    }

    /**
     * Check if contract is signed but not paid
     */
    public function isSignedNotPaid(): bool
    {
        return $this->status === self::STATUS_SIGNED_NOT_PAID;
    }

    /**
     * Check if contract is signed and paid
     */
    public function isSignedPaid(): bool
    {
        return $this->status === self::STATUS_SIGNED_PAID;
    }

    /**
     * Check if contract is closed
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Check if contract is free from HS
     */
    public function isFreeFromHS(): bool
    {
        return $this->status === self::STATUS_FREE_FROM_HS;
    }

    /**
     * Check if contract is paid troc
     */
    public function isPaidTroc(): bool
    {
        return $this->status === self::STATUS_PAID_TROC;
    }

    /**
     * Check if contract is on hold
     */
    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    /**
     * Check if contract is on site free
     */
    public function isOnSiteFree(): bool
    {
        return $this->status === self::STATUS_ON_SITE_FREE;
    }

    /**
     * Check if contract is animation
     */
    public function isAnimation(): bool
    {
        return $this->status === self::STATUS_ANIMATION;
    }

    /**
     * Check if contract is sponsor
     */
    public function isSponsor(): bool
    {
        return $this->status === self::STATUS_SPONSOR;
    }

    /**
     * Check if contract is signed (either paid or not paid)
     */
    public function isSigned(): bool
    {
        return in_array($this->status, [
            self::STATUS_SIGNED_NOT_PAID,
            self::STATUS_SIGNED_PAID,
        ]);
    }

    /**
     * Check if contract is finalized (signed and paid)
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, [
            self::STATUS_SIGNED_PAID,
            self::STATUS_PAID_TROC,
            self::STATUS_SPONSOR,
        ]);
    }

    /**
     * Check if contract is active (not closed)
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_INTERESTED,
            self::STATUS_SIGNED_NOT_PAID,
            self::STATUS_SIGNED_PAID,
            self::STATUS_FREE_FROM_HS,
            self::STATUS_PAID_TROC,
            self::STATUS_ON_HOLD,
            self::STATUS_ON_SITE_FREE,
            self::STATUS_ANIMATION,
            self::STATUS_SPONSOR,
        ]);
    }

    /**
     * Check if contract can be edited
     */
    public function canBeEdited(): bool
    {
        // Can edit interested and on hold contracts
        return in_array($this->status, [
            self::STATUS_INTERESTED,
            self::STATUS_ON_HOLD,
        ]);
    }

    /**
     * Check if contract can be signed
     */
    public function canBeSigned(): bool
    {
        // Can sign interested and on hold contracts
        return in_array($this->status, [
            self::STATUS_INTERESTED,
            self::STATUS_ON_HOLD,
        ]);
    }

    /**
     * Check if contract can be marked as paid
     */
    public function canBeMarkedAsPaid(): bool
    {
        // Only signed and not paid contracts can be marked as paid
        return $this->status === self::STATUS_SIGNED_NOT_PAID;
    }

    /**
     * Check if contract can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Only interested contracts can be deleted (no longer draft)
        return $this->status === self::STATUS_INTERESTED;
    }

    /**
     * Check if contract can be closed
     */
    public function canBeClosed(): bool
    {
        // Can close finalized contracts
        return in_array($this->status, [
            self::STATUS_SIGNED_PAID,
            self::STATUS_PAID_TROC,
            self::STATUS_SPONSOR,
        ]);
    }

    /**
     * Check if contract can be put on hold
     */
    public function canBePutOnHold(): bool
    {
        // Can put interested contracts on hold
        return $this->status === self::STATUS_INTERESTED;
    }

    /**
     * Scope for interested contracts
     */
    public function scopeInterested($query)
    {
        return $query->where('status', self::STATUS_INTERESTED);
    }

    /**
     * Scope for signed not paid contracts
     */
    public function scopeSignedNotPaid($query)
    {
        return $query->where('status', self::STATUS_SIGNED_NOT_PAID);
    }

    /**
     * Scope for signed paid contracts
     */
    public function scopeSignedPaid($query)
    {
        return $query->where('status', self::STATUS_SIGNED_PAID);
    }

    /**
     * Scope for closed contracts
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Scope for free from HS contracts
     */
    public function scopeFreeFromHS($query)
    {
        return $query->where('status', self::STATUS_FREE_FROM_HS);
    }

    /**
     * Scope for paid troc contracts
     */
    public function scopePaidTroc($query)
    {
        return $query->where('status', self::STATUS_PAID_TROC);
    }

    /**
     * Scope for on hold contracts
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    /**
     * Scope for on site free contracts
     */
    public function scopeOnSiteFree($query)
    {
        return $query->where('status', self::STATUS_ON_SITE_FREE);
    }

    /**
     * Scope for animation contracts
     */
    public function scopeAnimation($query)
    {
        return $query->where('status', self::STATUS_ANIMATION);
    }

    /**
     * Scope for sponsor contracts
     */
    public function scopeSponsor($query)
    {
        return $query->where('status', self::STATUS_SPONSOR);
    }

    /**
     * Scope for active contracts (not closed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_INTERESTED,
            self::STATUS_SIGNED_NOT_PAID,
            self::STATUS_SIGNED_PAID,
            self::STATUS_FREE_FROM_HS,
            self::STATUS_PAID_TROC,
            self::STATUS_ON_HOLD,
            self::STATUS_ON_SITE_FREE,
            self::STATUS_ANIMATION,
            self::STATUS_SPONSOR,
        ]);
    }

    /**
     * Scope for signed contracts (both paid and not paid)
     */
    public function scopeSigned($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SIGNED_NOT_PAID,
            self::STATUS_SIGNED_PAID,
        ]);
    }

    /**
     * Scope for finalized contracts (signed and paid)
     */
    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_SIGNED_PAID);
    }

    /**
     * Scope for pending payment contracts
     */
    public function scopePendingPayment($query)
    {
        return $query->where('status', self::STATUS_SIGNED_NOT_PAID);
    }

    /**
     * Scope for specific status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ==================== STATUS TRANSITION METHODS ====================

    /**
     * Mark contract as interested
     */
    public function markAsInterested(): bool
    {
        if ($this->isDraft()) {
            $this->status = self::STATUS_INTERESTED;
            return $this->save();
        }
        return false;
    }

    /**
     * Sign the contract (mark as signed but not paid)
     */
    public function sign(): bool
    {
        if ($this->canBeSigned()) {
            $this->status = self::STATUS_SIGNED_NOT_PAID;
            return $this->save();
        }
        return false;
    }

    /**
     * Mark contract as paid
     */
    public function markAsPaid(): bool
    {
        if ($this->canBeMarkedAsPaid()) {
            $this->status = self::STATUS_SIGNED_PAID;
            return $this->save();
        }
        return false;
    }

    /**
     * Put contract on hold
     */
    public function putOnHold(): bool
    {
        if ($this->canBePutOnHold()) {
            $this->status = self::STATUS_ON_HOLD;
            return $this->save();
        }
        return false;
    }

    /**
     * Close contract
     */
    public function close(): bool
    {
        if ($this->canBeClosed()) {
            $this->status = self::STATUS_CLOSED;
            return $this->save();
        }
        return false;
    }

    /**
     * Set contract as free from HS
     */
    public function setFreeFromHS(): bool
    {
        // Can be set from interested
        if ($this->status === self::STATUS_INTERESTED) {
            $this->status = self::STATUS_FREE_FROM_HS;
            return $this->save();
        }
        return false;
    }

    /**
     * Set contract as paid troc
     */
    public function setPaidTroc(): bool
    {
        // Can be set from interested
        if ($this->status === self::STATUS_INTERESTED) {
            $this->status = self::STATUS_PAID_TROC;
            return $this->save();
        }
        return false;
    }

    /**
     * Set contract as on site free
     */
    public function setOnSiteFree(): bool
    {
        // Can be set from interested
        if ($this->status === self::STATUS_INTERESTED) {
            $this->status = self::STATUS_ON_SITE_FREE;
            return $this->save();
        }
        return false;
    }

    /**
     * Set contract as animation
     */
    public function setAnimation(): bool
    {
        // Can be set from interested
        if ($this->status === self::STATUS_INTERESTED) {
            $this->status = self::STATUS_ANIMATION;
            return $this->save();
        }
        return false;
    }

    /**
     * Set contract as sponsor
     */
    public function setSponsor(): bool
    {
        // Can be set from interested
        if ($this->status === self::STATUS_INTERESTED) {
            $this->status = self::STATUS_SPONSOR;
            return $this->save();
        }
        return false;
    }

    /**
     * Get next possible status transitions
     */
    public function getNextPossibleStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_INTERESTED => [
                self::STATUS_SIGNED_NOT_PAID => 'Sign Contract',
                self::STATUS_FREE_FROM_HS => 'Set as Free From HS',
                self::STATUS_PAID_TROC => 'Set as Paid Troc',
                self::STATUS_ON_SITE_FREE => 'Set as On Site Free',
                self::STATUS_ANIMATION => 'Set as Animation',
                self::STATUS_SPONSOR => 'Set as Sponsor',
                self::STATUS_ON_HOLD => 'Put on Hold',
            ],
            self::STATUS_ON_HOLD => [
                self::STATUS_INTERESTED => 'Mark as Interested',
                self::STATUS_SIGNED_NOT_PAID => 'Sign Contract',
                self::STATUS_FREE_FROM_HS => 'Set as Free From HS',
                self::STATUS_PAID_TROC => 'Set as Paid Troc',
                self::STATUS_ON_SITE_FREE => 'Set as On Site Free',
                self::STATUS_ANIMATION => 'Set as Animation',
                self::STATUS_SPONSOR => 'Set as Sponsor',
            ],
            self::STATUS_SIGNED_NOT_PAID => [
                self::STATUS_SIGNED_PAID => 'Mark as Paid',
            ],
            self::STATUS_SIGNED_PAID => [
                self::STATUS_CLOSED => 'Close Contract',
            ],
            self::STATUS_PAID_TROC => [
                self::STATUS_CLOSED => 'Close Contract',
            ],
            self::STATUS_SPONSOR => [
                self::STATUS_CLOSED => 'Close Contract',
            ],
            self::STATUS_FREE_FROM_HS => [], // No further transitions
            self::STATUS_ON_SITE_FREE => [], // No further transitions
            self::STATUS_ANIMATION => [], // No further transitions
            self::STATUS_CLOSED => [], // No further transitions
            default => [],
        };
    }
    protected $wasRecentlyUpdated = false;
    protected static function booted()
    {
        static::creating(function (Contract $contract) {
            $contract->contract_no = Contract::generateContractNumber($contract);
            if (empty($contract->status)) {
                $contract->status = self::STATUS_INTERESTED; // Default to INT
            }
        });
        static::created(function (Contract $contract) {
            // $contract->sponsor_amount = $contract->SponsorPackage ? $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first() ? $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first()->pivot->total_price : 0 : 0;
            //$contract->special_design_amount = $contract->special_design_price * $contract->Stand->space;
            // $contract->sub_total_1 = $contract->space_amount + $contract->sponsor_amount + $contract->advertisment_amount +             $contract->special_design_amount + $contract->water_electricity_amount;
            // $contract->d_i_a = 0;// $contract->sub_total_1;
            // $contract->sub_total_2 = $contract->sub_total_1 - $contract->d_i_a;
            // $contract->vat_amount = $contract->sub_total_2 * $contract->Event->vat_rate / 100;
            // $contract->net_total = $contract->sub_total_2 + $contract->vat_amount;
            // $contract->save();
        });
        static::updated(function (Contract $contract) {
            if (!$contract->wasRecentlyUpdated) {
                // $contract->sponsor_amount = $contract->SponsorPackage ?
                //     $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first() ?
                //     $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first()->pivot->total_price : 0 : 0;
                //                 $contract->special_design_amount = $contract->special_design_price * $contract->Stand->space;
                // $contract->sub_total_1 = $contract->space_amount + $contract->sponsor_amount +
                //     $contract->advertisment_amount + $contract->special_design_amount +
                //     $contract->water_electricity_amount;
                // $contract->d_i_a = 0;//$contract->sub_total_1;
                // $contract->sub_total_2 = $contract->sub_total_1 - $contract->d_i_a;
                // $contract->vat_amount = $contract->sub_total_2 * $contract->Event->vat_rate / 100;
                // $contract->net_total = $contract->sub_total_2 + $contract->vat_amount;
                // $contract->wasRecentlyUpdated = true;
                //               $contract->save();
            }
        });
        static::deleting(function ($document) {
            // Check if file exists and delete it
            if ($document->path && Storage::exists($document->path)) {
                Storage::delete($document->path);
            }
        });
    }
    public static function generateContractNumber(Contract $contract)
    {
        $latestContract = Contract::where([['event_id', '=', $contract->event_id], /*['contract_type_id', '=', $contract->contract_type_id]*/])->orderBy('contract_no', 'desc')->first();
        if ($latestContract) {
            $lastContractNumber = intval(substr($latestContract->contract_no, -3));
            $newContractNumber = $lastContractNumber + 1;
        } else {
            $newContractNumber = 1;
        }
        return 'CR-' . $contract->Event->CODE . '-' . str_pad($newContractNumber, 3, '0', STR_PAD_LEFT);
    }
    protected $fillable = [
        'contract_no',
        'company_id',
        'contract_date',
        'stand_id',
        'price_id',
        'event_id',
        'space_amount',
        'sponsor_amount',
        'advertisment_amount',
        //'total_amount',
        'status',
        'path',
        'price_amount',
        'report_id',
        'contact_person',
        'exhabition_coordinator',
        'special_design_text',
        'special_design_price',
        'special_design_amount',
        'if_water',
        'if_electricity',
        'electricity_text',
        'water_electricity_amount',
        'new_product',
        'sponsor_package_id',
        'specify_text',
        'notes1',
        'notes2',
        'sub_total_1',
        'd_i_a',
        'sub_total_2',
        'vat_amount',
        'net_total',
        'category_id',
        'seller',
        'ads_check',
        'space_discount',
        'space_net',
        'sponsor_discount',
        'sponsor_net',
        'ads_discount',
        'ads_net',
        'eff_ads_check',
        'eff_ads_amount',
        'eff_ads_discount',
        'eff_ads_net',
        'ads_package_id',
        'eff_ads_package_id',
        'enable_tax_per_sqm',
        'tax_per_sqm_amount',
        'tax_per_sqm_total',
        'free_space',
    ];
    protected $casts = [
        'contrat_date' => 'date',
        'ads_check' => 'array',
        'eff_ads_check' => 'array',
        'enable_tax_per_sqm' => 'boolean',
        'tax_per_sqm_amount' => 'decimal:2',
        'tax_per_sqm_total' => 'decimal:2',
        'free_space' => 'decimal:0'
    ];

    protected $appends = [
        'status_display',
        'status_full_display',
        'status_color',
        'status_icon'
    ];
    public function Company()
    {
        return $this->belongsTo(Company::class);
    }
    public function Event()
    {
        return $this->belongsTo(Event::class);
    }
    public function Price()
    {
        return $this->belongsTo(Price::class);
    }
    public function Stand()
    {
        return $this->belongsTo(Stand::class);
    }
    public function Report()
    {
        return $this->belongsTo(Report::class);
    }

    public function ContactPerson()
    {
        return $this->belongsTo(Client::class, 'contact_person');
    }
    public function ExhabitionCoordinator()
    {
        return $this->belongsTo(Client::class, 'exhabition_coordinator');
    }
    public function SponsorPackage()
    {
        return $this->belongsTo(SponsorPackage::class);
    }
    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'seller', 'id', 'users');
    }
    public function AdsPackage()
    {
        return $this->belongsTo(AdsPackage::class);
    }
    public function EffAdsPackage()
    {
        return $this->belongsTo(EffAdsPackage::class);
    }
}
