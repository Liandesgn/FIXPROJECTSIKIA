<?php

namespace App\Http\Controllers\SuperAdmin;


use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanPdfController extends Controller
{
    public function download(Request $request)
    {
        $laporanController = app(LaporanController::class);
        $data = $laporanController->buildData($request);

        $tab = $data['tab'];
        $judul = $data['judulTab'][$tab];

        $dataPdf = match ($tab) {
            'rekap-kesehatan' => $data['rekapWilayah'],
            'kegiatan-posyandu' => $data['kegiatanPosyandu'],
            'data-risiko' => $data['dataRisiko'],
            'rekap-pemeriksaan' => $data['rekapPemeriksaan'],
        };

        $pdf = Pdf::loadView('superadmin.laporan.pdf', [
            'judul' => $judul,
            'periode' => $data['periode'],
            'namaWilayah' => $data['namaWilayah'],
            'tab' => $tab,
            'data' => $dataPdf,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'laporan-'.$tab.'-'.$data['periode'].'.pdf'
        );
    }
}
