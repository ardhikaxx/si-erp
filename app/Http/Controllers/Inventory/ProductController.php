<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        return view('inventory.products.index');
    }

    public function ajax()
    {
        $products = Product::with('warehouse', 'category', 'unit');

        if ($search = request('search.value')) {
            $products->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($warehouseId = request('filter_warehouse')) {
            $products->where('warehouse_id', $warehouseId);
        }

        return DataTables::of($products)
            ->addIndexColumn()
            ->addColumn('action', fn($product) => view('inventory.products.action', compact('product'))->render())
            ->addColumn('warehouse_name', fn($product) => $product->warehouse?->name)
            ->addColumn('category_name', fn($product) => $product->category?->name)
            ->addColumn('unit_name', fn($product) => $product->unit?->name)
            ->editColumn('current_stock', fn($product) => number_format($product->current_stock, 2))
            ->editColumn('created_at', fn($product) => $product->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action'])
            ->make(true);
    }

    public function stockIn()
    {
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.stock-in', compact('products', 'warehouses'));
    }

    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'warehouse_id'=> 'required|exists:warehouses,id',
            'quantity'    => 'required|numeric|min:0.01',
            'reference'   => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($validated['product_id']);
            $quantityBefore = $product->current_stock;

            $product->increment('current_stock', $validated['quantity']);

            InventoryMovement::create([
                'product_id'      => $product->id,
                'warehouse_id'    => $validated['warehouse_id'],
                'reference_type'  => 'manual_in',
                'reference_id'    => null,
                'type'            => 'in',
                'quantity_before' => $quantityBefore,
                'quantity'        => $validated['quantity'],
                'quantity_after'  => $product->fresh()->current_stock,
                'description'     => $validated['notes'] ?: ($validated['reference'] ?: 'Stock masuk manual'),
                'created_by'      => Auth::id(),
            ]);

            ActivityLogger::log(
                'Inventory',
                'stock_in',
                "Stock masuk: {$product->name} ({$validated['quantity']})",
                'product',
                $product->id,
                $validated
            );

            DB::commit();

            return redirect()->route('inventory.stock-in')
                ->with('success', 'Stock masuk berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mencatat stock masuk: ' . $e->getMessage());
        }
    }

    public function stockOut()
    {
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.stock-out', compact('products', 'warehouses'));
    }

    public function storeStockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'warehouse_id'=> 'required|exists:warehouses,id',
            'quantity'    => 'required|numeric|min:0.01',
            'reference'   => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($validated['product_id']);
            $quantityBefore = $product->current_stock;

            if ($product->current_stock < $validated['quantity']) {
                return redirect()->back()->withInput()
                    ->with('error', "Stok tidak mencukupi. Stok saat ini: {$product->current_stock}");
            }

            $product->decrement('current_stock', $validated['quantity']);

            InventoryMovement::create([
                'product_id'      => $product->id,
                'warehouse_id'    => $validated['warehouse_id'],
                'reference_type'  => 'manual_out',
                'reference_id'    => null,
                'type'            => 'out',
                'quantity_before' => $quantityBefore,
                'quantity'        => $validated['quantity'],
                'quantity_after'  => $product->fresh()->current_stock,
                'description'     => $validated['notes'] ?: ($validated['reference'] ?: 'Stock keluar manual'),
                'created_by'      => Auth::id(),
            ]);

            ActivityLogger::log(
                'Inventory',
                'stock_out',
                "Stock keluar: {$product->name} ({$validated['quantity']})",
                'product',
                $product->id,
                $validated
            );

            DB::commit();

            return redirect()->route('inventory.stock-out')
                ->with('success', 'Stock keluar berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal mencatat stock keluar: ' . $e->getMessage());
        }
    }

    public function transfer()
    {
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.transfer', compact('products', 'warehouses'));
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'from_warehouse_id'=> 'required|exists:warehouses,id',
            'to_warehouse_id'  => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity'        => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($validated['product_id']);
            $quantityBefore = $product->current_stock;

            if ($product->current_stock < $validated['quantity']) {
                return redirect()->back()->withInput()
                    ->with('error', "Stok tidak mencukupi untuk transfer. Stok saat ini: {$product->current_stock}");
            }

            $product->decrement('current_stock', $validated['quantity']);

            InventoryMovement::create([
                'product_id'      => $product->id,
                'warehouse_id'    => $validated['from_warehouse_id'],
                'reference_type'  => 'transfer',
                'reference_id'    => null,
                'type'            => 'out',
                'quantity_before' => $quantityBefore,
                'quantity'        => $validated['quantity'],
                'quantity_after'  => $product->fresh()->current_stock,
                'description'    => "Transfer ke gudang #{$validated['to_warehouse_id']}",
                'created_by'     => Auth::id(),
            ]);

            $targetQtyBefore = 0;
            $targetProduct = Product::where('id', $validated['product_id'])
                ->where('warehouse_id', $validated['to_warehouse_id'])
                ->first();

            if ($targetProduct) {
                $targetQtyBefore = $targetProduct->current_stock;
                $targetProduct->increment('current_stock', $validated['quantity']);
            }

            InventoryMovement::create([
                'product_id'      => $product->id,
                'warehouse_id'    => $validated['to_warehouse_id'],
                'reference_type'  => 'transfer',
                'reference_id'    => null,
                'type'            => 'in',
                'quantity_before' => $targetQtyBefore,
                'quantity'        => $validated['quantity'],
                'quantity_after'  => $targetProduct
                    ? $targetProduct->fresh()->current_stock
                    : $validated['quantity'],
                'description'    => "Transfer dari gudang #{$validated['from_warehouse_id']}",
                'created_by'     => Auth::id(),
            ]);

            ActivityLogger::log(
                'Inventory',
                'transfer',
                "Transfer stok: {$product->name} ({$validated['quantity']}) dari gudang #{$validated['from_warehouse_id']} ke #{$validated['to_warehouse_id']}",
                'product',
                $product->id,
                $validated
            );

            DB::commit();

            return redirect()->route('inventory.transfer')
                ->with('success', 'Transfer stok berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal transfer stok: ' . $e->getMessage());
        }
    }

    public function movements()
    {
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.movements', compact('products', 'warehouses'));
    }

    public function movementsAjax()
    {
        $movements = InventoryMovement::with('product', 'warehouse', 'creator');

        if ($search = request('search.value')) {
            $movements->where(function ($q) use ($search) {
                $q->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($productId = request('filter_product')) {
            $movements->where('product_id', $productId);
        }

        if ($warehouseId = request('filter_warehouse')) {
            $movements->where('warehouse_id', $warehouseId);
        }

        if ($type = request('filter_type')) {
            $movements->where('type', $type);
        }

        if ($dateFrom = request('filter_date_from')) {
            $movements->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = request('filter_date_to')) {
            $movements->whereDate('created_at', '<=', $dateTo);
        }

        return DataTables::of($movements)
            ->addIndexColumn()
            ->addColumn('product_name', fn($m) => $m->product?->name)
            ->addColumn('warehouse_name', fn($m) => $m->warehouse?->name)
            ->addColumn('creator_name', fn($m) => $m->creator?->name)
            ->editColumn('type', function ($m) {
                $labels = [
                    'in' => '<span class="badge bg-success">Masuk</span>',
                    'out' => '<span class="badge bg-danger">Keluar</span>',
                ];
                return $labels[$m->type] ?? '<span class="badge bg-secondary">' . $m->type . '</span>';
            })
            ->editColumn('quantity_before', fn($m) => number_format($m->quantity_before, 2))
            ->editColumn('quantity', fn($m) => number_format($m->quantity, 2))
            ->editColumn('quantity_after', fn($m) => number_format($m->quantity_after, 2))
            ->editColumn('created_at', fn($m) => $m->created_at->format('d/m/Y H:i'))
            ->rawColumns(['type'])
            ->make(true);
    }
}
