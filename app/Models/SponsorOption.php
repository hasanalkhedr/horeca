<?php

namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Model;

class SponsorOption extends Model
{
    protected $fillable = ['title', ];
    public function SponsorPackages() {
        return $this->belongsToMany(SponsorPackage::class,'sponsor_option_sponsor_package','option_id', 'package_id');
    }
    public function Currency() {
        return $this->belongsTo(Currency::class);
    }
}
