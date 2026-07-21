<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockOpnameController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.stock-opnames.index', compact('warehouses'));
    }

    public function ajax()
    {
        $opnames = StockOpname::with('warehouse', 'creator');

        if ($search = request('search.value')) {
            $opnames->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('warehouse', fn($wq) => $wq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($warehouseId = request('filter_warehouse')) {
            $opnames->where('warehouse_id', $warehouseId);
        }

        if ($status = request('filter_status')) {
            $opnames->where('status', $status);
        }

        return DataTables::of($opnames)
            ->addIndexColumn()
            ->addColumn('action', fn($opname) => view('inventory.stock-opnames.action', compact('opname'))->render())
            ->addColumn('warehouse_name', fn($opname) => $opname->warehouse?->name)
            ->addColumn('creator_name', fn($opname) => $opname->creator?->name)
            ->editColumn('status', function ($opname) {
                $labels = [
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'in_progress' => '<span class="badge bg-warning">Proses</span>',
                    'completed' => '<span class="badge bg-success">Selesai</span>',
                    'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
                ];
                return $labels[$opname->status] ?? '<span class="badge bg-secondary">' . $opname->status . '</span>';
            })
            ->editColumn('opname_date', fn($opname) => $opname->opname_date->format('d/m/Y'))
            ->editColumn('created_at', fn($opname) => $opname->created_at->format('d/m/Y H:i'))
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.stock-opnames.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date'  => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'code'         => DocumentNumber::generateSimple('OP', 'stock_opnames'),
                'warehouse_id' => $validated['warehouse_id'],
                'opname_date'  => $validated['opname_date'],
                'status'       => 'draft',
                'notes'        => $validated['notes'],
                'created_by'   => Auth::id(),
            ]);

            $products = Product::where('warehouse_id', $validated['warehouse_id'])
                ->where('current_stock', '>', 0)
                ->get();

            foreach ($products as $product) {
                StockOpnameItem::create([
                    'stock_opname_id'  => $opname->id,
                    'product_id'       => $product->id,
                    'system_quantity'  => $product->current_stock,
                    'actual_quantity'  => $product->current_stock,
                    'difference'       => 0,
                ]);
            }

            ActivityLogger::log(
                'StockOpname',
                'create',
                "Stock opname {$opname->code} berhasil dibuat",
                'stock_opname',
                $opname->id,
                $opname->toArray()
            );

            DB::commit();

            return redirect()->route('inventory.stock-opnames.edit', $opname->id)
                ->with('success', 'Stock opname berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat stock opname: ' . $e->getMessage());
        }
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load('warehouse', 'items.product', 'creator', 'approver');
        return view('inventory.stock-opnames.show', compact('stockOpname'));
    }

    public function edit(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'draft') {
            return redirect()->route('inventory.stock-opnames.show', $stockOpname->id)
                ->with('error', 'Stock opname yang sudah diproses tidak dapat diubah.');
        }

        $stockOpname->load('items.product');
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('inventory.stock-opnames.edit', compact('stockOpname', 'warehouses'));
    }

    public function update(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'draft') {
            return redirect()->route('inventory.stock-opnames.show', $stockOpname->id)
                ->with('error', 'Stock opname yang sudah diproses tidak dapat diubah.');
        }

        $validated = $request->validate([
            'opname_date' => 'required|date',
            'notes'       => 'nullable|string|max:500',
            'items'       => 'required|array',
            'items.*.id'  => 'required|exists:stock_opname_items,id',
            'items.*.actual_quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $stockOpname->update([
                'opname_date' => $validated['opname_date'],
                'notes'       => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = StockOpnameItem::findOrFail($itemData['id']);
                $item->update([
                    'actual_quantity' => $itemData['actual_quantity'],
                    'difference'      => $itemData['actual_quantity'] - $item->system_quantity,
                ]);
            }

            ActivityLogger::log(
                'StockOpname',
                'update',
                "Stock opname {$stockOpname->code} berhasil diperbarui",
                'stock_opname',
                $stockOpname->id,
                $stockOpname->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('inventory.stock-opnames.show', $stockOpname->id)
                ->with('success', 'Stock opname berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui stock opname: ' . $e->getMessage());
        }
    }

    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Stock opname yang sudah selesai tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $code = $stockOpname->code;
            $stockOpname->items()->delete();
            $stockOpname->delete();

            ActivityLogger::log(
                'StockOpname',
                'delete',
                "Stock opname {$code} berhasil dihapus",
                'stock_opname',
                $stockOpname->id
            );

            DB::commit();

            return redirect()->route('inventory.stock-opnames.index')
                ->with('success', 'Stock opname berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus stock opname: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $opname = StockOpname::with('items.product')->findOrFail($id);

            if ($opname->status !== 'draft' && $opname->status !== 'in_progress') {
                return redirect()->back()
                    ->with('error', 'Stock opname dengan status ini tidak dapat disetujui.');
            }

            $opname->update([
                'status'       => 'completed',
                'approved_by'  => Auth::id(),
                'approved_at'  => now(),
            ]);

            foreach ($opname->items as $item) {
                $product = $item->product;
                $quantityBefore = $product->current_stock;
                $quantityAfter = $item->actual_quantity;
                $diff = $quantityAfter - $quantityBefore;

                $product->update(['current_stock' => $quantityAfter]);

                InventoryMovement::create([
                    'product_id'     => $item->product_id,
                    'warehouse_id'   => $opname->warehouse_id,
                    'reference_type' => 'stock_opname',
                    'reference_id'   => $opname->id,
                    'type'           => $diff >= 0 ? 'in' : 'out',
                    'quantity_before'=> $quantityBefore,
                    'quantity'       => abs($diff),
                    'quantity_after' => $quantityAfter,
                    'description'    => "Penyesuaian stock opname {$opname->code}",
                    'created_by'     => Auth::id(),
                ]);
            }

            ActivityLogger::log(
                'StockOpname',
                'approve',
                "Stock opname {$opname->code} berhasil disetujui",
                'stock_opname',
                $opname->id,
                $opname->toArray()
            );

            DB::commit();

            return redirect()->route('inventory.stock-opnames.show', $opname->id)
                ->with('success', 'Stock opname berhasil disetujui dan stok telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menyetujui stock opname: ' . $e->getMessage());
        }
    }
}
