<?php

namespace App\Filament\Pages;

use App\Models\Market;
use App\Models\Transaction;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class DailyReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static string | UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 60;

    public ?string $filterDate = null;

    public ?string $filterJenis = null;

    public ?int $filterMarketId = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->filterDate = Carbon::today()->toDateString();
        $this->filterMarketId = $user?->market_id;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin_pusat', 'admin_pasar']) ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filter')
                ->label('Filter')
                ->icon('heroicon-o-funnel')
                ->modalHeading('Filter Laporan Harian')
                ->form([
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->default($this->filterDate)
                        ->maxDate(Carbon::today())
                        ->native(false),
                    Forms\Components\Select::make('market_id')
                        ->label('Pasar')
                        ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->visible(fn () => auth()->user()?->hasRole('admin_pusat'))
                        ->default($this->filterMarketId),
                    Forms\Components\Select::make('jenis')
                        ->label('Jenis Transaksi')
                        ->options([
                            'pemasukan' => 'Pemasukan',
                            'pengeluaran' => 'Pengeluaran',
                        ])
                        ->native(false)
                        ->placeholder('Semua jenis')
                        ->default($this->filterJenis),
                ])
                ->action(function (array $data): void {
                    $this->filterDate = $data['tanggal'] ?? $this->filterDate;
                    $this->filterJenis = $data['jenis'] ?? null;

                    if (auth()->user()?->hasRole('admin_pusat')) {
                        $this->filterMarketId = $data['market_id'] ?? null;
                    }

                    $this->resetTable();
                }),
            Actions\Action::make('resetFilters')
                ->label('Reset')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    $user = auth()->user();

                    $this->filterDate = Carbon::today()->toDateString();
                    $this->filterJenis = null;
                    $this->filterMarketId = $user?->hasRole('admin_pusat') ? null : $user?->market_id;

                    $this->resetTable();
                }),
            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->export()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return $this->baseQuery();
    }

    protected function baseQuery(): Builder
    {
        $user = auth()->user();

        $query = Transaction::query()
            ->with(['tenant', 'creator'])
            ->latest('tanggal')
            ->latest('id');

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin_pasar')) {
            $query->where('market_id', $user->market_id);
        } elseif ($user->hasRole('admin_pusat')) {
            if ($this->filterMarketId) {
                $query->where('market_id', $this->filterMarketId);
            }
        } else {
            return $query->whereRaw('1 = 0');
        }

        $date = $this->filterDate ?: Carbon::today()->toDateString();
        $query->whereDate('tanggal', $date);

        if ($this->filterJenis) {
            $query->where('jenis', $this->filterJenis);
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
                ->date('d M Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('jenis')
                ->label('Jenis')
                ->badge()
                ->color(fn (string $state) => $state === 'pemasukan' ? 'success' : 'danger')
                ->formatStateUsing(fn (string $state) => ucfirst($state)),
            Tables\Columns\TextColumn::make('subkategori')
                ->label('Subkategori')
                ->wrap(),
            Tables\Columns\TextColumn::make('tenant.nama')
                ->label('Penyewa')
                ->wrap()
                ->toggleable(),
            Tables\Columns\TextColumn::make('jumlah')
                ->label('Jumlah')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                ->summarize(Sum::make()->label('Total')->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))), 
            Tables\Columns\TextColumn::make('creator.name')
                ->label('Petugas')
                ->toggleable(),
            Tables\Columns\TextColumn::make('catatan')
                ->label('Catatan')
                ->limit(50)
                ->toggleable(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada transaksi pada tanggal ini.';
    }

    public function export(): StreamedResponse
    {
        $date = $this->filterDate ?: Carbon::today()->toDateString();
        $filename = sprintf('laporan-harian-%s.csv', $date);

        $records = $this->baseQuery()->orderBy('tanggal')->orderBy('id')->get();

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Tanggal', 'Jenis', 'Subkategori', 'Penyewa', 'Jumlah', 'Petugas', 'Catatan']);

            foreach ($records as $record) {
                fputcsv($handle, [
                    optional($record->tanggal)->format('Y-m-d'),
                    $record->jenis,
                    $record->subkategori,
                    $record->tenant?->nama,
                    $record->jumlah,
                    $record->creator?->name,
                    $record->catatan,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
