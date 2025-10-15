<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Market;
use App\Models\Tenant;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Penyewa';

    protected static ?string $modelLabel = 'Penyewa';

    protected static ?string $pluralModelLabel = 'Penyewa';

    protected static ?int $navigationSort = 20;

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
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('market_id')
                    ->label('Pasar')
                    ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                Hidden::make('market_id')
                    ->default(fn (?Tenant $record) => $record?->market_id ?? auth()->user()?->market_id)
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                Placeholder::make('market_name')
                    ->label('Pasar')
                    ->content(fn (?Tenant $record) => $record?->market?->name ?? auth()->user()?->market?->name)
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                TextInput::make('nama')
                    ->label('Nama Penyewa')
                    ->required()
                    ->maxLength(200),

                TextInput::make('nomor_lapak')
                    ->label('Nomor Lapak')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                        return $rule->where('market_id', $get('market_id') ?? auth()->user()?->market_id);
                    }),

                TextInput::make('hp')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(30)
                    ->nullable(),

                Textarea::make('alamat')
                    ->label('Alamat')
                    ->rows(3)
                    ->columnSpanFull()
                    ->nullable(),

                Placeholder::make('outstanding')
                    ->label('Outstanding')
                    ->content(fn (?Tenant $record) => $record?->formatted_outstanding ?? 'Rp 0')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('market.name')
                    ->label('Pasar')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nomor_lapak')
                    ->label('Nomor Lapak')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hp')
                    ->label('No. HP')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('outstanding')
                    ->label('Outstanding')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(),

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

                TernaryFilter::make('outstanding')
                    ->label('Status Outstanding')
                    ->trueLabel('Masih outstanding')
                    ->falseLabel('Lunas')
                    ->queries(
                        fn (Builder $query, array $data): Builder => $query->where('outstanding', '>', 0),
                        fn (Builder $query, array $data): Builder => $query->where('outstanding', '<=', 0),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Tenant $record) => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('market');

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

        return $query->where('market_id', -1);
    }
}
