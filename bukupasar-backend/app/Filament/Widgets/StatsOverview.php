<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $marketId = $user->market_id;

        $today = Carbon::today();

        $pemasukan = Transaction::forMarket($marketId)
            ->pemasukan()
            ->whereDate('tanggal', $today)
            ->sum('jumlah');

        $pengeluaran = Transaction::forMarket($marketId)
            ->pengeluaran()
            ->whereDate('tanggal', $today)
            ->sum('jumlah');

        $saldo = $pemasukan - $pengeluaran;

        return [
            Stat::make('Pemasukan Hari Ini', $this->formatCurrency($pemasukan))
                ->description('Total pemasukan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran Hari Ini', $this->formatCurrency($pengeluaran))
                ->description('Total pengeluaran hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Saldo Hari Ini', $this->formatCurrency($saldo))
                ->description('Pemasukan - Pengeluaran')
                ->color($saldo >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function formatCurrency(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
