<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHacizciKademeRequest;
use App\Http\Requests\UpdateMuvekkilPrimOranlariRequest;
use App\Http\Requests\UpdatePrimKademeAsamaRequest;
use App\Http\Requests\UpdatePrimKademePayRequest;
use App\Http\Requests\UpdateTahsilatYetkiRequest;
use App\Models\User;
use App\Services\YetkiService;
use Illuminate\Http\JsonResponse;

class YetkiController extends Controller
{
    public function __construct(
        private readonly YetkiService $yetkiService,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->yetkiService->users());
    }

    public function update(UpdateTahsilatYetkiRequest $request, User $user): JsonResponse
    {
        $record = $this->yetkiService->updateUserPermissions($user, $request->validated());

        return response()->json(['success' => true, 'record' => $record]);
    }

    public function primAyarlar(): JsonResponse
    {
        return response()->json($this->yetkiService->primAyarlar());
    }

    public function kademePay(UpdatePrimKademePayRequest $request): JsonResponse
    {
        $this->yetkiService->updateKademePay($request->validated('pay_oranlari'), $request->user());

        return response()->json(['success' => true]);
    }

    public function kademeAsama(UpdatePrimKademeAsamaRequest $request): JsonResponse
    {
        $this->yetkiService->updateKademeAsama($request->validated('asamalar'), $request->user());

        return response()->json(['success' => true]);
    }

    public function hacizciKademe(UpdateHacizciKademeRequest $request): JsonResponse
    {
        $this->yetkiService->updateHacizciKademe($request->validated('hacizciler'), $request->user());

        return response()->json(['success' => true]);
    }

    public function muvekkilOranlari(UpdateMuvekkilPrimOranlariRequest $request): JsonResponse
    {
        $this->yetkiService->updateMuvekkilOranlari($request->validated('muvekkil_oranlari'), $request->user());

        return response()->json(['success' => true]);
    }

    public function hacizciEkle(\Illuminate\Http\Request $request): JsonResponse
    {
        // Gelen verinin düzgün olup olmadığını kontrol et
        $validated = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'sicil_no' => ['nullable', 'string', 'max:255'],
            'kademe' => ['required', 'string'],
        ]);

        // Veritabanı işlemi için servise gönder
        $this->yetkiService->createHacizci($validated, $request->user());

        return response()->json(['success' => true]);
    }

    public function kademeEkle(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kademe_no' => ['required', 'integer', 'min:1'],
            'varsayilan_prim_orani' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $this->yetkiService->createKademe($validated, $request->user());

        return response()->json(['success' => true]);
    }

    public function portfoyEkle(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'muvekkil_id' => ['required', 'exists:muvekkiller,id'],
            'ad' => ['required', 'string', 'max:255'],
            'kod' => ['nullable', 'string', 'max:255'],
        ]);

        $this->yetkiService->createPortfoy($validated, $request->user());

        return response()->json(['success' => true]);
    }

    public function portfoyGuncelle(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'ad' => ['nullable', 'string', 'max:255'],
            'kod' => ['nullable', 'string', 'max:255'],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $this->yetkiService->updatePortfoy($id, $validated, $request->user());

        return response()->json(['success' => true]);
    }
}
