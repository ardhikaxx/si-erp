<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PositionController extends Controller
{
    public function index()
    {
        return view('master-data.positions.index');
    }

    public function ajax()
    {
        $positions = Position::with('department');

        if ($search = request('search.value')) {
            $positions->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($filterStatus = request('filter_status')) {
            $positions->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($positions)
            ->addIndexColumn()
            ->addColumn('action', function ($position) {
                return view('master-data.positions.action', compact('position'))->render();
            })
            ->addColumn('department_name', fn($p) => $p->department?->name)
            ->editColumn('is_active', function ($position) {
                return $position->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($p) => $p->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        return view('master-data.positions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:positions,code',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $position = Position::create($validated);

            ActivityLogger::log(
                'Position',
                'create',
                "Jabatan {$position->name} berhasil dibuat",
                'position',
                $position->id,
                $position->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.positions.index')
                ->with('success', 'Jabatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan jabatan: ' . $e->getMessage());
        }
    }

    public function show(Position $position)
    {
        $position->load('department');
        return view('master-data.positions.show', compact('position'));
    }

    public function edit(Position $position)
    {
        $departments = Department::where('is_active', true)->get();
        return view('master-data.positions.edit', compact('position', 'departments'));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => ['required', 'string', 'max:50', Rule::unique('positions', 'code')->ignore($position->id)],
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $position->update($validated);

            ActivityLogger::log(
                'Position',
                'update',
                "Jabatan {$position->name} berhasil diperbarui",
                'position',
                $position->id,
                $position->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.positions.index')
                ->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    public function destroy(Position $position)
    {
        DB::beginTransaction();
        try {
            $positionName = $position->name;
            $position->delete();

            ActivityLogger::log(
                'Position',
                'delete',
                "Jabatan {$positionName} berhasil dihapus",
                'position',
                $position->id
            );

            DB::commit();

            return redirect()->route('master-data.positions.index')
                ->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus jabatan: ' . $e->getMessage());
        }
    }
}
