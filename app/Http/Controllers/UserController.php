<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('admin-only');
        $users = User::withCount(['sales', 'purchases'])->orderBy('role')->orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,staff'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('admin-only');
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri yang sedang aktif.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,staff'],
        ]);

        $user->update(['role' => $validated['role']]);
        return redirect()->route('users.index')->with('success', "Role untuk {$user->name} berhasil diubah menjadi " . strtoupper($validated['role']) . ".");
    }
}