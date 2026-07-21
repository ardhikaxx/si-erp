<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TaxController extends Controller
{
    public function index()
    {
        return view('master-data.taxes.index');
    }

    public function ajax()
    {
        $taxes = Tax::query();

        if ($search = request('search.value')) {
            $taxes->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $taxes->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($taxes)
            ->addIndexColumn()
            ->addColumn('action', function ($tax) {
                return view('master-data.taxes.action', compact('tax'))->render();
            })
            ->editColumn('rate', fn($tax) => number_format($tax->rate, 2) . '%')
            ->editColumn('is_active', function ($tax) {
                return $tax->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($tax) => $tax->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.taxes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:taxes,code',
            'rate'        => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $tax = Tax::create($validated);

            ActivityLogger::log(
                'Tax',
                'create',
                "Pajak {$tax->name} berhasil dibuat",
                'tax',
                $tax->id,
                $tax->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.taxes.index')
                ->with('success', 'Pajak berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan pajak: ' . $e->getMessage());
        }
    }

    public function show(Tax $tax)
    {
        return view('master-data.taxes.show', compact('tax'));
    }

    public function edit(Tax $tax)
    {
        return view('master-data.taxes.edit', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => ['required', 'string', 'max:50', Rule::unique('taxes', 'code')->ignore($tax->id)],
            'rate'        => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $tax->update($validated);

            ActivityLogger::log(
                'Tax',
                'update',
                "Pajak {$tax->name} berhasil diperbarui",
                'tax',
                $tax->id,
                $tax->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.taxes.index')
                ->with('success', 'Pajak berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pajak: ' . $e->getMessage());
        }
    }

    public function destroy(Tax $tax)
    {
        DB::beginTransaction();
        try {
            $taxName = $tax->name;
            $tax->delete();

            ActivityLogger::log(
                'Tax',
                'delete',
                "Pajak {$taxName} berhasil dihapus",
                'tax',
                $tax->id
            );

            DB::commit();

            return redirect()->route('master-data.taxes.index')
                ->with('success', 'Pajak berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pajak: ' . $e->getMessage());
        }
    }
}
