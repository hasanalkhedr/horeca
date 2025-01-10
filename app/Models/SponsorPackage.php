<?php

namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Model;

class SponsorPackage extends Model
{
    protected $fillable = ['title', 'currency_id', 'total_price'];
    public function SponsorOptions() {
        return $this->belongsToMany(SponsorOption::class, 'sponsor_option_sponsor_package','package_id','option_id');
    }
    public function Currency() {
        return $this->belongsTo(Currency::class);
    }
}
