<?php

namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Model;

class SponsorPackage extends Model
{
    protected $fillable = ['id','title'];
    public function SponsorOptions() {
        return $this->belongsToMany(SponsorOption::class, 'sponsor_option_sponsor_package','package_id','option_id');
    }
    public function Currencies()
    {
        return $this->belongsToMany(Currency::class)
            ->withPivot('total_price');
    }
    public function Contracts() {
        return $this->hasMany(Contract::class);
    }
    public function Events() {
        return $this->belongsToMany(Event::class);
    }
}
