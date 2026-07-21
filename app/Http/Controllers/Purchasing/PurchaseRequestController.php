<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\ActivityLogger;
use App\Helpers\DocumentNumber;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PurchaseRequest::with(['supplier', 'department', 'creator'])->select('purchase_requests.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $colors = ['draft' => 'secondary', 'submitted' => 'info', 'pending_approval' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'danger'];
                    $color = $colors[$row->status] ?? 'secondary';
                    return "<span class='badge bg-{$color}'>{$row->status}</span>";
                })
                ->addColumn('total_format', fn($r) => 'Rp ' . number_format($r->total, 0, ',', '.'))
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('purchasing.purchase-requests.show', $row->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                    $btn .= ' <a href="' . route('purchasing.purchase-requests.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>';
                    $btn .= ' <button onclick="confirmDelete(' . $row->id . ')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
        return view('purchasing.purchase-requests.index');
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        $products = Product::where('status', 'active')->get();
        return view('purchasing.purchase-requests.form', [
            'model' => new PurchaseRequest(),
            'suppliers' => $suppliers,
            'departments' => $departments,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'department_id' => 'nullable|exists:departments,id',
            'request_date' => 'required|date',
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
            $code = DocumentNumber::generate('PR', 'purchase_requests', 'code', now());

            $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price']);

            $pr = PurchaseRequest::create([
                'code' => $code,
                'supplier_id' => $request->supplier_id,
                'department_id' => $request->department_id,
                'request_date' => $request->request_date,
                'expected_date' => $request->expected_date,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            ActivityLogger::log('Purchasing', 'Create', "Membuat Purchase Request: {$code}", 'purchase_request', $pr->id);

            DB::commit();
            return redirect()->route('purchasing.purchase-requests.index')->with('success', 'Purchase Request berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat Purchase Request: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $model = PurchaseRequest::with(['items.product', 'supplier', 'department', 'creator', 'approver'])->findOrFail($id);
        return view('purchasing.purchase-requests.show', compact('model'));
    }

    public function edit($id)
    {
        $model = PurchaseRequest::with('items')->findOrFail($id);
        if (!in_array($model->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Hanya PR dengan status Draft/Rejected yang dapat diedit.');
        }
        $suppliers = Supplier::where('status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        $products = Product::where('status', 'active')->get();
        return view('purchasing.purchase-requests.form', compact('model', 'suppliers', 'departments', 'products'));
    }

    public function update(Request $request, $id)
    {
        $pr = PurchaseRequest::findOrFail($id);
        if (!in_array($pr->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Hanya PR dengan status Draft/Rejected yang dapat diedit.');
        }

        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'department_id' => 'nullable|exists:departments,id',
            'request_date' => 'required|date',
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

            $pr->update([
                'supplier_id' => $request->supplier_id,
                'department_id' => $request->department_id,
                'request_date' => $request->request_date,
                'expected_date' => $request->expected_date,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'notes' => $request->notes,
            ]);

            $pr->items()->delete();
            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            ActivityLogger::log('Purchasing', 'Update', "Mengubah Purchase Request: {$pr->code}", 'purchase_request', $pr->id);

            DB::commit();
            return redirect()->route('purchasing.purchase-requests.index')->with('success', 'Purchase Request berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate Purchase Request.')->withInput();
        }
    }

    public function destroy($id)
    {
        $pr = PurchaseRequest::findOrFail($id);
        $pr->items()->delete();
        $pr->delete();

        ActivityLogger::log('Purchasing', 'Delete', "Menghapus Purchase Request: {$pr->code}", 'purchase_request', $pr->id);

        return response()->json(['success' => true, 'message' => 'Purchase Request berhasil dihapus.']);
    }

    public function submit($id)
    {
        $pr = PurchaseRequest::findOrFail($id);
        $pr->update(['status' => 'submitted']);

        ActivityLogger::log('Purchasing', 'Submit', "Mengajukan Purchase Request: {$pr->code}", 'purchase_request', $pr->id);

        return redirect()->back()->with('success', 'Purchase Request berhasil diajukan.');
    }

    public function approve($id)
    {
        $pr = PurchaseRequest::findOrFail($id);
        $pr->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        ActivityLogger::log('Purchasing', 'Approve', "Menyetujui Purchase Request: {$pr->code}", 'purchase_request', $pr->id);

        return redirect()->back()->with('success', 'Purchase Request berhasil disetujui.');
    }

    public function reject($id)
    {
        $pr = PurchaseRequest::findOrFail($id);
        $pr->update(['status' => 'rejected']);

        ActivityLogger::log('Purchasing', 'Reject', "Menolak Purchase Request: {$pr->code}", 'purchase_request', $pr->id);

        return redirect()->back()->with('success', 'Purchase Request ditolak.');
    }
}
