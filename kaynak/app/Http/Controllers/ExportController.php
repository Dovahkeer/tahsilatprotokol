<?php

namespace App\Http\Controllers;

use App\Services\ExportService;

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
}
