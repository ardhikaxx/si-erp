<?php

namespace App\Http\Controllers\HR;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employees = Employee::with(['department', 'position']);

            if ($search = request('search.value')) {
                $employees->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($filterDepartment = request('filter_department')) {
                $employees->where('department_id', $filterDepartment);
            }

            if ($filterStatus = request('filter_status')) {
                $employees->where('status', $filterStatus);
            }

            return DataTables::of($employees)
                ->addIndexColumn()
                ->addColumn('action', function ($employee) {
                    $btn = '<div class="d-flex gap-1">';
                    $btn .= '<a href="' . route('hr.employees.show', $employee->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('hr.employees.edit', $employee->id) . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= '<a href="' . route('hr.employees.exportPdf', $employee->id) . '" class="btn btn-secondary btn-sm" target="_blank"><i class="fas fa-print"></i></a>';
                    $btn .= '<button onclick="deleteConfirm(' . $employee->id . ', \'' . $employee->name . '\')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('department_id', fn($e) => $e->department?->name ?? '-')
                ->editColumn('position_id', fn($e) => $e->position?->name ?? '-')
                ->editColumn('status', function ($employee) {
                    return match ($employee->status) {
                        'active'     => '<span class="badge bg-success">Aktif</span>',
                        'inactive'   => '<span class="badge bg-warning">Nonaktif</span>',
                        'resigned'   => '<span class="badge bg-danger">Mengundurkan Diri</span>',
                        'terminated' => '<span class="badge bg-dark">PHK</span>',
                        default      => $employee->status,
                    };
                })
                ->editColumn('created_at', fn($e) => $e->created_at->format('d/m/Y H:i'))
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('hr.employees.index', compact('departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $supervisors = Employee::where('status', 'active')->orderBy('name')->get();
        $employee = new Employee();
        return view('hr.employees.form', compact('employee', 'departments', 'positions', 'supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255|unique:employees,email',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string',
            'place_of_birth'  => 'nullable|string|max:255',
            'date_of_birth'   => 'nullable|date',
            'gender'          => 'nullable|in:L,P',
            'religion'        => 'nullable|string|max:50',
            'marital_status'  => 'nullable|string|max:50',
            'id_number'       => 'nullable|string|max:50|unique:employees,id_number',
            'tax_id'          => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'bank_name'       => 'nullable|string|max:100',
            'department_id'   => 'nullable|exists:departments,id',
            'position_id'     => 'nullable|exists:positions,id',
            'supervisor_id'   => 'nullable|exists:employees,id',
            'join_date'       => 'nullable|date',
            'exit_date'       => 'nullable|date',
            'status'          => 'required|in:active,inactive,resigned,terminated',
            'employment_type' => 'nullable|string|max:50',
            'salary'          => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['code'] = DocumentNumber::generateSimple('EMP', 'employees');
            $validated['salary'] = $validated['salary'] ?? 0;

            $employee = Employee::create($validated);

            ActivityLogger::log(
                'Employee',
                'create',
                "Karyawan {$employee->name} berhasil dibuat",
                'employee',
                $employee->id,
                $employee->toArray()
            );

            DB::commit();

            return redirect()->route('hr.employees.index')
                ->with('success', 'Karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan karyawan: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'supervisor']);
        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $supervisors = Employee::where('status', 'active')->where('id', '!=', $employee->id)->orderBy('name')->get();
        return view('hr.employees.form', compact('employee', 'departments', 'positions', 'supervisors'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id)],
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string',
            'place_of_birth'  => 'nullable|string|max:255',
            'date_of_birth'   => 'nullable|date',
            'gender'          => 'nullable|in:L,P',
            'religion'        => 'nullable|string|max:50',
            'marital_status'  => 'nullable|string|max:50',
            'id_number'       => ['nullable', 'string', 'max:50', Rule::unique('employees', 'id_number')->ignore($employee->id)],
            'tax_id'          => 'nullable|string|max:100',
            'bank_account'    => 'nullable|string|max:50',
            'bank_name'       => 'nullable|string|max:100',
            'department_id'   => 'nullable|exists:departments,id',
            'position_id'     => 'nullable|exists:positions,id',
            'supervisor_id'   => 'nullable|exists:employees,id',
            'join_date'       => 'nullable|date',
            'exit_date'       => 'nullable|date',
            'status'          => 'required|in:active,inactive,resigned,terminated',
            'employment_type' => 'nullable|string|max:50',
            'salary'          => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['salary'] = $validated['salary'] ?? 0;
            $employee->update($validated);

            ActivityLogger::log(
                'Employee',
                'update',
                "Karyawan {$employee->name} berhasil diperbarui",
                'employee',
                $employee->id,
                $employee->toArray()
            );

            DB::commit();

            return redirect()->route('hr.employees.index')
                ->with('success', 'Karyawan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui karyawan: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        DB::beginTransaction();
        try {
            $employeeName = $employee->name;
            $employee->delete();

            ActivityLogger::log(
                'Employee',
                'delete',
                "Karyawan {$employeeName} berhasil dihapus",
                'employee',
                $employee->id
            );

            DB::commit();

            return redirect()->route('hr.employees.index')
                ->with('success', 'Karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus karyawan: ' . $e->getMessage());
        }
    }

    public function exportPdf($id)
    {
        $employee = Employee::with(['department', 'position', 'supervisor'])->findOrFail($id);
        return view('hr.employees.pdf', compact('employee'));
    }
}
