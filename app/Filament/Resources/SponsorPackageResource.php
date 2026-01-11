<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorPackageResource\Pages;
use App\Models\SponsorPackage;
use App\Models\SponsorOption;
use App\Models\Settings\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use function Livewire\Volt\layout;

class SponsorPackageResource extends Resource
{
    protected static ?string $model = SponsorPackage::class;

    protected static ?string $navigationIcon = 'heroicon-s-gift';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Package Information')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('e.g., Gold Sponsor, Silver Sponsor')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Currencies & Pricing')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Section::make('Package Pricing by Currency')
                                ->description('Select currencies and set prices for this package')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema(function (callable $get, $record) {
                                            $currencies = Currency::all();
                                            $schema = [];

                                            // Get existing currency prices for this record
                                            $existingPrices = [];
                                            if ($record) {
                                                $existingPrices = $record->Currencies->mapWithKeys(function ($currency) {
                                                    return [$currency->id => $currency->pivot->total_price];
                                                })->toArray();
                                            }

                                            foreach ($currencies as $currency) {
                                                $isSelected = isset($existingPrices[$currency->id]);
                                                $priceValue = $isSelected ? $existingPrices[$currency->id] : 0;

                                                $schema[] = Forms\Components\Card::make()
                                                    ->schema([
                                                        Forms\Components\Checkbox::make("currency_{$currency->id}_enabled")
                                                            ->label($currency->name)
                                                            ->helperText($currency->CODE)
                                                            ->default($isSelected)
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $set) use ($currency) {
                                                                if (!$state) {
                                                                    $set("currency_{$currency->id}_price", 0);
                                                                }
                                                            }),

                                                        Forms\Components\TextInput::make("currency_{$currency->id}_price")
                                                            ->label('Price')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->default($priceValue)
                                                            ->required()
                                                            ->prefix($currency->CODE . ' ')
                                                            ->disabled(fn(callable $get): bool => !$get("currency_{$currency->id}_enabled"))
                                                            ->visible(fn(callable $get): bool => $get("currency_{$currency->id}_enabled")),
                                                    ])
                                                    ->columnSpan(1);
                                            }

                                            return $schema;
                                        }),
                                ])
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Options')
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Forms\Components\Section::make('Package Options')
                                ->description('Select existing options or create new ones')
                                ->schema([
                                    // Section 1: Existing Options
                                    Forms\Components\Section::make('Select Existing Options')
                                        ->collapsible()
                                        ->schema([
                                            Forms\Components\Grid::make(3)
                                                ->schema(function (callable $get, $record) {
                                                    $options = SponsorOption::with('Currency')->get();
                                                    $schema = [];

                                                    // Get existing options with pivot data
                                                    $existingOptions = [];
                                                    if ($record) {
                                                        $existingOptions = $record->SponsorOptions->mapWithKeys(function ($option) {
                                                            return [
                                                                $option->id => [
                                                                ]
                                                            ];
                                                        })->toArray();
                                                    }

                                                    foreach ($options as $option) {
                                                        $isSelected = isset($existingOptions[$option->id]);

                                                        $schema[] = Forms\Components\Card::make()
                                                            ->schema([
                                                                Forms\Components\Checkbox::make("option_{$option->id}_enabled")
                                                                    ->label($option->title)
                                                                    ->default($isSelected)
                                                                    ->reactive(),
                                                            ])
                                                            ->columnSpan(1);
                                                    }

                                                    return $schema;
                                                }),
                                        ])
                                        ->columnSpanFull(),

                                    // Section 2: Create New Options
                                    Forms\Components\Section::make('Create New Options')
                                        ->collapsible()
                                        ->schema([
                                            Forms\Components\Repeater::make('new_options')
                                                ->label('')
                                                ->schema([
                                                    Forms\Components\Grid::make(4)
                                                        ->schema([
                                                            Forms\Components\TextInput::make('title')
                                                                ->label('Option Title')
                                                                ->required()
                                                                ->maxLength(255)
                                                                ->placeholder('e.g., Custom Banner Placement')
                                                                ->columnSpan(2),
                                                        ]),
                                                ])
                                                ->addActionLabel('Add New Option')
                                                ->reorderable()
                                                ->cloneable()
                                                ->collapsible()
                                                ->defaultItems(0)
                                                ->columnSpanFull(),
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium')
                    ->description(
                        fn($record): string =>
                        $record->SponsorOptions()->count() . ' options'
                    ),

                Tables\Columns\TextColumn::make('pricing_info')
                    ->label('Pricing')
                    ->getStateUsing(function ($record): string {
                        if ($record->Currencies->isEmpty()) {
                            return 'No pricing set';
                        }

                        $prices = $record->Currencies->take(2)->map(function ($currency) {
                            $code = $currency->CODE ?? 'USD';
                            $price = $currency->pivot->total_price ?? 0;
                            return $code . ' ' . number_format($price);
                        })->implode(', ');

                        return $prices . ($record->Currencies->count() > 2 ? '...' : '');
                    })
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('options_list')
                    ->label('Key Options')
                    ->getStateUsing(function ($record): string {
                        if ($record->SponsorOptions->isEmpty()) {
                            return 'No options';
                        }

                        return $record->SponsorOptions->take(3)->map(function ($option) {
                            $title = $option->title;
                            $quantity = $option->pivot->quantity ?? 1;
                            if ($quantity > 1) {
                                $title .= ' (x' . $quantity . ')';
                            }
                            $isIncluded = $option->pivot->is_included ?? true;
                            if (!$isIncluded) {
                                $title .= ' [Extra]';
                            }
                            return $title;
                        })->implode(', ') . ($record->SponsorOptions->count() > 3 ? '...' : '');
                    })
                    ->limit(50)
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                // Tables\Columns\TextColumn::make('Contracts_count')
                //     ->label('Contracts')
                //     ->counts('Contracts')
                //     ->sortable()
                //     ->color('primary')
                //     ->toggleable(),

                // Tables\Columns\TextColumn::make('Events_count')
                //     ->label('Events')
                //     ->counts('Events')
                //     ->sortable()
                //     ->color('info')
                //     ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_contracts')
                    ->label('Has Contracts')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->whereHas('Contracts')),

                Tables\Filters\Filter::make('has_events')
                    ->label('Has Events')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->whereHas('Events')),

                // Tables\Filters\SelectFilter::make('Currencies')
                //     ->relationship('Currencies', 'name')
                //     ->multiple()
                //     ->preload()
                //     ->searchable(),

                Tables\Filters\Filter::make('has_options')
                    ->label('Has Options')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->whereHas('SponsorOptions')),
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
                // Tables\Actions\Action::make('duplicate')
                //     ->label('Duplicate')
                //     ->icon('heroicon-o-document-duplicate')
                //     ->color('gray')
                //     ->action(function (SponsorPackage $record) {
                //         $newPackage = $record->replicate();
                //         $newPackage->title = $record->title . ' (Copy)';
                //         $newPackage->save();

                //         // Duplicate currencies
                //         foreach ($record->Currencies as $currency) {
                //             $newPackage->Currencies()->attach($currency->id, [
                //                 'total_price' => $currency->pivot->total_price
                //             ]);
                //         }

                //         // Duplicate options
                //         foreach ($record->SponsorOptions as $option) {
                //             $newPackage->SponsorOptions()->attach($option->id, [
                //                 'quantity' => $option->pivot->quantity,
                //                 'is_included' => $option->pivot->is_included
                //             ]);
                //         }

                //         return redirect()->to(self::getUrl('edit', ['record' => $newPackage]));
                //     }),
                Tables\Actions\DeleteAction::make()->label('')
                    ->before(function (SponsorPackage $record) {
                        if ($record->Contracts()->exists()) {
                            throw new \Exception('Cannot delete package with existing contracts. Delete contracts first.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->Contracts()->exists()) {
                                    throw new \Exception('One or more packages have contracts. Delete contracts first.');
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('title')
            ->recordUrl(fn($record): string => self::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSponsorPackages::route('/'),
            'create' => Pages\CreateSponsorPackage::route('/create'),
            'view' => Pages\ViewSponsorPackage::route('/{record}'),
            'edit' => Pages\EditSponsorPackage::route('/{record}/edit'),
        ];
    }
}
