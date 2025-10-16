<?php

namespace App\Filament\Pages;

use App\Models\Market;
use App\Models\Transaction;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string | null $navigationLabel = 'Laporan Bulanan';

    protected static ?int $navigationSort = 61;

    public ?string $filterMonth = null;

    public ?int $filterMarketId = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->filterMonth = Carbon::today()->format('Y-m');
        $this->filterMarketId = $user?->market_id;
    }

    public function getTitle(): string | Htmlable
    {
        $month = $this->filterMonth ?: Carbon::today()->format('Y-m');
        try {
            $date = Carbon::createFromFormat('Y-m', $month);
            return "Laporan Bulanan - " . $date->translatedFormat('F Y');
        } catch (\Exception) {
            return 'Laporan Bulanan';
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin_pusat', 'admin_pasar']) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filter')
                ->label('Filter')
                ->icon('heroicon-o-funnel')
                ->modalHeading('Filter Laporan Bulanan')
                ->form([
                    Forms\Components\TextInput::make('month')
                        ->label('Bulan')
                        ->prefixIcon('heroicon-m-calendar')
                        ->default($this->filterMonth)
                        ->rule('date_format:Y-m')
                        ->placeholder('YYYY-MM'),
                    Forms\Components\Select::make('market_id')
                        ->label('Pasar')
                        ->options(fn () => Market::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->visible(fn () => auth()->user()?->hasRole('admin_pusat'))
                        ->default($this->filterMarketId),
                ])
                ->action(function (array $data): void {
                    $this->filterMonth = $data['month'] ?? $this->filterMonth;

                    if (auth()->user()?->hasRole('admin_pusat')) {
                        $this->filterMarketId = $data['market_id'] ?? null;
                    }

                    $this->resetTable();
                }),
            Actions\Action::make('reset')
                ->label('Reset')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    $user = auth()->user();

                    $this->filterMonth = Carbon::today()->format('Y-m');
                    $this->filterMarketId = $user?->hasRole('admin_pusat') ? null : $user?->market_id;

                    $this->resetTable();
                }),
            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->export()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('total_pemasukan')
                    ->label('Total Pemasukan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(false),
                Tables\Columns\TextColumn::make('total_pengeluaran')
                    ->label('Total Pengeluaran')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(false),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(false),
            ])
            ->defaultSort('tanggal', 'asc')
            ->paginated([10, 15, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        $query = Transaction::query()
            ->selectRaw('DATE(tanggal) as tanggal')
            ->selectRaw('SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END) as total_pemasukan')
            ->selectRaw('SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END) as total_pengeluaran')
            ->selectRaw('SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE -jumlah END) as saldo')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy(DB::raw('DATE(tanggal)'), 'asc');

        if (! $user) {
            return $query->whereRaw('1=0');
        }

        if ($user->hasRole('admin_pasar')) {
            $query->where('market_id', $user->market_id);
        } elseif ($user->hasRole('admin_pusat')) {
            if ($this->filterMarketId) {
                $query->where('market_id', $this->filterMarketId);
            }
        } else {
            return $query->whereRaw('1=0');
        }

        $month = $this->filterMonth ?: Carbon::today()->format('Y-m');

        try {
            $parsed = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception) {
            $parsed = Carbon::today()->startOfMonth();
        }

        $query->whereBetween('tanggal', [$parsed->copy()->startOfMonth(), $parsed->copy()->endOfMonth()]);

        return $query;
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        $month = $this->filterMonth ?: Carbon::today()->format('Y-m');
        try {
            $date = Carbon::createFromFormat('Y-m', $month);
            return 'Belum ada transaksi pada ' . $date->translatedFormat('F Y');
        } catch (\Exception) {
            return 'Belum ada transaksi pada bulan ini.';
        }
    }

    public function export(): StreamedResponse
    {
        $month = $this->filterMonth ?: Carbon::today()->format('Y-m');
        $filename = sprintf('laporan-bulanan-%s.csv', $month);

        $records = $this->getTableQuery()->get();

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Tanggal', 'Total Pemasukan', 'Total Pengeluaran', 'Saldo']);

            foreach ($records as $record) {
                fputcsv($handle, [
                    Carbon::parse($record->tanggal)->format('Y-m-d'),
                    $record->total_pemasukan,
                    $record->total_pengeluaran,
                    $record->saldo,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
