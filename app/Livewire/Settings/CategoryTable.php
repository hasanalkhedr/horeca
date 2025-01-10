<?php
namespace App\Livewire\Settings;

use App\Models\Settings\Category;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class CategoryTable extends DataTableComponent
{
    protected $model = Category::class;
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
            Column::make('Actions')
                ->label(function ($row) {
                    return view('livewire.partials.category-actions')
                        ->with('category', $row);
                }),
        ];
    }

    public function builder(): \Illuminate\Database\Eloquent\Builder
    {
        return Category::query()->select(['id', 'name']);
    }

}
