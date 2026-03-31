<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProtokolRequest;
use App\Http\Requests\UpdateProtokolRequest;
use App\Http\Requests\UploadProtokolPdfRequest;
use App\Models\Protokol;
use App\Services\LookupService;
use App\Services\ProtokolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProtokolController extends Controller
{
    public function __construct(
        private readonly ProtokolService $protokolService,
        private readonly LookupService $lookupService,
    ) {
    }

    public function list(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['muvekkil_ids'] = $request->filled('muvekkil_ids')
            ? array_filter(explode(',', (string) $request->string('muvekkil_ids')))
            : [];

        $result = $this->protokolService->paginate($filters, $request->user());
        $paginator = $result['paginator'];

        return response()->json([
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'filtre_taksit_ozeti' => $result['filtre_taksit_ozeti'],
        ]);
    }

    public function show(Protokol $protokol, Request $request): JsonResponse
    {
        return response()->json($this->protokolService->detail($protokol, $request->user()));
    }

    public function store(StoreProtokolRequest $request): JsonResponse
    {
        $protokol = $this->protokolService->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'protokol' => $this->protokolService->detail($protokol, $request->user()),
        ]);
    }

    public function update(UpdateProtokolRequest $request, Protokol $protokol): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || $this->protokolService->canEdit($protokol, $request->user()), 403);

        $updated = $this->protokolService->update($protokol, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'protokol' => $this->protokolService->detail($updated, $request->user()),
        ]);
    }

    public function uploadPdf(UploadProtokolPdfRequest $request, Protokol $protokol): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || $this->protokolService->canEdit($protokol, $request->user()), 403);

        $updated = $this->protokolService->uploadPdf($protokol, $request->file('pdf'));

        return response()->json([
            'success' => true,
            'path' => $updated->protokol_pdf_dosya_yolu,
        ]);
    }

    public function pdf(Protokol $protokol)
    {
        return $this->protokolService->pdfResponse($protokol);
    }

    public function hacizciler(): JsonResponse
    {
        return response()->json($this->lookupService->listHacizciler());
    }

    public function portfoyler(): JsonResponse
    {
        return response()->json($this->lookupService->listPortfoyler());
    }

    public function portfoylerByMuvekkil(string $muvekkilId): JsonResponse
    {
        return response()->json($this->lookupService->listPortfoyler($muvekkilId));
    }

    public function borcluAra(Request $request): JsonResponse
    {
        $rows = $this->protokolService->forTahsilatSearch(
            $request->string('borclu_adi')->toString() ?: null,
            $request->string('borclu_tckn_vkn')->toString() ?: null,
            (int) $request->integer('limit', 25),
        );

        return response()->json($rows);
    }
}
