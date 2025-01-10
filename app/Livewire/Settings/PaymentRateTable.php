<?php
namespace App\Livewire\Settings;

use App\Models\Settings\PaymentRate;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class PaymentRateTable extends DataTableComponent
{
    protected $model = PaymentRate::class;
    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make('Title', 'title')
                ->sortable()
                ->searchable(),
            Column::make('Rate (%)', 'rate')
                ->sortable(),
            Column::make('Order', 'order')
                ->sortable(),
            Column::make('Date to Pay', 'date_to_pay')
                ->sortable(),
            Column::make('Event', 'event.name')
                ->sortable()
                ->searchable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.paymentRate-actions')
                        ->with('paymentRate', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return PaymentRate::query()->select(['payment_rates.id', 'title', 'rate', 'order', 'date_to_pay']);
    }

}
