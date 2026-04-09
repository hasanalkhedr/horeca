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

            Actions\Action::make('manageUserTargets')
                ->label('Manage User Targets')
                ->icon('heroicon-o-adjustments-vertical')
                ->color('warning')
                ->modalHeading('Manage User Targets for Event')
                ->modalDescription('Set performance targets for users for this event')
                ->modalSubmitActionLabel('Save Targets')
                ->modalWidth('7xl')
                ->form(function () {
                    return [
                        \Filament\Forms\Components\Grid::make()
                            ->schema([
                                // Table header using Grid
                                \Filament\Forms\Components\Grid::make(6)
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('header_user')
                                            ->label('User')
                                            ->content(''),
                                        \Filament\Forms\Components\Placeholder::make('header_space')
                                            ->label('Target Space (sqm)')
                                            ->content(''),
                                        \Filament\Forms\Components\Placeholder::make('header_amount')
                                            ->label('Target Amount ($)')
                                            ->content(''),
                                        \Filament\Forms\Components\Placeholder::make('header_sponsor')
                                            ->label('Target Sponsor ($)')
                                            ->content(''),
                                        \Filament\Forms\Components\Placeholder::make('header_notes')
                                            ->label('Notes')
                                            ->content(''),
                                        \Filament\Forms\Components\Placeholder::make('header_status')
                                            ->label('Current Status')
                                            ->content(''),
                                    ])
                                    ->extraAttributes(['class' => 'font-semibold border-b pb-2 mb-2']),

                                // Dynamic user rows
                                ...\App\Models\User::all()->map(function ($user) {
                                    $existingTarget = \App\Models\UserTarget::where('user_id', $user->id)
                                        ->where('event_id', $this->record->id)
                                        ->first();

                                    return \Filament\Forms\Components\Grid::make(6)
                                        ->schema([
                                            // Hidden user ID
                                            \Filament\Forms\Components\Hidden::make("users.{$user->id}.user_id")
                                                ->default($user->id),

                                            // User name
                                            \Filament\Forms\Components\Placeholder::make("users.{$user->id}.name")
                                                ->label('')
                                                ->content($user->name),

                                            // Target Space
                                            \Filament\Forms\Components\TextInput::make("users.{$user->id}.target_space")
                                                ->label('')
                                                ->numeric()
                                                ->step(0.01)
                                                ->suffix('sqm')
                                                ->default($existingTarget?->target_space ?? 0),

                                            // Target Amount
                                            \Filament\Forms\Components\TextInput::make("users.{$user->id}.target_space_amount")
                                                ->label('')
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('$')
                                                ->default($existingTarget?->target_space_amount ?? 0),

                                            // Target Sponsor Amount
                                            \Filament\Forms\Components\TextInput::make("users.{$user->id}.target_sponsor_amount")
                                                ->label('')
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('$')
                                                ->default($existingTarget?->target_sponsor_amount ?? 0),

                                            // Notes
                                            \Filament\Forms\Components\TextInput::make("users.{$user->id}.notes")
                                                ->label('')
                                                ->placeholder('Notes...')
                                                ->default($existingTarget?->notes),

                                            // Current Status
                                            \Filament\Forms\Components\Placeholder::make("users.{$user->id}.status")
                                                ->label('')
                                                ->content(function () use ($existingTarget) {
                                                    if (!$existingTarget) return 'No target';

                                                    $completion = $existingTarget->completion_percentage ?? 0;
                                                    return "{$existingTarget->status} ({$completion}%)";
                                                }),
                                        ])
                                        ->extraAttributes(['class' => 'border-b pb-2 mb-2']);
                                })->toArray(),
                            ])
                    ];
                })
                ->action(function (array $data) {
                    foreach ($data['users'] as $userId => $targetData) {
                        // Skip if all target values are 0 and no notes exist (to avoid creating empty targets)
                        if (($targetData['target_space'] == 0) &&
                            ($targetData['target_space_amount'] == 0) &&
                            ($targetData['target_sponsor_amount'] == 0) &&
                            empty($targetData['notes'])) {

                            // Delete existing target if it exists and all values are 0
                            \App\Models\UserTarget::where('user_id', $userId)
                                ->where('event_id', $this->record->id)
                                ->delete();
                            continue;
                        }

                        \App\Models\UserTarget::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'event_id' => $this->record->id,
                            ],
                            [
                                'target_space' => $targetData['target_space'],
                                'target_space_amount' => $targetData['target_space_amount'],
                                'target_sponsor_amount' => $targetData['target_sponsor_amount'],
                                'notes' => $targetData['notes'] ?? null,
                                'status' => 'active',
                            ]
                        );
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('User targets updated successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}
