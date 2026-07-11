<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:12|mixedCase|numbers|symbols',
            'role' => 'nullable|in:superadmin,admin,guru,staff',
            'is_active' => 'nullable|boolean',
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        // Prevent mass assignment of role - only admins can set roles
        if (auth()->user()->role !== 'superadmin') {
            unset($validated['role']);
        }
        
        // Set default role if not provided
        $validated['role'] = $validated['role'] ?? 'staff';
        $validated['is_active'] = $validated['is_active'] ?? true;
        
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:12|mixedCase|numbers|symbols',
            'role' => 'nullable|in:superadmin,admin,guru,staff',
            'is_active' => 'nullable|boolean',
            'phone' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);
        
        // Prevent mass assignment of role - only superadmin can change roles
        if (auth()->user()->role !== 'superadmin') {
            unset($validated['role']);
        }
        
        // Prevent user from deactivating themselves
        if ($user->id === auth()->id() && isset($validated['is_active']) && !$validated['is_active']) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }
        
        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

public function destroy(User $user)
{
    if ($user->id === auth()->id()) {
        return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
    }
    $user->delete();
    return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
}
}