<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        return view('master-data.suppliers.index');
    }

    public function ajax()
    {
        $suppliers = Supplier::query();

        if ($search = request('search.value')) {
            $suppliers->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $suppliers->where('status', $filterStatus);
        }

        if ($filterType = request('filter_type')) {
            $suppliers->where('type', $filterType);
        }

        return DataTables::of($suppliers)
            ->addIndexColumn()
            ->addColumn('action', function ($supplier) {
                return view('master-data.suppliers.action', compact('supplier'))->render();
            })
            ->editColumn('type', function ($supplier) {
                return $supplier->type === 'company'
                    ? '<span class="badge bg-info">Perusahaan</span>'
                    : '<span class="badge bg-secondary">Individu</span>';
            })
            ->editColumn('status', function ($supplier) {
                return match ($supplier->status) {
                    'active'    => '<span class="badge bg-success">Aktif</span>',
                    'inactive'  => '<span class="badge bg-warning">Nonaktif</span>',
                    'blacklist' => '<span class="badge bg-danger">Blacklist</span>',
                    default     => $supplier->status,
                };
            })
            ->editColumn('balance', fn($s) => number_format($s->balance, 2))
            ->editColumn('created_at', fn($s) => $s->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'type', 'status'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255|unique:suppliers,email',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id'         => 'nullable|string|max:100',
            'type'           => 'required|in:company,individual',
            'status'         => 'required|in:active,inactive,blacklist',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['code'] = DocumentNumber::generateSimple('SUPP', 'suppliers');

            $supplier = Supplier::create($validated);

            ActivityLogger::log(
                'Supplier',
                'create',
                "Pemasok {$supplier->name} berhasil dibuat",
                'supplier',
                $supplier->id,
                $supplier->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.suppliers.index')
                ->with('success', 'Pemasok berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan pemasok: ' . $e->getMessage());
        }
    }

    public function show(Supplier $supplier)
    {
        return view('master-data.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('master-data.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id'         => 'nullable|string|max:100',
            'type'           => 'required|in:company,individual',
            'status'         => 'required|in:active,inactive,blacklist',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $supplier->update($validated);

            ActivityLogger::log(
                'Supplier',
                'update',
                "Pemasok {$supplier->name} berhasil diperbarui",
                'supplier',
                $supplier->id,
                $supplier->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.suppliers.index')
                ->with('success', 'Pemasok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pemasok: ' . $e->getMessage());
        }
    }

    public function destroy(Supplier $supplier)
    {
        DB::beginTransaction();
        try {
            $supplierName = $supplier->name;
            $supplier->delete();

            ActivityLogger::log(
                'Supplier',
                'delete',
                "Pemasok {$supplierName} berhasil dihapus",
                'supplier',
                $supplier->id
            );

            DB::commit();

            return redirect()->route('master-data.suppliers.index')
                ->with('success', 'Pemasok berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pemasok: ' . $e->getMessage());
        }
    }
}
