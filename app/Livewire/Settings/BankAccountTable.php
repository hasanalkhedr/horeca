<?php
namespace App\Livewire\Settings;

use App\Models\Settings\BankAccount;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class BankAccountTable extends DataTableComponent
{
    protected $model = BankAccount::class;
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
            Column::make('IBAN', 'IBAN')
                ->sortable(),
            Column::make('Swift Code', 'swift_code')
                ->sortable(),
                Column::make('Event', 'event.name')
                ->sortable(),
                Column::make('Account Name', 'account_name')
                ->sortable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.bankAccount-actions')
                        ->with('bankAccount', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return BankAccount::query()->select(['bank_accounts.id', 'bank_accounts.name', 'IBAN', 'swift_code', 'account_name']);
    }

}
