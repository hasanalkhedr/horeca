<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractValue extends Model
{
    protected $fillable = ['contract_id', 'contract_field_id', 'field_value'];
    public function Contract(){
        return $this->belongsTo(Contract::class);
    }
    public function ContractField(){
        return $this->belongsTo(ContractField::class);
    }
}
