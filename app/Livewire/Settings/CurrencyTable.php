<?php
namespace App\Livewire\Settings;

use App\Models\Settings\Currency;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CurrencyTable extends DataTableComponent
{
    protected $model = Currency::class;
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
            Column::make('CODE', 'CODE')
                ->sortable()
                ->searchable(),
            Column::make('Rate (to USD)', 'rate_to_usd')
                ->sortable(),
                Column::make('Country', 'country')
                ->sortable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.currency-actions')
                        ->with('currency', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return Currency::query()->select(['id', 'name', 'CODE', 'country', 'rate_to_usd']);
    }

}
