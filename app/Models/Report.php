<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'components',
        'event_id',
        'bankAccount'
    ];

    protected $casts = [
        'components' => 'array',
        ];

    public function Event() {
        return $this->belongsTo(Event::class);
    }
    public function Contracts() {
        return $this->hasMany(Contract::class);
    }
}
