<?php

namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Model;

class AdsPackage extends Model
{
    protected $fillable = ['id', 'title', 'description', ];

    public function Currencies()
    {
        return $this->belongsToMany(Currency::class)
            ->withPivot('total_price');
    }
    public function Events()
    {
        return $this->belongsToMany(Event::class);
    }
    public function Contracts()
    {
        return $this->hasMany(Contract::class);
    }
    public function AdsOptions()
    {
        return $this->belongsToMany(AdsOption::class);
    }
}
