<?php

namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Model;

class EffAdsOption extends Model
{
    protected $fillable = ['title', 'description', ];

    public function Currencies()
    {
        return $this->belongsToMany(Currency::class)
            ->withPivot('price');
    }
    public function EffAdsPackages()
    {
        return $this->belongsToMany(EffAdsPackage::class)
            ->withPivot('id');
    }
}
