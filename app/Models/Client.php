<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['pipe_id', 'name', 'position', 'mobile', 'phone', 'email', 'company_id'];
    public function Company() {
        return $this->belongsTo(Company::class);
    }
    public function ContractsContact() {
        return $this->hasMany(Contract::class, 'contact_person');
    }
    public function ContractsCoordinator() {
        return $this->hasMany(Contract::class, 'exhabition_coordinator');
    }
}
