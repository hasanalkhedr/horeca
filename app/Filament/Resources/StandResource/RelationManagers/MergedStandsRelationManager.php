<?php

namespace App\Filament\Resources\StandResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;

class MergedStandsRelationManager extends RelationManager
{
    protected static string $relationship = 'mergedStands';

    protected static ?string $title = 'Merged Stands';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('no')
                    ->label('Original Stand Number')
                    ->disabled(),

                Forms\Components\TextInput::make('space')
                    ->label('Space (sqm)')
                    ->suffix('sqm')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options([
                        'Merged' => 'Merged',
                        'Available' => 'Available',
                    ])
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('no')
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('Original Stand No.'),

                Tables\Columns\TextColumn::make('space')
                    ->label('Space')
                    ->suffix(' sqm'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('unmerge')
                    ->label('Separate')
                    ->icon('heroicon-o-arrows-pointing-in')
                    ->action(function (Model $record) {
                        $parent = $record->parentStand;

                        // Restore the stand
                        $record->update([
                            'parent_stand_id' => null,
                            'status' => 'Available',
                        ]);

                        // Update parent stand space
                        if ($parent) {
                            $parent->update([
                                'space' => $parent->space - $record->space,
                            ]);

                            // If no more merged stands, convert parent back to regular stand
                            if ($parent->mergedStands()->count() == 0) {
                                $parent->update([
                                    'is_merged' => false,
                                    'no' => $parent->original_no ?? $parent->no,
                                    'original_no' => null,
                                ]);
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Stand Separated')
                            ->body("Stand #{$record->no} has been separated from merged stand")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record) {
                        $parent = $record->parentStand;

                        // Update parent stand space
                        if ($parent) {
                            $parent->update([
                                'space' => $parent->space - $record->space,
                            ]);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->is_merged && $ownerRecord->mergedStands()->count() > 0;
    }
}
