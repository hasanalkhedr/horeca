<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'position', 'nobile', 'phone', 'email', 'company_id'];
    public function Company() {
        return $this->belongsTo(Company::class);
    }
}
