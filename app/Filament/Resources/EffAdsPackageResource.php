<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EffAdsPackageResource\Pages;
use App\Filament\Resources\EffAdsPackageResource\RelationManagers;
use App\Models\EffAdsOption;
use App\Models\EffAdsPackage;
use App\Models\Settings\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EffAdsPackageResource extends Resource
{
    protected static ?string $model = EffAdsPackage::class;

    protected static ?string $navigationIcon = 'heroicon-s-gift';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationLabel = 'Effective Advertisement Packages';
    protected static ?string $modelLabel = 'Effective Advertisement Package';

    public static function form(Form $form): Form
    {
        $currencies = Currency::all();

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
                                        ->placeholder('e.g., Advertisement, ...')
                                        ->columnSpanFull(),
                                    Forms\Components\Textarea::make('description')
                                        ->required()
                                        ->maxLength(1024)
                                        ->placeholder('Description of package')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Package Pricing')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Section::make('Package Base Price by Currency')
                                ->description('Select currencies and set the base price for this package')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema(function () use ($currencies) {
                                            $schema = [];
                                            foreach ($currencies as $currency) {
                                                $schema[] = Forms\Components\Card::make()
                                                    ->schema([
                                                        Forms\Components\Checkbox::make("package_currency_{$currency->id}_enabled")
                                                            ->label($currency->name)
                                                            ->helperText($currency->CODE)
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $set) use ($currency) {
                                                                if (!$state) {
                                                                    $set("package_currency_{$currency->id}_price", 0);
                                                                }
                                                            }),
                                                        Forms\Components\TextInput::make("package_currency_{$currency->id}_price")
                                                            ->label('Total Price')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->required()
                                                            ->prefix($currency->CODE . ' ')
                                                            ->disabled(fn(callable $get): bool => !$get("package_currency_{$currency->id}_enabled"))
                                                            ->visible(fn(callable $get): bool => $get("package_currency_{$currency->id}_enabled")),
                                                    ])
                                                    ->columnSpan(1);
                                            }
                                            return $schema;
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Options')
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Forms\Components\Section::make('Package Options')
                                ->description('Select existing options or create new ones')
                                ->schema([
                                    // Section 1: Select Existing Options (Checkbox List)
                                    Forms\Components\Section::make('Select Existing Options')
                                        ->collapsible()
                                        ->schema([
                                            Forms\Components\Grid::make(3)
                                                ->schema(function ($record) use ($currencies) {
                                                    $options = EffAdsOption::with('Currencies')->get();
                                                    $schema = [];

                                                    // Get existing options for this record
                                                    $existingOptions = [];
                                                    if ($record) {
                                                        $existingOptions = $record->EffAdsOptions->pluck('id')->toArray();
                                                    }

                                                    foreach ($options as $option) {
                                                        $isSelected = in_array($option->id, $existingOptions);

                                                        $schema[] = Forms\Components\Card::make()
                                                            ->schema([
                                                                Forms\Components\Checkbox::make("existing_option_{$option->id}")
                                                                    ->label($option->title)
                                                                    ->helperText($option->description)
                                                                    ->default($isSelected)
                                                                    ->reactive(),

                                                                // Show option's existing currency prices (readonly)
                                                                Forms\Components\Fieldset::make('Current Pricing')
                                                                    ->schema(function () use ($option, $currencies) {
                                                                        $priceSchema = [];
                                                                        $optionCurrencies = $option->Currencies->keyBy('id');

                                                                        foreach ($currencies as $currency) {
                                                                            $hasPrice = $optionCurrencies->has($currency->id);
                                                                            $price = $hasPrice ? $optionCurrencies[$currency->id]->pivot->price : 0;

                                                                            $priceSchema[] = Forms\Components\Grid::make(2)
                                                                                ->schema([
                                                                                    Forms\Components\Placeholder::make("currency_{$currency->id}_label")
                                                                                        ->label($currency->CODE)
                                                                                        ->content($hasPrice ? number_format($price, 2) : '-')
                                                                                        ->extraAttributes(['class' => 'text-sm']),
                                                                                ])
                                                                                ->columnSpan(1);
                                                                        }
                                                                        return $priceSchema;
                                                                    })
                                                                    ->columns(2)
                                                                    ->hidden(fn(callable $get): bool => !$get("existing_option_{$option->id}"))
                                                                    ->columnSpanFull(),
                                                            ])
                                                            ->columnSpan(1);
                                                    }

                                                    return $schema;
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->columnSpanFull(),

                                    // Section 2: Create New Options
                                    Forms\Components\Section::make('Create New Options')
                                        ->collapsible()
                                        ->collapsed()
                                        ->schema([
                                            Forms\Components\Repeater::make('new_options')
                                                ->label('New Options')
                                                ->schema([
                                                    Forms\Components\TextInput::make('title')
                                                        ->label('Option Title')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder('e.g., Custom Banner Placement')
                                                        ->columnSpan(2),
                                                    Forms\Components\Textarea::make('description')
                                                        ->label('Description')
                                                        ->maxLength(1024)
                                                        ->placeholder('Option description')
                                                        ->columnSpan(2),

                                                    // Currency prices as checkboxes for new options
                                                    Forms\Components\Fieldset::make('Set Prices for This Option')
                                                        ->schema(function () use ($currencies) {
                                                            $schema = [];
                                                            foreach ($currencies as $currency) {
                                                                $schema[] = Forms\Components\Grid::make(2)
                                                                    ->schema([
                                                                        Forms\Components\Checkbox::make("new_currency_{$currency->id}_enabled")
                                                                            ->label($currency->name)
                                                                            ->helperText($currency->CODE)
                                                                            ->reactive()
                                                                            ->afterStateUpdated(function ($state, callable $set) use ($currency) {
                                                                                if (!$state) {
                                                                                    $set("new_currency_{$currency->id}_price", 0);
                                                                                }
                                                                            }),
                                                                        Forms\Components\TextInput::make("new_currency_{$currency->id}_price")
                                                                            ->label('Price')
                                                                            ->numeric()
                                                                            ->minValue(0)
                                                                            ->default(0)
                                                                            ->required()
                                                                            ->prefix($currency->CODE . ' ')
                                                                            ->disabled(fn(callable $get): bool => !$get("new_currency_{$currency->id}_enabled"))
                                                                            ->visible(fn(callable $get): bool => $get("new_currency_{$currency->id}_enabled")),
                                                                    ])
                                                                    ->columnSpan(1);
                                                            }
                                                            return $schema;
                                                        })
                                                        ->columns(2)
                                                        ->columnSpanFull(),
                                                ])
                                                ->columns(2)
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
                        $record->EffAdsOptions()->count() . ' options'
                    ),

                Tables\Columns\TextColumn::make('pricing_info')
                    ->label('Package Pricing')
                    ->getStateUsing(function ($record): string {
                        if ($record->Currencies->isEmpty()) {
                            return 'No pricing set';
                        }

                        $prices = $record->Currencies->take(2)->map(function ($currency) {
                            $code = $currency->CODE ?? 'USD';
                            $price = $currency->pivot->total_price ?? 0;
                            return $code . ' ' . number_format($price, 2);
                        })->implode(', ');

                        return $prices . ($record->Currencies->count() > 2 ? '...' : '');
                    })
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('options_info')
                    ->label('Options')
                    ->getStateUsing(function ($record): string {
                        if ($record->EffAdsOptions->isEmpty()) {
                            return 'No options';
                        }

                        $optionNames = $record->EffAdsOptions->take(3)->pluck('title')->implode(', ');
                        return $optionNames . ($record->EffAdsOptions->count() > 3 ? '...' : '');
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

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

                Tables\Filters\Filter::make('has_options')
                    ->label('Has Options')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->whereHas('EffAdsOptions')),
            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label('')
                    ->before(function (EffAdsPackage $record) {
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
            'index' => Pages\ListEffAdsPackages::route('/'),
            'create' => Pages\CreateEffAdsPackage::route('/create'),
            'view' => Pages\ViewEffAdsPackage::route('/{record}'),
            'edit' => Pages\EditEffAdsPackage::route('/{record}/edit'),
        ];
    }
}
