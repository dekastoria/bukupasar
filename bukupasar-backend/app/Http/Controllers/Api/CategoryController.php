<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $query = Category::forMarket($marketId)->orderBy('nama');

        if ($request->filled('jenis')) {
            $query->jenis($request->string('jenis'));
        }

        if ($request->has('aktif')) {
            $query->where('aktif', (bool) $request->boolean('aktif'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function byJenis(Request $request, string $jenis): JsonResponse
    {
        abort_unless(in_array($jenis, ['pemasukan', 'pengeluaran'], true), 404);

        $categories = Category::forMarket($request->user()->market_id)
            ->jenis($jenis)
            ->orderBy('nama')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
