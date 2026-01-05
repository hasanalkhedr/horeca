<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
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
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\Wizard::make([
                    // Step 1: Basic Contract Information
                    Forms\Components\Wizard\Step::make('Basic Information')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Section::make('Contract Details')
                                ->schema([
                                    Forms\Components\Select::make('event_id')
                                        ->label('Event')
                                        ->relationship('Event', 'name')
                                        ->required()
                                        ->reactive()
                                        ->default(function () {
                                            // Get event_id from query parameter if exists
                                            return request()->has('event_id') ? request('event_id') : null;
                                        })
                                        ->disabled(fn(): bool => request()->has('event_id'))
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('stand_id', null);
                                            $set('price_id', null);
                                            $set('report_id', null);
                                            $set('currency_id', null);
                                            $set('sponsor_package_id', null);
                                            $set('category_id', null);
                                        }),

                                    Forms\Components\Select::make('report_id')
                                        ->label('Report')
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
                                        })
                                        ->default(function () {
                                            // Get report type from session flash data
                                            return session('contract_report_id');
                                        })
                                        ->required(),

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
                                ->columns(2),

                            Forms\Components\Section::make('Company & Contacts')
                                ->schema([
                                    // Forms\Components\Select::make('company_id')
                                    //     ->label('Company')
                                    //     ->options(Company::query()->pluck('name', 'id'))
                                    //     ->searchable()
                                    //     ->required()
                                    //     ->reactive(),
                                    // Forms\Components\Select::make('company_id')
                                    //     ->label('Company (from Pipedrive)')
                                    //     ->searchable()
                                    //     ->getSearchResultsUsing(function (string $search) {
                                    //         $pipedriveService = app(\App\Services\PipedriveService::class);
                                    //         $companies = $pipedriveService->searchCompanies($search);

                                    //         // Transform Pipedrive data to option array
                                    //         $options = [];
                                    //         foreach ($companies as $company) {
                                    //             $options[$company['id']] = $company['name'] .
                                    //                 (isset($company['country']) ? ' - ' . $company['country'] : '');
                                    //         }

                                    //         return $options;
                                    //     })
                                    //     ->getOptionLabelUsing(function ($value) {
                                    //         // This ensures selected value shows correctly
                                    //         if (!$value)
                                    //             return null;

                                    //         $pipedriveService = app(\App\Services\PipedriveService::class);
                                    //         $companies = $pipedriveService->getCompanies();

                                    //         foreach ($companies as $company) {
                                    //             if ($company['id'] == $value) {
                                    //                 return $company['name'];
                                    //             }
                                    //         }

                                    //         return null;
                                    //     })
                                    //     ->required()
                                    //     ->reactive()
                                    //     ->hint('Search companies from Pipedrive')
                                    //     ->columnSpan(2),

                                    // Forms\Components\Select::make('exhabition_coordinator')
                                    //     ->label('Exhibition Coordinator')
                                    //     ->options(function (callable $get) {
                                    //         $companyId = $get('company_id');
                                    //         if (!$companyId) {
                                    //             return [];
                                    //         }
                                    //         return Client::where('company_id', $companyId)
                                    //             ->pluck('name', 'id')
                                    //             ->toArray();
                                    //     })
                                    //     ->searchable()
                                    //     ->reactive(),

                                    // Forms\Components\Select::make('contact_person')
                                    //     ->label('Daily Contact Person')
                                    //     ->options(function (callable $get) {
                                    //         $companyId = $get('company_id');
                                    //         if (!$companyId) {
                                    //             return [];
                                    //         }
                                    //         return Client::where('company_id', $companyId)
                                    //             ->pluck('name', 'id')
                                    //             ->toArray();
                                    //     })
                                    //     ->searchable(),
                                    // Forms\Components\Select::make('exhabition_coordinator')
                                    //     ->label('Exhibition Coordinator')
                                    //     ->options(function (callable $get) {
                                    //         $companyId = $get('company_id');
                                    //         if (!$companyId) {
                                    //             return [];
                                    //         }

                                    //         // Fetch persons associated with this organization from Pipedrive
                                    //         $pipedriveService = app(\App\Services\PipedriveService::class);

                                    //         // You may need to add a method to your service to fetch persons by org ID
                                    //         $persons = $pipedriveService->getPersonsByOrganizationId($companyId);

                                    //         $options = [];
                                    //         foreach ($persons as $person) {
                                    //             $options[$person['id']] = $person['name'] .
                                    //                 (isset($person['email']) ? ' (' . $person['email'] . ')' : '');
                                    //         }

                                    //         return $options;
                                    //     })
                                    //     ->searchable()
                                    //     ->reactive(),

                                    // Forms\Components\Select::make('contact_person')
                                    //     ->label('Daily Contact Person')
                                    //     ->options(function (callable $get) {
                                    //         // Same logic as above
                                    //         $companyId = $get('company_id');
                                    //         if (!$companyId) {
                                    //             return [];
                                    //         }

                                    //         $pipedriveService = app(\App\Services\PipedriveService::class);
                                    //         $persons = $pipedriveService->getPersonsByOrganizationId($companyId);

                                    //         $options = [];
                                    //         foreach ($persons as $person) {
                                    //             $options[$person['id']] = $person['name'];
                                    //         }

                                    //         return $options;
                                    //     })
                                    //     ->searchable(),

                                    Livewire::make('client-select')->columnSpanFull(),

                                    Forms\Components\Select::make('seller')
                                        ->label('Sales Person')
                                        ->options(User::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->required(),
                                ])
                                ->collapsible(),
                        ]),

                    // Step 2: Stand & Pricing
                    Forms\Components\Wizard\Step::make('Stand & Pricing')
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
                                            return Stand::where('event_id', $eventId)
                                                ->where(function ($query) use ($get) {
                                                    $query->where('status', 'Available')
                                                        ->orWhere('id', $get('stand_id'));
                                                })
                                                ->get()
                                                ->mapWithKeys(fn($stand) => [
                                                    $stand->id => "{$stand->no} | {$stand->space} sqm"
                                                ])
                                                ->toArray();
                                        })
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
                                ])
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
                                                $amount = $price->Currencies->first()?->pivot->amount ?? 0;
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
                                                ->minValue(0)
                                                ->visible(fn(callable $get): bool => $get('use_special_price'))
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateSpaceAmount($set, $get);
                                                }),
                                        ]),

                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('space_amount')
                                                ->label('Space Amount')
                                                ->numeric()
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
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateSpaceNet($set, $get);
                                                }),

                                            Forms\Components\TextInput::make('space_net')
                                                ->label('Net Amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),
                                        ]),
                                ]),

                            Forms\Components\Section::make('Category')
                                ->schema([
                                    Forms\Components\Select::make('category_id')
                                        ->label('Category')
                                        ->options(function (callable $get) {
                                            $eventId = $get('event_id');
                                            if (!$eventId) {
                                                return [];
                                            }
                                            $event = Event::find($eventId);
                                            return $event->Categories->pluck('name', 'id')->toArray();
                                        })
                                        ->searchable(),
                                ]),
                        ]),

                    // Step 3: Additional Services
                    Forms\Components\Wizard\Step::make('Additional Services')
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
                                        ->prefix(
                                            fn(callable $get): string =>
                                            Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                        )
                                        ->visible(
                                            fn(callable $get): bool =>
                                            $get('if_water') || $get('if_electricity')
                                        )
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            self::calculateTotal($set, $get);
                                        }),
                                ])
                                ->columns(2),

                            // Special Design
                            Forms\Components\Section::make('Special Design')
                                ->schema([
                                    Forms\Components\Textarea::make('special_design_text')
                                        ->label('Design Description')
                                        ->rows(2),

                                    Forms\Components\TextInput::make('special_design_price')
                                        ->label('Design Price per sqm')
                                        ->numeric()
                                        ->minValue(0)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            self::calculateSpecialDesignAmount($set, $get);
                                        }),

                                    Forms\Components\TextInput::make('special_design_amount')
                                        ->label('Total Design Amount')
                                        ->numeric()
                                        ->readOnly()
                                        ->prefix(
                                            fn(callable $get): string =>
                                            Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                        ),
                                ])
                                ->columns(3),

                            // New Product
                            Forms\Components\Section::make('New Product')
                                ->schema([
                                    Forms\Components\Textarea::make('new_product')
                                        ->label('New Product to Launch')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // Step 4: Packages
                    Forms\Components\Wizard\Step::make('Packages')
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
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),

                                            Forms\Components\TextInput::make('sponsor_discount')
                                                ->label('Discount')
                                                ->numeric()
                                                ->minValue(0)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateSponsorNet($set, $get);
                                                }),

                                            Forms\Components\TextInput::make('sponsor_net')
                                                ->label('Net Amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),
                                        ]),
                                ]),

                            // Advertisement Packages
                            Forms\Components\Section::make('Advertisement Packages')
                                ->schema([
                                    Forms\Components\Repeater::make('ads_selections')
                                        ->label('Advertisement Selections')
                                        ->schema([
                                            Forms\Components\Select::make('ads_package_id')
                                                ->label('Package')
                                                ->options(function (callable $get) {
                                                    $eventId = $get('../../../event_id');
                                                    if (!$eventId) {
                                                        return [];
                                                    }
                                                    $event = Event::find($eventId);
                                                    return $event->AdsPackages->pluck('title', 'id')->toArray();
                                                })
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    $set('ads_options', []);
                                                }),

                                            Forms\Components\CheckboxList::make('ads_options')
                                                ->label('Options')
                                                ->options(function (callable $get) {
                                                    $packageId = $get('ads_package_id');
                                                    $currencyId = $get('../../../currency_id');
                                                    if (!$packageId || !$currencyId) {
                                                        return [];
                                                    }

                                                    $package = AdsPackage::with([
                                                        'AdsOptions.Currencies' => function ($query) use ($currencyId) {
                                                            $query->where('currencies.id', $currencyId);
                                                        }
                                                    ])->find($packageId);

                                                    if (!$package)
                                                        return [];

                                                    $options = [];
                                                    foreach ($package->AdsOptions as $option) {
                                                        $price = $option->Currencies
                                                            ->where('id', $currencyId)
                                                            ->first()?->pivot->price ?? 0;
                                                        $options[$option->id] = "{$option->title} | {$price}";
                                                    }
                                                    return $options;
                                                })
                                                ->columns(2)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateAdsAmount($set, $get);
                                                }),
                                        ])
                                        ->collapsible()
                                        ->cloneable()
                                        ->defaultItems(0),

                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('advertisment_amount')
                                                ->label('Total Amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),

                                            Forms\Components\TextInput::make('ads_discount')
                                                ->label('Discount')
                                                ->numeric()
                                                ->minValue(0)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateAdsNet($set, $get);
                                                }),

                                            Forms\Components\TextInput::make('ads_net')
                                                ->label('Net Amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),
                                        ]),
                                ]),

                            // Effective Advertisement Packages
                            Forms\Components\Section::make('Effective Advertisement Packages')
                                ->schema([
                                    Forms\Components\Repeater::make('eff_ads_selections')
                                        ->label('Effective Advertisement Selections')
                                        ->schema([
                                            Forms\Components\Select::make('eff_ads_package_id')
                                                ->label('Package')
                                                ->options(function (callable $get) {
                                                    $eventId = $get('../../../event_id');
                                                    if (!$eventId) {
                                                        return [];
                                                    }
                                                    $event = Event::find($eventId);
                                                    return $event->EffAdsPackages->pluck('title', 'id')->toArray();
                                                })
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    $set('eff_ads_options', []);
                                                }),

                                            Forms\Components\CheckboxList::make('eff_ads_options')
                                                ->label('Options')
                                                ->options(function (callable $get) {
                                                    $packageId = $get('eff_ads_package_id');
                                                    $currencyId = $get('../../../currency_id');
                                                    if (!$packageId || !$currencyId) {
                                                        return [];
                                                    }

                                                    $package = EffAdsPackage::with([
                                                        'EffAdsOptions.Currencies' => function ($query) use ($currencyId) {
                                                            $query->where('currencies.id', $currencyId);
                                                        }
                                                    ])->find($packageId);

                                                    if (!$package)
                                                        return [];

                                                    $options = [];
                                                    foreach ($package->EffAdsOptions as $option) {
                                                        $price = $option->Currencies
                                                            ->where('id', $currencyId)
                                                            ->first()?->pivot->price ?? 0;
                                                        $options[$option->id] = "{$option->title} | {$price}";
                                                    }
                                                    return $options;
                                                })
                                                ->columns(2)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateEffAdsAmount($set, $get);
                                                }),
                                        ])
                                        ->collapsible()
                                        ->cloneable()
                                        ->defaultItems(0),

                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('eff_ads_amount')
                                                ->label('Total Amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),

                                            Forms\Components\TextInput::make('eff_ads_discount')
                                                ->label('Discount')
                                                ->numeric()
                                                ->minValue(0)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    self::calculateEffAdsNet($set, $get);
                                                }),

                                            Forms\Components\TextInput::make('eff_ads_net')
                                                ->label('Net Amount')
                                                ->numeric()
                                                ->readOnly()
                                                ->prefix(
                                                    fn(callable $get): string =>
                                                    Report::find($get('report_id'))?->Currency?->CODE ?? 'USD'
                                                ),
                                        ]),
                                ]),
                        ]),

                    // Step 5: Summary & Notes
                    Forms\Components\Wizard\Step::make('Summary & Notes')
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
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Financial Summary')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
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
                                ]),
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
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn(Contract $record): string => route('contracts.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('markInterested')
                    ->label('Mark as Interested')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(fn(Contract $record) => $record->markAsInterested())
                    ->visible(fn(Contract $record) => $record->isDraft()),

                Tables\Actions\Action::make('signContract')
                    ->label('Sign Contract')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->action(fn(Contract $record) => $record->sign())
                    ->visible(fn(Contract $record) => $record->canBeSigned()),

                Tables\Actions\Action::make('markPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn(Contract $record) => $record->markAsPaid())
                    ->visible(fn(Contract $record) => $record->canBeMarkedAsPaid()),
                // Tables\Actions\Action::make('upload_pdf')
                //     ->label('Upload PDF')
                //     ->icon('heroicon-o-document-arrow-up')
                //     ->color('warning')
                //     ->modalHeading('Upload Contract PDF')
                //     ->modalSubmitActionLabel('Upload')
                //     ->form([
                //         Forms\Components\FileUpload::make('file')
                //             ->label('PDF File')
                //             ->acceptedFileTypes(['application/pdf'])
                //             ->required()
                //             ->maxSize(5120), // 5MB
                //     ])
                //     ->action(function (Contract $record, array $data) {
                //         $path = $data['file']->storeAs(
                //             'uploads/contracts',
                //             $record->contract_no . '-' . $record->Event->CODE . '.pdf'
                //         );
                //         $record->update(['path' => $path]);
                //     }),

                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
}
