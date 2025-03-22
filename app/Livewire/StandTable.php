<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Stand;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class StandTable extends DataTableComponent
{
    protected $model = Stand::class;
    public $event;
    public $sum = 0;
    public function mount($event = null)
    {
        $this->event = $event;
        $this->sum = $this->builder()->sum('space');
    }
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setSecondaryHeaderTdAttributes(function (Column $column, $rows) {
                return ['class' => 'text-red-500 bg-green-100'];})
            ->setConfigurableAreas(['toolbar-right-start'=>
            ['livewire.partials.sumOfSpace', ['sumOfSpace'=>$this->builder()->sum('space')]],]);
    }
    public function columns(): array
    {
        return [
            Column::make('Stand No', 'no')
                ->sortable()
                ->searchable(),
            // Column::make('Stand CODE', 'CODE')
            //     ->sortable()
            //     ->collapseAlways()
            //     ->searchable(),
            Column::make('Space (sq. m)', 'space')
                ->sortable()
                ->secondaryHeader(function ($rows) {
                    return " Partial Total space: " . $rows->sum('space');
                }),
            // Column::make('Event', 'event.name')
            //     ->sortable(),
            // Column::make('Stand Type', 'standType.name')
            //   ->sortable(),
            Column::make('Category', 'category.name')
                ->sortable(),
            Column::make('Deductible', 'deductable')
                ->format(fn($value, $row) => $row->deductable ? 'Deductible' : 'Not Deductible')
                ->sortable(),
            Column::make('Status', 'status')
                ->label(function($row){
                    return view('livewire.partials.stand-status')
                        ->with('stand',  $row);
                })->sortable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.stand-actions')
                        ->with('stand', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        //dd($this->event);
        if ($this->event->id) {
            return Stand::query()
                ->where('event_id', $this->event->id)
                ->select([
                    'stands.id',
                    'no',
                    'stands.space',
                    'category_id',
                    'event_id',
                    'deductable', /*'stand_type_id'*/
                    'status',
                ]);
        } else {
            return Stand::query()
                ->select([
                    'stands.id',
                    'no',
                    'stands.space',
                    'category_id',
                    'event_id',
                    'deductable', /* 'stand_type_id'*/
                    'status',
                ]);
        }
    }
    public function filters(): array
    {
        if ($this->event->id) {
            return [
                SelectFilter::make('Status', 'status')
                    ->options(['Available' => 'Available', 'Sold' => 'Sold', 'Reserved' => 'Reserved'])
                    ->filter(function (Builder $builder, string $value) {
                        $builder->where('status', $value);
                    }),
                    SelectFilter::make('Deductable', 'deductable')
                    ->options(['Not Deductable', 'Deductable'])
                    ->filter(function (Builder $builder, string $value) {
                        $builder->where('deductable', $value);
                    }),
            ];
        } else {
            return [
                SelectFilter::make('Status', 'status')
                    ->options(['Available' => 'Available', 'Sold' => 'Sold', 'Reserved' => 'Reserved'])
                    ->filter(function (Builder $builder, string $value) {
                        $builder->where('status', $value);
                    }),
                SelectFilter::make('Event Stands', 'event_stands')
                    ->options(
                        Event::all()
                            ->keyBy('id')
                            ->map(fn($event) => $event->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('stands.event_id', $value);
                    }),
                SelectFilter::make('Deductable', 'deductable')
                    ->options(['Not Deductable', 'Deductable'])
                    ->filter(function (Builder $builder, string $value) {
                        $builder->where('deductable', $value);
                    }),
            ];
        }
    }
}
