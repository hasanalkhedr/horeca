<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\Settings\Category;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use App\Models\Settings\SponsorPackage;
use App\Models\Settings\AdsPackage;
use App\Models\Settings\EffAdsPackage;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Event Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $currencies = Currency::all();

        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Basic Event Information
                    Wizard\Step::make('Basic Event Information')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Section::make('Event Information')
                                ->schema([
                                    Forms\Components\TextInput::make('CODE')
                                        ->label('Event Code')
                                        ->required()
                                        ->unique(ignorable: fn($record) => $record)
                                        ->maxLength(50)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    Forms\Components\Textarea::make('description')
                                        ->rows(3)
                                        ->maxLength(1000)
                                        ->columnSpanFull(),

                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\DatePicker::make('start_date')
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->columnSpan(1),

                                            Forms\Components\DatePicker::make('end_date')
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->after('start_date')
                                                ->columnSpan(1),

                                            Forms\Components\DatePicker::make('apply_start_date')
                                                ->label('Application Start')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->columnSpan(1),

                                            Forms\Components\DatePicker::make('apply_deadline_date')
                                                ->label('Application Deadline')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->after('apply_start_date')
                                                ->columnSpan(1),
                                        ]),
                                ]),

                            Forms\Components\Section::make('Space Information')
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\TextInput::make('total_space')
                                                ->label('Total Space')
                                                ->numeric()
                                                ->minValue(0)
                                                ->suffix('sqm')
                                                ->required()
                                                ->columnSpan(1),

                                            Forms\Components\TextInput::make('space_to_sell')
                                                ->label('Space to Sell')
                                                ->numeric()
                                                ->minValue(0)
                                                ->suffix('sqm')
                                                ->required()
                                                ->columnSpan(1),

                                        ])
                                        ->afterStateHydrated(function ($state, callable $set, $record) {
                                            if (!$record)
                                                return;

                                            $set('free_space', $record->free_space . ' sqm');
                                            $set('remaining_space_to_sell', $record->remaining_space_to_sell . ' sqm');
                                            $set('remaining_free_space', $record->remaining_free_space . ' sqm');
                                        }),
                                ]),

                            Forms\Components\Section::make('Location & Tax')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('country')
                                                ->maxLength(100)
                                                ->columnSpan(1),

                                            Forms\Components\TextInput::make('city')
                                                ->maxLength(100)
                                                ->columnSpan(1),

                                            Forms\Components\TextInput::make('address')
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),

                                    Forms\Components\TextInput::make('vat_rate')
                                        ->label('VAT Rate (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%')
                                        ->default(0)
                                        ->columnSpan(1),
                                ]),
                        ]),

                    // Step 2: Event Currencies with Minimum Prices
                    Wizard\Step::make('Event Currencies')
                        ->icon('heroicon-o-currency-dollar')
                        ->description('Select currencies and set minimum prices')
                        ->schema([
                            Forms\Components\Section::make('Event Currencies')
                                ->description('Select which currencies are accepted for this event and set minimum prices')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema(function () use ($currencies) {
                                            $schema = [];
                                            foreach ($currencies as $currency) {
                                                $schema[] = Forms\Components\Card::make()
                                                    ->schema([
                                                        Forms\Components\Checkbox::make("event_currency_{$currency->id}_enabled")
                                                            ->label($currency->name)
                                                            ->helperText($currency->CODE)
                                                            ->reactive()
                                                            ->afterStateUpdated(function ($state, callable $set) use ($currency) {
                                                                if (!$state) {
                                                                    $set("event_currency_{$currency->id}_min_price", 0);
                                                                }
                                                            }),
                                                        Forms\Components\TextInput::make("event_currency_{$currency->id}_min_price")
                                                            ->label('Minimum Price')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->required()
                                                            ->prefix($currency->CODE . ' ')
                                                            ->disabled(fn(callable $get): bool => !$get("event_currency_{$currency->id}_enabled"))
                                                            ->visible(fn(callable $get): bool => $get("event_currency_{$currency->id}_enabled")),
                                                    ])
                                                    ->columnSpan(1);
                                            }
                                            return $schema;
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),
                        ]),

                    // Step 3: Price Packages
                    Wizard\Step::make('Price Packages')
                        ->icon('heroicon-o-tag')
                        ->description('Create price packages with currency-specific pricing')
                        ->schema([
                            Repeater::make('price_packages')
                                ->label('Price Packages')
                                ->schema([
                                    // Price Package Details
                                    Forms\Components\Section::make('Package Details')
                                        ->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('e.g., Standard Booth, Premium Booth')
                                                ->columnSpan(2),

                                            Textarea::make('description')
                                                ->maxLength(500)
                                                ->placeholder('Description of this price package')
                                                ->columnSpan(2),
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),

                                    // Currency Prices for this Package
                                    Forms\Components\Section::make('Package Pricing by Currency')
                                        ->description('Set prices for each currency (must meet minimum from previous step)')
                                        ->schema([
                                            Forms\Components\Grid::make(3)
                                                ->schema(function (callable $get) use ($currencies) {
                                                    $schema = [];
                                                    $currentPackageIndex = $get('index');

                                                    // Filter to only show currencies enabled in Step 2
                                                    $enabledCurrencies = [];
                                                    foreach ($currencies as $currency) {
                                                        $enabledField = "event_currency_{$currency->id}_enabled";
                                                        if ($get("../../{$enabledField}") ?? false) {
                                                            $enabledCurrencies[] = $currency;
                                                        }
                                                    }

                                                    // If no currencies are enabled, show a warning
                                                    if (empty($enabledCurrencies)) {
                                                        $schema[] = Forms\Components\Placeholder::make('no_currencies_warning')
                                                            ->content('⚠️ No currencies selected in Step 2. Please go back and select currencies first.')
                                                            ->extraAttributes(['class' => 'text-sm text-amber-600'])
                                                            ->columnSpanFull();

                                                        return $schema;
                                                    }

                                                    foreach ($enabledCurrencies as $currency) {
                                                        $minPrice = $get("../../event_currency_{$currency->id}_min_price") ?? 0;

                                                        $schema[] = Forms\Components\Card::make()
                                                            ->schema([
                                                                Forms\Components\Checkbox::make("price_package_currency_{$currency->id}_enabled")
                                                                    ->label($currency->name)
                                                                    ->helperText($currency->CODE . ' - Min: ' . $minPrice)
                                                                    ->reactive()
                                                                    ->default(function ($record, $get) use ($currency, $currentPackageIndex) {
                                                                        // Get existing value for this price package
                                                                        $pricePackages = $get('../../price_packages') ?? [];
                                                                        if (isset($pricePackages[$currentPackageIndex])) {
                                                                            $packageData = $pricePackages[$currentPackageIndex];
                                                                            return $packageData["price_package_currency_{$currency->id}_enabled"] ?? false;
                                                                        }
                                                                        return false;
                                                                    })
                                                                    ->afterStateUpdated(function ($state, callable $set) use ($currency, $minPrice) {
                                                                        if (!$state) {
                                                                            $set("price_package_currency_{$currency->id}_price", 0);
                                                                        } else {
                                                                            // Set to minimum price if enabled
                                                                            $set("price_package_currency_{$currency->id}_price", $minPrice);
                                                                        }
                                                                    }),
                                                                Forms\Components\TextInput::make("price_package_currency_{$currency->id}_price")
                                                                    ->label('Price')
                                                                    ->numeric()
                                                                    ->minValue($minPrice)
                                                                    ->required()
                                                                    ->prefix($currency->CODE . ' ')
                                                                    ->disabled(fn(callable $get): bool => !$get("price_package_currency_{$currency->id}_enabled"))
                                                                    ->visible(fn(callable $get): bool => $get("price_package_currency_{$currency->id}_enabled"))
                                                                    ->default(function ($record, $get) use ($currency, $currentPackageIndex, $minPrice) {
                                                                        // Get existing value for this price package
                                                                        $pricePackages = $get('../../price_packages') ?? [];
                                                                        if (isset($pricePackages[$currentPackageIndex])) {
                                                                            $packageData = $pricePackages[$currentPackageIndex];
                                                                            return $packageData["price_package_currency_{$currency->id}_price"] ?? $minPrice;
                                                                        }
                                                                        return $minPrice;
                                                                    })
                                                                    ->rule('gte:' . $minPrice),
                                                            ])
                                                            ->columnSpan(1);
                                                    }
                                                    return $schema;
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->addActionLabel('Add Price Package')
                                ->reorderable()
                                ->cloneable()
                                ->collapsible()
                                ->defaultItems(0)
                                ->columnSpanFull(),
                        ]),

                    // Step 4: Categories and Packages
                    Wizard\Step::make('Categories & Packages')
                        ->icon('heroicon-o-squares-2x2')
                        ->description('Select categories and packages for this event')
                        ->schema([
                            // Categories Section
                            Forms\Components\Section::make('Event Categories')
                                ->schema([
                                    Forms\Components\CheckboxList::make('categories')
                                        ->label('Select Categories')
                                        ->options(Category::all()->pluck('name', 'id'))
                                        ->columns(3)
                                        ->gridDirection('row')
                                        ->bulkToggleable()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),

                            // Sponsor Packages Section
                            Forms\Components\Section::make('Sponsor Packages')
                                ->schema([
                                    Forms\Components\CheckboxList::make('sponsor_packages')
                                        ->label('Select Sponsor Packages')
                                        ->options(\App\Models\SponsorPackage::all()->pluck('title', 'id'))
                                        ->columns(3)
                                        ->gridDirection('row')
                                        ->bulkToggleable()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),

                            // Ads Packages Section
                            Forms\Components\Section::make('Advertisement Packages')
                                ->schema([
                                    Forms\Components\CheckboxList::make('ads_packages')
                                        ->label('Select Advertisement Packages')
                                        ->options(\App\Models\AdsPackage::all()->pluck('title', 'id'))
                                        ->columns(3)
                                        ->gridDirection('row')
                                        ->bulkToggleable()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),

                            // Eff Ads Packages Section
                            Forms\Components\Section::make('Efficient Advertisement Packages')
                                ->schema([
                                    Forms\Components\CheckboxList::make('eff_ads_packages')
                                        ->label('Select Efficient Advertisement Packages')
                                        ->options(\App\Models\EffAdsPackage::all()->pluck('title', 'id'))
                                        ->columns(3)
                                        ->gridDirection('row')
                                        ->bulkToggleable()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString()
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
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn($record): string => $record->name)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_space')
                    ->label('Total Space')
                    ->suffix(' sqm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('space_to_sell')
                    ->label('To Sell')
                    ->suffix(' sqm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('remaining_space_to_sell')
                    ->label('Remaining')
                    ->suffix(' sqm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('free_space')
                    ->label('Free Space')
                    ->suffix(' sqm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vat_rate')
                    ->label('VAT')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->multiple(),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('start_date_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('createContract')
                    ->label('')
                    ->tooltip('Create Contract')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->modalHeading('Select Report')
                    ->modalDescription('Choose a report for the new contract')
                    ->modalSubmitActionLabel('Continue to Contract')
                    ->form([
                        Forms\Components\Select::make('report_id')
                            ->label('Select Report')
                            ->options(function (Event $record) {
                                return $record->Reports()
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Event $record, array $data) {
                        // Redirect with both parameters
                        return redirect()->route('filament.admin.resources.contracts.create', [
                            'event_id' => $record->id,
                            'report_id' => $data['report_id'],
                        ]);
                    }),

                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label('')
                    ->before(function (Event $record) {
                        if ($record->Stands()->exists()) {
                            throw new \Exception('Cannot delete event with existing stands. Delete stands first.');
                        }
                        if ($record->Contracts()->exists()) {
                            throw new \Exception('Cannot delete event with existing contracts. Delete contracts first.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->Stands()->exists()) {
                                    throw new \Exception('One or more events have stands. Delete stands first.');
                                }
                                if ($record->Contracts()->exists()) {
                                    throw new \Exception('One or more events have contracts. Delete contracts first.');
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('start_date', 'desc')
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\StandsRelationManager::class,
            // RelationManagers\ContractsRelationManager::class,
            // RelationManagers\PricesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
