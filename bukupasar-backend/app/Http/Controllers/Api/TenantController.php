<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $query = Tenant::forMarket($marketId)->orderByDesc('created_at');

        if ($request->filled('q')) {
            $query->search($request->string('q'));
        }

        $perPage = (int) $request->input('per_page', 15);

        $tenants = $query->paginate($perPage);

        return response()->json($tenants);
    }

    public function store(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:200'],
            'nomor_lapak' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tenants', 'nomor_lapak')->where('market_id', $marketId),
            ],
            'hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'foto_profile' => ['nullable', 'string', 'max:255'],
            'foto_ktp' => ['nullable', 'string', 'max:255'],
            'outstanding' => ['nullable', 'integer', 'min:0'],
        ]);

        $tenant = Tenant::create([
            ...$data,
            'market_id' => $marketId,
            'outstanding' => $data['outstanding'] ?? 0,
        ]);

        return response()->json([
            'message' => 'Tenant berhasil dibuat.',
            'data' => $tenant,
        ], 201);
    }

    public function show(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorizeTenant($request, $tenant);

        return response()->json([
            'data' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorizeTenant($request, $tenant);

        $marketId = $request->user()->market_id;

        $data = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:200'],
            'nomor_lapak' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('tenants', 'nomor_lapak')
                    ->where('market_id', $marketId)
                    ->ignore($tenant->id),
            ],
            'hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'foto_profile' => ['nullable', 'string', 'max:255'],
            'foto_ktp' => ['nullable', 'string', 'max:255'],
            'outstanding' => ['nullable', 'integer', 'min:0'],
        ]);

        $tenant->update($data);

        return response()->json([
            'message' => 'Tenant berhasil diperbarui.',
            'data' => $tenant,
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorizeTenant($request, $tenant);

        $tenant->delete();

        return response()->json([
            'message' => 'Tenant berhasil dihapus.',
        ]);
    }

    public function search(Request $request, string $query): JsonResponse
    {
        $results = Tenant::forMarket($request->user()->market_id)
            ->with('rentalType')
            ->search($query)
            ->orderBy('nomor_lapak')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $results,
        ]);
    }

    protected function authorizeTenant(Request $request, Tenant $tenant): void
    {
        abort_unless($tenant->market_id === $request->user()->market_id, 404);
    }
}
