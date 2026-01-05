<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\RelationManagers;
use App\Models\Event;
use App\Models\Report;
use App\Models\Settings\Currency;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Event Management';
    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Contract Templates';
    protected static ?string $modelLabel = 'Contract Template';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contract Template Information')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Event')
                            ->relationship('Event', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Daily Sales Report, Financial Summary')
                            ->columnSpan(2),

                        Forms\Components\Select::make('currency_id')
                            ->label('Currency')
                            ->relationship('Currency', 'CODE')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('components')
                            ->label('Contract Template Components')
                            ->helperText('Enter components separated by commas (e.g., sales_summary,top_products,customer_metrics)')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                                'credit_card' => 'Credit Card',
                                'check' => 'Check',
                                'other' => 'Other',
                            ])
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('bank_account')
                            ->label('Bank Account Number')
                            ->maxLength(100)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('bank_name_address')
                            ->label('Bank Name & Address')
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('swift_code')
                            ->label('SWIFT Code')
                            ->maxLength(50)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength(50)
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Forms\Components\Section::make('Contract Template Settings')
                    ->schema([
                        Forms\Components\Toggle::make('with_logo')
                            ->label('Include Logo')
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('report-logos')
                            ->maxSize(2048)
                            ->visible(fn(callable $get): bool => $get('with_logo'))
                            ->columnSpan(3),

                        Forms\Components\Toggle::make('show_categories')
                            ->label('Show Categories')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('special_price')
                            ->label('Special Pricing')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('with_options')
                            ->label('Include Options')
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->collapsible(),

                // Advanced Settings Section
                Forms\Components\Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('custom_field_1')
                                    ->label('Custom Field 1')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('custom_field_2')
                                    ->label('Custom Field 2')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('custom_field_3')
                                    ->label('Custom Field 3')
                                    ->maxLength(100),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn($record): string => $record->name),

                Tables\Columns\TextColumn::make('Event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('Currency.CODE')
                    ->label('Currency')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'bank_transfer' => 'success',
                        'cash' => 'gray',
                        'credit_card' => 'info',
                        'check' => 'warning',
                        'other' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('with_logo')
                    ->label('Logo')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('show_categories')
                    ->label('Categories')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('with_options')
                    ->label('Options')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('components_preview')
                    ->label('Components')
                    ->getStateUsing(function ($record): string {
                        if (!$record->components) {
                            return 'No components';
                        }

                        $components = is_array($record->components)
                            ? $record->components
                            : explode(',', $record->components);

                        return implode(', ', array_slice($components, 0, 2)) .
                            (count($components) > 2 ? '...' : '');
                    })
                    ->limit(30)
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(fn(): array => Event::pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('currency_id')
                    ->label('Currency')
                    ->options(fn(): array => Currency::pluck('CODE', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'check' => 'Check',
                        'other' => 'Other',
                    ]),

                Tables\Filters\Filter::make('with_logo')
                    ->label('Has Logo')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('with_logo', true)),

                Tables\Filters\Filter::make('show_categories')
                    ->label('Shows Categories')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('show_categories', true)),

                Tables\Filters\Filter::make('with_options')
                    ->label('Has Options')
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('with_options', true)),
            ])
            ->actions([
                Tables\Actions\Action::make('manage')
                    ->label('Manage Contract Template')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->url(fn(Report $record): string => route('report.editor', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn(Report $record): string => route('reports.show', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('createContract')
                    ->label('Create Contract')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->action(function (Report $record) {
                        // Store the selected report ID in session
                        session()->flash('contract_report_id', $record->id);
                        //session()->flash('contract_event_id', $record->event_id);

                        // Redirect to contract create page with event ID
                        return redirect()->route('filament.admin.resources.contracts.create', [
                            'event_id' => $record->event_id
                        ]);
                    }),
                // Tables\Actions\Action::make('download')
                //     ->label('Download')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->color('success')
                //     ->url(fn(Report $record): string => route('reports.show', $record))
                //     ->openUrlInNewTab(),

                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Report $record) {
                        if ($record->Contracts()->exists()) {
                            throw new \Exception('Cannot delete contract template with existing contracts. Delete contracts first.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->Contracts()->exists()) {
                                    throw new \Exception('One or more contract templates have contracts. Delete contracts first.');
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\ContractsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
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
