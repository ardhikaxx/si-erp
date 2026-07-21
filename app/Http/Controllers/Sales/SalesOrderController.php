<?php

namespace App\Http\Controllers\Sales;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function index()
    {
        $customers = Customer::where('status', 'active')->get();
        return view('sales.sales_orders.index', compact('customers'));
    }

    public function ajax()
    {
        $orders = SalesOrder::with('customer', 'creator');

        if ($search = request('search.value')) {
            $orders->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = request('filter_status')) {
            $orders->where('status', $status);
        }

        if ($customerId = request('filter_customer')) {
            $orders->where('customer_id', $customerId);
        }

        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('action', fn($o) => view('sales.sales_orders.action', compact('o'))->render())
            ->addColumn('customer_name', fn($o) => $o->customer?->name ?? '-')
            ->editColumn('code', fn($o) => '<span class="fw-medium">' . $o->code . '</span>')
            ->editColumn('order_date', fn($o) => $o->order_date?->format('d/m/Y'))
            ->editColumn('total', fn($o) => number_format($o->total, 0, ',', '.'))
            ->editColumn('status', function ($o) {
                $labels = [
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'submitted' => '<span class="badge bg-info">Submitted</span>',
                    'approved' => '<span class="badge bg-success">Disetujui</span>',
                    'rejected' => '<span class="badge bg-danger">Ditolak</span>',
                    'processing' => '<span class="badge bg-warning text-dark">Diproses</span>',
                    'completed' => '<span class="badge bg-success">Selesai</span>',
                    'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
                ];
                return $labels[$o->status] ?? '<span class="badge bg-secondary">' . $o->status . '</span>';
            })
            ->editColumn('created_at', fn($o) => $o->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'code', 'status'])
            ->make(true);
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $quotations = Quotation::where('status', 'accepted')
            ->whereDoesntHave('salesOrder')
            ->with('customer')
            ->get();
        return view('sales.sales_orders.create', compact('customers', 'products', 'warehouses', 'quotations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
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
            $order = SalesOrder::create([
                'code' => DocumentNumber::generateSimple('SO', 'sales_orders'),
                'customer_id' => $validated['customer_id'],
                'quotation_id' => $validated['quotation_id'] ?? null,
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            if ($validated['quotation_id']) {
                Quotation::where('id', $validated['quotation_id'])->update(['status' => 'converted']);
            }

            ActivityLogger::log(
                'SalesOrder',
                'create',
                "Sales Order {$order->code} berhasil dibuat",
                'sales_order',
                $order->id,
                $order->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.show', $order->id)
                ->with('success', 'Sales Order berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat sales order: ' . $e->getMessage());
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load('items.product', 'customer', 'warehouse', 'quotation', 'creator', 'approver');

        if (request()->wantsJson()) {
            return response()->json($salesOrder);
        }

        return view('sales.sales_orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales.sales-orders.show', $salesOrder->id)
                ->with('error', 'Sales Order dengan status ini tidak dapat diedit.');
        }

        $salesOrder->load('items.product');
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $quotations = Quotation::where('status', 'accepted')
            ->whereDoesntHave('salesOrder')
            ->with('customer')
            ->get();
        return view('sales.sales_orders.edit', compact('salesOrder', 'customers', 'products', 'warehouses', 'quotations'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales.sales-orders.show', $salesOrder->id)
                ->with('error', 'Sales Order dengan status ini tidak dapat diedit.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
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
            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'quotation_id' => $validated['quotation_id'] ?? null,
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'total' => $validated['total'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $salesOrder->items()->delete();

            foreach ($validated['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            ActivityLogger::log(
                'SalesOrder',
                'update',
                "Sales Order {$salesOrder->code} berhasil diperbarui",
                'sales_order',
                $salesOrder->id,
                $salesOrder->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.show', $salesOrder->id)
                ->with('success', 'Sales Order berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui sales order: ' . $e->getMessage());
        }
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if (!in_array($salesOrder->status, ['draft', 'rejected', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Sales Order dengan status ini tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $code = $salesOrder->code;
            $salesOrder->items()->delete();
            $salesOrder->delete();

            ActivityLogger::log(
                'SalesOrder',
                'delete',
                "Sales Order {$code} berhasil dihapus",
                'sales_order',
                $salesOrder->id
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.index')
                ->with('success', 'Sales Order berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus sales order: ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Hanya Sales Order dengan status Draft yang dapat disubmit.');
        }

        DB::beginTransaction();
        try {
            $order->update(['status' => 'submitted']);

            ActivityLogger::log(
                'SalesOrder',
                'submit',
                "Sales Order {$order->code} berhasil disubmit",
                'sales_order',
                $order->id,
                $order->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.show', $order->id)
                ->with('success', 'Sales Order berhasil disubmit.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal submit sales order: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== 'submitted') {
            return redirect()->back()
                ->with('error', 'Hanya Sales Order dengan status Submitted yang dapat disetujui.');
        }

        DB::beginTransaction();
        try {
            $order->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            ActivityLogger::log(
                'SalesOrder',
                'approve',
                "Sales Order {$order->code} berhasil disetujui",
                'sales_order',
                $order->id,
                $order->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.show', $order->id)
                ->with('success', 'Sales Order berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menyetujui sales order: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== 'submitted') {
            return redirect()->back()
                ->with('error', 'Hanya Sales Order dengan status Submitted yang dapat ditolak.');
        }

        DB::beginTransaction();
        try {
            $order->update(['status' => 'rejected']);

            ActivityLogger::log(
                'SalesOrder',
                'reject',
                "Sales Order {$order->code} berhasil ditolak",
                'sales_order',
                $order->id,
                $order->toArray()
            );

            DB::commit();

            return redirect()->route('sales.sales-orders.show', $order->id)
                ->with('success', 'Sales Order berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menolak sales order: ' . $e->getMessage());
        }
    }
}
