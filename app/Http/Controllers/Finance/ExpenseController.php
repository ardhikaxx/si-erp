<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('finance.expenses.index');
    }

    public function ajax()
    {
        $expenses = Expense::with('supplier', 'account');

        if ($search = request('search.value')) {
            $expenses->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($startDate = request('filter_start_date')) {
            $expenses->whereDate('expense_date', '>=', $startDate);
        }

        if ($endDate = request('filter_end_date')) {
            $expenses->whereDate('expense_date', '<=', $endDate);
        }

        if ($accountId = request('filter_account_id')) {
            $expenses->where('account_id', $accountId);
        }

        return DataTables::of($expenses)
            ->addIndexColumn()
            ->addColumn('action', function ($expense) {
                $btn = '<a href="' . route('finance.expenses.edit', $expense->id) . '" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a> ';
                $btn .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $expense->id . '" data-name="' . $expense->code . '" data-bs-toggle="tooltip" title="Hapus"><i class="fas fa-trash"></i></button>';
                return $btn;
            })
            ->editColumn('supplier_id', fn($e) => $e->supplier->name ?? '-')
            ->editColumn('account_id', fn($e) => $e->account->name ?? '-')
            ->editColumn('amount', fn($e) => number_format($e->amount, 2))
            ->editColumn('expense_date', fn($e) => $e->expense_date->format('d/m/Y'))
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $accounts = ChartOfAccount::where('type', 'expense')->where('is_active', true)->orderBy('code')->get();
        return view('finance.expenses.form', [
            'expense' => new Expense(),
            'suppliers' => $suppliers,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'account_id'   => 'required|exists:chart_of_accounts,id',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description'  => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['code'] = DocumentNumber::generateSimple('EXP', 'expenses');
            $validated['created_by'] = auth()->id();

            $expense = Expense::create($validated);

            ActivityLogger::log(
                'Expense',
                'create',
                "Pengeluaran {$expense->code} sebesar {$expense->amount} berhasil dicatat",
                'expense',
                $expense->id,
                $expense->toArray()
            );

            DB::commit();

            return redirect()->route('finance.expenses.index')
                ->with('success', 'Pengeluaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mencatat pengeluaran: ' . $e->getMessage());
        }
    }

    public function edit(Expense $expense)
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $accounts = ChartOfAccount::where('type', 'expense')->where('is_active', true)->orderBy('code')->get();
        return view('finance.expenses.form', compact('expense', 'suppliers', 'accounts'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'supplier_id'  => 'nullable|exists:suppliers,id',
            'account_id'   => 'required|exists:chart_of_accounts,id',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description'  => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense->update($validated);

            ActivityLogger::log(
                'Expense',
                'update',
                "Pengeluaran {$expense->code} berhasil diperbarui",
                'expense',
                $expense->id,
                $expense->toArray()
            );

            DB::commit();

            return redirect()->route('finance.expenses.index')
                ->with('success', 'Pengeluaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pengeluaran: ' . $e->getMessage());
        }
    }

    public function destroy(Expense $expense)
    {
        DB::beginTransaction();
        try {
            $code = $expense->code;
            $expense->delete();

            ActivityLogger::log(
                'Expense',
                'delete',
                "Pengeluaran {$code} berhasil dihapus",
                'expense',
                $expense->id
            );

            DB::commit();

            return redirect()->route('finance.expenses.index')
                ->with('success', 'Pengeluaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pengeluaran: ' . $e->getMessage());
        }
    }
}
