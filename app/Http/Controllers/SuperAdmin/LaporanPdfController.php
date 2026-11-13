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

        // Cek apakah laporan pada tab yang dipilih kosong.
        if ($data['kosong']) {
            return redirect()
                ->route('superadmin.laporan.index', $request->query())
                ->with('error', 'Belum ada data untuk laporan ini. Silakan pilih periode lain.');
        }

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
