<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use BackedEnum;
use App\Models\Market;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?int $navigationSort = 11;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin_pusat', 'admin_pasar']) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin_pusat')) {
            return true;
        }

        return $user->hasRole('admin_pasar') && $record->market_id === $user->market_id;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin_pusat', 'admin_pasar']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('market_id')
                    ->label('Pasar')
                    ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                Hidden::make('market_id')
                    ->default(fn (?User $record) => $record?->market_id ?? auth()->user()?->market_id)
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                Placeholder::make('market_name')
                    ->label('Pasar')
                    ->content(fn (?User $record) => $record?->market?->name ?? auth()->user()?->market?->name)
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(150),

                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                        return $rule->where('market_id', $get('market_id') ?? auth()->user()?->market_id);
                    }),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(150)
                    ->nullable()
                    ->unique(ignoreRecord: true),

                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(30)
                    ->nullable(),

                FileUpload::make('foto_profile')
                    ->label('Foto Profile')
                    ->image()
                    ->directory('profile-photos')
                    ->disk('public')
                    ->nullable()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->maxSize(2048)
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->confirmed()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state) => filled($state)),

                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(false),

                Select::make('role')
                    ->label('Role')
                    ->options(fn () => collect(static::assignableRoleNames())
                        ->mapWithKeys(fn ($role) => [$role => static::formatRoleLabel($role)])
                    )
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->afterStateHydrated(function (Select $component, ?User $record) {
                        if ($record) {
                            $component->state($record->getRoleNames()->first());
                        }
                    })
                    ->dehydrated(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('market.name')
                    ->label('Pasar')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::formatRoleColumn($state)),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('market_id')
                    ->label('Pasar')
                    ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name', fn ($query) => $query->whereIn('name', static::assignableRoleNames()))
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->visible(fn (User $record) => static::canDelete($record)),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasAnyRole(['admin_pusat', 'admin_pasar'])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['market', 'roles']);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin_pusat')) {
            return $query;
        }

        if ($user->hasRole('admin_pasar')) {
            return $query->where('market_id', $user->market_id);
        }

        return $query->where('id', $user->id);
    }

    protected static function assignableRoleNames(): array
    {
        $user = auth()->user();

        if ($user?->hasRole('admin_pusat')) {
            return Role::orderBy('name')->pluck('name')->all();
        }

        if ($user?->hasRole('admin_pasar')) {
            return Role::whereIn('name', ['admin_pasar', 'inputer', 'viewer'])
                ->orderBy('name')
                ->pluck('name')
                ->all();
        }

        return [];
    }

    protected static function formatRoleLabel(string $role): string
    {
        return Str::headline(str_replace('_', ' ', $role));
    }

    /**
     * @param  array<string>|Collection<string>|string|null  $state
     */
    protected static function formatRoleColumn(array|Collection|string|null $state): string
    {
        if ($state instanceof Collection) {
            return $state
                ->map(fn (string $role) => static::formatRoleLabel($role))
                ->implode(', ');
        }

        if (is_array($state)) {
            return collect($state)
                ->map(fn (string $role) => static::formatRoleLabel($role))
                ->implode(', ');
        }

        return $state ? static::formatRoleLabel($state) : '-';
    }
}
