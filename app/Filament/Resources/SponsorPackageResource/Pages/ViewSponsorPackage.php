<?php

namespace App\Filament\Resources\SponsorPackageResource\Pages;

use App\Filament\Resources\SponsorPackageResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSponsorPackage extends ViewRecord
{
    protected static string $resource = SponsorPackageResource::class;

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
                    ]),

                Infolists\Components\Tabs::make('Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Pricing')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('Currencies')
                                    ->label('')
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
                                            ->label('Price')
                                            ->money(fn ($record) => $record->CODE ?? 'USD')
                                            ->weight('bold')
                                            ->size('lg'),
                                    ])
                                    ->columns(3)
                                    ->grid(2),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Options')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('SponsorOptions')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label('Option')
                                            ->weight('medium')
                                            ->columnSpan(2),

                                        Infolists\Components\TextEntry::make('Currency.name')
                                            ->label('Currency')
                                            ->badge()
                                            ->color('gray'),

                                        Infolists\Components\TextEntry::make('pivot.quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->badge()
                                            ->color('primary'),

                                        Infolists\Components\IconEntry::make('pivot.is_included')
                                            ->label('Included')
                                            ->boolean()
                                            ->trueColor('success')
                                            ->falseColor('danger'),
                                    ])
                                    ->columns(4)
                                    ->grid(2),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Usage')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('Contracts_count')
                                            ->label('Total Contracts')
                                            ->getStateUsing(fn ($record) => $record->Contracts()->count())
                                            ->icon('heroicon-o-document-text')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('Events_count')
                                            ->label('Associated Events')
                                            ->getStateUsing(fn ($record) => $record->Events()->count())
                                            ->icon('heroicon-o-calendar')
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('options_count')
                                            ->label('Total Options')
                                            ->getStateUsing(fn ($record) => $record->SponsorOptions()->count())
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
