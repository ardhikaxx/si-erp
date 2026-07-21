<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        return view('finance.chart_of_accounts.index');
    }

    public function ajax()
    {
        $accounts = ChartOfAccount::with('parent')->orderBy('code');

        if ($search = request('search.value')) {
            $accounts->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($filterType = request('filter_type')) {
            $accounts->where('type', $filterType);
        }

        if ($filterStatus = request('filter_status')) {
            $accounts->where('is_active', $filterStatus === 'active');
        }

        return DataTables::of($accounts)
            ->addIndexColumn()
            ->addColumn('action', function ($account) {
                $btn = '<a href="' . route('finance.chart-of-accounts.edit', $account->id) . '" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a> ';
                $btn .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $account->id . '" data-name="' . $account->name . '" data-bs-toggle="tooltip" title="Hapus"><i class="fas fa-trash"></i></button>';
                return $btn;
            })
            ->editColumn('name', function ($account) {
                $indent = '';
                if ($account->parent_id) {
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $account->level ?? 0) . '└ ';
                }
                return $indent . e($account->name);
            })
            ->editColumn('type', function ($account) {
                $labels = [
                    'asset'     => '<span class="badge bg-primary">Aset</span>',
                    'liability' => '<span class="badge bg-warning">Kewajiban</span>',
                    'equity'    => '<span class="badge bg-info">Modal</span>',
                    'revenue'   => '<span class="badge bg-success">Pendapatan</span>',
                    'expense'   => '<span class="badge bg-danger">Beban</span>',
                ];
                return $labels[$account->type] ?? $account->type;
            })
            ->editColumn('balance', fn($a) => number_format($a->balance, 2))
            ->editColumn('is_active', function ($account) {
                return $account->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->rawColumns(['action', 'name', 'type', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        return view('finance.chart_of_accounts.form', [
            'account' => new ChartOfAccount(),
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'nullable|string|max:50|unique:chart_of_accounts,code',
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'category'    => 'nullable|string|max:100',
            'parent_id'   => 'nullable|exists:chart_of_accounts,id',
            'balance'     => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            if (empty($validated['code'])) {
                $validated['code'] = DocumentNumber::generateSimple('AC', 'chart_of_accounts');
            }
            $validated['balance'] = $validated['balance'] ?? 0;
            $validated['is_active'] = $request->boolean('is_active');

            $account = ChartOfAccount::create($validated);

            ActivityLogger::log(
                'ChartOfAccount',
                'create',
                "Akun {$account->name} berhasil dibuat",
                'chart_of_account',
                $account->id,
                $account->toArray()
            );

            DB::commit();

            return redirect()->route('finance.chart-of-accounts.index')
                ->with('success', 'Akun berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan akun: ' . $e->getMessage());
        }
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->where('id', '!=', $chartOfAccount->id)
            ->orderBy('code')
            ->get();

        return view('finance.chart_of_accounts.form', [
            'account' => $chartOfAccount,
            'accounts' => $accounts,
        ]);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('chart_of_accounts', 'code')->ignore($chartOfAccount->id)],
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'category'    => 'nullable|string|max:100',
            'parent_id'   => 'nullable|exists:chart_of_accounts,id',
            'balance'     => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $validated['balance'] = $validated['balance'] ?? 0;
            $validated['is_active'] = $request->boolean('is_active');

            $chartOfAccount->update($validated);

            ActivityLogger::log(
                'ChartOfAccount',
                'update',
                "Akun {$chartOfAccount->name} berhasil diperbarui",
                'chart_of_account',
                $chartOfAccount->id,
                $chartOfAccount->toArray()
            );

            DB::commit();

            return redirect()->route('finance.chart-of-accounts.index')
                ->with('success', 'Akun berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui akun: ' . $e->getMessage());
        }
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        DB::beginTransaction();
        try {
            if ($chartOfAccount->children()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus akun yang memiliki sub-akun.');
            }

            $name = $chartOfAccount->name;
            $chartOfAccount->delete();

            ActivityLogger::log(
                'ChartOfAccount',
                'delete',
                "Akun {$name} berhasil dihapus",
                'chart_of_account',
                $chartOfAccount->id
            );

            DB::commit();

            return redirect()->route('finance.chart-of-accounts.index')
                ->with('success', 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }
}
