<?php
namespace App\Livewire;

use App\Models\Brand;
use App\Models\Company;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class BrandTable extends DataTableComponent
{
    protected $model = Brand::class;
    public $company;
    public function mount($company = null)
    {
        $this->$company = $company;
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
            Column::make('Company', 'company.name')
                ->sortable()
                ->searchable(),
            Column::make('Logo', 'logo')
                ->sortable(),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.brand-actions')
                        ->with('brand', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        if ($this->company->id) {
            return Brand::query()->where('company_id', $this->company->id)->select([
                'brands.id',
                'brands.name',
                'brands.logo',
                'company_id',
            ]);
        } else {
            return Brand::query()->select([
                'brands.id',
                'brands.name',
                'brands.logo',
                'company_id',
            ]);
        }

    }
    public function filters(): array
    {
        if ($this->company->id) {
            return [];
        } else {
            return [
                SelectFilter::make('Company Brands', 'company_brands')
                    ->options(
                        Company::all()
                            ->keyBy('id')
                            ->map(fn($company) => $company->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('brands.company_id', $value);
                    })
            ];
        }

    }
}
