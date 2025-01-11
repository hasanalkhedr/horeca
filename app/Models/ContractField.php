<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractField extends Model
{
    protected $fillable = ['contract_type_id', 'field_name', 'field_type'];
    public function ContractType() {
        return $this->belongsTo(ContractType::class);
    }
    public function Event() {
        return $this->hasOneThrough(Event::class, ContractType::class, 'id', 'id', 'contract_type_id', 'event_id');
    }
    public function ContractValues() {
        return $this->hasMany(ContractValue::class);
    }
}
