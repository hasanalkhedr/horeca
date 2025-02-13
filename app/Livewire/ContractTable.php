<?php
namespace App\Livewire;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Event;
use App\Models\Stand;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class ContractTable extends DataTableComponent
{
    protected $model = Contract::class;
    public $event;
    public $contractType;
    public function mount($event = null, $contractType = null)
    {
        $this->event = $event;
        $this->contractType =  $contractType;
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
            Column::make('Amount', 'total_amount')
                ->secondaryHeader(function ($rows) {
                    return " Sum of Amount: " . $rows->sum('total_amount');
                }),
            Column::make('Form | Actions')
                ->label(function($row) {
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
            return Contract::query()->where('contracts.event_id', $this->event->id)->select(['contracts.id', 'contract_no',  'company_id', 'stand_id', 'price_id', 'contracts.event_id', 'contract_type_id','space_amount','sponsor_amount','advertisment_amount','total_amount','contracts.status', 'contracts.path']);

        } else {
            return Contract::query()->select(['contracts.id', 'contract_no',  'company_id', 'stand_id', 'price_id', 'contracts.event_id', 'contract_type_id','space_amount','sponsor_amount','advertisment_amount','total_amount','contracts.status', 'contracts.path']);
        }
    }
    public function filters(): array
    {
        $values = [];
        if ($this->event->id) {
            return [SelectFilter::make('Contract Type', 'contract_type')
            ->options($this->event->ContractTypes
                    ->keyBy('id')
                    ->map(fn($ct)=>$ct->name)->toArray(),
            )->filter(function(Builder $builder, string $value) {
                    $builder->where('contracts.contract_type_id',$value);
            })];
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
                SelectFilter::make('Contract Type', 'contract_type')
                    ->options(ContractType::all()
                            ->keyBy('id')
                            ->map(fn($ct)=>$ct->name)->toArray(),
                    )->filter(function(Builder $builder, string $value) {
                            $builder->where('contracts.contract_type_id',$value);
                    })
            ];

        }
    }
}
