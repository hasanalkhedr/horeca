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
            ->sidebarCollapsibleOnDesktop()
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
                'Event Management',
                'Contracts',
                'Settings',
                'User/Role Management',
            ])
            ->navigationItems([

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
