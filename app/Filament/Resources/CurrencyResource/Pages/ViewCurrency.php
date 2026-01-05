<?php

namespace App\Filament\Resources\CurrencyResource\Pages;

use App\Filament\Resources\CurrencyResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCurrency extends ViewRecord
{
    protected static string $resource = CurrencyResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Currency Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('CODE')
                                    ->label('Currency Code')
                                    ->badge()
                                    ->color('primary')
                                    ->size('lg')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('name')
                                    ->label('Currency Name')
                                    ->size('lg')
                                    ->weight('medium'),

                                Infolists\Components\TextEntry::make('country')
                                    ->label('Country/Region')
                                    ->placeholder('Not specified')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('rate_to_usd')
                                    ->label('Exchange Rate to USD')
                                    ->numeric(decimalPlaces: 6)
                                    ->suffix(' USD')
                                    ->weight('bold')
                                    ->color('success')
                                    ->columnSpanFull()
                                    ->helperText('1 ' . $this->record->CODE . ' = ' . number_format($this->record->rate_to_usd, 6) . ' USD'),
                            ]),
                    ]),

                Infolists\Components\Tabs::make('Usage Statistics')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Ads Packages')
                            ->icon('heroicon-o-gift')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('AdsPackages')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label('Package')
                                            ->badge()
                                            ->color('warning'),

                                        Infolists\Components\TextEntry::make('pivot.total_price')
                                            ->label('Price')
                                            ->formatStateUsing(fn($state) => $this->record->CODE . ' ' . number_format($state, 2))
                                            ->weight('bold'),
                                    ])
                                    ->columns(2)
                                    ->grid(2),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Ads Options')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('AdsOptions')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label('Option')
                                            ->badge()
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('pivot.price')
                                            ->label('Price')
                                            ->formatStateUsing(fn($state) => $this->record->CODE . ' ' . number_format($state, 2))
                                            ->weight('bold'),
                                    ])
                                    ->columns(2)
                                    ->grid(2),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Events')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('Events')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label('Event')
                                            ->badge()
                                            ->color('success'),

                                        Infolists\Components\TextEntry::make('pivot.min_price')
                                            ->label('Minimum Price')
                                            ->formatStateUsing(fn($state) => $this->record->CODE . ' ' . number_format($state, 2))
                                            ->weight('bold'),
                                    ])
                                    ->columns(2)
                                    ->grid(2),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Statistics')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('ads_packages_count')
                                            ->label('Total Ads Packages')
                                            ->getStateUsing(fn($record) => $record->AdsPackages()->count())
                                            ->icon('heroicon-o-gift')
                                            ->color('warning'),

                                        Infolists\Components\TextEntry::make('ads_options_count')
                                            ->label('Total Ads Options')
                                            ->getStateUsing(fn($record) => $record->AdsOptions()->count())
                                            ->icon('heroicon-o-list-bullet')
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('events_count')
                                            ->label('Total Events')
                                            ->getStateUsing(fn($record) => $record->Events()->count())
                                            ->icon('heroicon-o-calendar')
                                            ->color('success'),

                                        Infolists\Components\TextEntry::make('sponsor_packages_count')
                                            ->label('Sponsor Packages')
                                            ->getStateUsing(fn($record) => $record->SponsorPackages()->count())
                                            ->icon('heroicon-o-star')
                                            ->color('purple'),

                                        Infolists\Components\TextEntry::make('reports_count')
                                            ->label('Reports')
                                            ->getStateUsing(fn($record) => $record->Reports()->count())
                                            ->icon('heroicon-o-document-text')
                                            ->color('gray'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->before(function ($record) {
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
        ];
    }
}
