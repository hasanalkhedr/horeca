<?php

namespace App\Filament\Resources\EffAdsPackageResource\Pages;

use App\Filament\Resources\EffAdsPackageResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEffAdsPackage extends ViewRecord
{
    protected static string $resource = EffAdsPackageResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Package Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('Package Title')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Package Description')
                            ->size('lg')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Tabs::make('Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Pricing')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('Currencies')
                                    ->label('Package Pricing by Currency')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Currency')
                                            ->badge()
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('CODE')
                                            ->label('Code')
                                            ->badge()
                                            ->color('gray'),

                                        Infolists\Components\TextEntry::make('pivot.total_price')
                                            ->label('Total Price')
                                            ->formatStateUsing(fn($state) => number_format($state, 2))
                                            ->suffix(' ')
                                            ->prefix(fn($record) => $record->CODE . ' ')
                                            ->weight('bold')
                                            ->size('lg'),
                                    ])
                                    ->columns(3)
                                    ->grid(2),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Options')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('EffAdsOptions')
                                    ->label('Package Options')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label('Option Title')
                                            ->weight('bold')
                                            ->columnSpan(2)
                                            ->size('md'),

                                        Infolists\Components\TextEntry::make('description')
                                            ->label('Description')
                                            ->columnSpanFull()
                                            ->size('sm')
                                            ->color('gray'),

                                        Infolists\Components\Fieldset::make('Option Pricing')
                                            ->schema([
                                                Infolists\Components\RepeatableEntry::make('Currencies')
                                                    ->label('')
                                                    ->schema([
                                                        Infolists\Components\TextEntry::make('name')
                                                            ->label('Currency')
                                                            ->badge()
                                                            ->color('primary')
                                                            ->size('sm'),

                                                        Infolists\Components\TextEntry::make('pivot.price')
                                                            ->label('Price')
                                                            ->formatStateUsing(fn($state) => number_format($state, 2))
                                                            ->suffix(' ')
                                                            ->prefix(fn($record) => $record->CODE . ' ')
                                                            ->weight('medium')
                                                            ->size('sm'),
                                                    ])
                                                    ->columns(2)
                                                    ->grid(1),
                                            ])
                                            ->columnSpanFull()
                                            ->hidden(fn($record) => $record->Currencies->isEmpty()),
                                    ])
                                    ->columns(1)
                                    ->grid(1),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Usage')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('Contracts_count')
                                            ->label('Total Contracts')
                                            ->getStateUsing(fn($record) => $record->Contracts()->count())
                                            ->icon('heroicon-o-document-text')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('Events_count')
                                            ->label('Associated Events')
                                            ->getStateUsing(fn($record) => $record->Events()->count())
                                            ->icon('heroicon-o-calendar')
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('options_count')
                                            ->label('Total Options')
                                            ->getStateUsing(fn($record) => $record->EffAdsOptions()->count())
                                            ->icon('heroicon-o-list-bullet')
                                            ->color('success'),
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
                    if ($record->Contracts()->exists()) {
                        throw new \Exception('Cannot delete package with existing contracts. Delete the contracts first.');
                    }
                }),
        ];
    }
}
