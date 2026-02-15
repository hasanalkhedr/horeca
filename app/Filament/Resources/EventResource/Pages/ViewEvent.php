<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\StandResource;
use App\Filament\Resources\ContractResource;
use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Event Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('CODE')
                                    ->label('Event Code')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('name')
                                    ->label('Event Name')
                                    ->size('lg')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('vat_rate')
                                    ->label('VAT Rate')
                                    ->suffix('%'),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->markdown(),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('start_date')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('end_date')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('apply_start_date')
                                    ->label('App Start')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('apply_deadline_date')
                                    ->label('App Deadline')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Location')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('country')
                                    ->icon('heroicon-o-globe-alt'),

                                Infolists\Components\TextEntry::make('city')
                                    ->icon('heroicon-o-building-office'),

                                Infolists\Components\TextEntry::make('address')
                                    ->icon('heroicon-o-map-pin'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Space Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_space')
                                    ->label('Total Space')
                                    ->suffix(' sqm')
                                    ->icon('heroicon-o-square-3-stack-3d'),

                                Infolists\Components\TextEntry::make('space_to_sell')
                                    ->label('To Sell')
                                    ->suffix(' sqm')
                                    ->icon('heroicon-o-currency-dollar'),

                                Infolists\Components\TextEntry::make('remaining_space_to_sell')
                                    ->label('Remaining')
                                    ->suffix(' sqm')
                                    ->icon('heroicon-o-clock')
                                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),

                                Infolists\Components\TextEntry::make('free_space')
                                    ->label('Free Space')
                                    ->suffix(' sqm')
                                    ->icon('heroicon-o-check-circle'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Stand Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('Stands_count')
                                    ->label('Total Stands')
                                    ->getStateUsing(fn ($record) => $record->Stands()->count())
                                    ->icon('heroicon-o-map-pin')
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('sold_stands')
                                    ->label('Sold Stands')
                                    ->getStateUsing(fn ($record) => $record->soldStands()->count())
                                    ->icon('heroicon-o-check-badge')
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('available_stands')
                                    ->label('Available Stands')
                                    ->getStateUsing(fn ($record) => $record->availableStands()->count())
                                    ->icon('heroicon-o-check-circle')
                                    ->color('success'),
                            ]),
                    ])
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('viewAllStands')
                            ->label('View All Stands')
                            ->icon('heroicon-o-arrow-right')
                            ->url(fn (): string => StandResource::getUrl('index', [
                                'tableFilters' => [
                                    'event_id' => [
                                        'values' => [$this->record->id],
                                    ],
                                ],
                            ])),
                    ]),

                Infolists\Components\Section::make('Categories')
                    ->schema([
                        Infolists\Components\TextEntry::make('Categories.name')
                            ->badge()
                            ->separator(','),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Currencies')
                    ->schema([
                        Infolists\Components\TextEntry::make('Currencies.name')
                            ->badge()
                            ->separator(','),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Audit Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d/m/Y H:i'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    if ($record->Stands()->exists()) {
                        throw new \Exception('Cannot delete event with existing stands. Delete the stands first.');
                    }
                    if ($record->Contracts()->exists()) {
                        throw new \Exception('Cannot delete event with existing contracts. Delete the contracts first.');
                    }
                }),

            Actions\Action::make('viewStands')
                ->label('View Event Stands')
                ->icon('heroicon-o-map-pin')
                ->color('primary')
                ->url(fn (): string => StandResource::getUrl('index', [
                    'tableFilters' => [
                        'event_id' => [
                            'values' => [$this->record->id],
                        ],
                    ],
                ])),

            Actions\Action::make('createStand')
                ->label('Add New Stand')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn (): string => StandResource::getUrl('create', [
                    'event_id' => $this->record->id,
                ])),
        ];
    }
}
