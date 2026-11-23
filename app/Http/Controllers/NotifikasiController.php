<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan notifikasi milik pengguna yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifikasi = $user->notifications()
            ->latest()
            ->paginate(10);

        $jumlahBelumDibaca = $user->unreadNotifications()->count();

        return view('notifikasi.index', compact(
            'notifikasi',
            'jumlahBelumDibaca'
        ));
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(
        Request $request,
        string $notifikasi
    ): RedirectResponse {
        $data = $request->user()
            ->notifications()
            ->whereKey($notifikasi)
            ->firstOrFail();

        if (is_null($data->read_at)) {
            $data->markAsRead();
        }

        return back()->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Menandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }
}
