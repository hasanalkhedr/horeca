<?php

namespace App\Models;

use App\Models\Settings\Category;
use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    protected $fillable = ['no', 'space', 'category_id', 'deductable', /* 'stand_type_id', */'event_id', 'status'];

    protected static function booted()
    {
        static::created(function (Stand $stand) {
            $event = $stand->Event;
            $event->total_space = $event->total_space + $stand->space;
            $event->save();
        });
        static::updated(function (Stand $stand) {
            if (array_key_exists('space', $stand->getChanges())) {
                $event = $stand->Event;
                $event->total_space = $event->total_space - $stand->getOriginal('space');
                $event->total_space = $event->total_space + $stand->space;
                $event->save();
            }
        });
        static::deleted(function (Stand $stand) {
            $event = $stand->Event;
            $event->total_space = $event->total_space - $stand->space;
            $event->save();
        });
    }
    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    /*public function StandType()
    {
        return $this->belongsTo(StandType::class);
    }*/
    public function Event()
    {
        return $this->belongsTo(Event::class);
    }
    public function Contract(){
        return $this->hasOne(Contract::class);
    }
    public function ScopeAvailable($query) {
        return $query->where('status','Available');
    }
}
