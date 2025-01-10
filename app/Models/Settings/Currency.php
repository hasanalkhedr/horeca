<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['id', 'CODE', 'name', 'rate_to_usd', 'country'];

    public function Events(){
        return $this->belongsToMany(Currency::class);
    }
}
