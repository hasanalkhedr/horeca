<?php
namespace App\Livewire;

use App\Models\SponsorOption;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class SponsorOptionTable extends DataTableComponent
{
    protected $model = SponsorOption::class;
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

            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.sponsorOption-actions')
                        ->with('sponsorOption', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return SponsorOption::query()->select(['sponsor_options.id', 'title', ]);
    }

}
