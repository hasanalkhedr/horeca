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
    public function mount($event = null)
    {
        $this->event = $event;
    }
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setSecondaryHeaderTdAttributes(function (Column $column, $rows) {
                return ['class' => 'text-red-500 bg-green-100'];
            });
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
                    return " Sum of Space: " . $rows->sum('space');
                }),
            // Column::make('Event', 'event.name')
            //     ->sortable(),
           // Column::make('Stand Type', 'standType.name')
             //   ->sortable(),
            // Column::make('Category', 'category.name')
            //     ->sortable()->collapseAlways(),
            Column::make('Deductable', 'deductable')
                ->sortable(),
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
            return Stand::query()->where('event_id', $this->event->id)->select(['stands.id', 'no',  'stands.space', 'category_id', 'event_id', 'deductable', /*'stand_type_id'*/]);
        } else {
            //dd('hhhh');
            return Stand::query()->select(['stands.id', 'no',  'stands.space', 'category_id', 'event_id', 'deductable', /* 'stand_type_id'*/]);
        }
    }
    public function filters(): array
    {
        if ($this->event->id) {
            return [];
        } else {
            return [
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
                    ->filter(function(Builder $builder, string $value) {
                            $builder->where('deductable',$value);
                    })
            ];
        }
    }
}
