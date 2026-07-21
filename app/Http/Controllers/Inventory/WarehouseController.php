<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('inventory.warehouses.index');
    }

    public function ajax()
    {
        $warehouses = Warehouse::with('branch');

        if ($search = request('search.value')) {
            $warehouses->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('branch', fn($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($filterStatus = request('filter_status')) {
            $warehouses->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($warehouses)
            ->addIndexColumn()
            ->addColumn('action', fn($warehouse) => view('inventory.warehouses.action', compact('warehouse'))->render())
            ->addColumn('branch_name', fn($warehouse) => $warehouse->branch?->name)
            ->editColumn('is_active', function ($warehouse) {
                return $warehouse->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($warehouse) => $warehouse->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('inventory.warehouses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:warehouses,code',
            'address'   => 'nullable|string',
            'phone'     => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::create($validated);

            ActivityLogger::log(
                'Warehouse',
                'create',
                "Gudang {$warehouse->name} berhasil dibuat",
                'warehouse',
                $warehouse->id,
                $warehouse->toArray()
            );

            DB::commit();

            return redirect()->route('inventory.warehouses.index')
                ->with('success', 'Gudang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan gudang: ' . $e->getMessage());
        }
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('branch');
        return view('inventory.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('inventory.warehouses.edit', compact('warehouse', 'branches'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:255',
            'code'      => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'address'   => 'nullable|string',
            'phone'     => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $warehouse->update($validated);

            ActivityLogger::log(
                'Warehouse',
                'update',
                "Gudang {$warehouse->name} berhasil diperbarui",
                'warehouse',
                $warehouse->id,
                $warehouse->toArray()
            );

            DB::commit();

            return redirect()->route('inventory.warehouses.index')
                ->with('success', 'Gudang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui gudang: ' . $e->getMessage());
        }
    }

    public function destroy(Warehouse $warehouse)
    {
        DB::beginTransaction();
        try {
            $warehouseName = $warehouse->name;
            $warehouse->delete();

            ActivityLogger::log(
                'Warehouse',
                'delete',
                "Gudang {$warehouseName} berhasil dihapus",
                'warehouse',
                $warehouse->id
            );

            DB::commit();

            return redirect()->route('inventory.warehouses.index')
                ->with('success', 'Gudang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus gudang: ' . $e->getMessage());
        }
    }
}
