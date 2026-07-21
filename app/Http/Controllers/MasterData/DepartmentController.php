<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('master-data.departments.index');
    }

    public function ajax()
    {
        $departments = Department::query();

        if ($search = request('search.value')) {
            $departments->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $departments->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($departments)
            ->addIndexColumn()
            ->addColumn('action', function ($department) {
                return view('master-data.departments.action', compact('department'))->render();
            })
            ->editColumn('is_active', function ($department) {
                return $department->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($d) => $d->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $department = Department::create($validated);

            ActivityLogger::log(
                'Department',
                'create',
                "Departemen {$department->name} berhasil dibuat",
                'department',
                $department->id,
                $department->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.departments.index')
                ->with('success', 'Departemen berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan departemen: ' . $e->getMessage());
        }
    }

    public function show(Department $department)
    {
        return view('master-data.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('master-data.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => ['required', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $department->update($validated);

            ActivityLogger::log(
                'Department',
                'update',
                "Departemen {$department->name} berhasil diperbarui",
                'department',
                $department->id,
                $department->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.departments.index')
                ->with('success', 'Departemen berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui departemen: ' . $e->getMessage());
        }
    }

    public function destroy(Department $department)
    {
        DB::beginTransaction();
        try {
            $departmentName = $department->name;
            $department->delete();

            ActivityLogger::log(
                'Department',
                'delete',
                "Departemen {$departmentName} berhasil dihapus",
                'department',
                $department->id
            );

            DB::commit();

            return redirect()->route('master-data.departments.index')
                ->with('success', 'Departemen berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus departemen: ' . $e->getMessage());
        }
    }
}
