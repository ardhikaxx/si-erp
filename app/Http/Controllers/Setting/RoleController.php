<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::withCount('users', 'permissions');

            if ($search = request('search.value')) {
                $roles->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('action', function ($role) {
                    $btn = '<div class="d-flex gap-1">';
                    $btn .= '<a href="' . route('settings.roles.show', $role->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('settings.roles.edit', $role->id) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    if (!$role->is_system) {
                        $btn .= '<button onclick="deleteConfirm(' . $role->id . ', \'' . $role->name . '\')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('is_system', function ($role) {
                    return $role->is_system
                        ? '<span class="badge bg-danger">Sistem</span>'
                        : '<span class="badge bg-success">Kustom</span>';
                })
                ->editColumn('created_at', fn($r) => $r->created_at->format('d/m/Y H:i'))
                ->rawColumns(['action', 'is_system'])
                ->make(true);
        }

        return view('settings.roles.index');
    }

    public function create()
    {
        $role = new Role();
        return view('settings.roles.form', compact('role'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'is_system'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['guard_name'] = 'web';
            $validated['is_system'] = $validated['is_system'] ?? false;
            $role = Role::create($validated);

            ActivityLogger::log(
                'Role',
                'create',
                "Role {$role->name} berhasil dibuat",
                'role',
                $role->id,
                $role->toArray()
            );

            DB::commit();

            return redirect()->route('settings.roles.index')
                ->with('success', 'Role berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan role: ' . $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('settings.roles.show', compact('role', 'permissions'));
    }

    public function edit(Role $role)
    {
        return view('settings.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $role->update($validated);

            ActivityLogger::log(
                'Role',
                'update',
                "Role {$role->name} berhasil diperbarui",
                'role',
                $role->id,
                $role->toArray()
            );

            DB::commit();

            return redirect()->route('settings.roles.index')
                ->with('success', 'Role berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->back()->with('error', 'Role sistem tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $roleName = $role->name;
            $role->permissions()->detach();
            $role->delete();

            ActivityLogger::log(
                'Role',
                'delete',
                "Role {$roleName} berhasil dihapus",
                'role',
                $role->id
            );

            DB::commit();

            return redirect()->route('settings.roles.index')
                ->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }

    public function updatePermissions(Request $request, $id)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);
            $role->permissions()->sync($request->permissions ?? []);

            ActivityLogger::log(
                'Role',
                'updatePermissions',
                "Permission role {$role->name} berhasil diperbarui",
                'role',
                $role->id
            );

            DB::commit();

            return redirect()->route('settings.roles.show', $id)
                ->with('success', 'Permission role berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui permission: ' . $e->getMessage());
        }
    }
}
