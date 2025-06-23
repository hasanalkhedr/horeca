<?php

namespace App\Models\Settings;

use App\Models\Contract;
use App\Models\Event;
use App\Models\StandType;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [ 'name', 'category_id', 'currency_id',
                            'amount', 'event_id', 'description'];

    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    public function Currencies()
    {
        return $this->belongsToMany(Currency::class)
            ->withPivot('amount');
    }
    public function Event()
    {
        return $this->belongsTo(Event::class);
    }
    public function Contracts() {
        return $this->hasMany(Contract::class);
    }
}
