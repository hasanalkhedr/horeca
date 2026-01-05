<?php

namespace App\Models;

use App\Models\Settings\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'no',
        'space',
        'category_id',
        'deductable',
        'event_id',
        'status',
        'parent_stand_id',
        'is_merged',
        'original_no',
        'merge_group_id',
    ];

    protected $casts = [
        'is_merged' => 'boolean',
    ];

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

    public function Event()
    {
        return $this->belongsTo(Event::class);
    }

    public function Contract()
    {
        return $this->hasOne(Contract::class);
    }

    // Relationship for merged stands (children)
    public function mergedStands()
    {
        return $this->hasMany(Stand::class, 'parent_stand_id');
    }

    // Relationship for parent stand
    public function parentStand()
    {
        return $this->belongsTo(Stand::class, 'parent_stand_id');
    }

    // Scope to get all stands in the same merge group
    public function mergeGroup()
    {
        if ($this->parent_stand_id) {
            return $this->parentStand->mergeGroup();
        }

        return $this->hasMany(Stand::class, 'parent_stand_id')->orWhere('id', $this->id);
    }

    // Get all stands in the merge group including the parent
    public function getAllMergeGroupStands()
    {
        if ($this->parentStand) {
            return $this->parentStand->getAllMergeGroupStands();
        }

        return Stand::where('parent_stand_id', $this->id)
            ->orWhere('id', $this->id)
            ->get();
    }

    // Check if stand is part of a merge group
    public function isPartOfMergeGroup()
    {
        return $this->parent_stand_id || $this->mergedStands()->exists();
    }

    // Get the main/parent stand of the merge group
    public function getMainMergeStand()
    {
        if ($this->parent_stand_id) {
            return $this->parentStand;
        }
        return $this;
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    public function scopeDeductible($query)
    {
        return $query->where('deductable', true);
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'Sold');
    }

    public function scopeNotMerged($query)
    {
        return $query->where('is_merged', false)->whereNull('parent_stand_id');
    }

    public function scopeIsMerged($query)
    {
        return $query->where('is_merged', true)->orWhereNotNull('parent_stand_id');
    }

    // Get all stands that are available for merging (not part of any merge group)
    public function scopeAvailableForMerging($query)
    {
        return $query->where('status', 'Available')
            ->whereNull('parent_stand_id')
            ->where('is_merged', false);
    }
}
