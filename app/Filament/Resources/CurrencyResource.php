<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Settings\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Currency';
    protected static ?string $pluralModelLabel = 'Currencies';
    protected static ?string $navigationLabel = 'Currencies';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Currency Information')
                    ->description('Basic currency details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('CODE')
                                    ->label('Currency Code')
                                    ->required()
                                    ->maxLength(3)
                                    ->placeholder('e.g., USD, EUR')
                                    ->unique(ignoreRecord: true)
                                    ->rules(['alpha', 'size:3'])
                                    //->uppercase()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('name')
                                    ->label('Currency Name')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('e.g., US Dollar, Euro')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('country')
                                    ->label('Country/Region')
                                    ->maxLength(100)
                                    ->placeholder('e.g., United States, European Union')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('rate_to_usd')
                                    ->label('Exchange Rate to USD')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->step(0.000001)
                                    ->minValue(0.000001)
                                    ->suffix('USD')
                                    ->columnSpan(2)
                                    ->helperText('1 unit of this currency equals how many USD?'),
                            ]),
                    ]),

                Forms\Components\Section::make('Usage Information')
                    ->description('Statistics about currency usage')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('ads_packages_count')
                                    ->label('Used in Ads Packages')
                                    ->content(fn($record): string => $record ? $record->AdsPackages()->count() : '0')
                                    ->hintIcon('heroicon-o-gift'),

                                Forms\Components\Placeholder::make('ads_options_count')
                                    ->label('Used in Ads Options')
                                    ->content(fn($record): string => $record ? $record->AdsOptions()->count() : '0')
                                    ->hintIcon('heroicon-o-list-bullet'),

                                Forms\Components\Placeholder::make('events_count')
                                    ->label('Used in Events')
                                    ->content(fn($record): string => $record ? $record->Events()->count() : '0')
                                    ->hintIcon('heroicon-o-calendar'),
                            ]),
                    ])
                    ->hidden(fn($record) => !$record),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('CODE')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn($state): string => strtoupper($state))
                    ->description(fn($record): string => $record->country ?? '')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Currency Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('rate_to_usd')
                    ->label('Rate to USD')
                    ->numeric(decimalPlaces: 6)
                    ->sortable()
                    //->alignRight()
                    ->color('success')
                    ->suffix(' USD'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\Filter::make('has_ads_packages')
                //     ->label('Used in Ads Packages')
                //     ->toggle()
                //     ->query(fn(Builder $query): Builder => $query->whereHas('AdsPackages')),

                // Tables\Filters\Filter::make('has_ads_options')
                //     ->label('Used in Ads Options')
                //     ->toggle()
                //     ->query(fn(Builder $query): Builder => $query->whereHas('AdsOptions')),

                // Tables\Filters\Filter::make('has_events')
                //     ->label('Used in Events')
                //     ->toggle()
                //     ->query(fn(Builder $query): Builder => $query->whereHas('Events')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label('')
                    ->before(function (Currency $record) {
                        // Check if currency is being used anywhere before deletion
                        $usage = [];

                        if ($record->AdsPackages()->exists()) {
                            $usage[] = 'Ads Packages';
                        }

                        if ($record->AdsOptions()->exists()) {
                            $usage[] = 'Ads Options';
                        }

                        if ($record->Events()->exists()) {
                            $usage[] = 'Events';
                        }

                        if ($record->SponsorPackages()->exists()) {
                            $usage[] = 'Sponsor Packages';
                        }

                        if ($record->Reports()->exists()) {
                            $usage[] = 'Reports';
                        }

                        if (!empty($usage)) {
                            throw new \Exception('Cannot delete currency. It is being used in: ' . implode(', ', $usage));
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->AdsPackages()->exists() ||
                                    $record->AdsOptions()->exists() ||
                                    $record->Events()->exists() ||
                                    $record->SponsorPackages()->exists() ||
                                    $record->Reports()->exists()) {
                                    throw new \Exception('One or more currencies are in use. Remove them from all packages/options/events first.');
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('CODE')
            ->recordUrl(fn($record): string => self::getUrl('view', ['record' => $record]));
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'view' => Pages\ViewCurrency::route('/{record}'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
