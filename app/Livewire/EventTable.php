<?php
namespace App\Livewire;

use App\Models\Event;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class EventTable extends DataTableComponent
{
    protected $model = Event::class;
    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make('Event CODE', 'CODE')
                ->sortable()
                ->collapseAlways()
                ->searchable(),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Apply from date', 'apply_start_date')
                ->sortable()
                ->collapseAlways(),
            Column::make('Apply Deadline', 'apply_deadline_date')
                ->sortable()
                ->collapseAlways(),
            Column::make('Available Space to sell', 'remaining_space_to_sell')
                ->sortable(),
            Column::make('Available Free Space', 'remaining_free_space')
                ->sortable()
                ->collapseAlways(),
            Column::make('VAT Rate', 'vat_rate')
                ->sortable()
                ->collapseAlways(),
            Column::make('Description', 'description')
                ->sortable()
                ->searchable()
                ->collapseAlways(),
            Column::make('Start Date', 'start_date')
                ->sortable()
                ->collapseAlways(),
            Column::make('End Date', 'end_date')
                ->sortable()
                ->collapseAlways(),
            Column::make('Total Space', 'total_space')
                ->sortable()
                ->collapseAlways(),
            Column::make('Total Space to sell', 'space_to_sell')
                ->sortable()
                ->collapseAlways(),
            Column::make('Total Free Space', 'free_space')
                ->sortable()
                ->collapseAlways(),

            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.event-actions')
                        ->with('event', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        return Event::query()->select([
            'events.id',
            'events.name',
            'events.CODE',
            'description',
            'start_date',
            'end_date',
            'apply_start_date',
            'apply_deadline_date',
            'total_space',
            'space_to_sell',
            'free_space',
            'remaining_space_to_sell',
            'remaining_free_space',
            'vat_rate',
        ]);
    }

}
