<?php
namespace App\Livewire;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Event;
use App\Models\Report;
use App\Models\Stand;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class ContractTable extends DataTableComponent
{
    protected $model = Contract::class;
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
            Column::make('Contract No', 'contract_no')
                ->sortable()
                ->searchable(),
            Column::make('Company', 'company.name')
                ->sortable(),
            Column::make('Stand', 'stand.no')
                ->sortable(),
            Column::make('Amount', 'net_total')
                ->secondaryHeader(function ($rows) {
                    return " Sum of Amount: " . $rows->sum('net_total');
                }),
            Column::make('Form | Actions')
                ->label(function ($row) {
                    return view('livewire.partials.contract-form-link')->with('contract', $row);
                })
            // Column::make('Actions')
            //     ->label(function ($row) {
            //         return view('livewire.partials.stand-actions')
            //             ->with('stand', $row);
            //     }),
        ];
    }

    public function builder(): Builder
    {
        if ($this->event->id) {
            return Contract::query()->where('contracts.event_id', $this->event->id)->select([
                'contracts.id',
                'contract_no',
                'company_id',
                'stand_id',
                'price_id',
                'contracts.event_id',
                'space_amount',
                'sponsor_amount',
                'advertisment_amount',
                'contracts.status',
                'contracts.path',
                'price_amount',
                'report_id',
                'contact_person',
                'exhabition_coordinator',
                'special_design_text',
                'special_design_price',
                'special_design_amount',
                'if_water',
                'if_electricity',
                'electricity_text',
                'water_electricity_amount',
                'new_product',
                'sponsor_package_id',
                'specify_text',
                'notes1',
                'notes2',
                'sub_total_1',
                'd_i_a',
                'sub_total_2',
                'vat_amount',
                'net_total'
            ]);

        } else {
            return Contract::query()->select([
                'contracts.id',
                'contract_no',
                'company_id',
                'stand_id',
                'price_id',
                'contracts.event_id',
                'space_amount',
                'sponsor_amount',
                'advertisment_amount',
                'contracts.status',
                'contracts.path',
                'price_amount',
                'report_id',
                'contact_person',
                'exhabition_coordinator',
                'special_design_text',
                'special_design_price',
                'special_design_amount',
                'if_water',
                'if_electricity',
                'electricity_text',
                'water_electricity_amount',
                'new_product',
                'sponsor_package_id',
                'specify_text',
                'notes1',
                'notes2',
                'sub_total_1',
                'd_i_a',
                'sub_total_2',
                'vat_amount',
                'net_total'
            ]);
        }
    }
    public function filters(): array
    {
        $values = [];
        if ($this->event->id) {
            return [
                SelectFilter::make('Contract Template', 'contract_template')
                    ->options(
                        $this->event->Reports
                            ->keyBy('id')
                            ->map(fn($ct) => $ct->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('contracts.report_id', $value);
                    })
            ];
        } else {

            return [
                SelectFilter::make('Event Contracts', 'event_contracts')
                    ->options(
                        Event::all()
                            ->keyBy('id')
                            ->map(fn($event) => $event->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('contracts.event_id', $value);
                    }),
                SelectFilter::make('Contract Template', 'contract_template')
                    ->options(
                        Report::all()
                            ->keyBy('id')
                            ->map(fn($ct) => $ct->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('contracts.report_id', $value);
                    })
            ];

        }
    }
}
