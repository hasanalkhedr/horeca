<?php

namespace App\Models\Settings;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = ['id', 'name', 'IBAN', 'swift_code', 'account_name','event_id'];
    public function Event() {
        return $this->belongsTo(Event::class);
    }

}
