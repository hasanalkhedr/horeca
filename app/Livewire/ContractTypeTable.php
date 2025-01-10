<?php
namespace App\Livewire;

use App\Models\ContractType;
use App\Models\Event;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class ContractTypeTable extends DataTableComponent
{
    protected $model = ContractType::class;
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
            Column::make('Template Form', 'path')
                ->sortable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.contract_type-actions')
                        ->with('contract_type', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        if ($this->event->id) {
            return ContractType::query()->where('event_id', $this->event->id)->select([
                'contract_types.id',
                'contract_types.name',
                'contract_types.description',
                'event_id',
            ]);
        } else {
            return ContractType::query()->select([
                'contract_types.id',
                'contract_types.name',
                'contract_types.description',
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
                SelectFilter::make('Event Contracts', 'event_contracts')
                    ->options(
                        Event::all()
                            ->keyBy('id')
                            ->map(fn($event) => $event->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('contract_types.event_id', $value);
                    })
            ];
        }

    }
}
