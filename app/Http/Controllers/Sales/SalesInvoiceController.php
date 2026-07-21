<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('sales.sales_invoices.index', compact('customers'));
    }

    public function ajax()
    {
        $invoices = SalesInvoice::with('customer', 'creator');

        if ($search = request('search.value')) {
            $invoices->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = request('filter_status')) {
            $invoices->where('status', $status);
        }

        if ($paymentStatus = request('filter_payment_status')) {
            $invoices->where('payment_status', $paymentStatus);
        }

        if ($customerId = request('filter_customer')) {
            $invoices->where('customer_id', $customerId);
        }

        return DataTables::of($invoices)
            ->addIndexColumn()
            ->addColumn('action', fn($i) => view('sales.sales_invoices.action', compact('i'))->render())
            ->addColumn('customer_name', fn($i) => $i->customer?->name ?? '-')
            ->editColumn('code', fn($i) => '<span class="fw-medium">' . $i->code . '</span>')
            ->editColumn('invoice_date', fn($i) => $i->invoice_date?->format('d/m/Y'))
            ->editColumn('due_date', fn($i) => $i->due_date?->format('d/m/Y'))
            ->editColumn('total', fn($i) => number_format($i->total, 0, ',', '.'))
            ->editColumn('paid_amount', fn($i) => number_format($i->paid_amount ?? 0, 0, ',', '.'))
            ->editColumn('status', function ($i) {
                $labels = [
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'sent' => '<span class="badge bg-info">Terkirim</span>',
                    'confirmed' => '<span class="badge bg-success">Dikonfirmasi</span>',
                    'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
                ];
                return $labels[$i->status] ?? '<span class="badge bg-secondary">' . $i->status . '</span>';
            })
            ->editColumn('payment_status', function ($i) {
                $labels = [
                    'unpaid' => '<span class="badge bg-danger">Belum Dibayar</span>',
                    'partially_paid' => '<span class="badge bg-warning text-dark">Dibayar Sebagian</span>',
                    'paid' => '<span class="badge bg-success">Lunas</span>',
                    'overdue' => '<span class="badge bg-dark">Jatuh Tempo</span>',
                ];
                return $labels[$i->payment_status] ?? '<span class="badge bg-secondary">' . $i->payment_status . '</span>';
            })
            ->editColumn('created_at', fn($i) => $i->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'code', 'status', 'payment_status'])
            ->make(true);
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $salesOrders = SalesOrder::whereIn('status', ['approved', 'processing', 'completed'])->with('customer')->get();
        $quotations = Quotation::where('status', 'accepted')->with('customer')->get();
        return view('sales.sales_invoices.create', compact('customers', 'products', 'salesOrders', 'quotations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice = SalesInvoice::create([
                'code' => DocumentNumber::generateSimple('INV', 'sales_invoices'),
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'quotation_id' => $validated['quotation_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            ActivityLogger::log(
                'SalesInvoice',
                'create',
                "Invoice {$invoice->code} berhasil dibuat",
                'sales_invoice',
                $invoice->id,
                $invoice->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-invoices.show', $invoice->id)
                ->with('success', 'Invoice berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load('items.product', 'customer', 'salesOrder', 'quotation', 'creator');
        $payments = Payment::where('reference_type', 'sales_invoice')
            ->where('reference_id', $salesInvoice->id)
            ->with('paymentMethod', 'creator')
            ->latest()
            ->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('sales.sales_invoices.show', compact('salesInvoice', 'payments', 'paymentMethods'));
    }

    public function edit(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->payment_status === 'paid') {
            return redirect()->route('sales.sales-invoices.show', $salesInvoice->id)
                ->with('error', 'Invoice yang sudah lunas tidak dapat diedit.');
        }

        $salesInvoice->load('items.product');
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $salesOrders = SalesOrder::whereIn('status', ['approved', 'processing', 'completed'])->with('customer')->get();
        $quotations = Quotation::where('status', 'accepted')->with('customer')->get();
        return view('sales.sales_invoices.edit', compact('salesInvoice', 'customers', 'products', 'salesOrders', 'quotations'));
    }

    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->payment_status === 'paid') {
            return redirect()->route('sales.sales-invoices.show', $salesInvoice->id)
                ->with('error', 'Invoice yang sudah lunas tidak dapat diedit.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $salesInvoice->update([
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'quotation_id' => $validated['quotation_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $salesInvoice->items()->delete();

            foreach ($validated['items'] as $item) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            ActivityLogger::log(
                'SalesInvoice',
                'update',
                "Invoice {$salesInvoice->code} berhasil diperbarui",
                'sales_invoice',
                $salesInvoice->id,
                $salesInvoice->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-invoices.show', $salesInvoice->id)
                ->with('success', 'Invoice berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui invoice: ' . $e->getMessage());
        }
    }

    public function destroy(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->payment_status === 'paid') {
            return redirect()->back()
                ->with('error', 'Invoice yang sudah lunas tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $code = $salesInvoice->code;
            $salesInvoice->items()->delete();
            $salesInvoice->delete();

            ActivityLogger::log(
                'SalesInvoice',
                'delete',
                "Invoice {$code} berhasil dihapus",
                'sales_invoice',
                $salesInvoice->id
            );

            DB::commit();

            return redirect()->route('sales.sales-invoices.index')
                ->with('success', 'Invoice berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus invoice: ' . $e->getMessage());
        }
    }

    public function addPayment(Request $request, $id)
    {
        $invoice = SalesInvoice::findOrFail($id);

        if ($invoice->payment_status === 'paid') {
            return redirect()->back()
                ->with('error', 'Invoice ini sudah lunas.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'code' => DocumentNumber::generateSimple('PAY', 'payments'),
                'type' => 'incoming',
                'customer_id' => $invoice->customer_id,
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'reference' => $validated['reference'] ?? null,
                'reference_type' => 'sales_invoice',
                'reference_id' => $invoice->id,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $newPaidAmount = ($invoice->paid_amount ?? 0) + $validated['amount'];
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newPaidAmount >= $invoice->total ? 'paid' : 'partially_paid',
            ]);

            ActivityLogger::log(
                'SalesInvoice',
                'payment',
                "Pembayaran Rp " . number_format($validated['amount'], 0, ',', '.') . " untuk Invoice {$invoice->code} berhasil dicatat",
                'sales_invoice',
                $invoice->id,
                ['payment' => $payment->toArray(), 'invoice' => $invoice->fresh()->toArray()]
            );

            DB::commit();

            return redirect()->route('sales.sales-invoices.show', $invoice->id)
                ->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }
}
