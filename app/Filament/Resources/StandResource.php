<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\StandResource\Pages;
use App\Filament\Resources\StandResource\RelationManagers;
use App\Filament\Resources\StandResource\Widgets\StandSpaceStatsWidget;
use App\Filament\Resources\StandResource\Widgets\StandStatisticsWidget;
use App\Filament\Resources\StandResource\Widgets\StandStatsWidget;
use App\Models\Stand;
use App\Models\Event;
use App\Models\Settings\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StandResource extends Resource
{
    protected static ?string $model = Stand::class;

    protected static ?string $navigationIcon = 'heroicon-s-map-pin';
    protected static ?string $navigationGroup = 'Event Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stand Information')
                    ->schema([
                        Forms\Components\TextInput::make('no')
                            ->required()
                            ->label('Stand Number')
                            ->unique(ignorable: fn($record) => $record)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('space')
                            ->required()
                            ->numeric()
                            ->label('Space (sqm)')
                            ->suffix('sqm')
                            ->columnSpan(1),

                        Forms\Components\Select::make('category_id')
                            ->relationship('Category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Category')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Select::make('event_id')
                            ->relationship('Event', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Event')
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $set('no', null))
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('deductable')
                            ->label('Deductible?')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->options([
                                'Available' => 'Available',
                                'Sold' => 'Sold',
                                'Reserved' => 'Reserved',
                                'Merged' => 'Merged', // Add Merged status
                            ])
                            ->default('Available')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Merge Information')
                    ->schema([
                        Forms\Components\Placeholder::make('merge_status')
                            ->label('Merge Status')
                            ->content(function ($record) {
                                if (!$record)
                                    return '-';

                                if ($record->parentStand) {
                                    return "Child of Stand #{$record->parentStand->no}";
                                } elseif ($record->is_merged) {
                                    return "Parent (Merged Stand)";
                                } else {
                                    return "Not Merged";
                                }
                            }),

                        Forms\Components\Placeholder::make('merged_stands_count')
                            ->label('Merged Stands')
                            ->content(function ($record) {
                                if (!$record)
                                    return '-';

                                if ($record->parentStand) {
                                    $main = $record->parentStand;
                                    return $main->mergedStands()->count() . ' stands merged';
                                } elseif ($record->is_merged) {
                                    return $record->mergedStands()->count() . ' stands merged';
                                } else {
                                    return '0';
                                }
                            }),

                        Forms\Components\Placeholder::make('total_merged_space')
                            ->label('Total Merged Space')
                            ->content(function ($record) {
                                if (!$record)
                                    return '-';

                                if ($record->parentStand) {
                                    $main = $record->parentStand;
                                    $totalSpace = $main->space;
                                    foreach ($main->mergedStands as $merged) {
                                        $totalSpace += $merged->space;
                                    }
                                    return $totalSpace . ' sqm';
                                } elseif ($record->is_merged) {
                                    $totalSpace = $record->space;
                                    foreach ($record->mergedStands as $merged) {
                                        $totalSpace += $merged->space;
                                    }
                                    return $totalSpace . ' sqm';
                                } else {
                                    return $record->space . ' sqm';
                                }
                            }),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->where(function ($q) {
                    $q->where('is_merged', false)
                        ->orWhereNull('parent_stand_id');
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('Stand No.')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        // Add indicator for all merged stands
                        if ($record->parentStand || $record->is_merged) {
                            return $state . ' (Merged)';
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('space')
                    ->label('Space')
                    ->suffix(' sqm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('Category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('Event.name')
                    ->label('Event')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('deductable')
                    ->label('Deductible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Available' => 'success',
                        'Sold' => 'danger',
                        'Reserved' => 'warning',
                        'Merged' => 'primary',
                    })
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('parentStand.no')
                    ->label('Parent Stand')
                    ->default('-')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('merge_info')
                    ->label('Merge Group')
                    ->getStateUsing(function ($record) {
                        if ($record->parentStand) {
                            return "Child of #{$record->parentStand->no}";
                        } elseif ($record->mergedStands()->count() > 0) {
                            return "Parent of " . $record->mergedStands()->count() . " stands";
                        }
                        return '-';
                    })
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                Tables\Columns\TextColumn::make('Contract.Company.name')
                    ->label('Client info')
                    ->default('-'),
            ])
            ->deferLoading()
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('Event', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('All Events'),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Sold' => 'Sold',
                        'Reserved' => 'Reserved',
                    ]),

                Tables\Filters\TernaryFilter::make('deductable')
                    ->label('Deductible'),

                // Add merge status filter
                // Tables\Filters\SelectFilter::make('merge_status')
                //     ->label('Merge Status')
                //     ->options([
                //         'parent' => 'Parent (Merged Stands)',
                //         'child' => 'Child (Merged into another)',
                //         'none' => 'Not Merged',
                //     ])
                //     ->query(function (Builder $query, array $data) {
                //         if (!isset($data['value'])) {
                //             return $query;
                //         }

                //         return match ($data['value']) {
                //             'parent' => $query->where('is_merged', true),
                //             'child' => $query->whereNotNull('parent_stand_id'),
                //             'none' => $query->where('is_merged', false)->whereNull('parent_stand_id'),
                //             default => $query,
                //         };
                //     }),
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(5)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Merge Action (only for available, unmerged stands)
                    Tables\Actions\Action::make('merge')
                        ->label('Merge Stands')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('primary')
                        ->size('xs')
                        ->visible(function (Stand $record) {
                            // Only show if stand is available and not part of any merge group
                            return $record->status === 'Available'
                                && !$record->is_merged
                                && !$record->parentStand
                                && $record->mergedStands()->count() == 0;
                        })
                        ->form([
                            Forms\Components\Select::make('stands_to_merge')
                                ->label('Select Stands to Merge With')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(function (Stand $record) {
                                    // Get available stands from the same event, excluding current stand
                                    // and stands already in merge groups
                                    return Stand::query()
                                        ->where('event_id', $record->event_id)
                                        ->where('status', 'Available')
                                        ->where('id', '!=', $record->id)
                                        ->where('is_merged', false)
                                        ->whereNull('parent_stand_id')
                                        ->get()
                                        ->mapWithKeys(fn($stand) => [
                                            $stand->id => "Stand #{$stand->no} ({$stand->space} sqm)"
                                        ]);
                                })
                                ->required()
                                ->helperText('Select one or more stands to merge with this stand. All stands will be marked as merged.'),

                            Forms\Components\TextInput::make('new_stand_no')
                                ->label('New Stand Number for Parent')
                                ->required()
                                ->default(fn(Stand $record) => $record->no . '-M')
                                ->helperText('This will be the main stand number for the merged group'),
                        ])
                        ->action(function (Stand $record, array $data) {
                            $selectedStands = Stand::whereIn('id', $data['stands_to_merge'])->get();

                            // Calculate total space
                            $totalSpace = $record->space;
                            foreach ($selectedStands as $stand) {
                                $totalSpace += $stand->space;
                            }
                            $parentStand = $record;
                            $parentStand->update([
                                'no' => $data['new_stand_no'],
                                'space' => $totalSpace,
                                'is_merged' => true,
                                'original_no' => $record->no,
                            ]);

                            // Link selected stands to parent stand as merged children
                            foreach ($selectedStands as $stand) {
                                $stand->update([
                                    'parent_stand_id' => $parentStand->id,
                                    'is_merged' => true,
                                    //'status' => 'Merged',
                                ]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Stands Merged Successfully')
                                ->body("Successfully merged " . (count($selectedStands) + 1) . " stands into #{$parentStand->no}")
                                ->success()
                                ->send();
                        }),

                    // Unmerge Action (available for any merged stand - parent or child)
                    Tables\Actions\Action::make('unmerge')
                        ->label('Unmerge Stands')
                        ->icon('heroicon-o-arrows-pointing-in')
                        ->color('warning')
                        ->size('xs')
                        ->visible(function (Stand $record) {
                            // Show for any stand that is part of a merge group
                            return ($record->parentStand || $record->is_merged) && $record->status == 'Available';
                        })
                        ->form(function (Stand $record) {
                            // Get all stands in the merge group
                            if ($record->parentStand) {
                                $mainStand = $record->parentStand;
                                $mergeGroupStands = $mainStand->getAllMergeGroupStands();
                            } else {
                                $mainStand = $record;
                                $mergeGroupStands = $record->getAllMergeGroupStands();
                            }

                            $options = $mergeGroupStands->mapWithKeys(fn($stand) => [
                                $stand->id => "Stand #{$stand->no} ({$stand->space} sqm)" .
                                    ($stand->id === $mainStand->id ? ' [Parent]' : '')
                            ]);

                            return [
                                Forms\Components\CheckboxList::make('stands_to_unmerge')
                                    ->label('Select Stands to Unmerge')
                                    ->options($options)
                                    ->required()
                                    ->default([$record->id]) // Pre-select the current stand
                                    ->helperText('Select one or more stands to separate from the merged group.'),

                                Forms\Components\Radio::make('remaining_action')
                                    ->label('Action for Remaining Stands')
                                    ->options([
                                        'keep_merged' => 'Keep remaining stands merged',
                                        'split_all' => 'Split all stands individually',
                                    ])
                                    ->default('keep_merged')
                                    ->required()
                                    ->visible(fn(callable $get) => count($get('stands_to_unmerge') ?? []) < count($options)),
                            ];
                        })
                        ->action(function (Stand $record, array $data) {
                            // Get the main/parent stand
                            $mainStand = $record->parentStand ?? $record;

                            // Get all stands in the merge group
                            $mergeGroupStands = $mainStand->getAllMergeGroupStands();
                            $selectedStandIds = $data['stands_to_unmerge'];
                            $selectedStands = $mergeGroupStands->whereIn('id', $selectedStandIds);
                            $remainingStands = $mergeGroupStands->whereNotIn('id', $selectedStandIds);

                            // Handle selected stands
                            foreach ($selectedStands as $stand) {
                                $stand->update([
                                    'parent_stand_id' => null,
                                    'is_merged' => false,
                                ]);
                            }

                            // Handle remaining stands
                            if ($remainingStands->count() > 0) {
                                if ($data['remaining_action'] === 'keep_merged') {
                                    // Check if main stand was removed
                                    if (!$remainingStands->contains('id', $mainStand->id)) {
                                        // Select a new parent from remaining stands
                                        $newParent = $remainingStands->first();
                                        $newParent->update([
                                            'is_merged' => true,
                                            'parent_stand_id' => null,
                                            'no' => $newParent->no . '-M',
                                            'original_no' => $newParent->no
                                        ]);

                                        // Update other remaining stands to point to new parent
                                        foreach ($remainingStands->where('id', '!=', $newParent->id) as $stand) {
                                            $stand->update([
                                                'parent_stand_id' => $newParent->id,
                                            ]);
                                        }

                                        // Recalculate space for new parent
                                        $newParentSpace = $newParent->space;
                                        foreach ($remainingStands->where('id', '!=', $newParent->id) as $stand) {
                                            $newParentSpace += $stand->space;
                                        }
                                        $newParent->update(['space' => $newParentSpace]);
                                        $mainStand->update([
                                            'no' => $mainStand->original_no,
                                            'space' => $mainStand->space - $newParent->space,
                                            'is_merged' => false,
                                        ]);
                                    } else {
                                        // Main stand still exists, recalculate its space
                                        $mainStandSpace = $mainStand->space - $selectedStands->sum('space');
                                        // foreach ($remainingStands->where('id', '!=', $mainStand->id) as $stand) {
                                        //     $mainStandSpace += $stand->space;
                                        // }
                                        $mainStand->update(['space' => $mainStandSpace]);
                                    }
                                } else {
                                    // Split all remaining stands individually
                                    foreach ($remainingStands as $stand) {
                                        $stand->update([
                                            'parent_stand_id' => null,
                                            'is_merged' => false,
                                            'status' => 'Available',
                                        ]);
                                    }
                                    $mainStand->update([
                                        'no' => $mainStand->original_no,
                                        'space' => $mainStand->space - $mergeGroupStands->whereNotIn('id', [$mainStand->id])->sum('space'),
                                        'is_merged' => false,
                                        'parent_stand_id' => null,
                                    ]);
                                }
                            }

                            // If main stand has no more merged stands and is not selected for unmerge
                            if ($mainStand->exists && !$selectedStands->contains('id', $mainStand->id)) {
                                $mainStand->update([
                                    'is_merged' => $remainingStands->count() > 1,
                                ]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Stands Unmerged Successfully')
                                ->body("Successfully processed " . count($selectedStands) . " stands")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('changeStatus')
                        ->label(fn($record) => $record->status === 'Available' ? 'Reserve Stand' : 'Release Stand')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->size('xs')
                        ->visible(function ($record) {
                            // Only show for stands that are not merged and not sold
                            return $record->status !== 'Sold'
                                && $record->status !== 'Merged'
                                && !$record->parentStand;
                        })
                        ->action(function (Stand $record) {
                            $oldStatus = $record->status;
                            $newStatus = $record->status === 'Available' ? 'Reserved' : 'Available';
                            $record->status = $newStatus;
                            $record->save();

                            \Filament\Notifications\Notification::make()
                                ->title('Status Updated')
                                ->body("Stand #{$record->no} changed from {$oldStatus} to {$newStatus}")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => !$record->parentStand), // Don't allow editing child stands directly
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Stand $record) {
                            if ($record->Contract) {
                                throw new \Exception('Cannot delete stand with an existing contract. Delete the contract first.');
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options([
                                'Available' => 'Available',
                                'Reserved' => 'Reserved',
                            ])
                            ->required()
                            ->helperText('Note: Cannot change to "Sold" or "Merged" status via bulk action'),
                    ])
                    ->action(function ($records, array $data) {
                        foreach ($records as $record) {
                            // Skip sold and merged stands
                            if ($record->status !== 'Sold' && $record->status !== 'Merged') {
                                $record->status = $data['status'];
                                $record->save();
                            }
                        }
                    })
                    ->deselectRecordsAfterCompletion(),


                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->Contract) {
                                    throw new \Exception('One or more stands have contracts. Delete contracts first.');
                                }
                            }
                        }),
                ]),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')->label('Export Stands')
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('no', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MergedStandsRelationManager::class,
        ];
    }
    public static function getWidgets(): array
    {
        return [
            StandStatisticsWidget::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStands::route('/'),
            'create' => Pages\CreateStand::route('/create'),
            //'view' => Pages\ViewStand::route('/{record}'),
            'edit' => Pages\EditStand::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['no', 'Event.name', 'Category.name'];
    }
}
