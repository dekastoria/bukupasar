<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Market;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use BackedEnum;
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
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $modelLabel = 'Pembayaran';

    protected static ?string $pluralModelLabel = 'Pembayaran';

    protected static ?int $navigationSort = 31;

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
                    ->default(fn (?Payment $record) => $record?->market_id ?? auth()->user()?->market_id)
                    ->dehydrated()
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                Placeholder::make('market_name')
                    ->label('Pasar')
                    ->content(fn (?Payment $record) => $record?->market?->name ?? auth()->user()?->market?->name)
                    ->visible(fn () => auth()->user()?->hasRole('admin_pasar')),

                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(fn (?Payment $record) => $record?->tanggal ?? now())
                    ->maxDate(now())
                    ->native(false),

                Select::make('tenant_id')
                    ->label('Penyewa')
                    ->options(fn (callable $get) => static::getTenantOptions($get('market_id') ?? auth()->user()?->market_id))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->live(),

                TextInput::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->minValue(1)
                    ->prefix('Rp')
                    ->required()
                    ->rule(fn (callable $get): ValidationRule => function (string $attribute, $value, $fail) use ($get) {
                        $tenantId = $get('tenant_id');

                        if (! $tenantId) {
                            return;
                        }

                        $tenant = Tenant::find($tenantId);

                        if (! $tenant) {
                            return;
                        }

                        if ((int) $value > (int) $tenant->outstanding) {
                            $fail('Jumlah melebihi outstanding tenant.');
                        }
                    }),

                Placeholder::make('tenant_outstanding')
                    ->label('Outstanding Saat Ini')
                    ->content(fn (callable $get) => static::getTenantOutstandingLabel($get('tenant_id')))
                    ->columnSpanFull(),

                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull()
                    ->nullable(),
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

                TextColumn::make('tenant.nama')
                    ->label('Penyewa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),

                TextColumn::make('creator.name')
                    ->label('Petugas')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
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

    protected static function getTenantOutstandingLabel(?int $tenantId): string
    {
        if (! $tenantId) {
            return '-';
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return '-';
        }

        return 'Rp ' . number_format((int) $tenant->outstanding, 0, ',', '.');
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
}
