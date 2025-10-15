<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function daily(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;
        $date = Carbon::parse($request->input('date', now()->format('Y-m-d')));

        $transactions = Transaction::forMarket($marketId)
            ->byDate($date)
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->with(['tenant:id,market_id,nama,nomor_lapak', 'creator:id,name'])
            ->get();

        $totals = [
            'pemasukan' => $transactions->where('jenis', 'pemasukan')->sum('jumlah'),
            'pengeluaran' => $transactions->where('jenis', 'pengeluaran')->sum('jumlah'),
        ];

        return response()->json([
            'date' => $date->toDateString(),
            'totals' => $totals,
            'saldo' => $totals['pemasukan'] - $totals['pengeluaran'],
            'transactions' => $transactions,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->startOfMonth();
        $to = $toInput ? Carbon::parse($toInput)->endOfDay() : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $transactions = Transaction::forMarket($marketId)
            ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
            ->get();

        $pemasukan = $transactions->where('jenis', 'pemasukan')->sum('jumlah');
        $pengeluaran = $transactions->where('jenis', 'pengeluaran')->sum('jumlah');

        $byCategory = $transactions
            ->groupBy(fn (Transaction $tx) => $tx->jenis.'|'.$tx->subkategori)
            ->map(function ($group) {
                /** @var Transaction $first */
                $first = $group->first();

                return [
                    'jenis' => $first->jenis,
                    'subkategori' => $first->subkategori,
                    'total' => $group->sum('jumlah'),
                ];
            })
            ->values();

        return response()->json([
            'range' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'totals' => [
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo' => $pemasukan - $pengeluaran,
            ],
            'by_category' => $byCategory,
        ]);
    }

    public function cashbook(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;
        $date = Carbon::parse($request->input('date', now()->format('Y-m-d')));

        $transactions = Transaction::forMarket($marketId)
            ->whereDate('tanggal', '<=', $date)
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

        $running = 0;
        $entries = $transactions->map(function (Transaction $transaction) use (&$running) {
            $running += $transaction->isPemasukan() ? $transaction->jumlah : -$transaction->jumlah;

            return [
                'id' => $transaction->id,
                'tanggal' => $transaction->tanggal->toDateString(),
                'jenis' => $transaction->jenis,
                'subkategori' => $transaction->subkategori,
                'jumlah' => $transaction->jumlah,
                'saldo' => $running,
            ];
        });

        return response()->json([
            'date' => $date->toDateString(),
            'entries' => $entries,
            'closing_balance' => $running,
        ]);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;
        $month = Carbon::parse($request->input('month', now()->format('Y-m')));

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $transactions = Transaction::forMarket($marketId)
            ->dateRange($start, $end)
            ->get();

        $pemasukan = $transactions->where('jenis', 'pemasukan')->sum('jumlah');
        $pengeluaran = $transactions->where('jenis', 'pengeluaran')->sum('jumlah');

        $byCategory = $transactions
            ->groupBy(['jenis', 'subkategori'])
            ->map(function ($jenisGroup) {
                return $jenisGroup->map(function ($items) {
                    return $items->sum('jumlah');
                });
            });

        return response()->json([
            'month' => $month->format('Y-m'),
            'totals' => [
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'laba_rugi' => $pemasukan - $pengeluaran,
            ],
            'breakdown' => $byCategory,
        ]);
    }
}
