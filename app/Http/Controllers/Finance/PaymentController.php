<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index()
    {
        return view('finance.payments.index');
    }

    public function ajax()
    {
        $payments = Payment::with('customer', 'supplier', 'paymentMethod', 'account');

        if ($search = request('search.value')) {
            $payments->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($filterType = request('filter_type')) {
            $payments->where('type', $filterType);
        }

        if ($startDate = request('filter_start_date')) {
            $payments->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate = request('filter_end_date')) {
            $payments->whereDate('payment_date', '<=', $endDate);
        }

        return DataTables::of($payments)
            ->addIndexColumn()
            ->addColumn('action', function ($payment) {
                $btn = '<a href="' . route('finance.payments.edit', $payment->id) . '" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a> ';
                $btn .= '<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $payment->id . '" data-name="' . $payment->code . '" data-bs-toggle="tooltip" title="Hapus"><i class="fas fa-trash"></i></button>';
                return $btn;
            })
            ->editColumn('type', function ($payment) {
                return $payment->type === 'incoming'
                    ? '<span class="badge bg-success">Pemasukan</span>'
                    : '<span class="badge bg-danger">Pengeluaran</span>';
            })
            ->addColumn('party', function ($p) {
                if ($p->customer) return $p->customer->name;
                if ($p->supplier) return $p->supplier->name;
                return '-';
            })
            ->editColumn('payment_method_id', fn($p) => $p->paymentMethod->name ?? '-')
            ->editColumn('account_id', fn($p) => $p->account->name ?? '-')
            ->editColumn('amount', fn($p) => number_format($p->amount, 2))
            ->editColumn('payment_date', fn($p) => $p->payment_date->format('d/m/Y'))
            ->rawColumns(['action', 'type', 'party'])
            ->make(true);
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        return view('finance.payments.form', [
            'payment' => new Payment(),
            'customers' => $customers,
            'suppliers' => $suppliers,
            'paymentMethods' => $paymentMethods,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'              => 'required|in:incoming,outgoing',
            'customer_id'       => 'nullable|exists:customers,id',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'account_id'        => 'required|exists:chart_of_accounts,id',
            'amount'            => 'required|numeric|min:0',
            'payment_date'      => 'required|date',
            'reference'         => 'nullable|string|max:255',
            'reference_type'    => 'nullable|string|max:100',
            'reference_id'      => 'nullable|integer',
            'notes'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $validated['code'] = DocumentNumber::generateSimple('PAY', 'payments');
            $validated['created_by'] = auth()->id();

            $payment = Payment::create($validated);

            ActivityLogger::log(
                'Payment',
                'create',
                "Pembayaran {$payment->code} sebesar {$payment->amount} berhasil dicatat",
                'payment',
                $payment->id,
                $payment->toArray()
            );

            DB::commit();

            return redirect()->route('finance.payments.index')
                ->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function edit(Payment $payment)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        return view('finance.payments.form', compact('payment', 'customers', 'suppliers', 'paymentMethods', 'accounts'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'type'              => 'required|in:incoming,outgoing',
            'customer_id'       => 'nullable|exists:customers,id',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'account_id'        => 'required|exists:chart_of_accounts,id',
            'amount'            => 'required|numeric|min:0',
            'payment_date'      => 'required|date',
            'reference'         => 'nullable|string|max:255',
            'reference_type'    => 'nullable|string|max:100',
            'reference_id'      => 'nullable|integer',
            'notes'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment->update($validated);

            ActivityLogger::log(
                'Payment',
                'update',
                "Pembayaran {$payment->code} berhasil diperbarui",
                'payment',
                $payment->id,
                $payment->toArray()
            );

            DB::commit();

            return redirect()->route('finance.payments.index')
                ->with('success', 'Pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }

    public function destroy(Payment $payment)
    {
        DB::beginTransaction();
        try {
            $code = $payment->code;
            $payment->delete();

            ActivityLogger::log(
                'Payment',
                'delete',
                "Pembayaran {$code} berhasil dihapus",
                'payment',
                $payment->id
            );

            DB::commit();

            return redirect()->route('finance.payments.index')
                ->with('success', 'Pembayaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }
}
