<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSpreadsheetRequest;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __construct(
        private readonly ImportService $importService,
    ) {
    }

    public function protokolTemplate()
    {
        return $this->importService->protokolTemplate();
    }

    public function protokolImport(ImportSpreadsheetRequest $request): JsonResponse
    {
        return response()->json(
            $this->importService->importProtokoller($request->file('file'), $request->user())
        );
    }

    public function izlenceTemplate()
    {
        return $this->importService->izlenceTemplate();
    }

    public function izlenceImport(ImportSpreadsheetRequest $request): JsonResponse
    {
        return response()->json(
            $this->importService->importIzlenceGecmis($request->file('file'))
        );
    }
}
