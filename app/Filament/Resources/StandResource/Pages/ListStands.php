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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListStands extends ListRecords
{
    protected static string $resource = StandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

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
                        ->reactive()
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
