<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller
{
    public function index()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('sales.quotations.index', compact('customers'));
    }

    public function ajax()
    {
        $quotations = Quotation::with('customer', 'creator');

        if ($search = request('search.value')) {
            $quotations->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = request('filter_status')) {
            $quotations->where('status', $status);
        }

        if ($customerId = request('filter_customer')) {
            $quotations->where('customer_id', $customerId);
        }

        return DataTables::of($quotations)
            ->addIndexColumn()
            ->addColumn('action', fn($q) => view('sales.quotations.action', compact('q'))->render())
            ->addColumn('customer_name', fn($q) => $q->customer?->name ?? '-')
            ->editColumn('code', fn($q) => '<span class="fw-medium">' . $q->code . '</span>')
            ->editColumn('quotation_date', fn($q) => $q->quotation_date?->format('d/m/Y'))
            ->editColumn('valid_until', fn($q) => $q->valid_until?->format('d/m/Y'))
            ->editColumn('total', fn($q) => number_format($q->total, 0, ',', '.'))
            ->editColumn('status', function ($q) {
                $labels = [
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'sent' => '<span class="badge bg-info">Terkirim</span>',
                    'accepted' => '<span class="badge bg-success">Diterima</span>',
                    'rejected' => '<span class="badge bg-danger">Ditolak</span>',
                    'expired' => '<span class="badge bg-warning text-dark">Kedaluwarsa</span>',
                    'converted' => '<span class="badge bg-primary">Dikonversi</span>',
                ];
                return $labels[$q->status] ?? '<span class="badge bg-secondary">' . $q->status . '</span>';
            })
            ->editColumn('created_at', fn($q) => $q->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'code', 'status'])
            ->make(true);
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        return view('sales.quotations.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:quotation_date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
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
            $quotation = Quotation::create([
                'code' => DocumentNumber::generateSimple('QT', 'quotations'),
                'customer_id' => $validated['customer_id'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'status' => $validated['status'] ?? 'draft',
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            ActivityLogger::log(
                'Quotation',
                'create',
                "Quotation {$quotation->code} berhasil dibuat",
                'quotation',
                $quotation->id,
                $quotation->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('success', 'Quotation berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat quotation: ' . $e->getMessage());
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('items.product', 'customer', 'creator');

        if (request()->wantsJson()) {
            return response()->json($quotation);
        }

        return view('sales.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('error', 'Quotation dengan status ini tidak dapat diedit.');
        }

        $quotation->load('items.product');
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        return view('sales.quotations.edit', compact('quotation', 'customers', 'products'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('error', 'Quotation dengan status ini tidak dapat diedit.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:quotation_date',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
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
            $quotation->update([
                'customer_id' => $validated['customer_id'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'status' => $validated['status'] ?? 'draft',
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
            ]);

            $quotation->items()->delete();

            foreach ($validated['items'] as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            ActivityLogger::log(
                'Quotation',
                'update',
                "Quotation {$quotation->code} berhasil diperbarui",
                'quotation',
                $quotation->id,
                $quotation->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('success', 'Quotation berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui quotation: ' . $e->getMessage());
        }
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return redirect()->back()
                ->with('error', 'Quotation yang sudah dikonversi tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $code = $quotation->code;
            $quotation->items()->delete();
            $quotation->delete();

            ActivityLogger::log(
                'Quotation',
                'delete',
                "Quotation {$code} berhasil dihapus",
                'quotation',
                $quotation->id
            );

            DB::commit();

            return redirect()->route('sales.quotations.index')
                ->with('success', 'Quotation berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus quotation: ' . $e->getMessage());
        }
    }

    public function convertToSO($id)
    {
        $quotation = Quotation::with('items.product', 'customer')->findOrFail($id);

        if ($quotation->status === 'converted') {
            return redirect()->back()
                ->with('error', 'Quotation ini sudah dikonversi ke Sales Order.');
        }

        if ($quotation->status !== 'accepted') {
            return redirect()->back()
                ->with('error', 'Hanya quotation dengan status Diterima yang dapat dikonversi.');
        }

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create([
                'code' => DocumentNumber::generateSimple('SO', 'sales_orders'),
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'order_date' => now(),
                'expected_date' => null,
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'tax' => $quotation->tax,
                'shipping_cost' => $quotation->shipping_cost,
                'total' => $quotation->total,
                'status' => 'draft',
                'notes' => $quotation->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $quotation->update(['status' => 'converted']);

            ActivityLogger::log(
                'Quotation',
                'convert',
                "Quotation {$quotation->code} berhasil dikonversi ke Sales Order {$salesOrder->code}",
                'quotation',
                $quotation->id,
                ['quotation' => $quotation->toArray(), 'sales_order' => $salesOrder->toArray()]
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.show', $salesOrder->id)
                ->with('success', "Quotation {$quotation->code} berhasil dikonversi ke Sales Order {$salesOrder->code}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal mengkonversi quotation: ' . $e->getMessage());
        }
    }
}
