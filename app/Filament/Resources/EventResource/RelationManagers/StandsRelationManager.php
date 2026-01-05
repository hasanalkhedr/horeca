<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\StandResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StandsRelationManager extends RelationManager
{
    protected static string $relationship = 'Stands';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('no')
                    ->required()
                    ->numeric()
                    ->label('Stand Number'),

                Forms\Components\TextInput::make('space')
                    ->required()
                    ->numeric()
                    ->label('Space (sqm)')
                    ->suffix('sqm'),

                Forms\Components\Select::make('category_id')
                    ->relationship('Category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Category'),

                Forms\Components\Toggle::make('deductable')
                    ->label('Deductible?'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Sold' => 'Sold',
                        'Reserved' => 'Reserved',
                        'Blocked' => 'Blocked',
                    ])
                    ->default('Available')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('no')
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('Stand #')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('space')
                    ->label('Space')
                    ->suffix(' sqm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('Category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('deductable')
                    ->label('Deductible')
                    ->boolean(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Available' => 'success',
                        'Sold' => 'danger',
                        'Reserved' => 'warning',
                        'Blocked' => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Available' => 'Available',
                        'Sold' => 'Sold',
                        'Reserved' => 'Reserved',
                        'Blocked' => 'Blocked',
                    ]),

                Tables\Filters\TernaryFilter::make('deductable')
                    ->label('Deductible'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => StandResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
