<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\PrimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TahsilatDashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly PrimService $primService,
    ) {
    }

    public function dashboardData(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessTab('dashboard'), 403);

        return response()->json($this->dashboardService->build());
    }

    public function beklentiProtokoller(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessTab('dashboard'), 403);

        return response()->json(
            $this->dashboardService->expectationProtocols($request->query('muvekkil_id'))
        );
    }

    public function primPivot(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAccessTab('prim'), 403);

        return response()->json(
            $this->primService->pivotTable(
                (int) $request->integer('ay', now()->month),
                (int) $request->integer('yil', now()->year),
            )
        );
    }
}
