<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalTypeResource\Pages;
use App\Models\Market;
use App\Models\RentalType;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RentalTypeResource extends Resource
{
    protected static ?string $model = RentalType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Jenis Sewa';

    protected static ?string $modelLabel = 'Jenis Sewa';

    protected static ?string $pluralModelLabel = 'Jenis Sewa';

    protected static ?int $navigationSort = 15;

    // Hide dari navigation, tapi tetap accessible via URL
    public static function shouldRegisterNavigation(): bool
    {
        return false; // Menu di-hide, akses via filter di Penyewa sudah cukup
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

        // Tidak bisa delete jika ada tenant yang menggunakan
        if ($record->tenants()->count() > 0) {
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
                Forms\Components\Select::make('market_id')
                    ->label('Pasar')
                    ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                Forms\Components\Hidden::make('market_id')
                    ->default(fn (?RentalType $record) => $record?->market_id ?? auth()->user()?->market_id)
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                Forms\Components\Placeholder::make('market_name')
                    ->label('Pasar')
                    ->content(fn (?RentalType $record) => $record?->market?->name ?? auth()->user()?->market?->name)
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Jenis Sewa')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                        return $rule->where('market_id', $get('market_id') ?? auth()->user()?->market_id);
                    })
                    ->helperText('Contoh: Lapak, Kios, Toko, Ruko, Lapak Ikan, dll'),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Non-aktifkan jika jenis sewa tidak digunakan lagi'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('market.name')
                    ->label('Pasar')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Jenis Sewa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tenants_count')
                    ->label('Jumlah Penyewa')
                    ->counts('tenants')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('market_id')
                    ->label('Pasar')
                    ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->visible(fn (RentalType $record) => static::canDelete($record)),
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
            'index' => Pages\ListRentalTypes::route('/'),
            'create' => Pages\CreateRentalType::route('/create'),
            'edit' => Pages\EditRentalType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('market')->withCount('tenants');

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
