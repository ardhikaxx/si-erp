<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Revenue;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        $totalRevenue = Revenue::whereMonth('revenue_date', $month)
            ->whereYear('revenue_date', $year)
            ->sum('amount');

        $totalExpense = Expense::whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $totalSalesInvoices = SalesInvoice::count();
        $unpaidInvoices = SalesInvoice::whereIn('payment_status', ['unpaid', 'partial'])->count();

        $totalReceivables = SalesInvoice::whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->select(DB::raw('SUM(total - COALESCE(paid_amount, 0)) as receivables'))
            ->value('receivables') ?? 0;

        $totalPayables = PurchaseOrder::where('status', 'approved')
            ->select(DB::raw('SUM(total - COALESCE(paid_amount, 0)) as payables'))
            ->value('payables') ?? 0;

        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();
        $totalProducts = Product::count();

        $lowStockProducts = Product::whereColumn('current_stock', '<=', 'stock_min')
            ->where('stock_min', '>', 0)
            ->limit(10)
            ->get();

        $recentPurchaseOrders = PurchaseOrder::with('supplier')
            ->latest()
            ->take(5)
            ->get();

        $recentSalesOrders = SalesOrder::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $recentActivityLogs = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        $chartData = $this->getMonthlyChartData();

        $bestSellingProducts = $this->getBestSellingProducts();

        $chartLabels = $chartData['months'];
        $revenueData = $chartData['revenues'];
        $expenseData = $chartData['expenses'];

        return view('dashboard.index', compact(
            'totalRevenue',
            'totalExpense',
            'totalSalesInvoices',
            'unpaidInvoices',
            'totalReceivables',
            'totalPayables',
            'totalCustomers',
            'totalSuppliers',
            'totalProducts',
            'lowStockProducts',
            'recentPurchaseOrders',
            'recentSalesOrders',
            'recentActivityLogs',
            'chartLabels',
            'revenueData',
            'expenseData',
            'bestSellingProducts',
        ));
    }

    private function getMonthlyChartData(): array
    {
        $months = collect();
        $revenues = collect();
        $expenses = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $label = $date->isoFormat('MMM Y');

            $months->push($label);

            $revenues->push(
                Revenue::whereMonth('revenue_date', $date->month)
                    ->whereYear('revenue_date', $date->year)
                    ->sum('amount')
            );

            $expenses->push(
                Expense::whereMonth('expense_date', $date->month)
                    ->whereYear('expense_date', $date->year)
                    ->sum('amount')
            );
        }

        return [
            'months'   => $months,
            'revenues' => $revenues,
            'expenses' => $expenses,
        ];
    }

    private function getBestSellingProducts(): array
    {
        $fromSalesOrder = SalesOrderItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_qty')
        )
            ->groupBy('product_id');

        $fromSalesInvoice = SalesInvoiceItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_qty')
        )
            ->groupBy('product_id');

        $combined = DB::table(DB::raw("({$fromSalesOrder->toSql()}) as so"))
            ->mergeBindings($fromSalesOrder->getQuery())
            ->unionAll(
                DB::table(DB::raw("({$fromSalesInvoice->toSql()}) as si"))
                    ->mergeBindings($fromSalesInvoice->getQuery())
            );

        $bestSelling = DB::table(DB::raw("({$combined->toSql()}) as combined"))
            ->mergeBindings($combined)
            ->select('product_id', DB::raw('SUM(total_qty) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        $products = Product::whereIn('id', $bestSelling->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return $bestSelling->map(function ($item) use ($products) {
            return [
                'product'        => $products->get($item->product_id),
                'total_quantity' => $item->total_quantity,
            ];
        })->toArray();
    }
}
