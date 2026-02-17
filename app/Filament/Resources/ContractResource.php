<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\ContractResource\Pages;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Report;
use App\Models\Stand;
use App\Models\User;
use App\Models\Company;
use App\Models\Client;
use App\Filament\Helpers\ContractCalculations;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class ContractResource extends Resource
{
    use ContractCalculations;

    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contracts';
    protected static ?int $navigationSort = 5;

    /**
     * Cache key for form data
     */
    protected static function getFormDataCacheKey($eventId, $reportId, $currencyId = null): string
    {
        $key = "contract_form_data_{$eventId}_{$reportId}";
        if ($currencyId) {
            $key .= "_{$currencyId}";
        }
        return $key;
    }

    /**
     * Load all form data in a single query
     */
    protected static function loadFormData(callable $get, bool $flag = false): array
    {
        $eventId = !$flag ? $get('event_id') : $get('../../event_id');
        $reportId = !$flag ? $get('report_id') : $get('../../report_id');
        $currencyId = !$flag ? $get('currency_id') : $get('../../currency_id');

        if (!$eventId || !$reportId) {
            return [];
        }

        return Cache::remember(
            self::getFormDataCacheKey($eventId, $reportId, $currencyId),
            60, // 1 minute
            function () use ($eventId, $reportId, $currencyId) {
                // Load event with all related data in a single query
                $event = Event::with([
                    'Categories' => fn($q) => $q->select(['id', 'name']),
                    'Stands' => function ($q) {
                    $q->select([
                        'id',
                        'no',
                        'space',
                        'status',
                        'event_id',
                        'category_id',
                        'deductable',
                        'is_merged',
                        'parent_stand_id',
                    ]);
                },
                    'Prices.Currencies' => function ($q) use ($currencyId) {
                    $q->when($currencyId, fn($q) => $q->where('currencies.id', $currencyId));
                },
                    'SponsorPackages.Currencies' => function ($q) use ($currencyId) {
                    $q->when($currencyId, fn($q) => $q->where('currencies.id', $currencyId));
                },
                    'AdsPackages.AdsOptions.Currencies' => function ($q) use ($currencyId) {
                    $q->when($currencyId, fn($q) => $q->where('currencies.id', $currencyId));
                },
                    'EffAdsPackages.EffAdsOptions.Currencies' => function ($q) use ($currencyId) {
                    $q->when($currencyId, fn($q) => $q->where('currencies.id', $currencyId));
                },
                ])->find($eventId);

                // Load report with currency
                $report = Report::with('currency')
                    ->find($reportId);

                return [
                    'event' => $event,
                    'report' => $report,
                    'categories' => $event?->Categories ?? collect(),
                    'stands' => $event?->Stands ?? collect(),
                    'prices' => $event?->Prices ?? collect(),
                    'sponsorPackages' => $event?->SponsorPackages ?? collect(),
                    'adsPackages' => $event?->AdsPackages ?? collect(),
                    'effAdsPackages' => $event?->EffAdsPackages ?? collect(),
                    'currencyCode' => $report?->currency?->CODE ?? 'USD',
                    'vatRate' => $event?->vat_rate ?? 0,
                    'components' => $report?->components ?? [],
                ];
            }
        );
    }

    /**
     * Get currency code with fallback
     */
    protected static function getCurrencyCode(callable $get, array $formData = null): string
    {
        if ($formData) {
            return $formData['currencyCode'];
        }

        return Report::find($get('report_id'))?->currency?->CODE ?? 'USD';
    }

    /**
     * Clear form data cache when event or report changes
     */
    protected static function clearFormCache($eventId, $reportId): void
    {
        Cache::forget(self::getFormDataCacheKey($eventId, $reportId));
        Cache::forget(self::getFormDataCacheKey($eventId, $reportId, null));
    }
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

                                        // Clear cache when event changes
                                        if ($state) {
                                            $currentReportId = request()->input('report_id');
                                            if ($currentReportId) {
                                                self::clearFormCache($state, $currentReportId);
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('report_id')
                                    ->label('Contract Template')
                                    ->options(function (callable $get) {
                                        $eventId = $get('event_id');
                                        if (!$eventId) {
                                            return [];
                                        }

                                        // Cache this query
                                        return Cache::remember("reports_event_{$eventId}", 300, function () use ($eventId) {
                                            return Report::where('event_id', $eventId)
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        });
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $formData = self::loadFormData($get);
                                        $set('currency_id', $formData['report']?->currency_id ?? null);

                                        // Clear cache when report changes
                                        $eventId = $get('event_id');
                                        if ($eventId && $state) {
                                            self::clearFormCache($eventId, $state);
                                        }
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

                                        return Cache::remember("company_search_{$search}", 300, function () use ($search) {
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
                                        });
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        return Cache::remember("company_label_{$value}", 300, function () use ($value) {
                                            return Company::find($value)?->name;
                                        });
                                    })
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (!$state) {
                                            $set('exhabition_coordinator', null);
                                            $set('contact_person', null);
                                            return;
                                        }

                                        $company = Cache::remember("company_pipe_{$state}", 300, function () use ($state) {
                                            return Company::where('pipe_id', $state)->first();
                                        });

                                        if (!$company) {
                                            $company = self::createCompanyFromPipedrive($state);
                                            Cache::forget("company_pipe_{$state}");
                                            Cache::forget("company_label_{$company->id}");
                                        }

                                        $set('company_id', $company->id);
                                        $set('exhabition_coordinator', null);
                                        $set('contact_person', null);
                                    })
                                    ->required(),

                                Forms\Components\Select::make('exhabition_coordinator')
                                    ->label('Exhibition Coordinator')
                                    ->options(
                                        fn(Get $get) => Cache::remember(
                                            "clients_company_{$get('company_id')}",
                                            300,
                                            fn() => Client::where('company_id', $get('company_id'))
                                                ->pluck('name', 'id')
                                        )
                                    )
                                    ->disabled(fn(Get $get) => !$get('company_id')),

                                Forms\Components\Select::make('contact_person')
                                    ->label('Daily Contact Person')
                                    ->options(
                                        fn(Get $get) => Cache::remember(
                                            "clients_company_{$get('company_id')}",
                                            300,
                                            fn() => Client::where('company_id', $get('company_id'))
                                                ->pluck('name', 'id')
                                        )
                                    )
                                    ->disabled(fn(Get $get) => !$get('company_id')),

                                Forms\Components\Select::make('seller')
                                    ->label('Sales Person')
                                    ->options(function () {
                                        return Cache::remember('all_users', 300, function () {
                                            return User::all()->pluck('name', 'id');
                                        });
                                    })
                                    ->searchable()
                                    ->required(),
                            ])->columns(4)
                            ->collapsible(),

                        Forms\Components\Section::make('Category')
                            ->schema([
                                Forms\Components\Radio::make('category_id')
                                    ->label('Category')
                                    ->options(function (callable $get) {
                                        $formData = self::loadFormData($get);
                                        return $formData['categories']->pluck('name', 'id')->toArray();
                                    })
                                    ->columns(4)
                                    ->required(false),
                            ])->collapsible(),
                    ])->collapsible()
                    ->visible(function ($get): bool {
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
                                // Toggle between single stand and merge mode
                                Forms\Components\Toggle::make('enable_merge_mode')
                                    ->label('Merge Multiple Stands')
                                    ->helperText('Enable to merge multiple stands into one')
                                    ->reactive()
                                    ->default(false)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // When enabling merge mode, clear single stand selection
                                            //$set('stand_id', null);
                                            $set('merge_stands', []);
                                            $set('merged_stand_id', null);
                                        } else {
                                            // When disabling merge mode, clear merge selections
                                            $set('merge_stands', []);
                                            $set('merged_stand_id', null);
                                            $set('suggested_merge_no', '');
                                        }
                                    })
                                    ->columnSpanFull(),

                                // Single stand selection (disabled when merge mode is enabled)
                                Forms\Components\Select::make('stand_id')
                                    ->label('Stand')
                                    ->options(function (callable $get) {
                                        $formData = self::loadFormData($get);
                                        $currentStandId = $get('stand_id');
                                        $mergedStandId = $get('merged_stand_id') ?? null;

                                        return $formData['stands']
                                            ->filter(function ($stand) use ($currentStandId, $mergedStandId) {
                                                // Show available stands or the current selected stand
                                                $isAvailable = $stand->status === 'Available';
                                                $isCurrent = $stand->id == $currentStandId;
                                                $isMergedSelected = $stand->id == $mergedStandId;

                                                return $isAvailable || $isCurrent || $isMergedSelected;
                                            })
                                            ->mapWithKeys(fn($stand) => [
                                                $stand->id => $stand->is_merged
                                                    ? "📦 {$stand->no} | {$stand->space} sqm (Merged)"
                                                    : "📍 {$stand->no} | {$stand->space} sqm"
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn(callable $get) => $get('enable_merge_mode'))
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::calculateSpaceAmount($set, $get);

                                        // Clear merge selection when selecting a stand directly
                                        if ($state) {
                                            $set('merge_stands', []);
                                            $set('merged_stand_id', null);
                                        }
                                    })
                                    ->visible(fn($state, callable $get) => (!$get('enable_merge_mode') && $get('show_after_merge')) || ($state)),
                                Hidden::make('show_after_merge')->default(true)->dehydrated(),
                                // MERGE MODE SECTION (only visible when merge mode is enabled)
                                Forms\Components\Placeholder::make('merge_mode_header')
                                    ->label('Merge Mode Active')
                                    ->content('You are now in merge mode. Add stands below to merge them into one.')
                                    ->extraAttributes(['class' => 'font-medium text-green-700 bg-green-50 p-3 rounded-md'])
                                    ->columnSpanFull()
                                    ->visible(fn(callable $get) => $get('enable_merge_mode') && $get('event_id') && $get('report_id')),

                                // Hidden fields for merge tracking
                                Forms\Components\Hidden::make('merged_stand_id')
                                    ->dehydrated(false),

                                // Merge functionality buttons (only in merge mode)
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('addStandForMerge')
                                        ->label('+ Add Stand for Merging')
                                        ->icon('heroicon-o-plus')
                                        ->color('gray')
                                        ->size('sm')
                                        ->visible(fn(callable $get) => $get('enable_merge_mode') && $get('event_id') && $get('report_id'))
                                        ->action(function (callable $get, callable $set) {
                                            $mergeStands = $get('merge_stands') ?? [];
                                            $mergeStands[] = ['stand_id' => null];
                                            $set('merge_stands', $mergeStands);
                                        }),

                                    Forms\Components\Actions\Action::make('clearAllMergeStands')
                                        ->label('Clear All Stands')
                                        ->icon('heroicon-o-x-mark')
                                        ->color('danger')
                                        ->size('sm')
                                        ->visible(fn(callable $get) => $get('enable_merge_mode') && !empty($get('merge_stands')))
                                        ->action(function (callable $set) {
                                            $set('merge_stands', []);
                                            $set('merged_stand_id', null);
                                            $set('suggested_merge_no', '');
                                        }),
                                ])->columnSpanFull()
                                    ->visible(fn(callable $get) => $get('enable_merge_mode')),

                                // Dynamic stand selections for merging (only in merge mode)
                                Forms\Components\Repeater::make('merge_stands')
                                    ->label('Stands to Merge')
                                    ->schema([
                                        Forms\Components\Select::make('stand_id')
                                            ->label('Stand')
                                            ->options(function (callable $get, $state) {
                                                $formData = self::loadFormData($get, true);
                                                if (!$formData['stands']) {
                                                    return [];
                                                }

                                                // Get all selected stand IDs from the repeater
                                                $allMergeStands = $get('../../merge_stands') ?? [];
                                                $selectedIds = collect($allMergeStands)
                                                    ->pluck('stand_id')
                                                    ->filter()
                                                    ->toArray();

                                                return $formData['stands']
                                                    ->filter(function ($stand) use ($selectedIds, $state) {
                                                        // Include the current value even if selected elsewhere
                                                        if ($stand->id == $state) {
                                                            return true;
                                                        }

                                                        // Only show available, non-merged stands
                                                        return $stand->status === 'Available'
                                                            && !$stand->is_merged
                                                            && !$stand->parent_stand_id
                                                            && !in_array($stand->id, $selectedIds);
                                                    })
                                                    ->mapWithKeys(fn($stand) => [
                                                        $stand->id => "Stand #{$stand->no} ({$stand->space} sqm)"
                                                    ]);
                                            })
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Recalculate total space when stands change
                                                $mergeStands = $get('../../merge_stands') ?? [];
                                                $totalSpace = 0;
                                                $standNumbers = [];

                                                foreach ($mergeStands as $item) {
                                                    if (!empty($item['stand_id'])) {
                                                        $stand = Stand::find($item['stand_id']);
                                                        if ($stand) {
                                                            $totalSpace += $stand->space;
                                                            $standNumbers[] = $stand->no;
                                                        }
                                                    }
                                                }

                                                // Generate suggested merged stand number
                                                if (count($standNumbers) > 0) {
                                                    sort($standNumbers);
                                                    $set('../../suggested_merge_no', implode('-', $standNumbers) . '-M');
                                                }
                                            }),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Add Another Stand')
                                    ->reorderable(false)
                                    ->deleteAction(
                                        fn($action) => $action->requiresConfirmation()
                                    )
                                    ->columnSpanFull()
                                    ->hidden(fn(callable $get) => !$get('enable_merge_mode')),

                                // Merge information (only in merge mode)
                                Forms\Components\Placeholder::make('merge_info')
                                    ->label('Merge Information')
                                    ->content(function (callable $get) {
                                        $mergeStands = $get('merge_stands') ?? [];
                                        $standIds = collect($mergeStands)
                                            ->pluck('stand_id')
                                            ->filter()
                                            ->unique()
                                            ->toArray();

                                        if (count($standIds) < 2) {
                                            return 'Select at least 2 stands to merge';
                                        }

                                        $stands = Stand::whereIn('id', $standIds)->get();
                                        $firstStand = $stands->first();
                                        $totalSpace = $stands->sum('space');
                                        $standNumbers = $stands->pluck('no')->sort()->values();
                                        $suggestedNo = $get('suggested_merge_no') ?? $standNumbers->implode('-') . '-M';

                                        $info = "✅ Selected stands: " . $standNumbers->implode(', ') . "\n";
                                        $info .= "📏 Total space: {$totalSpace} sqm\n";
                                        $info .= "🎯 First stand (#{$firstStand->no}) will become the parent\n";
                                        $info .= "🔢 New stand number: {$suggestedNo}";

                                        return $info;
                                    })
                                    ->columnSpanFull()
                                    ->visible(fn(callable $get) => $get('enable_merge_mode') && count($get('merge_stands') ?? []) >= 2),

                                // Merge options (only in merge mode)
                                Forms\Components\Fieldset::make('Merge Options')
                                    ->schema([
                                        Forms\Components\TextInput::make('suggested_merge_no')
                                            ->label('New Stand Number')
                                            ->required()
                                            ->default(function (callable $get) {
                                                $mergeStands = $get('merge_stands') ?? [];
                                                $standIds = collect($mergeStands)
                                                    ->pluck('stand_id')
                                                    ->filter()
                                                    ->unique()
                                                    ->toArray();

                                                if (count($standIds) >= 1) {
                                                    $stands = Stand::whereIn('id', $standIds)->get();
                                                    $standNumbers = $stands->pluck('no')->sort()->values();
                                                    return $standNumbers->implode('-') . '-M';
                                                    // }
                                                }

                                                return '';
                                            })
                                            ->helperText('Enter the stand number for the merged stand')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->visible(fn(callable $get) => $get('enable_merge_mode') && count($get('merge_stands') ?? []) >= 2),

                                // Merge action button (only in merge mode)
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('mergeStands')
                                        ->label('Merge Selected Stands')
                                        ->icon('heroicon-o-arrows-right-left')
                                        ->color('primary')
                                        ->size('sm')
                                        ->visible(fn(callable $get) => $get('enable_merge_mode') && count($get('merge_stands') ?? []) >= 2)
                                        ->requiresConfirmation()
                                        ->modalHeading('Merge Stands')
                                        ->modalDescription(function (callable $get) {
                                            $mergeStands = $get('merge_stands') ?? [];
                                            $standIds = collect($mergeStands)
                                                ->pluck('stand_id')
                                                ->filter()
                                                ->unique()
                                                ->toArray();

                                            $stands = Stand::whereIn('id', $standIds)->get();
                                            $standList = $stands->pluck('no')->sort()->implode(', ');
                                            $totalSpace = $stands->sum('space');
                                            $suggestedNo = $get('suggested_merge_no');
                                            $firstStand = $stands->first();

                                            $description = "You are about to merge the following stands:\n";
                                            $description .= "Stands: {$standList}\n";
                                            $description .= "Total space: {$totalSpace} sqm\n";
                                            $description .= "First stand (#{$firstStand->no}) will become the parent\n";
                                            $description .= "New stand number: {$suggestedNo}\n\n";
                                            $description .= "This action cannot be undone.";

                                            return $description;
                                        })
                                        ->modalSubmitActionLabel('Merge Stands')
                                        ->modalCancelActionLabel('Cancel')
                                        ->action(function (callable $get, callable $set, array $data) {
                                            $mergeStands = $get('merge_stands') ?? [];
                                            $standIds = collect($mergeStands)
                                                ->pluck('stand_id')
                                                ->filter()
                                                ->unique()
                                                ->toArray();

                                            if (count($standIds) < 2) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('Please select at least 2 stands to merge')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            // Get the stands from database
                                            $stands = Stand::whereIn('id', $standIds)->get();

                                            // Sort stands by selection order (maintain first stand)
                                            $firstStandId = $standIds[0];
                                            $firstStand = $stands->firstWhere('id', $firstStandId);
                                            $otherStands = $stands->where('id', '!=', $firstStandId);

                                            // Check if all stands are available and not merged
                                            foreach ($stands as $stand) {
                                                if ($stand->status !== 'Available') {
                                                    Notification::make()
                                                        ->title('Error')
                                                        ->body("Stand #{$stand->no} is not available. Current status: {$stand->status}")
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                if ($stand->is_merged || $stand->parent_stand_id) {
                                                    Notification::make()
                                                        ->title('Error')
                                                        ->body("Stand #{$stand->no} is already part of a merge group")
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }
                                            }

                                            // Get event_id from form
                                            $eventId = $get('event_id');
                                            $reportId = $get('report_id');

                                            if (!$eventId || !$reportId) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('Please select an event and contract template first')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            // Get event to access category_id
                                            $event = Event::find($eventId);
                                            if (!$event) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('Event not found')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            // Calculate total space
                                            $totalSpace = $stands->sum('space');

                                            // Use provided stand number
                                            $newStandNo = $get('suggested_merge_no');

                                            // Check if stand number already exists
                                            $existingStand = Stand::where('event_id', $eventId)
                                                ->where('no', $newStandNo)
                                                ->first();

                                            if ($existingStand) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body("Stand number {$newStandNo} already exists in this event")
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            $parentStand = null;

                                            // Use first stand as parent (same logic as Stand resource)
                                            $parentStand = $firstStand;
                                            $parentStand->update([
                                                'no' => $newStandNo,
                                                'space' => $totalSpace,
                                                'is_merged' => true,
                                                'original_no' => $firstStand->no,
                                            ]);

                                            // Mark other stands as merged children
                                            foreach ($otherStands as $stand) {
                                                $stand->update([
                                                    'parent_stand_id' => $parentStand->id,
                                                    'is_merged' => true,
                                                    'status' => 'Available',
                                                ]);
                                            }

                                            // IMPORTANT: Set the merged stand as selected and disable merge mode
                                            $set('merged_stand_id', $parentStand->id);
                                            $set('stand_id', $parentStand->id); // This selects it in the dropdown
                                            $set('enable_merge_mode', false); // Disable merge mode after successful merge

                                            // Clear merge selections
                                            $set('merge_stands', []);
                                            $set('suggested_merge_no', '');

                                            $set('show_after_merge', false);
                                            // Clear form cache
                                            self::clearFormCache($eventId, $reportId);

                                            // Trigger space calculation with the new stand
                                            self::calculateSpaceAmount($set, $get);

                                            Notification::make()
                                                ->title('Stands Merged Successfully')
                                                ->body("Successfully merged " . count($stands) . " stands into #{$newStandNo}")
                                                ->success()
                                                ->send();
                                        }),
                                ])->columnSpanFull()
                                    ->visible(fn(callable $get) => $get('enable_merge_mode') && count($get('merge_stands') ?? []) >= 2),

                                // Show selected merged stand info after merge
                                Forms\Components\Placeholder::make('merged_stand_result')
                                    ->label('Merged Stand Selected')
                                    ->content(function (callable $get) {
                                        $mergedStandId = $get('merged_stand_id');
                                        if (!$mergedStandId) {
                                            return '';
                                        }

                                        $stand = Stand::find($mergedStandId);
                                        if (!$stand) {
                                            return '';
                                        }

                                        $childStands = $stand->getAllMergeGroupStands();
                                        $childList = $childStands->where('id', '!=', $stand->id)
                                            ->pluck('no')
                                            ->implode(', ');

                                        return "✅ Merged Stand #{$stand->no} selected\n" .
                                            "📏 Total Space: {$stand->space} sqm\n" .
                                            "🔗 Includes: {$childList}";
                                    })
                                    ->extraAttributes(['class' => 'bg-green-50 p-3 rounded-md'])
                                    ->columnSpanFull()
                                    ->visible(fn(callable $get) => !$get('enable_merge_mode') && !empty($get('merged_stand_id'))),

                                Section::make('Free Space')->schema([
                                    Forms\Components\TextInput::make('free_space')
                                        ->label('Free SQM')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->reactive()
                                        ->debounce(500)
                                        ->maxValue(function (callable $get) {
                                            $stand = Stand::find($get('stand_id'));
                                            return $stand?->space ?? 0;
                                        })
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            self::calculateSpaceAmount($set, $get);
                                        }),
                                ]),
                            ])
                            ->collapsible()
                            ->columns(2)
                            ->visible(function ($get): bool {
                                $eventId = $get('event_id');
                                $reportId = $get('report_id');
                                return $eventId && $reportId;
                            }),


                        Forms\Components\Section::make('Pricing')
                            ->schema([
                                Section::make([
                                    Forms\Components\Radio::make('price_id')
                                        ->label('Select Price Package')
                                        ->options(function (callable $get) {
                                            $formData = self::loadFormData($get);
                                            $currencyId = $get('currency_id');

                                            if (!$currencyId)
                                                return [];

                                            $options = [];
                                            foreach ($formData['prices'] as $price) {
                                                $amount = $price->currencies
                                                    ->firstWhere('id', $currencyId)?->pivot->amount ?? 0;
                                                $options[$price->id] = "{$price->name} | Min: {$amount}";
                                            }
                                            return $options;
                                        })
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            self::calculateSpaceAmount($set, $get);
                                        }),

                                    Forms\Components\TextInput::make('price_amount')
                                        ->label('SQM Price Amount')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->reactive()
                                        ->debounce(500)
                                        ->rules([
                                            function ($get) {
                                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                                    $eventId = $get('event_id');
                                                    $priceID = $get('price_id');
                                                    if (!$eventId || !$priceID) {
                                                        return;
                                                    }

                                                    $event = Event::find($eventId);
                                                    if (!$event) {
                                                        return;
                                                    }

                                                    $price = self::getPriceWithCurrency($priceID, $get('currency_id'));

                                                    if ($price && $value > 0 && $value < $price->Currencies->first()?->pivot->amount) {
                                                        $fail("Price must be at least {$price->Currencies->first()?->pivot->amount}");
                                                    }
                                                };
                                            },
                                        ])
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            self::calculateSpaceAmount($set, $get);
                                        }),

                                    Placeholder::make('min_price_alert')
                                        ->content(function (callable $get) {
                                            $event = Event::find($get('event_id'));
                                            $price = $event?->Prices()->where('prices.id', $get('price_id'))->first();
                                            $minPrice = $price?->Currencies->first()?->pivot->amount ?? 0;
                                            $currentPrice = (float) $get('price_amount');

                                            return "Price ($currentPrice) is below minimum required price ($minPrice)";
                                        })
                                        ->visible(function (callable $get) {
                                           $price = self::getPriceWithCurrency($get('price_id'), $get('currency_id'));
                                            if (!$price)
                                                return false;

                                            $currentPrice = (float) $get('price_amount');
                                            $minPrice = (float) $price->Currencies->first()?->pivot->amount;

                                            return $currentPrice > 0 && $currentPrice < $minPrice;
                                        })
                                        ->extraAttributes([
                                            'class' => 'border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20',
                                        ]),
                                ])->columns(2),

                                // Tax per sqm section
                                Forms\Components\Fieldset::make('Additional Taxes')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_tax_per_sqm')
                                            ->label('Enable Tax per Square Meter')
                                            ->reactive()
                                            ->default(false)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (!$state) {
                                                    $set('tax_per_sqm_amount', 0);
                                                }
                                                self::calculateSpaceAmount($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('tax_per_sqm_amount')
                                            ->label('Tax Amount per Sqm')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->visible(fn(callable $get): bool => $get('enable_tax_per_sqm'))
                                            ->prefix(
                                                fn(callable $get): string =>
                                                self::getCurrencyCode($get)
                                            )
                                            ->reactive()
                                            ->debounce(500)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                self::calculateSpaceAmount($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('tax_per_sqm_total')
                                            ->label('Total Tax Amount')
                                            ->readOnly()
                                           ->visible(fn(callable $get): bool => $get('enable_tax_per_sqm')),
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
                                                self::getCurrencyCode($get)
                                            ),

                                        Forms\Components\TextInput::make('space_discount')
                                            ->label('Discount')
                                            ->numeric()
                                            ->minValue(0)
                                            ->reactive()
                                            ->debounce(500)
                                            ->default(0)
                                            ->rules([
                                                function ($get) {
                                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                                        $eventId = $get('event_id');
                                                        $currencyId = $get('currency_id');
                                                        if (!$eventId || !$currencyId) {
                                                            return;
                                                        }

                                                        $event = Event::find($eventId);
                                                        if (!$event) {
                                                            return;
                                                        }

                                                        $price = self::getPriceWithCurrency($get('price_id'), $currencyId);

                                                        $minPrice = (float) $price->Currencies->first()?->pivot->amount;
                                                        $space = Stand::find($get('stand_id'))?->space - $get('free_space');
                                                        $totalSpaceAmount = $get('space_amount');
                                                        if ($price && $value > 0 && $totalSpaceAmount - $value < $minPrice * $space) {
                                                            $fail("With this discount,SQM Price becomes below minimum required price ($minPrice). \nSQM Price must be at least {$minPrice}");
                                                        }
                                                    };
                                                },
                                            ])
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
                                                self::getCurrencyCode($get)
                                            ),
                                        Placeholder::make('discount_alert')
                                            ->content(function (callable $get) {
                                                $price = self::getPriceWithCurrency($get('price_id'), $get('currency_id'));
                                                if (!$price)
                                                    return false;

                                                $minPrice = (float) $price->Currencies->first()?->pivot->amount;

                                                return "With this discount,SQM Price becomes below minimum required price ($minPrice)";
                                            })
                                            ->visible(function (callable $get) {
                                                $price = self::getPriceWithCurrency($get('price_id'), $get('currency_id'));
                                                if (!$price)
                                                    return false;
                                                $minPrice = (float) $price->Currencies->first()?->pivot->amount;
                                                $space = Stand::find($get('stand_id'))?->space - $get('free_space');
                                                $totalSpaceAmount = $get('space_amount');
                                                $discount = $get('space_discount');
                                                return ($totalSpaceAmount - $discount) > 0 && ($totalSpaceAmount - $discount) < $space * $minPrice;
                                            })
                                            ->extraAttributes([
                                                'class' => 'border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20',
                                            ]),
                                    ]),
                            ])->collapsible(),
                    ])->collapsible()
                    ->collapsed()
                    ->visible(function ($get): bool {
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
                                        self::getCurrencyCode($get)
                                    )
                                    ->visible(
                                        fn(callable $get): bool =>
                                        $get('if_water') || $get('if_electricity')
                                    )
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        self::calculateTotal($set, $get);
                                    }),
                            ])->collapsible()
                            ->columns(2)
                            ->visible(function (callable $get) {
                                $formData = self::loadFormData($get);
                                return in_array('water-section', $formData['components']);
                            }),

                        // New Product
                        Forms\Components\Section::make('New Product')
                            ->schema([
                                Forms\Components\Textarea::make('new_product')
                                    ->label('New Product to Launch')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])->visible(function (callable $get) {
                                $formData = self::loadFormData($get);
                                return in_array('new-product-section', $formData['components']);
                            }),
                    ])->collapsible()->collapsed()
                    ->visible(function ($get): bool {
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
                                        $formData = self::loadFormData($get);
                                        $currencyId = $get('currency_id');

                                        if (!$currencyId)
                                            return [];

                                        $options = [];
                                        foreach ($formData['sponsorPackages'] as $package) {
                                            $price = $package->currencies
                                                ->firstWhere('id', $currencyId)?->pivot->total_price ?? 0;
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
                                                self::getCurrencyCode($get)
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
                                                self::getCurrencyCode($get)
                                            ),
                                    ]),
                            ])->collapsible()
                            ->visible(function (callable $get) {
                                $formData = self::loadFormData($get);
                                return in_array('sponsor-section', $formData['components']);
                            }),

                        // Advertisement Packages
                        Forms\Components\Section::make('Advertisement Packages')
                            ->schema([
                                Forms\Components\Hidden::make('ads_check')
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->default([]),

                                Forms\Components\CheckboxList::make('ads_options_display')
                                    ->label('Advertisement Options')
                                    ->options(function (callable $get) {
                                        $formData = self::loadFormData($get);
                                        $currencyId = $get('currency_id');

                                        if (!$currencyId)
                                            return [];

                                        $options = [];
                                        foreach ($formData['adsPackages'] as $package) {
                                            foreach ($package->adsOptions as $option) {
                                                $price = $option->currencies
                                                    ->firstWhere('id', $currencyId)?->pivot->price ?? 0;
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
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record && $record->ads_check) {
                                            $component->state($record->ads_check);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('ads_check', $state ?? []);
                                        self::calculateAdsAmount($set, $get, $state ?? []);
                                    }),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('advertisment_amount')
                                            ->label('Total Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                self::getCurrencyCode($get)
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
                                                self::getCurrencyCode($get)
                                            ),
                                    ]),
                            ])
                            ->collapsible()
                            ->visible(function (callable $get) {
                                $formData = self::loadFormData($get);
                                return in_array('advertisement-section', $formData['components']);
                            }),

                        // Effective Advertisement Packages
                        Forms\Components\Section::make('Effective Advertisement Packages')
                            ->schema([
                                Forms\Components\Hidden::make('eff_ads_check')
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->default([]),

                                Forms\Components\CheckboxList::make('eff_ads_options_display')
                                    ->label('Effective Advertisement Options')
                                    ->options(function (callable $get) {
                                        $formData = self::loadFormData($get);
                                        $currencyId = $get('currency_id');

                                        if (!$currencyId)
                                            return [];

                                        $options = [];
                                        foreach ($formData['effAdsPackages'] as $package) {
                                            foreach ($package->effAdsOptions as $option) {
                                                $price = $option->currencies
                                                    ->firstWhere('id', $currencyId)?->pivot->price ?? 0;
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
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record && $record->eff_ads_check) {
                                            $component->state($record->eff_ads_check);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('eff_ads_check', $state ?? []);
                                        self::calculateEffAdsAmount($set, $get, $state ?? []);
                                    }),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('eff_ads_amount')
                                            ->label('Total Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->readOnly()
                                            ->prefix(
                                                fn(callable $get): string =>
                                                self::getCurrencyCode($get)
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
                                                self::getCurrencyCode($get)
                                            ),
                                    ]),
                            ])
                            ->collapsible()
                            ->visible(function (callable $get) {
                                $formData = self::loadFormData($get);
                                return in_array('effective-advertisement-section', $formData['components']);
                            }),
                    ])->collapsible()->collapsed()
                    ->visible(function ($get): bool {
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
                                $formData = self::loadFormData($get);
                                return in_array('notes-section', $formData['components']);
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
                                                $formData = self::loadFormData($get);
                                                return number_format($get('sub_total_1') ?? 0, 2) . ' ' .
                                                    $formData['currencyCode'];
                                            }),

                                        Forms\Components\Placeholder::make('d_i_a_display')
                                            ->label('Discount (D/I/A)')
                                            ->content(function (callable $get) {
                                                $formData = self::loadFormData($get);
                                                return number_format($get('d_i_a') ?? 0, 2) . ' ' .
                                                    $formData['currencyCode'];
                                            }),

                                        Forms\Components\Placeholder::make('sub_total_2_display')
                                            ->label('Sub Total 2 (Net)')
                                            ->content(function (callable $get) {
                                                $formData = self::loadFormData($get);
                                                return number_format($get('sub_total_2') ?? 0, 2) . ' ' .
                                                    $formData['currencyCode'];
                                            }),
                                        Forms\Components\Placeholder::make('tax_per_sqm_display')
                                            ->label('Tax per Sqm Total')
                                            ->content(function (callable $get) {
                                                $taxPerSqmTotal = $get('tax_per_sqm_total') ?? 0;
                                                $formData = self::loadFormData($get);
                                                return number_format($taxPerSqmTotal, 2) . ' ' . $formData['currencyCode'];
                                            })
                                            ->visible(fn(callable $get) => ($get('tax_per_sqm_total') ?? 0) > 0),
                                        Forms\Components\Placeholder::make('vat_amount_display')
                                            ->label('VAT Amount')
                                            ->content(function (callable $get) {
                                                $formData = self::loadFormData($get);
                                                return number_format($get('vat_amount') ?? 0, 2) . ' ' .
                                                    $formData['currencyCode'] . " ({$formData['vatRate']}%)";
                                            }),

                                        Forms\Components\Placeholder::make('net_total_display')
                                            ->label('Net Total')
                                            ->content(function (callable $get) {
                                                $formData = self::loadFormData($get);
                                                return number_format($get('net_total') ?? 0, 2) . ' ' .
                                                    $formData['currencyCode'];
                                            })
                                            ->extraAttributes(['class' => 'text-xl font-bold']),
                                    ]),
                            ])->collapsible(),
                    ])->collapsible()
                    ->visible(function ($get): bool {
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
                    ->visible(fn(Contract $record) => $record->status !== Contract::STATUS_SIGNED_PAID),
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
            ->headerActions([
                FilamentExportHeaderAction::make('export')->label('Export Contracts')
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
                ->icon('heroicon-s-list-bullet')
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn(): bool => request()->routeIs(static::getRouteBaseName() . '.index')),

            NavigationItem::make('New Contract')
                ->icon('heroicon-s-plus-circle')
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
                ->icon('heroicon-s-document')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_DRAFT]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_DRAFT)->count())
            ,

            NavigationItem::make('INT')
                ->icon('heroicon-s-document-arrow-up')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_INTERESTED]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_INTERESTED)->count())
            ,
            NavigationItem::make('S&NP')
                ->icon('heroicon-s-document-check')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_SIGNED_NOT_PAID]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_SIGNED_NOT_PAID)->count())
            ,
            NavigationItem::make('S&P')
                ->icon('heroicon-s-document-currency-dollar')
                ->url(static::getUrl('index', ['tableFilters[status][value]' => Contract::STATUS_SIGNED_PAID]))
                ->badge(fn() => static::getModel()::where('status', Contract::STATUS_SIGNED_PAID)->count())
            ,
        ];
    }




    // private function processContractData(array $data): array
    // {
    //     // Process stand selection
    //     $data = $this->processStandSelection($data);

    //     // Ensure tax per sqm fields are properly set
    //     $enableTaxPerSqm = $data['enable_tax_per_sqm'] ?? false;
    //     if (!$enableTaxPerSqm) {
    //         $data['tax_per_sqm_amount'] = 0;
    //         $data['tax_per_sqm_total'] = 0;
    //     }

    //     // Calculate base space amount if not already calculated
    //     if (!isset($data['base_space_amount'])) {
    //         $data['base_space_amount'] = $data['space_amount'] - ($data['tax_per_sqm_total'] ?? 0);
    //     }

    //     return $data;
    // }
    // protected function beforeCreate(array $data): array
    // {
    //     // return $this->processStandSelection($data);
    //     return $this->processContractData($data);

    // }

    // protected function beforeSave(array $data): array
    // {
    //     dd($data);
    //     // return $this->processStandSelection($data);
    //     return $this->processContractData($data);
    // }

    // private function processStandSelection(array $data): array
    // {
    //     // Validate stand exists and is available
    //     if (!empty($data['stand_id'])) {
    //         $stand = Stand::find($data['stand_id']);
    //         if ($stand && $stand->status === 'Available') {
    //             $data['stand_id'] = $stand->id;

    //             // Update stand status based on contract status
    //             $contractStatus = $data['status'] ?? Contract::STATUS_DRAFT;
    //             if (
    //                 $contractStatus === Contract::STATUS_SIGNED_PAID ||
    //                 $contractStatus === Contract::STATUS_SIGNED_NOT_PAID
    //             ) {
    //                 $stand->update(['status' => 'Sold']);
    //             }

    //             // Clear caches
    //             self::clearFormCache($data['event_id'] ?? null, $data['report_id'] ?? null);
    //         } else {
    //             throw new \Exception('Selected stand is not available or does not exist');
    //         }
    //     }

    //     return $data;
    // }
}
