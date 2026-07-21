<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index()
    {
        return view('master-data.branches.index');
    }

    public function ajax()
    {
        $branches = Branch::with('company');

        if ($search = request('search.value')) {
            $branches->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($filterStatus = request('filter_status')) {
            $branches->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($branches)
            ->addIndexColumn()
            ->addColumn('action', function ($branch) {
                return view('master-data.branches.action', compact('branch'))->render();
            })
            ->addColumn('company_name', fn($branch) => $branch->company?->name)
            ->editColumn('is_active', function ($branch) {
                return $branch->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', fn($branch) => $branch->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        $companies = Company::where('is_active', true)->get();
        return view('master-data.branches.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:50|unique:branches,code',
            'address'    => 'nullable|string',
            'phone'      => 'nullable|string|max:50',
            'email'      => 'nullable|email|max:255',
            'is_active'  => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $branch = Branch::create($validated);

            ActivityLogger::log(
                'Branch',
                'create',
                "Cabang {$branch->name} berhasil dibuat",
                'branch',
                $branch->id,
                $branch->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.branches.index')
                ->with('success', 'Cabang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan cabang: ' . $e->getMessage());
        }
    }

    public function show(Branch $branch)
    {
        $branch->load('company');
        return view('master-data.branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $companies = Company::where('is_active', true)->get();
        return view('master-data.branches.edit', compact('branch', 'companies'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name'       => 'required|string|max:255',
            'code'       => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'address'    => 'nullable|string',
            'phone'      => 'nullable|string|max:50',
            'email'      => 'nullable|email|max:255',
            'is_active'  => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $branch->update($validated);

            ActivityLogger::log(
                'Branch',
                'update',
                "Cabang {$branch->name} berhasil diperbarui",
                'branch',
                $branch->id,
                $branch->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.branches.index')
                ->with('success', 'Cabang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui cabang: ' . $e->getMessage());
        }
    }

    public function destroy(Branch $branch)
    {
        DB::beginTransaction();
        try {
            $branchName = $branch->name;
            $branch->delete();

            ActivityLogger::log(
                'Branch',
                'delete',
                "Cabang {$branchName} berhasil dihapus",
                'branch',
                $branch->id
            );

            DB::commit();

            return redirect()->route('master-data.branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus cabang: ' . $e->getMessage());
        }
    }
}
