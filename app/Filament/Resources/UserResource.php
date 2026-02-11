<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';
    protected static ?string $navigationGroup = 'User/Role Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->description('Basic user information and profile picture')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_picture')
                            ->label('Profile Picture')
                            ->image()
                            ->avatar()
                            ->directory('profile-pictures')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('200')
                            ->imageResizeTargetHeight('200')
                            ->circleCropper()
                            ->columnSpanFull()
                            ->helperText('Upload a square image for best results. Max size: 2MB'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->unique(ignorable: fn($record) => $record)
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->confirmed()
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->dehydrated(fn($state) => filled($state))
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->helperText(fn(string $context): string =>
                                        $context === 'create'
                                            ? 'Minimum 8 characters'
                                            : 'Leave blank to keep current password'
                                    ),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->requiredWith('password')
                                    ->same('password')
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Roles & Permissions')
                    ->description('Assign roles and permissions to the user')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->options(Role::all()->pluck('name', 'id'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Role Name')
                                    ->required()
                                    ->unique('roles', 'name')
                                    ->maxLength(255),

                                Forms\Components\Select::make('guard_name')
                                    ->label('Guard')
                                    ->options([
                                        'web' => 'Web',
                                        'api' => 'API',
                                    ])
                                    ->default('web')
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Account Status')
                    ->description('Manage user account settings')
                    ->schema([
                        Forms\Components\Toggle::make('email_verified_at')
                            ->label('Email Verified')
                            ->default(true)
                            ->dehydrateStateUsing(fn($state) => $state ? now() : null)
                            ->formatStateUsing(fn($state, $record) => $record?->email_verified_at !== null),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled()
                            ->dehydrated(false)
                            ->displayFormat('F j, Y g:i A')
                            ->visible(fn($record) => $record !== null),

                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Last Updated')
                            ->disabled()
                            ->dehydrated(false)
                            ->displayFormat('F j, Y g:i A')
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->profile_picture_url)
                    ->size(40)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TagsColumn::make('roles.name')
                    ->label('Roles')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->colors([
                        'primary',
                        'success',
                        'warning',
                        'danger',
                    ]),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('Contracts_count')
                    ->label('Contracts')
                    ->counts('Contracts')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('impersonate')
                        ->label('Impersonate')
                        ->icon('heroicon-o-user')
                        ->color('warning')
                        ->action(function (User $record) {
                            // Store original user ID in session
                            session()->put('impersonator_id', auth()->id());
                            // Log in as the selected user
                            auth()->login($record);
                            return redirect('/admin');
                        })
                        ->visible(fn() => auth()->user()->hasRole('Super Admin')), // Only for super admins

                    Tables\Actions\Action::make('verify_email')
                        ->label('Verify Email')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (User $record) {
                            $record->email_verified_at = now();
                            $record->save();

                            \Filament\Notifications\Notification::make()
                                ->title('Email Verified')
                                ->body("{$record->name}'s email has been verified.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn(User $record): bool => $record->email_verified_at === null),

                    Tables\Actions\DeleteAction::make()
                        ->before(function (User $record) {
                            // Prevent deleting yourself
                            if ($record->id === auth()->id()) {
                                throw new \Exception('You cannot delete your own account.');
                            }

                            // Check if user has contracts
                            if ($record->Contracts()->exists()) {
                                throw new \Exception('Cannot delete user with assigned contracts. Reassign contracts first.');
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify_emails')
                        ->label('Verify Selected Emails')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->email_verified_at = now();
                                $record->save();
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Emails Verified')
                                ->body(count($records) . ' user(s) email verified.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('assign_roles')
                        ->label('Assign Roles')
                        ->icon('heroicon-o-user-group')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('roles')
                                ->label('Roles')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->options(Role::all()->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->syncRoles($data['roles']);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Roles Assigned')
                                ->body(count($records) . ' user(s) updated with new roles.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Prevent deleting yourself
                                if ($record->id === auth()->id()) {
                                    throw new \Exception('You cannot delete your own account.');
                                }

                                // Check if user has contracts
                                if ($record->Contracts()->exists()) {
                                    throw new \Exception('One or more users have assigned contracts. Reassign contracts first.');
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
