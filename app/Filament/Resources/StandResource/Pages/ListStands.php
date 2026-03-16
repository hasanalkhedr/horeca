<?php

namespace App\Filament\Resources\StandResource\Pages;

use App\Filament\Resources\StandResource;
use App\Filament\Resources\StandResource\Widgets\StandSpaceStatsWidget;
use App\Filament\Resources\StandResource\Widgets\StandStatisticsWidget;
use App\Filament\Resources\StandResource\Widgets\StandStatsWidget;
use App\Imports\StandsImport;
use App\Models\Event;
use App\Models\Settings\Category;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListStands extends ListRecords
{
    use HasFiltersForm;

    protected static string $resource = StandResource::class;

    public function filtersForm(Form $form): Form
    {
        return StandResource::filtersForm($form);
    }

    // Override the table filters to trigger widget refresh
    protected function tableUpdatedFilters(): void
    {
        \Log::info('Table filters updated, dispatching refresh-widget event');
        \Log::info('Current tableFilters:', $this->tableFilters ?? 'not set');
        $this->dispatch('refresh-widget');
    }

    // Override the table search to trigger widget refresh
    protected function tableUpdatedSearch(): void
    {
        \Log::info('Table search updated, dispatching refresh-widget event');
        $this->dispatch('refresh-widget');
    }

    // Add this method to handle filter reset
    protected function tableResetFilters(): void
    {
        \Log::info('Table filters reset, dispatching refresh-widget event with null');
        $this->dispatch('refresh-widget', null);
    }

    // Add method to handle filter clearing
    protected function tableClearedFilters(): void
    {
        \Log::info('Table filters cleared, dispatching refresh-widget event with null');
        $this->dispatch('refresh-widget', null);
    }

    // Add method to handle filter removal
    protected function tableRemovedFilter(): void
    {
        \Log::info('Table filter removed, checking if all filters are cleared');
        $filters = $this->tableFilters ?? [];
        $hasActiveFilters = false;

        foreach ($filters as $key => $filter) {
            if (isset($filter['values']) && !empty($filter['values'])) {
                $hasActiveFilters = true;
                break;
            }
            if (isset($filter['value']) && $filter['value'] !== null) {
                $hasActiveFilters = true;
                break;
            }
        }

        if ($hasActiveFilters) {
            \Log::info('Still has active filters, dispatching with filter data');
            $this->dispatch('refresh-widget', $filters);
        } else {
            \Log::info('All filters cleared, dispatching null');
            $this->dispatch('refresh-widget', null);
        }
    }

    // Add this method to test if it's being called
    public function updatedTableFilters(): void
    {
        \Log::info('updatedTableFilters called with:', $this->tableFilters ?? 'not set');

        // Check if filters are empty (all cleared)
        $filters = $this->tableFilters ?? [];
        $hasActiveFilters = false;

        foreach ($filters as $key => $filter) {
            if (isset($filter['values']) && !empty($filter['values'])) {
                $hasActiveFilters = true;
                break;
            }
            if (isset($filter['value']) && $filter['value'] !== null) {
                $hasActiveFilters = true;
                break;
            }
        }

        if ($hasActiveFilters) {
            \Log::info('Filters are active, dispatching with filter data');
            $this->dispatch('refresh-widget', $filters);
        } else {
            \Log::info('All filters cleared, dispatching null');
            $this->dispatch('refresh-widget', null);
        }
    }

    // Add method to handle table filter reset specifically
    protected function tableFiltersReset(): void
    {
        \Log::info('Table filters reset button clicked, clearing tableFilters');
        \Log::info('Before reset - tableFilters:', $this->tableFilters);
        $this->tableFilters = [];
        \Log::info('After reset - tableFilters:', $this->tableFilters);
        $this->dispatch('refresh-widget', null);
    }

    // Add method to handle table filter form reset (the actual method being called)
    public function resetTableFiltersForm(): void
    {
        \Log::info('resetTableFiltersForm called, clearing tableFilters');
        \Log::info('Before reset - tableFilters:', $this->tableFilters);
        $this->tableFilters = [];
        \Log::info('After reset - tableFilters:', $this->tableFilters);
        $this->dispatch('refresh-widget', null);
    }

    // Add method to handle individual table filter removal
    public function removeTableFilter(string $filterName, string|null $field = null, bool $isRemovingAllFilters = false): void
    {
        \Log::info('removeTableFilter called for filter: ' . $filterName);
        \Log::info('Before removal - tableFilters:', $this->tableFilters);

        // Remove the specific filter
        if (isset($this->tableFilters[$filterName])) {
            unset($this->tableFilters[$filterName]);
        }

        \Log::info('After removal - tableFilters:', $this->tableFilters);

        // Check if any filters remain
        $hasActiveFilters = false;
        foreach ($this->tableFilters as $key => $filter) {
            if (isset($filter['values']) && !empty($filter['values'])) {
                $hasActiveFilters = true;
                break;
            }
            if (isset($filter['value']) && $filter['value'] !== null) {
                $hasActiveFilters = true;
                break;
            }
        }

        if (!$hasActiveFilters) {
            \Log::info('No active filters remain, dispatching null to widget');
            $this->dispatch('refresh-widget', null);
        } else {
            \Log::info('Still has active filters, dispatching filter data');
            $this->dispatch('refresh-widget', $this->tableFilters);
        }
    }

    // Add method to handle when individual filters are removed
    protected function tableFilterRemoved(): void
    {
        \Log::info('Individual filter removed, checking if all filters are cleared');
        \Log::info('Current tableFilters:', $this->tableFilters);

        $filters = $this->tableFilters ?? [];
        $hasActiveFilters = false;

        foreach ($filters as $key => $filter) {
            if (isset($filter['values']) && !empty($filter['values'])) {
                $hasActiveFilters = true;
                break;
            }
            if (isset($filter['value']) && $filter['value'] !== null) {
                $hasActiveFilters = true;
                break;
            }
        }

        if (!$hasActiveFilters) {
            \Log::info('No active filters remain, dispatching null to widget');
            $this->dispatch('refresh-widget', null);
        } else {
            \Log::info('Still has active filters, dispatching filter data');
            $this->dispatch('refresh-widget', $filters);
        }
    }

    // Add magic method to catch any other filter-related method calls
    public function __call($method, $parameters)
    {
        if (str_contains($method, 'filter') || str_contains($method, 'Filter')) {
            \Log::info("Unknown filter method called: " . $method, $parameters);
            \Log::info("Current tableFilters before operation:", $this->tableFilters ?? 'not set');

            // If this looks like a reset operation, clear the filters first
            if (str_contains($method, 'reset') || str_contains($method, 'clear') || str_contains($method, 'remove')) {
                \Log::info('Detected reset/clear operation, clearing tableFilters');
                $this->tableFilters = [];
                $this->dispatch('refresh-widget', null);
                return;
            }

            // Check if this might be a filter reset/clear operation
            $filters = $this->tableFilters ?? [];
            $hasActiveFilters = false;

            foreach ($filters as $key => $filter) {
                if (isset($filter['values']) && !empty($filter['values'])) {
                    $hasActiveFilters = true;
                    break;
                }
                if (isset($filter['value']) && $filter['value'] !== null) {
                    $hasActiveFilters = true;
                    break;
                }
            }

            if (!$hasActiveFilters) {
                \Log::info('No active filters found, dispatching null to widget');
                $this->dispatch('refresh-widget', null);
            }
        }

        return parent::__call($method, $parameters);
    }

    // Add a method to intercept any Livewire call that might reset filters
    public function callMethod($method, $params = [], $captureReturnValue = false)
    {
        \Log::info("Livewire callMethod intercepted: " . $method, $params);

        if (str_contains($method, 'reset') || str_contains($method, 'clear')) {
            \Log::info('Intercepted reset method, clearing tableFilters');
            $this->tableFilters = [];
            $this->dispatch('refresh-widget', null);
        }

        return parent::callMethod($method, $params, $captureReturnValue);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Add custom reset action for filters
            Actions\Action::make('resetFilters')
                ->label('Reset Filters')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->action(function () {
                    \Log::info('Custom reset action triggered, clearing all filters');
                    $this->reset(['tableFilters']);
                    $this->dispatch('refresh-widget', null);
                }),

            // Action 1: Create Multiple Stands Modal
            Actions\Action::make('createMultiple')
                ->label('Create Multiple Stands')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('event_id')
                        ->relationship('Event', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label('Event')
                        ->reactive(),

                    Forms\Components\Select::make('category_id')
                        ->options(function($get) {
                            $event = Event::find($get('event_id'));
                            return $event?->Categories->pluck('name', 'id');
                        })
                        //->relationship('Category', 'name')
                        // ->required()
                        ->searchable()
                        ->preload()
                        ->label('Category'),

                    Forms\Components\TextInput::make('space')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->label('Space (sqm)')
                        ->suffix('sqm'),

                    Forms\Components\Toggle::make('deductable')
                        ->label('Deductible?')
                        ->default(false),

                    Forms\Components\Select::make('status')
                        ->options([
                            'Available' => 'Available',
                            'Reserved' => 'Reserved',
                        ])
                        ->default('Available')
                        ->required(),

                    Forms\Components\TextInput::make('name_prefix')
                        ->required()
                        // ->numeric()
                        // ->minValue(1)
                        ->label('Stand No# prefix'),
                        //->helperText('First stand number'),

                    Forms\Components\TextInput::make('count')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->label('Number of Stands to Create')
                        ->helperText('Maximum 100 stands at once')
                        //->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $numbers = [];
                            for ($i = 0; $i < $state; $i++) {
                                $numbers[] = ['stand_no' => ''];
                            }
                            $set('stand_numbers', $numbers);
                        }),

                    // Repeater::make('stand_numbers')
                    //     ->label('Custom Stand Numbers (Optional)')
                    //     ->schema([
                    //         Forms\Components\TextInput::make('stand_no')
                    //             ->label('Stand Number')
                    //             ->numeric()
                    //             ->required(),
                    //     ])
                    //     ->columns(2)
                    //     ->helperText('If left empty, sequential numbers will be used')
                    //     ->default([])
                    //     ->hidden(fn(callable $get) => !$get('count') || $get('count') == 0),
                ])
                ->action(function (array $data) {
                    try {
                        DB::beginTransaction();

                        $eventId = $data['event_id'];
                        $categoryId = $data['category_id'];
                        $space = $data['space'];
                        $deductable = $data['deductable'];
                        $status = $data['status'];
                        $prefix = $data['name_prefix'];
                        $count = $data['count'];
                        //$customNumbers = collect($data['stand_numbers'] ?? [])->pluck('stand_no')->filter()->toArray();

                        // Get existing stand numbers for this event
                        $existingStands = \App\Models\Stand::where('event_id', $eventId)
                            ->pluck('no')
                            ->toArray();

                        $createdCount = 0;
                        $errors = [];

                        for ($i = 1; $i <= $count; $i++) {
                            // Check if stand number already exists
                            if (in_array($prefix.'-'.$i, $existingStands)) {
                                $errors[] = "Stand #{$prefix}-{$i} already exists";
                                continue;
                            }

                            // Create the stand
                            \App\Models\Stand::create([
                                'no' => $prefix.'-'.$i,
                                'space' => $space,
                                'category_id' => $categoryId,
                                'deductable' => $deductable,
                                'event_id' => $eventId,
                                'status' => $status,
                            ]);

                            $createdCount++;
                            $existingStands[] = $prefix.'-'.$i; // Add to existing to prevent duplicates in same batch
                        }

                        DB::commit();

                        // Show notification
                        if ($createdCount > 0) {
                            $message = "Successfully created {$createdCount} stand(s)";
                            if (!empty($errors)) {
                                $message .= ". " . count($errors) . " stand(s) were skipped (duplicates)";
                            }

                            Notification::make()
                                ->title('Success')
                                ->body($message)
                                ->success()
                                ->send();
                        }

                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Partial Success')
                                ->body(implode("\n", $errors))
                                ->warning()
                                ->send();
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();

                        Notification::make()
                            ->title('Error')
                            ->body('Failed to create stands: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalWidth('xl'),

            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $templatePath = public_path('stands-template.xlsx');

                    if (!file_exists($templatePath)) {
                        Notification::make()
                            ->title('Template Not Found')
                            ->body('The template file was not found at: ' . $templatePath)
                            ->danger()
                            ->send();
                        return;
                    }

                    return response()->download($templatePath, 'stands-template.xlsx');
                })
                ->extraModalFooterActions([
                    Actions\Action::make('downloadTemplateAndClose')
                        ->label('Download and Close')
                        ->action(function () {
                            $templatePath = public_path('stands-template.xlsx');

                            if (!file_exists($templatePath)) {
                                Notification::make()
                                    ->title('Template Not Found')
                                    ->body('The template file was not found.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            return response()->download($templatePath, 'stands-template.xlsx');
                        }),
                ]),

            Actions\Action::make('importStands')
                ->label('Import from Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('event_id')
                        ->relationship('Event', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label('Select Event')
                        ->helperText('Stands will be imported for this event'),

                    Forms\Components\FileUpload::make('file')
                        ->label('Excel File')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv',
                            'application/octet-stream',
                        ])
                        ->maxSize(10240) // 10MB
                        ->helperText('Download the template first to ensure correct format')
                        ->disk('local')
                        ->directory('imports/stands')
                        ->preserveFilenames()
                        ->storeFiles(false) // This is IMPORTANT for temporary storage
                        ->visibility('private'),

                    Forms\Components\Checkbox::make('skip_duplicates')
                        ->label('Skip duplicates?')
                        ->helperText('Skip stands that already exist')
                        ->default(false),

                    Forms\Components\Checkbox::make('update_existing')
                        ->label('Update existing stands?')
                        ->helperText('If checked, existing stands with same number will be updated')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    try {
                        // Get the uploaded file
                        $file = $data['file'];

                        if (!$file) {
                            Notification::make()
                                ->title('No File Selected')
                                ->body('Please select a file to import.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Get the temporary file path
                        $filePath = $file->getRealPath();

                        if (!file_exists($filePath)) {
                            // Alternative: check in storage path
                            $filePath = storage_path('app/' . $file);

                            if (!file_exists($filePath)) {
                                Notification::make()
                                    ->title('File Not Found')
                                    ->body('Unable to locate the uploaded file. Please try again.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        // Import using your existing StandsImport class
                        $import = new StandsImport($data['event_id']);

                        Excel::import($import, $filePath);

                        // Get statistics
                        $created = $import->getCreatedCount();
                        $updated = $import->getUpdatedCount();
                        $errors = $import->getErrors();

                        $message = "Import completed successfully!\n";
                        $message .= "• Created: {$created} new stand(s)\n";
                        $message .= "• Updated: {$updated} existing stand(s)";

                        if (!empty($errors)) {
                            $message .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));
                            if (count($errors) > 10) {
                                $message .= "\n... and " . (count($errors) - 10) . " more errors";
                            }

                            Notification::make()
                                ->title('Import Completed with Errors')
                                ->body($message)
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import Successful')
                                ->body($message)
                                ->success()
                                ->send();
                        }

                    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                        $failures = $e->failures();

                        $errorMessages = [];
                        foreach ($failures as $failure) {
                            $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                        }

                        Notification::make()
                            ->title('Validation Errors')
                            ->body(implode("\n", array_slice($errorMessages, 0, 10)))
                            ->danger()
                            ->send();

                    } catch (\Exception $e) {
                        \Log::error('Stand Import Error: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString()
                        ]);

                        Notification::make()
                            ->title('Import Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalWidth('xl'),

        ];
    }


    // Optional: If you want widgets in header instead of footer
    protected function getHeaderWidgets(): array
    {
        return [
            StandStatisticsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 7;
    }

    // protected function getTableQuery(): Builder
    // {
    //     return parent::getTableQuery()
    //         ->where('is_merged', false)
    //         ->orWhere('parent_stand_id', null);
    // }
}
