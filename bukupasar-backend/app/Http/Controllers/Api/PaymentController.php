<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $query = Payment::forMarket($marketId)
            ->with(['tenant:id,market_id,nama,nomor_lapak,outstanding', 'creator:id,name'])
            ->latest('tanggal');

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
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

        $data = $request->validate([
            'tenant_id' => ['required', Rule::exists('tenants', 'id')->where('market_id', $marketId)],
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string'],
        ]);

        $payment = DB::transaction(function () use ($data, $request, $marketId) {
            /** @var Tenant $tenant */
            $tenant = Tenant::where('id', $data['tenant_id'])
                ->where('market_id', $marketId)
                ->lockForUpdate()
                ->firstOrFail();

            $outstanding = (int) $tenant->outstanding;

            if ($outstanding <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => ['Tenant ini tidak memiliki tunggakan.'],
                ]);
            }

            if ($data['jumlah'] > $outstanding) {
                throw ValidationException::withMessages([
                    'jumlah' => [sprintf(
                        'Pembayaran melebihi tunggakan. Maksimal Rp %s',
                        number_format($outstanding, 0, ',', '.')
                    )],
                ]);
            }

            $payment = Payment::create([
                ...$data,
                'market_id' => $marketId,
                'created_by' => $request->user()->id,
            ]);

            $newOutstanding = max(0, $outstanding - $data['jumlah']);
            $tenant->update(['outstanding' => $newOutstanding]);

            return $payment;
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat.',
            'data' => $payment->load(['tenant', 'creator']),
        ], 201);
    }

    protected function ensureCanCreate(Request $request): void
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin_pusat', 'admin_pasar', 'inputer']),
            403,
            'Anda tidak memiliki akses.'
        );
    }
}
