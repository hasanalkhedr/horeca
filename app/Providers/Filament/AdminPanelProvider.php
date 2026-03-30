<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Login;
use App\Filament\Resources\ContractResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Widgets\ContractsChartWidget;
use App\Filament\Widgets\EventDashboardWidget;
use App\Filament\Widgets\EventStatsWidget;
use App\Filament\Widgets\FilterableStandsContractsWidget;
use App\Filament\Widgets\MultiEventDashboardWidget;
use App\Filament\Widgets\StandsAndContractsChartWidget;
use App\Filament\Widgets\StandsChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Asset;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandLogo(asset('/images/logo.png'))
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            //->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            // ->sidebarWidth('15rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                //Pages\Dashboard::class,
                Dashboard::class, // Custom
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // EventStatsWidget::class,
                //     //StandsAndContractsChartWidget::class,
                // StandsChartWidget::class,
                // ContractsChartWidget::class,
                // FilterableStandsContractsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->plugin(
                \Hasnayeen\Themes\ThemesPlugin::make()
            )
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                'Contract Filters',
                'Event Management',
                'Contracts',
                'Settings',
                'User/Role Management',
            ])
            ->navigationItems([
                NavigationItem::make('INT')
                    ->label('INT (Interested)')
                    ->icon('heroicon-s-document-arrow-up')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_INTERESTED]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_INTERESTED)->count())
                    ->group('Contract Filters')
                    ->sort(1),

                NavigationItem::make('S&NP')
                    ->label('S&NP (Signed & Not Paid)')
                    ->icon('heroicon-s-document-check')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_SIGNED_NOT_PAID]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_SIGNED_NOT_PAID)->count())
                    ->group('Contract Filters')
                    ->sort(2),

                NavigationItem::make('S&P')
                    ->label('S&P (Signed & Paid)')
                    ->icon('heroicon-s-check-circle')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_SIGNED_PAID]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_SIGNED_PAID)->count())
                    ->group('Contract Filters')
                    ->sort(3),

                NavigationItem::make('Closed')
                    ->icon('heroicon-s-x-circle')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_CLOSED]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_CLOSED)->count())
                    ->group('Contract Filters')
                    ->sort(4),

                NavigationItem::make('Free From HS')
                    ->icon('heroicon-s-gift')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_FREE_FROM_HS]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_FREE_FROM_HS)->count())
                    ->group('Contract Filters')
                    ->sort(5),

                NavigationItem::make('Paid Troc')
                    ->icon('heroicon-s-banknotes')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_PAID_TROC]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_PAID_TROC)->count())
                    ->group('Contract Filters')
                    ->sort(6),

                NavigationItem::make('On Hold')
                    ->icon('heroicon-s-pause-circle')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_ON_HOLD]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_ON_HOLD)->count())
                    ->group('Contract Filters')
                    ->sort(7),

                NavigationItem::make('On Site Free')
                    ->icon('heroicon-s-home')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_ON_SITE_FREE]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_ON_SITE_FREE)->count())
                    ->group('Contract Filters')
                    ->sort(8),

                NavigationItem::make('Animation')
                    ->icon('heroicon-s-sparkles')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_ANIMATION]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_ANIMATION)->count())
                    ->group('Contract Filters')
                    ->sort(9),

                NavigationItem::make('Sponsor')
                    ->icon('heroicon-s-star')
                    ->url(fn() => \App\Filament\Resources\ContractResource::getUrl('index', ['tableFilters[status][value]' => \App\Models\Contract::STATUS_SPONSOR]))
                    ->badge(fn() => \App\Models\Contract::where('status', \App\Models\Contract::STATUS_SPONSOR)->count())
                    ->group('Contract Filters')
                    ->sort(10),
            ])
            ->renderHook(
                'panels::head.end',
                fn() => '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>'
            )
            ->renderHook(
                'panels::global-search.after',
                fn() => view('filament.components.new-contract-button'),
            )
        ;
    }
}
