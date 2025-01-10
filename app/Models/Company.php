<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable=[
    'name',
    'CODE',
    'commerical_registry_number',
    'vat_number',
    'country',
    'city',
    'street',
    'po_box',
    'mobile',
    'phone',
    'additional_number',
    'fax',
    'email',
    'website',
    'facebook_link',
    'instagram_link',
    'x_link',
    'stand_name',
    'logo',
    ];

    public function Brands(){
        return $this->hasMany(Brand::class);
    }
    public function Clients() {
        return $this->hasmany(Client::class);
    }
    public function Contracts() {
        return $this->hasMany(Contract::class);
    }
}
