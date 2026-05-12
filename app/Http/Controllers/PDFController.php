<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Http\Controllers\AOSController;

class PDFController extends Controller
{
    // ─── EXPORT PDF ───
    public function aosPdf(Request $request)
    {

        $AOSController = new AOSController();
        $data = $AOSController->getAosReportData($request);

        $pdf = Pdf::loadView('pdf.aos', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('AOS-Report-' .  date('Y-m', strtotime($data['period'])) . '.pdf');
    }
}
