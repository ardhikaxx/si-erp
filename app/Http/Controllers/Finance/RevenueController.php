<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RevenueController extends Controller
{
    public function index()
    {
        return view('finance.revenues.index');
    }

    public function ajax()
    {
        $revenues = Revenue::with('customer', 'account');

        if ($search = request('search.value')) {
            $revenues->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($startDate = request('filter_start_date')) {
            $revenues->whereDate('revenue_date', '>=', $startDate);
        }

        if ($endDate = request('filter_end_date')) {
            $revenues->whereDate('revenue_date', '<=', $endDate);
        }

        if ($accountId = request('filter_account_id')) {
            $revenues->where('account_id', $accountId);
        }

        return DataTables::of($revenues)
            ->addIndexColumn()
            ->addColumn('action', function ($revenue) {
                $btn = '<a href="' . route('finance.revenues.edit', $revenue->id) . '" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a> ';
                $btn .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $revenue->id . '" data-name="' . $revenue->code . '" data-bs-toggle="tooltip" title="Hapus"><i class="fas fa-trash"></i></button>';
                return $btn;
            })
            ->editColumn('customer_id', fn($r) => $r->customer->name ?? '-')
            ->editColumn('account_id', fn($r) => $r->account->name ?? '-')
            ->editColumn('amount', fn($r) => number_format($r->amount, 2))
            ->editColumn('revenue_date', fn($r) => $r->revenue_date->format('d/m/Y'))
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $accounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();
        return view('finance.revenues.form', [
            'revenue' => new Revenue(),
            'customers' => $customers,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'  => 'nullable|exists:customers,id',
            'account_id'   => 'required|exists:chart_of_accounts,id',
            'amount'       => 'required|numeric|min:0',
            'revenue_date' => 'required|date',
            'description'  => 'nullable|string',
            'reference_type' => 'nullable|string|max:100',
            'reference_id'   => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $validated['code'] = DocumentNumber::generateSimple('REV', 'revenues');
            $validated['created_by'] = auth()->id();

            $revenue = Revenue::create($validated);

            ActivityLogger::log(
                'Revenue',
                'create',
                "Pendapatan {$revenue->code} sebesar {$revenue->amount} berhasil dicatat",
                'revenue',
                $revenue->id,
                $revenue->toArray()
            );

            DB::commit();

            return redirect()->route('finance.revenues.index')
                ->with('success', 'Pendapatan berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mencatat pendapatan: ' . $e->getMessage());
        }
    }

    public function edit(Revenue $revenue)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $accounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->orderBy('code')->get();
        return view('finance.revenues.form', compact('revenue', 'customers', 'accounts'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        $validated = $request->validate([
            'customer_id'  => 'nullable|exists:customers,id',
            'account_id'   => 'required|exists:chart_of_accounts,id',
            'amount'       => 'required|numeric|min:0',
            'revenue_date' => 'required|date',
            'description'  => 'nullable|string',
            'reference_type' => 'nullable|string|max:100',
            'reference_id'   => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $revenue->update($validated);

            ActivityLogger::log(
                'Revenue',
                'update',
                "Pendapatan {$revenue->code} berhasil diperbarui",
                'revenue',
                $revenue->id,
                $revenue->toArray()
            );

            DB::commit();

            return redirect()->route('finance.revenues.index')
                ->with('success', 'Pendapatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pendapatan: ' . $e->getMessage());
        }
    }

    public function destroy(Revenue $revenue)
    {
        DB::beginTransaction();
        try {
            $code = $revenue->code;
            $revenue->delete();

            ActivityLogger::log(
                'Revenue',
                'delete',
                "Pendapatan {$code} berhasil dihapus",
                'revenue',
                $revenue->id
            );

            DB::commit();

            return redirect()->route('finance.revenues.index')
                ->with('success', 'Pendapatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pendapatan: ' . $e->getMessage());
        }
    }
}
