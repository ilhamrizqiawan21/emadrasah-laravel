<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $sorts = [
            'judul' => 'judul',
            'status' => 'status',
            'prioritas' => 'prioritas',
            'deadline' => 'deadline',
            'created' => 'created_at',
        ];
        $sort = $sorts[$request->input('sort')] ?? null;
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query = Task::with('assignedTo')
            ->when($request->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($request->prioritas, fn ($query, string $prioritas) => $query->where('prioritas', $prioritas))
            ->when($request->assigned_to, fn ($query, string $userId) => $query->where('assigned_to', $userId))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('judul', 'like', '%'.$request->search.'%')
                        ->orWhere('deskripsi', 'like', '%'.$request->search.'%');
                });
            });

        if ($sort) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderByRaw("case status when 'antrean' then 1 when 'proses' then 2 when 'selesai' then 3 else 4 end")
                ->orderByRaw("case prioritas when 'tinggi' then 1 when 'sedang' then 2 when 'rendah' then 3 else 4 end")
                ->orderBy('deadline');
        }

        $tasks = $query->paginate(15)
            ->withQueryString()
            ->through(fn (Task $task) => $this->taskPayload($task));
        $users = $this->userOptions();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'prioritas' => $request->prioritas,
                'assigned_to' => $request->assigned_to,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tasks/Form', [
            'users' => $this->userOptions(),
        ]);
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
            'keterangan' => 'Status awal: '.$task->status,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Task $task): Response
    {
        $task->load(['assignedTo', 'creator']);
        $logs = $task->logs()->with('user')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Tasks/Show', [
            'task' => $this->taskPayload($task),
            'logs' => $logs->map(fn (TaskLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'keterangan' => $log->keterangan,
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                ] : null,
            ]),
        ]);
    }

    public function edit(Task $task): Response
    {
        $task->load(['assignedTo', 'creator']);

        return Inertia::render('Tasks/Form', [
            'task' => $this->taskPayload($task),
            'users' => $this->userOptions(),
        ]);
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
                'new_status' => $request->status,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Status tugas diperbarui.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus.');
    }

    private function userOptions()
    {
        return User::orderBy('name')->get(['id', 'name', 'email']);
    }

    private function taskPayload(Task $task): array
    {
        return [
            'id' => $task->id,
            'judul' => $task->judul,
            'deskripsi' => $task->deskripsi,
            'assigned_to' => $task->assigned_to,
            'assigned_to_user' => $task->assignedTo ? [
                'id' => $task->assignedTo->id,
                'name' => $task->assignedTo->name,
                'email' => $task->assignedTo->email,
            ] : null,
            'created_by' => $task->created_by,
            'creator' => $task->creator ? [
                'id' => $task->creator->id,
                'name' => $task->creator->name,
            ] : null,
            'prioritas' => $task->prioritas,
            'deadline' => $task->deadline?->toDateString(),
            'status' => $task->status,
            'kategori' => $task->kategori,
            'progress_persen' => $task->progress_persen ?? 0,
            'attachment_url' => $task->attachment ? asset('storage/'.$task->attachment) : null,
            'created_at' => $task->created_at?->format('Y-m-d H:i'),
            'updated_at' => $task->updated_at?->format('Y-m-d H:i'),
            'is_overdue' => $task->deadline && $task->deadline->isPast() && $task->status !== 'selesai',
        ];
    }
}
