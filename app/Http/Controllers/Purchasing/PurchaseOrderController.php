<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PurchaseOrder::with(['supplier', 'warehouse', 'creator'])->select('purchase_orders.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $colors = ['draft' => 'secondary', 'submitted' => 'info', 'pending_approval' => 'warning', 'approved' => 'success', 'ordered' => 'primary', 'partially_received' => 'warning', 'received' => 'success', 'cancelled' => 'danger', 'completed' => 'success'];
                    $color = $colors[$row->status] ?? 'secondary';
                    return "<span class='badge bg-{$color}'>{$row->status}</span>";
                })
                ->addColumn('total_format', fn($r) => 'Rp ' . number_format($r->total, 0, ',', '.'))
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('purchasing.purchase-orders.show', $row->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $btn .= ' <a href="' . route('purchasing.purchase-orders.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>';
                    $btn .= ' <button onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('purchasing.purchase-orders.index');
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('status', 'active')->get();
        $purchaseRequests = PurchaseRequest::whereIn('status', ['approved', 'submitted'])->get();
        return view('purchasing.purchase-orders.form', [
            'model' => new PurchaseOrder(),
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'purchaseRequests' => $purchaseRequests,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $code = DocumentNumber::generate('PO', 'purchase_orders', 'code', now());

            $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price']);

            $po = PurchaseOrder::create([
                'code' => $code,
                'supplier_id' => $request->supplier_id,
                'purchase_request_id' => $request->purchase_request_id,
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'shipping_cost' => 0,
                'total' => $subtotal,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            ActivityLogger::log('Purchasing', 'Create', "Membuat Purchase Order: {$code}", 'purchase_order', $po->id);

            DB::commit();
            return redirect()->route('purchasing.purchase-orders.index')->with('success', 'Purchase Order berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat Purchase Order.')->withInput();
        }
    }

    public function show($id)
    {
        $model = PurchaseOrder::with(['items.product', 'supplier', 'warehouse', 'purchaseRequest', 'creator', 'approver', 'goodsReceipts'])->findOrFail($id);
        return view('purchasing.purchase-orders.show', compact('model'));
    }

    public function edit($id)
    {
        $model = PurchaseOrder::with('items')->findOrFail($id);
        if (!in_array($model->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Hanya PO dengan status Draft/Rejected yang dapat diedit.');
        }
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('status', 'active')->get();
        $purchaseRequests = PurchaseRequest::whereIn('status', ['approved', 'submitted'])->get();
        return view('purchasing.purchase-orders.form', compact('model', 'suppliers', 'warehouses', 'products', 'purchaseRequests'));
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if (!in_array($po->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Hanya PO dengan status Draft/Rejected yang dapat diedit.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price']);

            $po->update([
                'supplier_id' => $request->supplier_id,
                'purchase_request_id' => $request->purchase_request_id,
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'notes' => $request->notes,
            ]);

            $po->items()->delete();
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            ActivityLogger::log('Purchasing', 'Update', "Mengubah Purchase Order: {$po->code}", 'purchase_order', $po->id);

            DB::commit();
            return redirect()->route('purchasing.purchase-orders.index')->with('success', 'Purchase Order berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate Purchase Order.')->withInput();
        }
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->items()->delete();
        $po->delete();

        ActivityLogger::log('Purchasing', 'Delete', "Menghapus Purchase Order: {$po->code}", 'purchase_order', $po->id);

        return response()->json(['success' => true, 'message' => 'Purchase Order berhasil dihapus.']);
    }

    public function submit($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->update(['status' => 'submitted']);

        ActivityLogger::log('Purchasing', 'Submit', "Mengajukan Purchase Order: {$po->code}", 'purchase_order', $po->id);

        return redirect()->back()->with('success', 'Purchase Order berhasil diajukan.');
    }

    public function approve($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        ActivityLogger::log('Purchasing', 'Approve', "Menyetujui Purchase Order: {$po->code}", 'purchase_order', $po->id);

        return redirect()->back()->with('success', 'Purchase Order berhasil disetujui.');
    }

    public function reject($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->update(['status' => 'rejected']);

        ActivityLogger::log('Purchasing', 'Reject', "Menolak Purchase Order: {$po->code}", 'purchase_order', $po->id);

        return redirect()->back()->with('success', 'Purchase Order ditolak.');
    }
}
