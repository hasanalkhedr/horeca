<?php
namespace App\Livewire\Settings;

use App\Models\Settings\Price;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PriceTable extends DataTableComponent
{
    protected $model = Price::class;
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
            //Column::make('Pricing Strategy', 'pricingStrategy.name')
              //  ->sortable(),
            //Column::make('Category', 'category.name')
              //  ->sortable(),
            /*Column::make('Stand Type', 'standType.name')
                ->sortable(),*/
            Column::make('Currency', 'currency.CODE')
                ->sortable(),
            Column::make('Amount', 'amount')
                ->sortable(),
            Column::make('Description', 'description')
                ->collapseAlways(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.price-actions')
                        ->with('price', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return Price::query()->select(['prices.id', 'prices.name', 'currency_id', 'amount', 'description' /*'stand_type_id'*/]);
    }

}
