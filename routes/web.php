<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\BranchController;
use App\Http\Controllers\MasterData\DepartmentController;
use App\Http\Controllers\MasterData\PositionController;
use App\Http\Controllers\MasterData\ProductCategoryController;
use App\Http\Controllers\MasterData\ProductUnitController;
use App\Http\Controllers\MasterData\TaxController;
use App\Http\Controllers\MasterData\PaymentMethodController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\ProductController as InventoryProductController;
use App\Http\Controllers\Inventory\StockOpnameController;
use App\Http\Controllers\Purchasing\PurchaseRequestController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Sales\QuotationController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Finance\RevenueController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\ChartOfAccountController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\CRM\CustomerInteractionController;
use App\Http\Controllers\Setting\UserController;
use App\Http\Controllers\Setting\RoleController;
use App\Http\Controllers\Setting\PermissionController;
use App\Http\Controllers\Setting\ActivityLogController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'changePassword'])->name('password');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unreadCount');
    });

    Route::prefix('master-data')->name('master-data.')->middleware('permission:master-data')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('positions', PositionController::class);
        Route::resource('product-categories', ProductCategoryController::class);
        Route::resource('product-units', ProductUnitController::class);
        Route::resource('taxes', TaxController::class);
        Route::resource('payment-methods', PaymentMethodController::class);
        Route::resource('customers', CustomerController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('products', ProductController::class);
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('products/export/template', [ProductController::class, 'exportTemplate'])->name('products.exportTemplate');
        Route::get('products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export');
    });

    Route::prefix('inventory')->name('inventory.')->middleware('permission:inventory')->group(function () {
        Route::resource('warehouses', WarehouseController::class);
        Route::resource('stock-opnames', StockOpnameController::class);
        Route::post('stock-opnames/{id}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve');
        Route::resource('products', InventoryProductController::class);
        Route::get('stock-masuk', [InventoryProductController::class, 'stockIn'])->name('stock-in');
        Route::post('stock-masuk', [InventoryProductController::class, 'storeStockIn'])->name('stock-in.store');
        Route::get('stock-keluar', [InventoryProductController::class, 'stockOut'])->name('stock-out');
        Route::post('stock-keluar', [InventoryProductController::class, 'storeStockOut'])->name('stock-out.store');
        Route::get('mutasi-stok', [InventoryProductController::class, 'transfer'])->name('transfer');
        Route::post('mutasi-stok', [InventoryProductController::class, 'storeTransfer'])->name('transfer.store');
        Route::get('movements', [InventoryProductController::class, 'movements'])->name('movements');
    });

    Route::prefix('purchasing')->name('purchasing.')->middleware('permission:purchasing')->group(function () {
        Route::resource('purchase-requests', PurchaseRequestController::class);
        Route::post('purchase-requests/{id}/submit', [PurchaseRequestController::class, 'submit'])->name('purchase-requests.submit');
        Route::post('purchase-requests/{id}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
        Route::post('purchase-requests/{id}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject');
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::post('purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
        Route::post('purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('purchase-orders/{id}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
        Route::resource('goods-receipts', GoodsReceiptController::class);
    });

    Route::prefix('sales')->name('sales.')->middleware('permission:sales')->group(function () {
        Route::resource('quotations', QuotationController::class);
        Route::post('quotations/{id}/convert', [QuotationController::class, 'convertToSO'])->name('quotations.convert');
        Route::resource('sales-orders', SalesOrderController::class);
        Route::post('sales-orders/{id}/submit', [SalesOrderController::class, 'submit'])->name('sales-orders.submit');
        Route::post('sales-orders/{id}/approve', [SalesOrderController::class, 'approve'])->name('sales-orders.approve');
        Route::post('sales-orders/{id}/reject', [SalesOrderController::class, 'reject'])->name('sales-orders.reject');
        Route::resource('sales-invoices', SalesInvoiceController::class);
        Route::post('sales-invoices/{id}/payment', [SalesInvoiceController::class, 'addPayment'])->name('sales-invoices.payment');
    });

    Route::prefix('finance')->name('finance.')->middleware('permission:finance')->group(function () {
        Route::resource('revenues', RevenueController::class);
        Route::resource('expenses', ExpenseController::class);
        Route::resource('payments', PaymentController::class);
        Route::resource('chart-of-accounts', ChartOfAccountController::class);
    });

    Route::prefix('hr')->name('hr.')->middleware('permission:hr')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('employees/{id}/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.exportPdf');
    });

    Route::prefix('crm')->name('crm.')->middleware('permission:crm')->group(function () {
        Route::resource('interactions', CustomerInteractionController::class);
    });

    Route::prefix('settings')->name('settings.')->middleware('permission:settings')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');
        Route::resource('roles', RoleController::class);
        Route::post('roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');
        Route::resource('permissions', PermissionController::class);
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::prefix('laporan')->name('reports.')->middleware('permission:reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/penjualan', [ReportController::class, 'sales'])->name('sales');
        Route::get('/pembelian', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/stok', [ReportController::class, 'stock'])->name('stock');
        Route::get('/keuangan', [ReportController::class, 'finance'])->name('finance');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });
});
