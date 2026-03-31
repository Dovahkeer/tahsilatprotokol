<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTahsilatRequest;
use App\Http\Requests\TahsilatIptalRequest;
use App\Http\Requests\TahsilatReddetRequest;
use App\Http\Requests\UpdateTahsilatRequest;
use App\Http\Requests\UploadTahsilatDekontRequest;
use App\Models\Tahsilat;
use App\Models\TahsilatDekontu;
use App\Services\TahsilatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TahsilatController extends Controller
{
    public function __construct(
        private readonly TahsilatService $tahsilatService,
    ) {
    }

    public function list(Request $request): JsonResponse
    {
        $paginator = $this->tahsilatService->paginate($request->all());

        return response()->json([
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ]);
    }

    public function show(Tahsilat $tahsilat): JsonResponse
    {
        return response()->json($this->tahsilatService->detail($tahsilat));
    }

    public function store(StoreTahsilatRequest $request): JsonResponse
    {
        $tahsilat = $this->tahsilatService->create(
            $request->validated(),
            $request->file('dekont'),
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'tahsilat' => $this->tahsilatService->detail($tahsilat),
        ]);
    }

    public function update(UpdateTahsilatRequest $request, Tahsilat $tahsilat): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || (string) $tahsilat->created_by === (string) $request->user()->id, 403);

        $updated = $this->tahsilatService->update($tahsilat, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'tahsilat' => $this->tahsilatService->detail($updated),
        ]);
    }

    public function uploadDekont(UploadTahsilatDekontRequest $request, Tahsilat $tahsilat): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || (string) $tahsilat->created_by === (string) $request->user()->id, 403);

        $dekont = $this->tahsilatService->uploadDekont($tahsilat, $request->file('dekont'), $request->user());

        return response()->json([
            'success' => true,
            'dekont' => [
                'id' => (string) $dekont->id,
                'original_name' => $dekont->original_name,
            ],
        ]);
    }

    public function onayla(Tahsilat $tahsilat, Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || (bool) optional($request->user()->yetkiKaydi)->tahsilat_takip_sorumlusu, 403);

        $updated = $this->tahsilatService->approve($tahsilat, $request->user());

        return response()->json([
            'success' => true,
            'tahsilat' => $this->tahsilatService->detail($updated),
        ]);
    }

    public function reddet(TahsilatReddetRequest $request, Tahsilat $tahsilat): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || (bool) optional($request->user()->yetkiKaydi)->tahsilat_takip_sorumlusu, 403);

        $updated = $this->tahsilatService->reject($tahsilat, $request->validated('red_nedeni'), $request->user());

        return response()->json([
            'success' => true,
            'tahsilat' => $this->tahsilatService->detail($updated),
        ]);
    }

    public function iptal(TahsilatIptalRequest $request, Tahsilat $tahsilat): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $updated = $this->tahsilatService->cancel($tahsilat, $request->validated('iptal_nedeni'), $request->user());

        return response()->json([
            'success' => true,
            'tahsilat' => $this->tahsilatService->detail($updated),
        ]);
    }

    public function dekontView(TahsilatDekontu $dekont)
    {
        return $this->tahsilatService->dekontResponse($dekont);
    }
}
