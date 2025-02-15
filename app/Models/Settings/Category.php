<?php

namespace App\Models\Settings;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Stand;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];
    public function Events() {
        return $this->belongsToMany(Event::class);
    }
    public function Stands() {
        return $this->hasMany(Stand::class);
    }
    public function Prices(){
        return $this->hasMany(Price::class);
    }
    public function Contracts() {
        return $this->hasMany(Contract::class);
    }
}
