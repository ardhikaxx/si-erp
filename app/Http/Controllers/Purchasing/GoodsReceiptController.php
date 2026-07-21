<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = GoodsReceipt::with(['purchaseOrder', 'warehouse', 'creator'])->select('goods_receipts.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $colors = ['draft' => 'secondary', 'completed' => 'success', 'cancelled' => 'danger'];
                    $color = $colors[$row->status] ?? 'secondary';
                    return "<span class='badge bg-{$color}'>{$row->status}</span>";
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('purchasing.goods-receipts.show', $row->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $btn .= ' <button onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('purchasing.goods-receipts.index');
    }

    public function create()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')->whereIn('status', ['approved', 'ordered', 'partially_received'])->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('purchasing.goods-receipts.form', [
            'model' => new GoodsReceipt(),
            'purchaseOrders' => $purchaseOrders,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'receipt_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $code = DocumentNumber::generate('GR', 'goods_receipts', 'code', now());

            $gr = GoodsReceipt::create([
                'code' => $code,
                'purchase_order_id' => $request->purchase_order_id,
                'warehouse_id' => $request->warehouse_id,
                'receipt_date' => $request->receipt_date,
                'receipt_number' => $request->receipt_number,
                'status' => 'completed',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            $po = PurchaseOrder::find($request->purchase_order_id);
            $totalReceivedQty = 0;
            $totalOrderedQty = 0;

            foreach ($request->items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                $product = Product::find($item['product_id']);
                $qtyBefore = $product->current_stock;

                $product->increment('current_stock', $item['quantity']);

                InventoryMovement::create([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $gr->id,
                    'type' => 'in',
                    'quantity_before' => $qtyBefore,
                    'quantity' => $item['quantity'],
                    'quantity_after' => $qtyBefore + $item['quantity'],
                    'price' => $item['price'],
                    'description' => 'Penerimaan barang dari PO: ' . $po->code,
                    'created_by' => auth()->id(),
                ]);

                $poItem = PurchaseOrderItem::where('purchase_order_id', $request->purchase_order_id)
                    ->where('product_id', $item['product_id'])
                    ->first();
                if ($poItem) {
                    $poItem->increment('received_quantity', $item['quantity']);
                }
            }

            $poItems = PurchaseOrderItem::where('purchase_order_id', $request->purchase_order_id)->get();
            $allReceived = $poItems->every(fn($i) => $i->received_quantity >= $i->quantity);
            $anyReceived = $poItems->sum('received_quantity') > 0;

            if ($allReceived) {
                $po->update(['status' => 'received']);
            } elseif ($anyReceived) {
                $po->update(['status' => 'partially_received']);
            }

            ActivityLogger::log('Purchasing', 'Create', "Membuat Goods Receipt: {$code}", 'goods_receipt', $gr->id);

            DB::commit();
            return redirect()->route('purchasing.goods-receipts.index')->with('success', 'Goods Receipt berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat Goods Receipt: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $model = GoodsReceipt::with(['items.product', 'purchaseOrder.supplier', 'warehouse', 'creator'])->findOrFail($id);
        return view('purchasing.goods-receipts.show', compact('model'));
    }

    public function edit($id)
    {
        return back()->with('error', 'Goods Receipt tidak dapat diedit.');
    }

    public function update(Request $request, $id)
    {
        return back()->with('error', 'Goods Receipt tidak dapat diupdate.');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $gr = GoodsReceipt::findOrFail($id);
            foreach ($gr->items as $item) {
                Product::where('id', $item->product_id)->decrement('current_stock', $item->quantity);
                InventoryMovement::where('reference_type', 'goods_receipt')->where('reference_id', $gr->id)->delete();
            }
            $gr->items()->delete();
            $gr->delete();

            ActivityLogger::log('Purchasing', 'Delete', "Menghapus Goods Receipt: {$gr->code}", 'goods_receipt', $gr->id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Goods Receipt berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus Goods Receipt.'], 500);
        }
    }
}
