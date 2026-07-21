<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function index()
    {
        return view('master-data.companies.index');
    }

    public function ajax()
    {
        $companies = Company::query();

        if ($search = request('search.value')) {
            $companies->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filterStatus = request('filter_status')) {
            $companies->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($companies)
            ->addIndexColumn()
            ->addColumn('action', function ($company) {
                return view('master-data.companies.action', compact('company'))->render();
            })
            ->editColumn('is_active', function ($company) {
                return $company->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->editColumn('created_at', function ($company) {
                return $company->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        return view('master-data.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'alias'       => 'nullable|string|max:255',
            'address'     => 'nullable|string',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'tax_id'      => 'nullable|string|max:100',
            'currency'    => 'nullable|string|max:10|default:IDR',
            'timezone'    => 'nullable|string|max:100|default:Asia/Jakarta',
            'date_format' => 'nullable|string|max:20|default:d/m/Y',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $company = Company::create($validated);

            ActivityLogger::log(
                'Company',
                'create',
                "Perusahaan {$company->name} berhasil dibuat",
                'company',
                $company->id,
                $company->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.companies.index')
                ->with('success', 'Perusahaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan perusahaan: ' . $e->getMessage());
        }
    }

    public function show(Company $company)
    {
        return view('master-data.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('master-data.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'alias'       => 'nullable|string|max:255',
            'address'     => 'nullable|string',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'tax_id'      => 'nullable|string|max:100',
            'currency'    => 'nullable|string|max:10',
            'timezone'    => 'nullable|string|max:100',
            'date_format' => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $company->update($validated);

            ActivityLogger::log(
                'Company',
                'update',
                "Perusahaan {$company->name} berhasil diperbarui",
                'company',
                $company->id,
                $company->toArray()
            );

            DB::commit();

            return redirect()->route('master-data.companies.index')
                ->with('success', 'Perusahaan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui perusahaan: ' . $e->getMessage());
        }
    }

    public function destroy(Company $company)
    {
        DB::beginTransaction();
        try {
            $companyName = $company->name;
            $company->delete();

            ActivityLogger::log(
                'Company',
                'delete',
                "Perusahaan {$companyName} berhasil dihapus",
                'company',
                $company->id
            );

            DB::commit();

            return redirect()->route('master-data.companies.index')
                ->with('success', 'Perusahaan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus perusahaan: ' . $e->getMessage());
        }
    }
}
