<?php

namespace App\Models;

use App\Models\Settings\Category;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $wasRecentlyUpdated = false;

     protected $with = ['Prices.Currencies'];

    protected static function booted()
    {
        static::creating(function (Event $event) {
            $event->free_space = 0;
            $event->remaining_free_space = 0;
            $event->remaining_space_to_sell = 0;
        });

        static::created(function (Event $event) {
            $event->free_space = $event->total_space - $event->space_to_sell;
            $event->remaining_free_space = $event->free_space;
            $event->remaining_space_to_sell = $event->space_to_sell;
        });

        static::updated(function (Event $event) {
            if (!$event->wasRecentlyUpdated) {
                if (array_key_exists('total_space', $event->getChanges()) || array_key_exists('space_to_sell', $event->getChanges())) {
                    $event->free_space = $event->total_space - $event->space_to_sell;
                    $event->remaining_free_space = $event->free_space;
                    $event->remaining_space_to_sell = $event->space_to_sell;
                    $event->wasRecentlyUpdated = true;
                }
            }
        });
    }

    protected $fillable = [
        'CODE',
        'name',
        'description',
        'start_date',
        'end_date',
        'apply_start_date',
        'apply_deadline_date',
        'total_space',
        'space_to_sell',
        'free_space',
        'remaining_space_to_sell',
        'remaining_free_space',
        'vat_rate',
        'country',
        'city',
        'address'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'apply_start_date' => 'date',
        'apply_deadline_date' => 'date',
    ];

    public function Categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function Currencies()
    {
        return $this->belongsToMany(Currency::class)
            ->withPivot('min_price');
    }

    public function Stands()
    {
        return $this->hasMany(Stand::class);
    }

    public function availableStands()
    {
        return $this->stands()->available()->deductible();
    }

    public function soldStands()
    {
        return $this->stands()->sold();
    }

    public function Prices()
    {
        return $this->hasMany(Price::class)->with('Currencies');
    }

    public function Contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function Reports()
    {
        return $this->hasMany(Report::class);
    }

    public function SponsorPackages()
    {
        return $this->belongsToMany(SponsorPackage::class);
    }

    public function AdsPackages()
    {
        return $this->belongsToMany(AdsPackage::class);
    }

    public function EffAdsPackages()
    {
        return $this->belongsToMany(EffAdsPackage::class);
    }
}
