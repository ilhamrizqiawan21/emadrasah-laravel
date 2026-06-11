<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
public function index(Request $request)
{
    $query = Task::orderByRaw("FIELD(status, 'antrean', 'proses', 'selesai')");
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('prioritas')) {
        $query->where('prioritas', $request->prioritas);
    }
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('judul', 'like', '%' . $request->search . '%')
              ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
        });
    }
    
    $tasks = $query->paginate(15);
    return view('tasks.index', compact('tasks'));
}

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required',
            'deskripsi' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'deadline' => 'nullable|date',
            'status' => 'required|in:antrean,proses,selesai',
            'kategori' => 'nullable|string|max:50',
            'progress_persen' => 'nullable|integer|min:0|max:100',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('task-attachments', 'public');
            $validated['attachment'] = $path;
        }

        $validated['created_by'] = Auth::id();

        $task = Task::create($validated);

        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'Tugas dibuat',
            'keterangan' => 'Status awal: ' . $task->status,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Task $task)
    {
        $logs = $task->logs()->with('user')->orderBy('created_at', 'desc')->get();
        return view('tasks.show', compact('task', 'logs'));
    }

    public function edit(Task $task)
    {
        $users = User::orderBy('name')->get();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'judul' => 'required',
            'deskripsi' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'deadline' => 'nullable|date',
            'status' => 'required|in:antrean,proses,selesai',
            'kategori' => 'nullable|string|max:50',
            'progress_persen' => 'nullable|integer|min:0|max:100',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('task-attachments', 'public');
            $validated['attachment'] = $path;
        }

        $task->update($validated);

        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'Tugas diperbarui',
            'keterangan' => 'Data tugas diubah',
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diupdate.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:antrean,proses,selesai']);
        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);
        
        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'action' => 'Status berubah',
            'keterangan' => "Dari '{$oldStatus}' menjadi '{$request->status}'",
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status tugas diperbarui.',
                'new_status' => $request->status
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Status tugas diperbarui.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus.');
    }
}