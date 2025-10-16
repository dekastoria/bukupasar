<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $query = Transaction::forMarket($marketId)
            ->with(['tenant:id,market_id,nama,nomor_lapak', 'creator:id,name'])
            ->latest('tanggal');

        if ($request->filled('jenis')) {
            $query->jenis($request->string('jenis'));
        }

        if ($request->filled('subkategori')) {
            $query->subkategori($request->string('subkategori'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->string('from'));
            $to = Carbon::parse($request->string('to'));
            $query->dateRange($from, $to);
        } elseif ($request->filled('date')) {
            $query->byDate(Carbon::parse($request->string('date')));
        }

        $perPage = (int) $request->input('per_page', 15);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureCanCreate($request);

        $marketId = $request->user()->market_id;

        $data = $request->validate($this->rules($marketId));

        $this->validateBusinessRules($request, $data, $marketId);

        $transaction = Transaction::create([
            ...$data,
            'market_id' => $marketId,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat.',
            'data' => $transaction->load(['tenant', 'creator']),
        ], 201);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorizeTransaction($request, $transaction);

        return response()->json([
            'data' => $transaction->load(['tenant', 'creator']),
        ]);
    }

    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorizeTransaction($request, $transaction);
        $this->ensureCanMutate($request->user(), $transaction);

        $marketId = $request->user()->market_id;

        $data = $request->validate($this->rules($marketId, $transaction->id, partial: true));

        $current = $transaction->only(['tanggal', 'jenis', 'subkategori', 'jumlah', 'tenant_id', 'catatan']);
        $this->validateBusinessRules($request, array_merge($current, $data), $marketId);

        $transaction->update($data);

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => $transaction->fresh()->load(['tenant', 'creator']),
        ]);
    }

    public function destroy(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorizeTransaction($request, $transaction);
        $this->ensureCanMutate($request->user(), $transaction);

        $transaction->delete();

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    protected function rules(int $marketId, ?int $transactionId = null, bool $partial = false): array
    {
        $baseRules = [
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', Rule::in(['pemasukan', 'pengeluaran'])],
            'subkategori' => ['required', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tenant_id' => ['nullable', Rule::exists('tenants', 'id')->where('market_id', $marketId)],
            'catatan' => ['nullable', 'string'],
        ];

        if ($partial) {
            return array_map(function ($rules) {
                if (is_array($rules)) {
                    return array_merge(['sometimes'], $rules);
                }

                return ['sometimes', $rules];
            }, $baseRules);
        }

        return $baseRules;
    }

    protected function validateBusinessRules(Request $request, array $data, int $marketId): void
    {
        $tanggal = Carbon::parse($data['tanggal']);
        $backdateDays = (int) \App\Models\Setting::getValue($marketId, 'backdate_days', 60);

        abort_if(
            now()->diffInDays($tanggal, false) > 0,
            422,
            'Tanggal tidak boleh di masa depan.'
        );

        abort_if(
            now()->diffInDays($tanggal, false) < -$backdateDays,
            422,
            sprintf('Tanggal tidak boleh lebih dari %d hari yang lalu.', $backdateDays)
        );

        $allowedDaysMode = \App\Models\Setting::getValue($marketId, 'allowed_days_mode', 'everyday');
        $allowedDays = array_filter(explode(',', (string) \App\Models\Setting::getValue($marketId, 'allowed_days_values', '')));

        if ($allowedDaysMode === 'selected' && ! empty($allowedDays)) {
            $isoDay = $tanggal->dayOfWeekIso;

            abort_if(
                ! in_array((string) $isoDay, $allowedDays, true),
                422,
                'Tanggal ini tidak diperbolehkan untuk input transaksi.'
            );
        }

        $category = Category::forMarket($marketId)
            ->where('jenis', $data['jenis'])
            ->where('nama', $data['subkategori'])
            ->first();

        abort_if(! $category, 422, 'Subkategori tidak valid.');

        if ($category->wajib_keterangan) {
            abort_if(
                empty($data['catatan']),
                422,
                'Kategori ini mewajibkan catatan.'
            );
        }

        if ($category->nama === 'Sewa') {
            abort_if(
                empty($data['tenant_id']),
                422,
                'Tenant wajib dipilih untuk transaksi sewa.'
            );
        }
    }

    protected function ensureCanCreate(Request $request): void
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin_pusat', 'admin_pasar', 'inputer']),
            403,
            'Anda tidak memiliki akses.'
        );
    }

    protected function authorizeTransaction(Request $request, Transaction $transaction): void
    {
        abort_unless($transaction->market_id === $request->user()->market_id, 404);
    }

    protected function ensureCanMutate($user, Transaction $transaction): void
    {
        if ($user->hasRole(['admin_pusat', 'admin_pasar'])) {
            return;
        }

        abort_unless($user->hasRole('inputer'), 403, 'Anda tidak memiliki akses.');

        abort_unless($transaction->created_by === $user->id, 403, 'Anda hanya dapat mengubah transaksi milik sendiri.');

        abort_unless(
            $transaction->created_at && $transaction->created_at->diffInHours(now()) <= (int) \App\Models\Setting::getValue($transaction->market_id, 'inputer_edit_window_hours', 24),
            403,
            'Batas waktu edit transaksi telah terlewati.'
        );
    }
}
