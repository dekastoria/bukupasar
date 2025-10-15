<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Market;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?int $navigationSort = 30;

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
        return static::canViewAny();
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
                    ->default(fn (?Transaction $record) => $record?->market_id ?? auth()->user()?->market_id)
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                Placeholder::make('market_name')
                    ->label('Pasar')
                    ->content(fn (?Transaction $record) => $record?->market?->name ?? auth()->user()?->market?->name)
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(fn (?Transaction $record) => $record?->tanggal ?? now())
                    ->maxDate(now())
                    ->native(false),

                Select::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ])
                    ->required()
                    ->live()
                    ->native(false),

                Select::make('subkategori')
                    ->label('Subkategori')
                    ->options(fn (callable $get) => static::getCategoryOptions($get('market_id') ?? auth()->user()?->market_id, $get('jenis')))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->reactive(),

                Select::make('tenant_id')
                    ->label('Penyewa (opsional)')
                    ->options(fn (callable $get) => static::getTenantOptions($get('market_id') ?? auth()->user()?->market_id))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable(),

                TextInput::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->minValue(1)
                    ->prefix('Rp')
                    ->required(),

                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull()
                    ->required(fn (callable $get) => static::categoryRequiresNote(
                        $get('market_id') ?? auth()->user()?->market_id,
                        $get('subkategori')
                    ))
                    ->helperText('Wajib diisi jika kategori meminta keterangan.'),
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

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => $state === 'pemasukan' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->sortable(),

                TextColumn::make('subkategori')
                    ->label('Subkategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tenant.nama')
                    ->label('Penyewa')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),

                TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('market_id')
                    ->label('Pasar')
                    ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                    ->visible(fn () => auth()->user()?->hasRole('admin_pusat')),

                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ]),

                SelectFilter::make('subkategori')
                    ->label('Subkategori')
                    ->options(fn () => static::getAllCategoryOptions())
                    ->searchable(),

                SelectFilter::make('tenant_id')
                    ->label('Penyewa')
                    ->relationship('tenant', 'nama', fn (Builder $query) => $query->orderBy('nama')),

                SelectFilter::make('created_by')
                    ->label('Petugas')
                    ->options(fn () => static::getCreatorOptions())
                    ->searchable(),

                Filter::make('tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['market', 'tenant', 'creator']);

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

    protected static function getCategoryOptions(?int $marketId, ?string $jenis): array
    {
        if (! $marketId || ! $jenis) {
            return [];
        }

        return Category::query()
            ->forMarket($marketId)
            ->jenis($jenis)
            ->active()
            ->orderBy('nama')
            ->pluck('nama', 'nama')
            ->all();
    }

    protected static function getAllCategoryOptions(): array
    {
        $user = auth()->user();

        $query = Category::query()->active()->orderBy('nama');

        if ($user?->hasRole('admin_pasar')) {
            $query->forMarket($user->market_id);
        }

        return $query->pluck('nama', 'nama')->all();
    }

    protected static function getTenantOptions(?int $marketId): array
    {
        if (! $marketId) {
            return [];
        }

        return Tenant::query()
            ->forMarket($marketId)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    protected static function getCreatorOptions(): array
    {
        $user = auth()->user();

        $query = User::query()->orderBy('name');

        if ($user?->hasRole('admin_pasar')) {
            $query->where('market_id', $user->market_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    protected static function categoryRequiresNote(?int $marketId, ?string $subkategori): bool
    {
        if (! $marketId || ! $subkategori) {
            return false;
        }

        return Category::query()
            ->forMarket($marketId)
            ->where('nama', $subkategori)
            ->where('wajib_keterangan', true)
            ->exists();
    }
}
