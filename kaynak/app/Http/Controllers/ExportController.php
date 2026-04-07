<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {
    }

    public function excel()
    {
        return $this->exportService->excel();
    }

    public function mailOrderPdf()
    {
        return $this->exportService->mailOrderPdf();
    }

    // YENİ: Vade takip indirme köprüsü
    public function vadeTakip(Request $request)
    {
        return $this->exportService->vadeTakip($request);
    }
}