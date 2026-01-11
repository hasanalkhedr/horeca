<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Profile Information')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('profile_picture')
                                ->label('')
                                ->circular()
                                ->defaultImageUrl(fn($record) => $record->profile_picture_url)
                                ->size(120)
                                ->columnSpan(1),

                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('Full Name')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->weight('bold'),

                                    Infolists\Components\TextEntry::make('email')
                                        ->label('Email Address')
                                        ->icon('heroicon-o-envelope'),

                                    Infolists\Components\TextEntry::make('email_verified_at')
                                        ->label('Email Status')
                                        ->badge()
                                        ->color(fn($state): string => $state ? 'success' : 'danger')
                                        ->formatStateUsing(fn($state): string => $state ? 'Verified' : 'Not Verified')
                                        ->icon(fn($state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),

                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Member Since')
                                        ->dateTime('F j, Y'),
                                ])
                                ->columnSpan(2),
                        ])->from('lg'),
                    ]),

                Infolists\Components\Section::make('Roles & Permissions')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('roles')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->badge()
                                    ->color('primary'),
                            ])
                            ->grid(2)
                            //->emptyStateLabel('No roles assigned'),
                    ]),

                Infolists\Components\Section::make('Activity Summary')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('Contracts_count')
                                    ->label('Total Contracts')
                                    ->numeric()
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->formatStateUsing(fn($state) => number_format($state)),

                                Infolists\Components\TextEntry::make('Contracts_sum_net_total')
                                    ->label('Total Sales')
                                    ->money('USD')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('last_contract')
                                    ->label('Last Contract')
                                    ->getStateUsing(function ($record) {
                                        $lastContract = $record->Contracts()->latest()->first();
                                        return $lastContract ? $lastContract->contract_no . ' (' . $lastContract->created_at->format('M d, Y') . ')' : 'None';
                                    })
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load contracts count and sum
        $data['Contracts_count'] = $this->record->Contracts()->count();
        $data['Contracts_sum_net_total'] = $this->record->Contracts()->sum('net_total');

        return $data;
    }
}
