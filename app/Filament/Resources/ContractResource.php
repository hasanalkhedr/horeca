<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Livewire\ClientSelect;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Report;
use App\Models\Stand;
use App\Models\Settings\Price;
use App\Models\Settings\Category;
use App\Models\SponsorPackage;
use App\Models\AdsPackage;
use App\Models\EffAdsPackage;
use App\Models\User;
use App\Models\Company;
use App\Models\Client;
use App\Filament\Helpers\ContractCalculations;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;


class ContractResource extends Resource
{
    use ContractCalculations;

    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contracts';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Step 1: Basic Contract Information
                Forms\Components\Section::make('Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Section::make('Contract Details')
                            ->schema([
                                Forms\Components\Select::make('event_id')
                                    ->label('Event')
                                    ->relationship(
                                        name: 'Event',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn($query) => $query->select(['id', 'name', 'vat_rate'])
                                    )
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('stand_id', null);
                                        $set('price_id', null);
                                        $set('report_id', null);
                                        $set('currency_id', null);
                                        $set('sponsor_package_id', null);
                                        $set('category_id', null);
                                    }),

                                Forms\Components\Select::make('report_id')
                                    ->label('Contract Template')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        if (!$eventId) {
                                            return [];
                                        }
                                        return Report::where('event_id', $eventId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $report = Report::find($state);
                                        $set('currency_id', $report?->currency_id);
                                    })->required(),

                                Forms\Components\Hidden::make('currency_id')
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('contract_no')
                                    ->label('Contract Number')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(fn(): string => 'Auto-generated'),

