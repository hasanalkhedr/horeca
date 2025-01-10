<?php
namespace App\Livewire;

use App\Models\Client;
use App\Models\Company;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
class ClientTable extends DataTableComponent
{
    protected $model = Client::class;
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
            Column::make('Position in Company', 'position')
                ->sortable(),
            Column::make('Contact', 'mobile')
                ->format(function ($value, $row) {
                    return $row->mobile . ', ' . $row->phone . ', ' . $row->email;
                }),
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.client-actions')
                        ->with('client', $row);
                }),
        ];
    }

    public function builder(): Builder
    {
        if ($this->company->id) {
            return Client::query()->where('company_id', $this->company->id)->select([
                'clients.id',
                'clients.name',
                'position',
                'clients.mobile',
                'clients.phone',
                'clients.email',
                'company_id',
            ]);
        } else {
            return Client::query()->select([
                'clients.id',
                'clients.name',
                'position',
                'clients.mobile',
                'clients.phone',
                'clients.email',
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
                SelectFilter::make('Company Persons', 'company_persons')
                    ->options(
                        Company::all()
                            ->keyBy('id')
                            ->map(fn($company) => $company->name)->toArray(),
                    )->filter(function (Builder $builder, string $value) {
                        $builder->where('clients.company_id', $value);
                    })
            ];
        }

    }
}
