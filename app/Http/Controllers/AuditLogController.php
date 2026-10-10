<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'jenis' => 'nullable|in:'.implode(',', AuditLog::JENIS),
            'aksi' => 'nullable|in:'.implode(',', AuditLog::AKSI),
            'user_id' => 'nullable|integer',
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'cari' => 'nullable|string|max:100',
        ]);

        $tipe = $request->jenis ? array_search($request->jenis, AuditLog::JENIS, true) : null;

        $logs = AuditLog::query()
            ->when($tipe, fn ($q) => $q->where('auditable_type', $tipe))
            ->when($request->aksi, fn ($q) => $q->where('aksi', $request->aksi))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->dari, fn ($q) => $q->whereDate('created_at', '>=', $request->dari))
            ->when($request->sampai, fn ($q) => $q->whereDate('created_at', '<=', $request->sampai))
            ->when($request->cari, fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('label', 'like', '%'.$request->cari.'%')->orWhere('user_nama', 'like', '%'.$request->cari.'%');
            }))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);
        $jenisList = AuditLog::JENIS;

        return view('audit-log.index', compact('logs', 'users', 'jenisList'));
    }
}
