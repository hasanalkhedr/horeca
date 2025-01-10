<?php
namespace App\Livewire;

use App\Models\SponsorPackage;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SponsorPackageTable extends DataTableComponent
{
    protected $model = SponsorPackage::class;
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
            Column::make('Currency', 'currency.CODE')
                ->sortable(),
            Column::make('Total Price', 'total_price')
                ->sortable(),
            //Column::make('Options', 'SponsorOptions'),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.sponsorPackage-actions')
                        ->with('sponsorPackage', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return SponsorPackage::query()->select(['sponsor_packages.id', 'title', 'currency_id','total_price']);
    }

}
