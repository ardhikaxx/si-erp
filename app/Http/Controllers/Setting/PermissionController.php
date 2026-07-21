<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::query();

            if ($search = request('search.value')) {
                $permissions->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('group', 'like', "%{$search}%");
                });
            }

            if ($filterGroup = request('filter_group')) {
                $permissions->where('group', $filterGroup);
            }

            return DataTables::of($permissions->orderBy('group')->orderBy('name'))
                ->addIndexColumn()
                ->addColumn('action', function ($permission) {
                    $btn = '<div class="d-flex gap-1">';
                    $btn .= '<a href="' . route('settings.permissions.edit', $permission->id) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button onclick="deleteConfirm(' . $permission->id . ', \'' . $permission->name . '\')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('group', fn($p) => $p->group ? '<span class="badge bg-secondary">' . $p->group . '</span>' : '-')
                ->editColumn('created_at', fn($p) => $p->created_at->format('d/m/Y H:i'))
                ->rawColumns(['action', 'group'])
                ->make(true);
        }

        $groups = Permission::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');
        return view('settings.permissions.index', compact('groups'));
    }

    public function create()
    {
        $groups = Permission::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');
        $permission = new Permission();
        return view('settings.permissions.form', compact('permission', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255|unique:permissions,name',
            'group' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $validated['guard_name'] = 'web';
            $permission = Permission::create($validated);

            ActivityLogger::log(
                'Permission',
                'create',
                "Permission {$permission->name} berhasil dibuat",
                'permission',
                $permission->id,
                $permission->toArray()
            );

            DB::commit();

            return redirect()->route('settings.permissions.index')
                ->with('success', 'Permission berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan permission: ' . $e->getMessage());
        }
    }

    public function edit(Permission $permission)
    {
        $groups = Permission::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');
        return view('settings.permissions.form', compact('permission', 'groups'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'group' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $permission->update($validated);

            ActivityLogger::log(
                'Permission',
                'update',
                "Permission {$permission->name} berhasil diperbarui",
                'permission',
                $permission->id,
                $permission->toArray()
            );

            DB::commit();

            return redirect()->route('settings.permissions.index')
                ->with('success', 'Permission berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui permission: ' . $e->getMessage());
        }
    }

    public function destroy(Permission $permission)
    {
        DB::beginTransaction();
        try {
            $permissionName = $permission->name;
            $permission->delete();

            ActivityLogger::log(
                'Permission',
                'delete',
                "Permission {$permissionName} berhasil dihapus",
                'permission',
                $permission->id
            );

            DB::commit();

            return redirect()->route('settings.permissions.index')
                ->with('success', 'Permission berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus permission: ' . $e->getMessage());
        }
    }
}
