<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna dengan filter pencarian dan role.
     */
    public function index(Request $request)
    {
        $roles = Role::all();
        $departments = Department::all();

        $users = User::with(['roles', 'department'])
            ->when($request->role, function ($query, $roleName) {
                return $query->role($roleName);
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    /**
     * Tampilkan form untuk membuat pengguna baru.
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.create', compact('roles', 'departments'));
    }

    /**
     * Tampilkan detail pengguna (Solusi untuk error UserController::show).
     */
    public function show(User $user)
    {
        $user->load(['roles', 'department']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Tampilkan form untuk mengedit pengguna.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::all();
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'departments', 'userRoles'));
    }

    // app/Http/Controllers/UserController.php

    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', Rules\Password::defaults()],
            'phone'         => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles'         => ['required', 'array', 'min:1'],
            'roles.*'       => ['exists:roles,id'],
        ], [
            'roles.required' => 'Pilih minimal satu role untuk pengguna ini.',
            'roles.min'      => 'Pilih minimal satu role untuk pengguna ini.',
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
        ]);

        // Ambil instance Role berdasarkan ID dari request
        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'         => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles'         => ['required', 'array', 'min:1'],
            'roles.*'       => ['exists:roles,id'],
        ], [
            'roles.required' => 'Pilih minimal satu role untuk pengguna ini.',
            'roles.min'      => 'Pilih minimal satu role untuk pengguna ini.',
        ]);

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => [Rules\Password::defaults()],
            ]);

            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // Ambil instance Role berdasarkan ID dari request
        $roles = Role::whereIn('id', $request->roles)->get();
        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
