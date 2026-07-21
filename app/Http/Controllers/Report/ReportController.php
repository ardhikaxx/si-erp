<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\Revenue;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $customerId = $request->input('customer_id');
        $status = $request->input('status');

        $query = SalesInvoice::with('customer')
            ->whereBetween('invoice_date', [$startDate, $endDate]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->latest('invoice_date')->get();

        $summary = (object) [
            'total_count' => $invoices->count(),
            'total_revenue' => $invoices->sum('total'),
            'average_order' => $invoices->count() > 0 ? $invoices->sum('total') / $invoices->count() : 0,
        ];

        $chartData = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays(29 - $i)->format('Y-m-d');
            $chartData[$date] = 0;
        }

        $salesByDate = SalesInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->select(DB::raw("DATE(invoice_date) as date"), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        foreach ($salesByDate as $date => $total) {
            $chartData[$date] = (float) $total;
        }

        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();

        return view('reports.sales', compact(
            'startDate', 'endDate', 'customerId', 'status',
            'invoices', 'summary', 'chartData', 'customers', 'categories'
        ));
    }

    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');

        $query = PurchaseOrder::with('supplier')
            ->whereBetween('order_date', [$startDate, $endDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->latest('order_date')->get();

        $summary = (object) [
            'total_count' => $orders->count(),
            'total_cost' => $orders->sum('total'),
            'total_items' => $orders->sum(function ($o) {
                return $o->items()->sum('quantity');
            }),
        ];

        $chartData = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays(29 - $i)->format('Y-m-d');
            $chartData[$date] = 0;
        }

        $purchasesByDate = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->select(DB::raw("DATE(order_date) as date"), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        foreach ($purchasesByDate as $date => $total) {
            $chartData[$date] = (float) $total;
        }

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('reports.purchases', compact(
            'startDate', 'endDate', 'supplierId', 'status',
            'orders', 'summary', 'chartData', 'suppliers'
        ));
    }

    public function stock(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $categoryId = $request->input('category_id');

        $query = Product::with(['category', 'warehouse', 'unit']);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->get();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();

        $summary = (object) [
            'total_products' => $products->count(),
            'total_stock' => $products->sum('current_stock'),
            'low_stock_count' => $products->where('current_stock', '<=', 'stock_min')->where('stock_min', '>', 0)->count(),
            'overstock_count' => $products->filter(function ($p) {
                return $p->stock_max > 0 && $p->current_stock >= $p->stock_max;
            })->count(),
        ];

        return view('reports.stock', compact(
            'products', 'warehouses', 'categories', 'warehouseId', 'categoryId', 'summary'
        ));
    }

    public function finance(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $revenues = Revenue::with('account')
            ->whereBetween('revenue_date', [$startDate, $endDate])
            ->latest('revenue_date')
            ->get();

        $expenses = Expense::with('account')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date')
            ->get();

        $totalRevenue = $revenues->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $difference = $totalRevenue - $totalExpense;

        $monthlyLabels = [];
        $monthlyRevenues = [];
        $monthlyExpenses = [];

        $start = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $end = \Carbon\Carbon::parse($endDate)->startOfMonth();

        while ($start->lte($end)) {
            $label = $start->isoFormat('MMM Y');
            $monthlyLabels[] = $label;

            $monthlyRevenues[] = (float) Revenue::whereMonth('revenue_date', $start->month)
                ->whereYear('revenue_date', $start->year)
                ->sum('amount');

            $monthlyExpenses[] = (float) Expense::whereMonth('expense_date', $start->month)
                ->whereYear('expense_date', $start->year)
                ->sum('amount');

            $start->addMonth();
        }

        $chartData = [
            'labels' => $monthlyLabels,
            'revenues' => $monthlyRevenues,
            'expenses' => $monthlyExpenses,
        ];

        return view('reports.finance', compact(
            'startDate', 'endDate',
            'revenues', 'expenses',
            'totalRevenue', 'totalExpense', 'difference',
            'chartData'
        ));
    }

    public function export($type, Request $request)
    {
        $filename = 'laporan-' . $type . '-' . now()->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($type, $request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            switch ($type) {
                case 'sales':
                    fputcsv($handle, ['Kode', 'Tanggal', 'Customer', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status']);
                    $query = SalesInvoice::with('customer');
                    if ($request->filled('start_date')) {
                        $query->whereDate('invoice_date', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('invoice_date', '<=', $request->end_date);
                    }
                    $query->latest('invoice_date')->chunk(500, function ($invoices) use ($handle) {
                        foreach ($invoices as $inv) {
                            fputcsv($handle, [
                                $inv->code,
                                $inv->invoice_date?->format('d/m/Y'),
                                $inv->customer?->name ?? '-',
                                $inv->subtotal,
                                $inv->discount,
                                $inv->tax,
                                $inv->total,
                                $inv->status,
                            ]);
                        }
                    });
                    break;

                case 'purchases':
                    fputcsv($handle, ['Kode', 'Tanggal', 'Supplier', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status']);
                    $query = PurchaseOrder::with('supplier');
                    if ($request->filled('start_date')) {
                        $query->whereDate('order_date', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('order_date', '<=', $request->end_date);
                    }
                    $query->latest('order_date')->chunk(500, function ($orders) use ($handle) {
                        foreach ($orders as $po) {
                            fputcsv($handle, [
                                $po->code,
                                $po->order_date?->format('d/m/Y'),
                                $po->supplier?->name ?? '-',
                                $po->subtotal,
                                $po->discount,
                                $po->tax,
                                $po->total,
                                $po->status,
                            ]);
                        }
                    });
                    break;

                case 'stock':
                    fputcsv($handle, ['Kode', 'Nama Produk', 'Kategori', 'Gudang', 'Stok Min', 'Stok Saat Ini', 'Stok Max']);
                    $query = Product::with(['category', 'warehouse']);
                    if ($request->filled('warehouse_id')) {
                        $query->where('warehouse_id', $request->warehouse_id);
                    }
                    if ($request->filled('category_id')) {
                        $query->where('category_id', $request->category_id);
                    }
                    $query->orderBy('name')->chunk(500, function ($products) use ($handle) {
                        foreach ($products as $p) {
                            fputcsv($handle, [
                                $p->code,
                                $p->name,
                                $p->category?->name ?? '-',
                                $p->warehouse?->name ?? '-',
                                $p->stock_min,
                                $p->current_stock,
                                $p->stock_max,
                            ]);
                        }
                    });
                    break;

                case 'finance':
                    fputcsv($handle, ['Tipe', 'Kode', 'Akun', 'Tanggal', 'Jumlah', 'Deskripsi']);
                    $revQuery = Revenue::with('account');
                    $expQuery = Expense::with('account');
                    if ($request->filled('start_date')) {
                        $revQuery->whereDate('revenue_date', '>=', $request->start_date);
                        $expQuery->whereDate('expense_date', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $revQuery->whereDate('revenue_date', '<=', $request->end_date);
                        $expQuery->whereDate('expense_date', '<=', $request->end_date);
                    }
                    $revQuery->latest('revenue_date')->chunk(500, function ($items) use ($handle) {
                        foreach ($items as $r) {
                            fputcsv($handle, [
                                'Pemasukan',
                                $r->code,
                                $r->account?->name ?? '-',
                                $r->revenue_date?->format('d/m/Y'),
                                $r->amount,
                                $r->description,
                            ]);
                        }
                    });
                    $expQuery->latest('expense_date')->chunk(500, function ($items) use ($handle) {
                        foreach ($items as $e) {
                            fputcsv($handle, [
                                'Pengeluaran',
                                $e->code,
                                $e->account?->name ?? '-',
                                $e->expense_date?->format('d/m/Y'),
                                $e->amount,
                                $e->description,
                            ]);
                        }
                    });
                    break;
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
