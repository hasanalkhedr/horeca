<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    protected $fillable = ['event_id', 'name', 'description', 'path'];
    public function Event() {
        return $this->belongsTo(Event::class);
    }
    public function ContractFields() {
        return $this->hasMany(ContractField::class);
    }
    public function Contracts() {
        return $this->hasMany(Contract::class);
    }
}
