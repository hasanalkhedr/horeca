<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTargetResource\Pages;
use App\Filament\Resources\UserTargetResource\RelationManagers;
use App\Models\UserTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserTargetResource extends Resource
{
    protected static ?string $model = UserTarget::class;

    protected static ?string $navigationIcon = 'heroicon-s-user';

    protected static ?string $navigationLabel = 'User-Event Targets';

    protected static ?string $modelLabel = 'User Target';

    protected static ?string $pluralModelLabel = 'User Targets';

    protected static ?string $navigationGroup = 'Event Management';
    protected static ?int $navigationSort = 2;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Target Assignment')
                    ->description('Assign performance targets to users for specific events')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User (Seller)'),

                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Event'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'expired' => 'Expired',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Target Values')
                    ->description('Set the target metrics for this user')
                    ->schema([
                        Forms\Components\TextInput::make('target_space')
                            ->label('Target Space (sqm)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('sqm')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('target_space_amount')
                            ->label('Target Space Amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('target_sponsor_amount')
                            ->label('Target Sponsor Amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Add any additional notes about this target...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('target_space')
                    ->label('Target Space')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' sqm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('achieved_space')
                    ->label('Achieved Space')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' sqm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Completion')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($record) => $record->getCompletionColor())
                    ->icon(fn ($record) => match(true) {
                        $record->completion_percentage >= 100 => 'heroicon-o-check-circle',
                        $record->completion_percentage >= 75 => 'heroicon-o-arrow-trending-up',
                        $record->completion_percentage >= 50 => 'heroicon-o-arrow-right',
                        default => 'heroicon-o-arrow-trending-down',
                    }),

                Tables\Columns\TextColumn::make('contracts_count')
                    ->label('Contracts')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_space_amount')
                    ->label('Target Amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('achieved_space_amount')
                    ->label('Achieved Amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'active',
                        'success' => 'completed',
                        'danger' => 'expired',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('updateAchieved')
                    ->label('Update Achieved')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (UserTarget $record) {
                        $record->updateAchievedValues();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserTargets::route('/'),
            'create' => Pages\CreateUserTarget::route('/create'),
            'edit' => Pages\EditUserTarget::route('/{record}/edit'),
        ];
    }
}
