<?php

namespace App\Http\Controllers;

use App\Services\LookupService;
use Illuminate\Http\JsonResponse;

class MuvekkilController extends Controller
{
    public function __construct(
        private readonly LookupService $lookupService,
    ) {
    }

    public function list(): JsonResponse
    {
        return response()->json($this->lookupService->listMuvekkiller());
    }
}
