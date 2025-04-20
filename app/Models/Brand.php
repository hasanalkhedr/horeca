<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['pipe_id', 'name', 'logo', 'company_id'];
    public function Company() {
        return $this->belongsTo(Company::class);
    }
}
