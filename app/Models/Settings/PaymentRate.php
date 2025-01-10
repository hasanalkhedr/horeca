<?php

// app/Models/PaymentRate.php

namespace App\Models\Settings;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRate extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'title', 'rate', 'order', 'date_to_pay', 'event_id'];

    public function Event()
    {
        return $this->belongsTo(related: Event::class);
    }
}
