<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RentalType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $marketId = $request->user()->market_id;

        $rentalTypes = RentalType::where('market_id', $marketId)
            ->orderBy('nama')
            ->get();

        return response()->json([
            'data' => $rentalTypes,
        ]);
    }
}
