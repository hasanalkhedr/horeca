<?php
namespace App\Livewire;

use App\Models\Company;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class CompanyTable extends DataTableComponent
{
    protected $model = Company::class;
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
            Column::make('commerical_registry_number', 'commerical_registry_number')
                ->sortable()
                ->collapseAlways(),
            Column::make('vat_number', 'vat_number')
                ->sortable()
                ->collapseAlways(),
            Column::make('Address', 'country')
            ->format(function ($value, $row) {
                return $row->country . ', ' . $row->city . ', ' . $row->street;
            })
                ->sortable()
                ->collapseAlways(),
            Column::make('P.O.Box', 'po_box')
                ->sortable()
                ->collapseAlways(),
            Column::make('Mobile', 'mobile')
                ->sortable()
                ->collapseAlways(),
            Column::make('Phone', 'phone')
                ->sortable()
                ->collapseAlways(),
            Column::make('Additional Number', 'additional_number')
                ->sortable()
                ->collapseAlways(),
            Column::make('Fax', 'fax')
                ->sortable()
                ->collapseAlways(),
            Column::make('Email', 'email')
                ->sortable()
                ->collapseAlways(),
            Column::make('Website', 'website')
                ->sortable()
                ->collapseAlways(),
            Column::make('Facebook', 'facebook_link')
                ->sortable()
                ->collapseAlways(),
            Column::make('instagram', 'instagram_link')
                ->sortable()
                ->collapseAlways(),
            Column::make('X (twitter)', 'x_link')
                ->sortable()
                ->collapseAlways(),
            Column::make('Stand Name', 'stand_name')
                ->sortable()
                ->collapseAlways(),
            Column::make('Logo', 'logo')
                ->sortable()
                ->collapseAlways(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.company-actions')
                        ->with('company', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        return Company::query()->select([
            'id',
            'name',
            'CODE',
            'commerical_registry_number',
            'vat_number',
            'country',
            'city',
            'street',
            'po_box',
            'mobile',
            'phone',
            'additional_number',
            'fax',
            'email',
            'website',
            'facebook_link',
            'instagram_link',
            'x_link',
            'stand_name',
            'logo',
        ]);
    }
    public function filters(): array
    {
        return [];
    }
}
