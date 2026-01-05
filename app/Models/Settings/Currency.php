<?php

namespace App\Models\Settings;

use App\Models\AdsOption;
use App\Models\AdsPackage;
use App\Models\EffAdsOption;
use App\Models\EffAdsPackage;
use App\Models\Report;
use App\Models\SponsorPackage;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['id', 'CODE', 'name', 'rate_to_usd', 'country'];

    public function Events(){
    return $this->belongsToMany(\App\Models\Event::class, 'currency_event') // Specify table name
        ->withPivot('min_price')
        ->withTimestamps();
}
    public function Reports() {
        return $this->hasMany(Report::class);
    }
    public function Prices(){
        return $this->belongsToMany(Price::class)
            ->withPivot('amount');
    }
    public function AdsPackages()
    {
        return $this->belongsToMany(AdsPackage::class)
            ->withPivot('total_price');
    }
    public function SponsorPackages()
    {
        return $this->belongsToMany(SponsorPackage::class)
            ->withPivot('total_price');
    }
    public function AdsOptions()
    {
        return $this->belongsToMany(AdsOption::class)
            ->withPivot('price');
    }
    public function EffAdsPackages()
    {
        return $this->belongsToMany(EffAdsPackage::class)
            ->withPivot('total_price');
    }
    public function EffAdsOptions()
    {
        return $this->belongsToMany(EffAdsOption::class)
            ->withPivot('price');
    }
}
