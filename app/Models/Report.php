<?php
namespace App\Models;

use App\Models\Settings\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'components',
        'event_id',
        'payment_method',
        'bank_account',
        'bank_name_address',
        'swift_code',
        'iban',
        'with_logo',
        'logo_path',
        'currency_id'
    ];

    protected $casts = [
        'components' => 'array',
    ];

    public function Event()
    {
        return $this->belongsTo(Event::class);
    }
    public function Contracts()
    {
        return $this->hasMany(Contract::class);
    }
    public function Currency() {
        return $this->belongsTo(Currency::class);
    }
}
