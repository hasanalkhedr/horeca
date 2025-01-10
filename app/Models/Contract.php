<?php

namespace App\Models;

use App\Models\Settings\Price;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $wasRecentlyUpdated = false;
    // protected static function booted()
    // {
    //     static::created(function (Contract $contract) {
    //         $contract->contract_no =
    //         $contract->save();
    //     });
    //     static::updated(function (Contract $contract) {

    //         if(!$contract->wasRecentlyUpdated){
    //             $contract->wasRecentlyUpdated = true;
    //             $contract->save();
    //         }

    //     });
    // }
    protected $fillable = ['contract_no', 'company_id', 'stand_id', 'price_id', 'event_id'];
    public function Company(){
        return $this->belongsTo(Company::class);
    }
    public function Event(){
        return $this->belongsTo(Event::class);
    }
    public function Price(){
        return $this->belongsTo(Price::class);
    }
    public function Stand(){
        return $this->belongsTo(Stand::class);
    }
    public function ContractType() {
        return $this->belongsTo(ContractType::class);
    }
}
