<?php

namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Model;

class AdsOption extends Model
{
    protected $fillable = ['title', 'description', ];

    public function Currencies()
    {
        return $this->belongsToMany(Currency::class)
            ->withPivot('price');
    }
    public function AdsPackages()
    {
        return $this->belongsToMany(AdsPackage::class)
            ->withPivot('id');
    }
}
