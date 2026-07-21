<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles');

            if ($search = request('search.value')) {
                $users->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($filterRole = request('filter_role')) {
                $users->whereHas('roles', fn($q) => $q->where('id', $filterRole));
            }

            if ($filterStatus = request('filter_status')) {
                $users->where('is_active', $filterStatus === 'active');
            }

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('action', function ($user) {
                    $btn = '<div class="d-flex gap-1">';
                    $btn .= '<a href="' . route('settings.users.show', $user->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('settings.users.edit', $user->id) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button onclick="toggleActive(' . $user->id . ', \'' . $user->name . '\')" class="btn btn-sm ' . ($user->is_active ? 'btn-secondary' : 'btn-success') . '"><i class="fas fa-' . ($user->is_active ? 'ban' : 'check') . '"></i></button>';
                    $btn .= '<button onclick="deleteConfirm(' . $user->id . ', \'' . $user->name . '\')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('role', function ($user) {
                    return $user->roles->pluck('name')->map(fn($r) => '<span class="badge bg-info me-1">' . $r . '</span>')->implode('');
                })
                ->editColumn('is_active', function ($user) {
                    return $user->is_active
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-danger">Nonaktif</span>';
                })
                ->editColumn('last_login_at', fn($u) => $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('d/m/Y H:i') : '-')
                ->editColumn('created_at', fn($u) => $u->created_at->format('d/m/Y H:i'))
                ->rawColumns(['action', 'role', 'is_active'])
                ->make(true);
        }

        $roles = Role::orderBy('name')->get();
        return view('settings.users.index', compact('roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $user = new User();
        return view('settings.users.form', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:50',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = $validated['is_active'] ?? true;

            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => $validated['password'],
                'phone'     => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            if (!empty($validated['roles'])) {
                $user->roles()->attach($validated['roles']);
            }

            ActivityLogger::log(
                'User',
                'create',
                "Pengguna {$user->name} berhasil dibuat",
                'user',
                $user->id,
                $user->toArray()
            );

            DB::commit();

            return redirect()->route('settings.users.index')
                ->with('success', 'Pengguna berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan pengguna: ' . $e->getMessage());
        }
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('settings.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');
        return view('settings.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => 'nullable|string|max:50',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['is_active'] = $validated['is_active'] ?? true;

            if ($request->filled('password')) {
                $request->validate(['password' => 'string|min:8|confirmed']);
                $validated['password'] = Hash::make($request->password);
            }

            $user->update($validated);

            if (isset($validated['roles'])) {
                $user->roles()->sync($validated['roles']);
            }

            ActivityLogger::log(
                'User',
                'update',
                "Pengguna {$user->name} berhasil diperbarui",
                'user',
                $user->id,
                $user->toArray()
            );

            DB::commit();

            return redirect()->route('settings.users.index')
                ->with('success', 'Pengguna berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            $userName = $user->name;
            $user->delete();

            ActivityLogger::log(
                'User',
                'delete',
                "Pengguna {$userName} berhasil dihapus",
                'user',
                $user->id
            );

            DB::commit();

            return redirect()->route('settings.users.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

            ActivityLogger::log(
                'User',
                'toggleActive',
                "Pengguna {$user->name} berhasil {$status}",
                'user',
                $user->id
            );

            DB::commit();

            return redirect()->route('settings.users.index')
                ->with('success', "Pengguna berhasil {$status}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengubah status pengguna: ' . $e->getMessage());
        }
    }
}