                                Forms\Components\DatePicker::make('contract_date')
                                    ->label('Contract Date')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\Select::make('status')
                                    ->label('Contract Status')
                                    ->options(Contract::getStatuses())
                                    ->default(Contract::STATUS_DRAFT)
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),
                            ])
                            ->collapsible()
                            ->columns(3),
                    ])->collapsible(),

                Forms\Components\Section::make('Client Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Section::make('Company & Contacts')
                            ->schema([
                               Forms\Components\Select::make('company_id')
                                    ->label('Company')
                                    ->searchable()
                                    ->preload(false)
                                    ->reactive()
                                    ->getSearchResultsUsing(function (string $search): array {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }

                                        $response = Http::get(
                                            config('services.pipedrive.base_url') . '/organizations/search',
                                            [
                                                'term' => $search,
                                                'fields' => 'name',
                                                'api_token' => config('services.pipedrive.api_key'),
                                            ]
                                        );

                                        return collect($response->json('data.items') ?? [])
                                            ->mapWithKeys(fn($item) => [
                                                $item['item']['id'] => $item['item']['name'],
                                            ])
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        return Company::find($value)?->name;
                                    })
                                    ->afterStateUpdated(function ($state, Set $set) {

                                        if (!$state) {
                                            $set('exhabition_coordinator', null);
                                            $set('contact_person', null);
                                            return;
                                        }

                                        // Check local DB
                                        $company = Company::where('pipe_id', $state)->first();

                                        if (!$company) {
                                            $company = self::createCompanyFromPipedrive($state);
                                        }

                                        $set('company_id', $company->id);
                                        $set('exhabition_coordinator', null);
                                        $set('contact_person', null);
                                    })
                                    ->required(),
                                Forms\Components\Select::make('exhabition_coordinator')
                                    ->label('Exhibition Coordinator')
                                    // ->reactive()
                                    ->options(
                                        fn(Get $get) =>
                                        Client::where('company_id', $get('company_id'))
                                            ->pluck('name', 'id')
                                    )
                                    ->disabled(fn(Get $get) => !$get('company_id')),

                                Forms\Components\Select::make('contact_person')
                                    ->label('Daily Contact Person')
                                    // ->reactive()
                                    ->options(
                                        fn(Get $get) =>
                                        Client::where('company_id', $get('company_id'))
                                            ->pluck('name', 'id')
                                    )
                                    ->disabled(fn(Get $get) => !$get('company_id')),
                                //Livewire::make('client-select')->columnSpanFull(),

                                Forms\Components\Select::make('seller')
                                    ->label('Sales Person')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])->columns(4)
                            ->collapsible(),
                        Forms\Components\Section::make('Category')
                            ->schema([
                                Forms\Components\Radio::make('category_id')
                                    ->label('Category')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        if (!$eventId) {
                                            return [];
                                        }
                                        $event = Event::find($eventId);
                                        return $event->Categories->pluck('name', 'id')->toArray();
                                    })
                                    ->columns(4)
                                    // ->reactive()
                                    ->required(false),
                            ])->collapsible(),
                    ])->collapsible()
                    ->visible(function ($set, $get): bool {
                        $eventId = $get('event_id');
                        $reportId = $get('report_id');
                        return $eventId && $reportId;
                    }),

                // Step 2: Stand & Pricing
                Forms\Components\Section::make('Stand & Pricing')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Section::make('Stand Selection')
                            ->schema([
                                Forms\Components\Select::make('stand_id')
                                    ->label('Stand')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        if (!$eventId) {
                                            return [];
                                        }

                                        // Cache this query result
                                        return cache()->remember("stands_event_{$eventId}", 300, function () use ($eventId, $get) {
                                            return Stand::query()
                                                ->select(['id', 'no', 'space', 'status'])
                                                ->where('event_id', $eventId)
                                                ->where(function ($query) use ($get) {
                                                    $query->where('status', 'Available')
                                                        ->orWhere('id', $get('stand_id'));
                                                })
                                                ->get()
                                                ->mapWithKeys(fn($stand) => [
                                                    $stand->id => "{$stand->no} | {$stand->space} sqm"
                                                ])
                                                ->toArray();
                                        });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::calculateSpaceAmount($set, $get);
                                    }),

                                Forms\Components\Placeholder::make('stand_space')
                                    ->label('Stand Space')
                                    ->content(function (callable $get) {
                                        $standId = $get('stand_id');
                                        if (!$standId) {
                                            return 'Select a stand';
                                        }
                                        $stand = Stand::find($standId);
                                        return $stand ? "{$stand->space} sqm" : 'N/A';
                                    }),
                            ])->collapsible()
                            ->columns(2),

                        Forms\Components\Section::make('Pricing')
                            ->schema([
                                Forms\Components\Radio::make('price_id')
                                    ->label('Select Price Package')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        $currencyId = $get('currency_id');
                                        if (!$eventId || !$currencyId) {
                                            return [];
                                        }

                                        $prices = Price::where('event_id', $eventId)
                                            ->with([
                                                'Currencies' => function ($query) use ($currencyId) {
                                                    $query->where('currencies.id', $currencyId);
                                                }
                                            ])
                                            ->get();

                                        $options = [];
                                        foreach ($prices as $price) {
                                            $amount = $price->Currencies()->where('currencies.id', $currencyId)->first()?->pivot->amount ?? 0;
                                            $options[$price->id] = "{$price->name} | {$amount}";
                                        }
                                        return $options;
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::calculateSpaceAmount($set, $get);
                                    }),

                                Forms\Components\Fieldset::make('Special Price')
                                    ->schema([
                                        Forms\Components\Toggle::make('use_special_price')
                                            ->label('Use Special Price')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $set('price_id', null);
                                                } else {
                                                    $set('price_amount', null);
                                                }
                                                self::calculateSpaceAmount($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('price_amount')
                                            ->label('Special Price Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->visible(fn(callable $get): bool => $get('use_special_price'))
                                            //->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                self::calculateSpaceAmount($set, $get);
                                            }),
                                    ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('space_amount')
                                            ->label('Space Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),

                                        Forms\Components\TextInput::make('space_discount')
                                            ->label('Discount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->reactive()
                                            ->debounce(500)
                                            ->default(0)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                self::calculateSpaceNet($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('space_net')
                                            ->label('Net Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),
                                    ]),
                            ])->collapsible(),
                    ])->collapsible()
                    ->collapsed()
                    ->visible(function ($set, $get): bool {
                        $eventId = $get('event_id');
                        $reportId = $get('report_id');
                        return $eventId && $reportId;
                    }),

                // Step 3: Additional Services
                Forms\Components\Section::make('Additional Services')
                    ->icon('heroicon-o-plus-circle')
                    ->schema([
                        // Water & Electricity
                        Forms\Components\Section::make('Water & Electricity')
                            ->schema([
                                Forms\Components\Toggle::make('if_water')
                                    ->label('Water Point Needed')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) {
                                            $set('water_electricity_amount', 0);
                                        }
                                    }),

                                Forms\Components\Toggle::make('if_electricity')
                                    ->label('Extra Electricity')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) {
                                            $set('electricity_text', '');
                                            $set('water_electricity_amount', 0);
                                        }
                                    }),

                                Forms\Components\TextInput::make('electricity_text')
                                    ->label('WATT Needed')
                                    ->visible(fn(callable $get): bool => $get('if_electricity')),

                                Forms\Components\TextInput::make('water_electricity_amount')
                                    ->label('Water & Electricity Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix(
                                        fn(callable $get): string =>
                                        Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                    )
                                    ->visible(
                                        fn(callable $get): bool =>
                                        $get('if_water') || $get('if_electricity')
                                    )
                                    //->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::calculateTotal($set, $get);
                                    }),
                            ])->collapsible()
                            ->columns(2)
                            ->visible(function (callable $get) {
                                $reportId = $get('report_id');
                                if (!$reportId)
                                    return false;

                                $report = Report::find($reportId);
                                $components = $report?->components ?? [];
                                return in_array('water-section', $components);
                            }),

                        // // Special Design
                        // Forms\Components\Section::make('Special Design')
                        //     ->schema([
                        //         Forms\Components\Textarea::make('special_design_text')
                        //             ->label('Design Description')
                        //             ->rows(2),

                        //         Forms\Components\TextInput::make('special_design_price')
                        //             ->label('Design Price per sqm')
                        //             ->numeric()
                        //             ->minValue(0)
                        //             ->reactive()
                        //             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        //                 self::calculateSpecialDesignAmount($set, $get);
                        //             }),

                        //         Forms\Components\TextInput::make('special_design_amount')
                        //             ->label('Total Design Amount')
                        //             ->numeric()
                        //             ->readOnly()
                        //             ->prefix(
                        //                 fn(callable $get): string =>
                        //                 Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                        //             ),
                        //     ])
                        //     ->columns(3),

                        // New Product
                        Forms\Components\Section::make('New Product')
                            ->schema([
                                Forms\Components\Textarea::make('new_product')
                                    ->label('New Product to Launch')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])->visible(function (callable $get) {
                                $reportId = $get('report_id');
                                if (!$reportId)
                                    return false;

                                $report = Report::find($reportId);
                                $components = $report?->components ?? [];
                                return in_array('new-product-section', $components);
                            }),
                    ])->collapsible()->collapsed()
                    ->visible(function ($set, $get): bool {
                        $eventId = $get('event_id');
                        $reportId = $get('report_id');
                        return $eventId && $reportId;
                    }),

                // Step 4: Packages
                Forms\Components\Section::make('Packages')
                    ->icon('heroicon-o-gift')
                    ->schema([
                        // Sponsor Packages
                        Forms\Components\Section::make('Sponsor Package')
                            ->schema([
                                Forms\Components\Select::make('sponsor_package_id')
                                    ->label('Sponsor Package')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        $currencyId = $get('currency_id');
                                        if (!$eventId || !$currencyId) {
                                            return [];
                                        }

                                        $event = Event::find($eventId);
                                        $packages = $event->SponsorPackages;

                                        $options = [];
                                        foreach ($packages as $package) {
                                            $price = $package->Currencies
                                                ->where('id', $currencyId)
                                                ->first()?->pivot->total_price ?? 0;
                                            $options[$package->id] = "{$package->title} | {$price}";
                                        }
                                        return $options;
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::calculateSponsorAmount($set, $get);
                                    }),

                                Forms\Components\Textarea::make('specify_text')
                                    ->label('Specify Text')
                                    ->rows(2),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('sponsor_amount')
                                            ->label('Sponsor Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),

                                        Forms\Components\TextInput::make('sponsor_discount')
                                            ->label('Discount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->reactive()
                                            ->debounce(500)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                self::calculateSponsorNet($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('sponsor_net')
                                            ->label('Net Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),
                                    ]),
                            ])->collapsible()
                            ->visible(function (callable $get) {
                                $reportId = $get('report_id');
                                if (!$reportId)
                                    return false;

                                $report = Report::find($reportId);
                                $components = $report?->components ?? [];
                                return in_array('sponsor-section', $components);
                            }),

                        // Advertisement Packages
                        Forms\Components\Section::make('Advertisement Packages')
                            ->schema([
                                // Hidden field to store the array of selected package_option combinations
                                Forms\Components\Hidden::make('ads_check')
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->default([]),

                                // Dynamic checkbox list
                                Forms\Components\CheckboxList::make('ads_options_display')
                                    ->label('Advertisement Options')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        $currencyId = $get('currency_id');

                                        if (!$eventId || !$currencyId) {
                                            return [];
                                        }

                                        $event = Event::find($eventId);
                                        $packages = $event->AdsPackages;
                                        $options = [];

                                        foreach ($packages as $package) {
                                            $loadedPackage = AdsPackage::with([
                                                'AdsOptions.Currencies' => function ($query) use ($currencyId) {
                                                    $query->where('currencies.id', $currencyId);
                                                }
                                            ])->find($package->id);

                                            if (!$loadedPackage)
                                                continue;

                                            foreach ($loadedPackage->AdsOptions as $option) {
                                                $price = $option->Currencies
                                                    ->where('id', $currencyId)
                                                    ->first()?->pivot->price ?? 0;

                                                // Store as packageID_optionID format
                                                $key = "{$package->id}_{$option->id}";
                                                $options[$key] = "{$package->title} - {$option->title} | {$price}";
                                            }
                                        }

                                        return $options;
                                    })
                                    ->columns(2)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->reactive()
                                    ->dehydrated(false) // Don't save this display field
                                    ->afterStateHydrated(function ($component, $record) {
                                        // Convert stored ads_check array to display format
                                        if ($record && $record->ads_check) {
                                            $displaySelections = [];
                                            foreach ($record->ads_check as $selection) {
                                                $displaySelections[] = $selection; // Already in packageID_optionID format
                                            }
                                            $component->state($displaySelections);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Update the actual ads_check field
                                        $set('ads_check', $state ?? []);

                                        // Calculate total amount
                                        self::calculateAdsAmount($set, $get, $state ?? []);
                                    }),

                                // Total amount section
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('advertisment_amount')
                                            ->label('Total Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),

                                        Forms\Components\TextInput::make('ads_discount')
                                            ->label('Discount')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->reactive()
                                            ->debounce(500)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                self::calculateAdsNet($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('ads_net')
                                            ->label('Net Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),
                                    ]),
                            ])
                            ->collapsible()
                            ->visible(function (callable $get) {
                                $reportId = $get('report_id');
                                if (!$reportId)
                                    return false;

                                $report = Report::find($reportId);
                                $components = $report?->components ?? [];
                                return in_array('advertisement-section', $components);
                            }),

                        // Effective Advertisement Packages
                        Forms\Components\Section::make('Effective Advertisement Packages')
                            ->schema([
                                // Hidden field to store the array of selected package_option combinations
                                Forms\Components\Hidden::make('eff_ads_check')
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->default([]),

                                // Dynamic checkbox list
                                Forms\Components\CheckboxList::make('eff_ads_options_display')
                                    ->label('Effective Advertisement Options')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        $currencyId = $get('currency_id');

                                        if (!$eventId || !$currencyId) {
                                            return [];
                                        }

                                        $event = Event::find($eventId);
                                        $packages = $event->EffAdsPackages;
                                        $options = [];

                                        foreach ($packages as $package) {
                                            $loadedPackage = EffAdsPackage::with([
                                                'EffAdsOptions.Currencies' => function ($query) use ($currencyId) {
                                                    $query->where('currencies.id', $currencyId);
                                                }
                                            ])->find($package->id);

                                            if (!$loadedPackage)
                                                continue;

                                            foreach ($loadedPackage->EffAdsOptions as $option) {
                                                $price = $option->Currencies
                                                    ->where('id', $currencyId)
                                                    ->first()?->pivot->price ?? 0;

                                                // Store as packageID_optionID format
                                                $key = "{$package->id}_{$option->id}";
                                                $options[$key] = "{$package->title} - {$option->title} | {$price}";
                                            }
                                        }

                                        return $options;
                                    })
                                    ->columns(2)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->reactive()
                                    ->dehydrated(false) // Don't save this display field
                                    ->afterStateHydrated(function ($component, $record) {
                                        // Convert stored ads_check array to display format
                                        if ($record && $record->eff_ads_check) {
                                            $effdisplaySelections = [];
                                            foreach ($record->eff_ads_check as $selection) {
                                                $effdisplaySelections[] = $selection; // Already in packageID_optionID format
                                            }
                                            $component->state($effdisplaySelections);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Update the actual ads_check field
                                        $set('eff_ads_check', $state ?? []);

                                        // Calculate total amount
                                        self::calculateEffAdsAmount($set, $get, $state ?? []);
                                    }),

                                // Total amount section
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('eff_ads_amount')
                                            ->label('Total Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),

                                        Forms\Components\TextInput::make('eff_ads_discount')
                                            ->label('Discount')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->reactive()
                                            ->debounce(500)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                self::calculateEffAdsNet($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('eff_ads_net')
                                            ->label('Net Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                            ),
                                    ]),
                            ])
                            ->collapsible()
                            ->visible(function (callable $get) {
                                $reportId = $get('report_id');
                                if (!$reportId)
                                    return false;

                                $report = Report::find($reportId);
                                $components = $report?->components ?? [];
                                return in_array('effective-advertisement-section', $components);
                            }),
                    ])->collapsible()->collapsed()
                    ->visible(function ($set, $get): bool {
                        $eventId = $get('event_id');
                        $reportId = $get('report_id');
                        return $eventId && $reportId;
                    }),

                // Step 5: Summary & Notes
                Forms\Components\Section::make('Summary & Notes')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\Section::make('Contract Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes1')
                                    ->label('Notes 1')
                                    ->rows(3)
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('notes2')
                                    ->label('Notes 2')
                                    ->rows(3)
                                    ->columnSpan(1),
                            ])->collapsible()->collapsed()
                            ->visible(function (callable $get) {
                                $reportId = $get('report_id');
                                if (!$reportId)
                                    return false;

                                $report = Report::find($reportId);
                                $components = $report?->components ?? [];
                                return in_array('notes-section', $components);
                            })
                            ->columns(2),

                        Forms\Components\Section::make('Financial Summary')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Hidden::make('sub_total_1'),
                                        Hidden::make('d_i_a'),
                                        Hidden::make('sub_total_2'),
                                        Hidden::make('vat_amount'),
                                        Hidden::make('net_total'),
                                        Forms\Components\Placeholder::make('sub_total_1_display')
                                            ->label('Sub Total 1')
                                            ->content(function (callable $get) {
                                                return number_format($get('sub_total_1') ?? 0, 2) . ' ' .
                                                    (Report::find($get('report_id'))?->Currency?->CODE ?? 'USD');
                                            }),

                                        Forms\Components\Placeholder::make('d_i_a_display')
                                            ->label('Discount (D/I/A)')
                                            ->content(function (callable $get) {
                                                return number_format($get('d_i_a') ?? 0, 2) . ' ' .
                                                    (Report::find($get('report_id'))?->Currency?->CODE ?? 'USD');
                                            }),

                                        Forms\Components\Placeholder::make('sub_total_2_display')
                                            ->label('Sub Total 2 (Net)')
                                            ->content(function (callable $get) {
                                                return number_format($get('sub_total_2') ?? 0, 2) . ' ' .
                                                    (Report::find($get('report_id'))?->Currency?->CODE ?? 'USD');
                                            }),

                                        Forms\Components\Placeholder::make('vat_amount_display')
                                            ->label('VAT Amount')
                                            ->content(function (callable $get) {
                                                $vatRate = Event::find($get('event_id'))?->vat_rate ?? 0;
                                                return number_format($get('vat_amount') ?? 0, 2) . ' ' .
                                                    (Report::find($get('report_id'))?->Currency?->CODE ?? 'USD') .
                                                    " ({$vatRate}%)";
                                            }),

                                        Forms\Components\Placeholder::make('net_total_display')
                                            ->label('Net Total')
                                            ->content(function (callable $get) {
                                                return number_format($get('net_total') ?? 0, 2) . ' ' .
                                                    (Report::find($get('report_id'))?->Currency?->CODE ?? 'USD');
                                            })
                                            ->extraAttributes(['class' => 'text-xl font-bold']),
                                    ]),
                            ])->collapsible(),
                    ])->collapsible()
                    ->visible(function ($set, $get): bool {
                        $eventId = $get('event_id');
                        $reportId = $get('report_id');
                        return $eventId && $reportId;
                    }),
            ]);
    }

    protected static function createCompanyFromPipedrive(int $pipeId): Company
    {
        $response = Http::get(
            config('services.pipedrive.base_url') . "/organizations/{$pipeId}",
            ['api_token' => config('services.pipedrive.api_key')]
        );

        $data = $response->json('data');

        $company = Company::create([
            'pipe_id' => $data['id'],
            'name' => $data['name'],
            'CODE' => $data['label'] ?? 'NOT_SET',
            'country' => self::countryFromJson($data['affb271863709112a116d275ffc4d573ed7853c7'] ?? null),
            'city' => self::cityFromJson($data['6182d0aaa2d68c248363bf6ff1cef5ef87c21799'] ?? null),
            'street' => $data['e7c3f6cfd690ba7594c56660244a785d6b43c30e'] ?? null,
            'email' => $data['09a7583fcb843a55ef2830d5ee06b2a88edd9623'] ?? null,
            'website' => $data['b553abbde7758c4d1bc07731fb21dfdfe1d81c4a'] ?? null,
        ]);

        // Fetch people
        $people = Http::get(
            config('services.pipedrive.base_url') . "/organizations/{$pipeId}/persons",
            ['api_token' => config('services.pipedrive.api_key')]
        )->json('data') ?? [];

        foreach ($people as $person) {
            $company->clients()->create([
                'pipe_id' => $person['id'],
                'name' => $person['name'],
                'mobile' => $person['phone'][0]['value'] ?? null,
                'email' => $person['email'][0]['value'] ?? null,
                'position' => $person['job_title'] ?? null,
            ]);
        }

        return $company;
    }
    protected static function countryFromJson(?string $code): ?string
    {
        if (!$code)
            return null;

        $data = json_decode(File::get('countries.json'), true);
        return collect($data)->firstWhere('id', $code)['label'] ?? $code;
    }

    protected static function cityFromJson(?string $code): ?string
    {
        if (!$code)
            return null;

        $data = json_decode(File::get('cities.json'), true);
        return collect($data)->firstWhere('id', $code)['label'] ?? $code;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_no')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('Event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('Company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('Stand.no')
                    ->label('Stand')
                    ->formatStateUsing(
                        fn($state, $record): string =>
                        "{$state} | {$record->Stand?->space} sqm"
                    ),

                Tables\Columns\TextColumn::make('status_full_display')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->status_color)
                    ->icon(fn($record) => $record->status_icon)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('net_total')
                    ->label('Total Amount')
                    ->money(
                        fn($record): string =>
                        $record->Report?->Currency?->CODE ?? 'USD'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('contract_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('Seller.name')
                    ->label('Sales Person')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(Event::pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('contract_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('contract_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('contract_date', '<=', $date),
                            );
                    }),
            ], FiltersLayout::AboveContent)->filtersFormColumns(4)
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn(Contract $record): string => route('contracts.preview', $record))
                    ->openUrlInNewTab(),
                // Tables\Actions\Action::make('markInterested')
                //     ->label('Mark as Interested')
                //     ->icon('heroicon-o-eye')
                //     ->color('info')
                //     ->action(fn(Contract $record) => $record->markAsInterested())
                //     ->visible(fn(Contract $record) => $record->isDraft()),

                // Tables\Actions\Action::make('signContract')
                //     ->label('Sign Contract')
                //     ->icon('heroicon-o-document-check')
                //     ->color('warning')
                //     ->action(fn(Contract $record) => $record->sign())
                //     ->visible(fn(Contract $record) => $record->canBeSigned()),

                // Tables\Actions\Action::make('markPaid')
                //     ->label('Mark as Paid')
                //     ->icon('heroicon-o-check-circle')
                //     ->color('success')
                //     ->action(fn(Contract $record) => $record->markAsPaid())
                //     ->visible(fn(Contract $record) => $record->canBeMarkedAsPaid()),

                Tables\Actions\Action::make('updateStatus')
                    ->label('')
                    ->tooltip('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->form(function (Contract $record) {
                        $nextStatuses = $record->getNextPossibleStatuses();

                        return [
                            Forms\Components\Select::make('status')
                                ->label('Select New Status')
                                ->options($nextStatuses)
                                ->required()
                                ->native(false)
                                ->columnSpanFull(),
                        ];
                    })
                    ->action(function (Contract $record, array $data) {
                        $status = $data['status'];

                        switch ($status) {
                            case Contract::STATUS_INTERESTED:
                                $record->markAsInterested();
                                break;
                            case Contract::STATUS_SIGNED_NOT_PAID:
                                $record->sign();
                                break;
                            case Contract::STATUS_SIGNED_PAID:
                                $record->markAsPaid();
                                break;
                        }

                        Notification::make()
                            ->title('Status Updated')
                            ->body("Contract status changed to: " . $record->getStatusDisplayAttribute())
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Update Contract Status')
                    ->modalSubmitActionLabel('Update')
                    ->modalCancelActionLabel('Cancel')
                    ->visible(fn(Contract $record) => !empty($record->getNextPossibleStatuses())),

                Tables\Actions\EditAction::make()
                    ->label('')
                    ->visible(fn(Contract $record) => $record->status === Contract::STATUS_DRAFT),
                // Tables\Actions\DeleteAction::make()
                //     ->before(function (Contract $record) {
                //         if ($record->Stand) {
                //             $record->Stand->update(['status' => 'Available']);
                //         }
                //     }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()
                //         ->before(function ($records) {
                //             foreach ($records as $record) {
                //                 if ($record->Stand) {
                //                     $record->Stand->update(['status' => 'Available']);
                //                 }
                //             }
                //         }),
                // ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('contract_date', 'desc')
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }


    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('All Contracts')
                ->icon('heroicon-o-list-bullet')
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn(): bool => request()->routeIs(static::getRouteBaseName() . '.index')),

            NavigationItem::make('New Contract')
                ->icon('heroicon-o-plus-circle')
                ->url(static::getUrl('create'))
                ->isActiveWhen(fn(): bool => request()->routeIs(static::getRouteBaseName() . '.create'))
                ->badge(function () {
                    // Optional: Show today's count
                    $todayCount = static::getModel()::whereDate('created_at', today())->count();
                    return $todayCount > 0 ? $todayCount : null;
                })
            ,

            // Optional: Add quick filters
            NavigationItem::make('Drafts')
                ->icon('heroicon-o-document')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_DRAFT]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_DRAFT)->count())
            ,

            NavigationItem::make('INT')
                ->icon('heroicon-o-document-check')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_INTERESTED]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_INTERESTED)->count())
            ,
            NavigationItem::make('S&NP')
                ->icon('heroicon-o-document-check')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_SIGNED_NOT_PAID]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_SIGNED_NOT_PAID)->count())
            ,
            NavigationItem::make('S&P')
                ->icon('heroicon-o-document-check')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_SIGNED_PAID]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_SIGNED_PAID)->count())
            ,
        ];
    }
}
