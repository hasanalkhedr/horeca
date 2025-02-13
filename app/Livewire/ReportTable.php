<?php
namespace App\Livewire;

use App\Models\ContractType;
use App\Models\Event;
use App\Models\Report;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class ReportTable extends DataTableComponent
{
    protected $model = Report::class;
    public $event;
    public function mount($event = null)
    {
        $this->$event = $event;
    }
    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Event', 'event.name')
                ->sortable()
                ->searchable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.report-actions')
                        ->with('report', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        if ($this->event->id) {
            return Report::query()->where('event_id', $this->event->id)->select([
                'reports.id',
                'reports.name',
                'event_id',
            ]);
        } else {
            return Report::query()->select([
                'reports.id',
                'reports.name',
                'event_id',
            ]);
        }

    }
    public function filters(): array
    {
        if ($this->event->id) {
            return [];
        } else {
            return [
                SelectFilter::make('Event Contracts Templates', 'event_contracts')
                    ->options(
                        Event::all()
                            ->keyBy('id')
                            ->map(fn($event) => $event->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('reports.event_id', $value);
                    })
            ];
        }

    }
}
