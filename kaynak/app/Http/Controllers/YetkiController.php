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
}
