<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, fn ($query, string $role) => $query->where('role', $role))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->when(
                in_array($request->input('sort'), ['name', 'email', 'role', 'is_active', 'created_at'], true),
                fn ($query) => $query->orderBy($request->input('sort'), $request->input('direction') === 'asc' ? 'asc' : 'desc'),
                fn ($query) => $query->latest()
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'role' => $request->role,
                'status' => $request->status,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
            'roles' => config('emadrasah.roles'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Form', [
            'roles' => config('emadrasah.roles'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'role' => ['required', Rule::in(array_keys(config('emadrasah.roles')))],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        AuditLogger::logModelChange(
            $user,
            'created',
            null,
            AuditLogger::sanitizeModelAttributes($user, $user->getAttributes())
        );

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Form', [
            'user' => $user->only(['id', 'name', 'email', 'phone', 'alamat', 'role', 'is_active']),
            'roles' => config('emadrasah.roles'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'role' => ['required', Rule::in(array_keys(config('emadrasah.roles')))],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === auth()->id() && (! $validated['is_active'] || $validated['role'] !== 'admin')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan atau mengubah role akun sendiri.');
        }

        $passwordChanged = $request->filled('password');

        if ($passwordChanged) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $dirty = AuditLogger::sanitizeModelAttributes($user, $user->fill($validated)->getDirty());
        if ($passwordChanged) {
            $dirty['password_changed'] = true;
        }
        $oldValues = AuditLogger::onlySanitizedKeys($user, $user->getOriginal(), array_keys($dirty));

        $user->save();

        if ($dirty !== []) {
            AuditLogger::logModelChange($user, 'updated', $oldValues, $dirty);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $oldValues = AuditLogger::sanitizeModelAttributes($user, $user->getOriginal());

        $user->delete();

        AuditLogger::logModelChange($user, 'deleted', $oldValues, null);

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
