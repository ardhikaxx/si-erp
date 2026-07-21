<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductUnitController extends Controller
{
    public function index()
    {
        return view('master-data.product-units.index');
    }

    public function ajax()
    {
        $units = ProductUnit::query();

        if ($search = request('search.value')) {
            $units->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $units->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($units)
            ->addIndexColumn()
            ->addColumn('action', function ($unit) {
                return view('master-data.product-units.action', compact('unit'))->render();
            })
            ->editColumn('is_active', function ($unit) {
                return $unit->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($u) => $u->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.product-units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:product_units,code',
            'symbol'    => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $unit = ProductUnit::create($validated);

            ActivityLogger::log(
                'ProductUnit',
                'create',
                "Satuan produk {$unit->name} berhasil dibuat",
                'product_unit',
                $unit->id,
                $unit->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.product-units.index')
                ->with('success', 'Satuan produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan satuan produk: ' . $e->getMessage());
        }
    }

    public function show(ProductUnit $productUnit)
    {
        return view('master-data.product-units.show', compact('productUnit'));
    }

    public function edit(ProductUnit $productUnit)
    {
        return view('master-data.product-units.edit', compact('productUnit'));
    }

    public function update(Request $request, ProductUnit $productUnit)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => ['required', 'string', 'max:50', Rule::unique('product_units', 'code')->ignore($productUnit->id)],
            'symbol'    => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $productUnit->update($validated);

            ActivityLogger::log(
                'ProductUnit',
                'update',
                "Satuan produk {$productUnit->name} berhasil diperbarui",
                'product_unit',
                $productUnit->id,
                $productUnit->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.product-units.index')
                ->with('success', 'Satuan produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui satuan produk: ' . $e->getMessage());
        }
    }

    public function destroy(ProductUnit $productUnit)
    {
        DB::beginTransaction();
        try {
            $unitName = $productUnit->name;
            $productUnit->delete();

            ActivityLogger::log(
                'ProductUnit',
                'delete',
                "Satuan produk {$unitName} berhasil dihapus",
                'product_unit',
                $productUnit->id
            );

            DB::commit();

            return redirect()->route('master-data.product-units.index')
                ->with('success', 'Satuan produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus satuan produk: ' . $e->getMessage());
        }
    }
}
