<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Kotak masuk notifikasi pribadi; berlaku untuk semua role dan hanya menyentuh notifikasi milik sendiri. */
class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = $request->user()->notifications()->paginate(15);

        return view('notifikasi.index', compact('notifikasi'));
    }

    /** Tandai dibaca lalu lanjut ke halaman tujuan notifikasi. */
    public function baca(Request $request, string $id)
    {
        $notifikasi = $request->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();

        return redirect($notifikasi->data['url'] ?? route('notifikasi.index'));
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
