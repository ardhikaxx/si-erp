<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissions();
        $this->createCompanyAndBranches();
        $this->createMasterData();
        $this->createChartOfAccounts();
        $this->createUsers();
        $this->createCustomers();
        $this->createSuppliers();
        $this->createProducts();
        $this->createSettings();
    }

    private function createPermissions(): void
    {
        $groups = [
            'Dashboard' => ['dashboard.view'],
            'Master Data' => [
                'master-data.view', 'master-data.create', 'master-data.edit', 'master-data.delete',
                'companies.view', 'companies.create', 'companies.edit', 'companies.delete',
                'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
                'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
                'positions.view', 'positions.create', 'positions.edit', 'positions.delete',
                'product-categories.view', 'product-categories.create', 'product-categories.edit', 'product-categories.delete',
                'product-units.view', 'product-units.create', 'product-units.edit', 'product-units.delete',
                'taxes.view', 'taxes.create', 'taxes.edit', 'taxes.delete',
                'payment-methods.view', 'payment-methods.create', 'payment-methods.edit', 'payment-methods.delete',
                'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
                'products.view', 'products.create', 'products.edit', 'products.delete',
                'products.import', 'products.export',
            ],
            'Inventory' => [
                'inventory.view',
                'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
                'stock-opnames.view', 'stock-opnames.create', 'stock-opnames.edit', 'stock-opnames.delete', 'stock-opnames.approve',
                'stock-in.create', 'stock-out.create', 'transfer.create', 'movements.view',
            ],
            'Purchasing' => [
                'purchasing.view',
                'purchase-requests.view', 'purchase-requests.create', 'purchase-requests.edit', 'purchase-requests.delete',
                'purchase-requests.submit', 'purchase-requests.approve', 'purchase-requests.reject',
                'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.edit', 'purchase-orders.delete',
                'purchase-orders.submit', 'purchase-orders.approve', 'purchase-orders.reject',
                'goods-receipts.view', 'goods-receipts.create', 'goods-receipts.edit', 'goods-receipts.delete',
            ],
            'Sales' => [
                'sales.view',
                'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete', 'quotations.convert',
                'sales-orders.view', 'sales-orders.create', 'sales-orders.edit', 'sales-orders.delete',
                'sales-orders.approve', 'sales-orders.reject',
                'sales-invoices.view', 'sales-invoices.create', 'sales-invoices.edit', 'sales-invoices.delete',
                'sales-invoices.payment',
            ],
            'Finance' => [
                'finance.view',
                'chart-of-accounts.view', 'chart-of-accounts.create', 'chart-of-accounts.edit', 'chart-of-accounts.delete',
                'revenues.view', 'revenues.create', 'revenues.edit', 'revenues.delete',
                'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
                'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            ],
            'HR' => [
                'hr.view',
                'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
                'employees.export',
            ],
            'CRM' => [
                'crm.view',
                'interactions.view', 'interactions.create', 'interactions.edit', 'interactions.delete',
            ],
            'Reports' => [
                'reports.view',
                'reports.sales', 'reports.purchases', 'reports.stock', 'reports.finance',
                'reports.export',
            ],
            'Settings' => [
                'settings.view',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.permissions',
                'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
                'activity-logs.view',
                'settings.edit',
            ],
        ];

        foreach ($groups as $group => $permissions) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => 'web',
                    'group' => $group,
                ]);
            }
        }
    }

    private function createRoles(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'is_system' => true, 'description' => 'Akses penuh ke seluruh sistem'],
            ['name' => 'Admin', 'is_system' => true, 'description' => 'Mengelola operasional sistem dan pengguna'],
            ['name' => 'Manager', 'is_system' => false, 'description' => 'Melihat dashboard dan laporan lintas departemen'],
            ['name' => 'Finance', 'is_system' => false, 'description' => 'Mengelola transaksi keuangan dan laporan'],
            ['name' => 'Purchasing', 'is_system' => false, 'description' => 'Mengelola pembelian dan supplier'],
            ['name' => 'Warehouse', 'is_system' => false, 'description' => 'Mengelola produk, stok, dan gudang'],
            ['name' => 'Sales', 'is_system' => false, 'description' => 'Mengelola penjualan dan customer'],
            ['name' => 'HR', 'is_system' => false, 'description' => 'Mengelola data karyawan dan kepegawaian'],
            ['name' => 'Staff', 'is_system' => false, 'description' => 'Akses terbatas sesuai permission yang diberikan'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => 'web'],
                $role
            );
        }
    }

    private function assignPermissions(): void
    {
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

        $admin = Role::where('name', 'Admin')->first();
        $adminPerms = Permission::whereIn('group', ['Dashboard', 'Master Data', 'Settings'])->pluck('id');
        $admin->permissions()->sync($adminPerms);

        $manager = Role::where('name', 'Manager')->first();
        $managerPerms = Permission::whereIn('name', [
            'dashboard.view',
            'reports.view', 'reports.sales', 'reports.purchases', 'reports.stock', 'reports.finance', 'reports.export',
            'purchase-requests.view', 'purchase-requests.approve', 'purchase-requests.reject',
            'purchase-orders.view', 'purchase-orders.approve', 'purchase-orders.reject',
            'sales-orders.view', 'sales-orders.approve', 'sales-orders.reject',
            'employees.view',
        ])->pluck('id');
        $manager->permissions()->sync($managerPerms);

        $finance = Role::where('name', 'Finance')->first();
        $financePerms = Permission::whereIn('group', ['Dashboard', 'Finance'])->pluck('id');
        $finance->permissions()->sync($financePerms);

        $purchasing = Role::where('name', 'Purchasing')->first();
        $purchasingPerms = Permission::whereIn('group', ['Dashboard', 'Purchasing'])->pluck('id');
        $purchasingPerms = $purchasingPerms->merge(
            Permission::whereIn('name', ['suppliers.view', 'products.view'])->pluck('id')
        );
        $purchasing->permissions()->sync($purchasingPerms);

        $warehouse = Role::where('name', 'Warehouse')->first();
        $warehousePerms = Permission::whereIn('group', ['Dashboard', 'Inventory'])->pluck('id');
        $warehousePerms = $warehousePerms->merge(
            Permission::whereIn('name', ['products.view', 'products.create', 'products.edit'])->pluck('id')
        );
        $warehouse->permissions()->sync($warehousePerms);

        $sales = Role::where('name', 'Sales')->first();
        $salesPerms = Permission::whereIn('group', ['Dashboard', 'Sales', 'CRM'])->pluck('id');
        $salesPerms = $salesPerms->merge(
            Permission::whereIn('name', ['customers.view', 'customers.create', 'customers.edit', 'products.view'])->pluck('id')
        );
        $sales->permissions()->sync($salesPerms);

        $hr = Role::where('name', 'HR')->first();
        $hrPerms = Permission::whereIn('group', ['Dashboard', 'HR'])->pluck('id');
        $hr->permissions()->sync($hrPerms);

        $staff = Role::where('name', 'Staff')->first();
        $staffPerms = Permission::whereIn('name', [
            'dashboard.view',
        ])->pluck('id');
        $staff->permissions()->sync($staffPerms);
    }

    private function createCompanyAndBranches(): void
    {
        $company = Company::firstOrCreate(
            ['email' => 'info@sierp.co.id'],
            [
                'name' => 'PT. Solusi Integrasi ERP',
                'alias' => 'SI-ERP',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'info@sierp.co.id',
                'website' => 'https://sierp.co.id',
                'tax_id' => '01.234.567.8-999.000',
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'date_format' => 'd/m/Y',
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'HQ'],
            [
                'company_id' => $company->id,
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'hq@sierp.co.id',
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'BDG'],
            [
                'company_id' => $company->id,
                'name' => 'Cabang Bandung',
                'address' => 'Jl. Asia Afrika No. 45, Bandung',
                'phone' => '022-87654321',
                'email' => 'bdg@sierp.co.id',
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'SBY'],
            [
                'company_id' => $company->id,
                'name' => 'Cabang Surabaya',
                'address' => 'Jl. Tunjungan No. 78, Surabaya',
                'phone' => '031-56789012',
                'email' => 'sby@sierp.co.id',
                'is_active' => true,
            ]
        );
    }

    private function createMasterData(): void
    {
        $deptData = [
            ['name' => 'IT', 'code' => 'IT'],
            ['name' => 'Keuangan', 'code' => 'FIN'],
            ['name' => 'HRD', 'code' => 'HRD'],
            ['name' => 'Pemasaran', 'code' => 'MKT'],
            ['name' => 'Penjualan', 'code' => 'SALES'],
            ['name' => 'Pembelian', 'code' => 'PURCH'],
            ['name' => 'Gudang', 'code' => 'WH'],
            ['name' => 'Operasional', 'code' => 'OPS'],
        ];
        foreach ($deptData as $d) {
            Department::firstOrCreate(['code' => $d['code']], $d);
        }

        $positionData = [
            ['name' => 'Direktur Utama', 'code' => 'DIRUT', 'department_code' => 'OPS'],
            ['name' => 'Manager IT', 'code' => 'MGR-IT', 'department_code' => 'IT'],
            ['name' => 'Staff IT', 'code' => 'STF-IT', 'department_code' => 'IT'],
            ['name' => 'Manager Keuangan', 'code' => 'MGR-FIN', 'department_code' => 'FIN'],
            ['name' => 'Staff Keuangan', 'code' => 'STF-FIN', 'department_code' => 'FIN'],
            ['name' => 'Manager HRD', 'code' => 'MGR-HRD', 'department_code' => 'HRD'],
            ['name' => 'Staff HRD', 'code' => 'STF-HRD', 'department_code' => 'HRD'],
            ['name' => 'Manager Pemasaran', 'code' => 'MGR-MKT', 'department_code' => 'MKT'],
            ['name' => 'Manager Penjualan', 'code' => 'MGR-SALES', 'department_code' => 'SALES'],
            ['name' => 'Sales Executive', 'code' => 'SLS-EXEC', 'department_code' => 'SALES'],
            ['name' => 'Manager Pembelian', 'code' => 'MGR-PURCH', 'department_code' => 'PURCH'],
            ['name' => 'Staff Pembelian', 'code' => 'STF-PURCH', 'department_code' => 'PURCH'],
            ['name' => 'Kepala Gudang', 'code' => 'KABAG-WH', 'department_code' => 'WH'],
            ['name' => 'Staff Gudang', 'code' => 'STF-WH', 'department_code' => 'WH'],
        ];
        foreach ($positionData as $p) {
            $dept = Department::where('code', $p['department_code'])->first();
            Position::firstOrCreate(
                ['code' => $p['code']],
                ['name' => $p['name'], 'department_id' => $dept?->id, 'is_active' => true]
            );
        }

        $categories = [
            ['name' => 'Elektronik', 'code' => 'ELEC'],
            ['name' => 'Komputer & Aksesoris', 'code' => 'KOMP'],
            ['name' => 'Alat Tulis Kantor (ATK)', 'code' => 'ATK'],
            ['name' => 'Perlengkapan Kantor', 'code' => 'PRLK'],
            ['name' => 'Furniture', 'code' => 'FURN'],
            ['name' => 'Bahan Baku', 'code' => 'BAHAN'],
            ['name' => 'Jasa', 'code' => 'JASA'],
        ];
        foreach ($categories as $c) {
            ProductCategory::firstOrCreate(['code' => $c['code']], $c);
        }

        $units = [
            ['name' => 'Pieces', 'code' => 'PCS', 'symbol' => 'pcs'],
            ['name' => 'Unit', 'code' => 'UNIT', 'symbol' => 'unit'],
            ['name' => 'Box', 'code' => 'BOX', 'symbol' => 'box'],
            ['name' => 'Pack', 'code' => 'PAK', 'symbol' => 'pak'],
            ['name' => 'Liter', 'code' => 'LTR', 'symbol' => 'L'],
            ['name' => 'Kilogram', 'code' => 'KG', 'symbol' => 'kg'],
            ['name' => 'Meter', 'code' => 'MTR', 'symbol' => 'm'],
            ['name' => 'Lembar', 'code' => 'LBR', 'symbol' => 'lbr'],
        ];
        foreach ($units as $u) {
            ProductUnit::firstOrCreate(['code' => $u['code']], $u);
        }

        $taxes = [
            ['name' => 'PPN 11%', 'code' => 'PPN-11', 'rate' => 11.00],
            ['name' => 'PPN 12%', 'code' => 'PPN-12', 'rate' => 12.00],
            ['name' => 'Non Pajak', 'code' => 'NON-PAJAK', 'rate' => 0],
            ['name' => 'PPh 23', 'code' => 'PPH-23', 'rate' => 2.00],
        ];
        foreach ($taxes as $t) {
            Tax::firstOrCreate(['code' => $t['code']], $t);
        }

        $paymentMethods = [
            ['name' => 'Transfer Bank', 'code' => 'TRANSFER'],
            ['name' => 'Tunai', 'code' => 'CASH'],
            ['name' => 'Kartu Kredit', 'code' => 'CC'],
            ['name' => 'Kartu Debit', 'code' => 'DEBIT'],
            ['name' => 'Giro', 'code' => 'GIRO'],
            ['name' => 'Virtual Account', 'code' => 'VA'],
        ];
        foreach ($paymentMethods as $pm) {
            PaymentMethod::firstOrCreate(['code' => $pm['code']], $pm);
        }

        $whData = [
            ['name' => 'Gudang Pusat', 'code' => 'WH-PST'],
            ['name' => 'Gudang Bandung', 'code' => 'WH-BDG'],
            ['name' => 'Gudang Surabaya', 'code' => 'WH-SBY'],
        ];
        foreach ($whData as $w) {
            Warehouse::firstOrCreate(
                ['code' => $w['code']],
                ['name' => $w['name'], 'is_active' => true]
            );
        }
    }

    private function createChartOfAccounts(): void
    {
        $accounts = [
            ['code' => '1-0000', 'name' => 'ASET', 'type' => 'asset', 'category' => 'current_asset'],
            ['code' => '1-1000', 'name' => 'Kas & Bank', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-0000'],
            ['code' => '1-1100', 'name' => 'Kas Tunai', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-1000'],
            ['code' => '1-1200', 'name' => 'Bank BCA', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-1000'],
            ['code' => '1-1300', 'name' => 'Bank Mandiri', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-1000'],
            ['code' => '1-2000', 'name' => 'Piutang', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-0000'],
            ['code' => '1-2100', 'name' => 'Piutang Usaha', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-2000'],
            ['code' => '1-3000', 'name' => 'Persediaan', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-0000'],
            ['code' => '1-3100', 'name' => 'Persediaan Barang Dagang', 'type' => 'asset', 'category' => 'current_asset', 'parent_code' => '1-3000'],
            ['code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'liability', 'category' => 'current_liability'],
            ['code' => '2-1000', 'name' => 'Hutang Usaha', 'type' => 'liability', 'category' => 'current_liability', 'parent_code' => '2-0000'],
            ['code' => '2-2000', 'name' => 'Hutang Pajak', 'type' => 'liability', 'category' => 'current_liability', 'parent_code' => '2-0000'],
            ['code' => '3-0000', 'name' => 'EKUITAS', 'type' => 'equity', 'category' => 'equity'],
            ['code' => '3-1000', 'name' => 'Modal', 'type' => 'equity', 'category' => 'equity', 'parent_code' => '3-0000'],
            ['code' => '3-2000', 'name' => 'Laba Ditahan', 'type' => 'equity', 'category' => 'equity', 'parent_code' => '3-0000'],
            ['code' => '4-0000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'category' => 'operating_revenue'],
            ['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent_code' => '4-0000'],
            ['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent_code' => '4-0000'],
            ['code' => '5-0000', 'name' => 'BEBAN', 'type' => 'expense', 'category' => 'operating_expense'],
            ['code' => '5-1000', 'name' => 'Beban Gaji', 'type' => 'expense', 'category' => 'operating_expense', 'parent_code' => '5-0000'],
            ['code' => '5-2000', 'name' => 'Beban Sewa', 'type' => 'expense', 'category' => 'operating_expense', 'parent_code' => '5-0000'],
            ['code' => '5-3000', 'name' => 'Beban Listrik & Air', 'type' => 'expense', 'category' => 'operating_expense', 'parent_code' => '5-0000'],
            ['code' => '5-4000', 'name' => 'Beban Transportasi', 'type' => 'expense', 'category' => 'operating_expense', 'parent_code' => '5-0000'],
            ['code' => '5-5000', 'name' => 'Beban Operasional Lainnya', 'type' => 'expense', 'category' => 'operating_expense', 'parent_code' => '5-0000'],
            ['code' => '5-6000', 'name' => 'Beban Pemasaran', 'type' => 'expense', 'category' => 'operating_expense', 'parent_code' => '5-0000'],
        ];

        foreach ($accounts as $acc) {
            $parentId = null;
            if (isset($acc['parent_code'])) {
                $parent = ChartOfAccount::where('code', $acc['parent_code'])->first();
                $parentId = $parent?->id;
            }
            ChartOfAccount::firstOrCreate(
                ['code' => $acc['code']],
                [
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'category' => $acc['category'],
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]
            );
        }
    }

    private function createUsers(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $financeRole = Role::where('name', 'Finance')->first();
        $purchasingRole = Role::where('name', 'Purchasing')->first();
        $warehouseRole = Role::where('name', 'Warehouse')->first();
        $salesRole = Role::where('name', 'Sales')->first();
        $hrRole = Role::where('name', 'HR')->first();

        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@sierp.co.id', 'password' => 'password', 'role' => $superAdminRole],
            ['name' => 'Admin Sistem', 'email' => 'admin@sierp.co.id', 'password' => 'password', 'role' => $adminRole],
            ['name' => 'Budi Santoso', 'email' => 'manager@sierp.co.id', 'password' => 'password', 'role' => $managerRole],
            ['name' => 'Dewi Lestari', 'email' => 'finance@sierp.co.id', 'password' => 'password', 'role' => $financeRole],
            ['name' => 'Ahmad Rizky', 'email' => 'purchasing@sierp.co.id', 'password' => 'password', 'role' => $purchasingRole],
            ['name' => 'Rudi Hermawan', 'email' => 'warehouse@sierp.co.id', 'password' => 'password', 'role' => $warehouseRole],
            ['name' => 'Siti Nurhaliza', 'email' => 'sales@sierp.co.id', 'password' => 'password', 'role' => $salesRole],
            ['name' => 'Fitri Handayani', 'email' => 'hr@sierp.co.id', 'password' => 'password', 'role' => $hrRole],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if (!$user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }
    }

    private function createCustomers(): void
    {
        $customers = [
            ['code' => 'CUST-2026-00001', 'name' => 'PT. Maju Jaya Abadi', 'phone' => '021-1112223', 'email' => 'info@majujaya.co.id', 'address' => 'Jl. Gatot Subroto No. 55, Jakarta', 'type' => 'company', 'status' => 'active'],
            ['code' => 'CUST-2026-00002', 'name' => 'CV. Berkah Makmur', 'phone' => '022-3334445', 'email' => 'berkah@makmur.co.id', 'address' => 'Jl. Merdeka No. 23, Bandung', 'type' => 'company', 'status' => 'active'],
            ['code' => 'CUST-2026-00003', 'name' => 'PT. Sukses Selalu', 'phone' => '031-5556667', 'email' => 'info@sukses.co.id', 'address' => 'Jl. Raya Darmo No. 88, Surabaya', 'type' => 'company', 'status' => 'active'],
            ['code' => 'CUST-2026-00004', 'name' => 'Toko Elektronik Jaya', 'phone' => '021-7778889', 'email' => 'jaya@elektronik.com', 'address' => 'Jl. Mangga Dua No. 12, Jakarta', 'type' => 'company', 'status' => 'active'],
            ['code' => 'CUST-2026-00005', 'name' => 'Andi Pratama', 'phone' => '08123456789', 'email' => 'andi@email.com', 'address' => 'Jl. Anggrek No. 5, Jakarta', 'type' => 'individual', 'status' => 'active'],
            ['code' => 'CUST-2026-00006', 'name' => 'PT. Niaga Sejahtera', 'phone' => '024-8889990', 'email' => 'niaga@sejahtera.com', 'address' => 'Jl. Pemuda No. 34, Semarang', 'type' => 'company', 'status' => 'active'],
            ['code' => 'CUST-2026-00007', 'name' => 'CV. Terang Abadi', 'phone' => '061-2223334', 'email' => 'terang@abadi.com', 'address' => 'Jl. Sisingamangaraja No. 67, Medan', 'type' => 'company', 'status' => 'inactive'],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(['code' => $c['code']], $c);
        }
    }

    private function createSuppliers(): void
    {
        $suppliers = [
            ['code' => 'SUPP-2026-00001', 'name' => 'PT. Sumber Rezeki', 'phone' => '021-9998887', 'email' => 'sumber@rezeki.com', 'address' => 'Jl. Industri Raya No. 1, Jakarta', 'type' => 'company', 'status' => 'active'],
            ['code' => 'SUPP-2026-00002', 'name' => 'CV. Indo Teknologi', 'phone' => '022-7776665', 'email' => 'indo@teknologi.com', 'address' => 'Jl. Cihampelas No. 78, Bandung', 'type' => 'company', 'status' => 'active'],
            ['code' => 'SUPP-2026-00003', 'name' => 'PT. Bahan Baku Utama', 'phone' => '031-4443332', 'email' => 'bahan@baku.com', 'address' => 'Jl. Margorejo Indah No. 56, Surabaya', 'type' => 'company', 'status' => 'active'],
            ['code' => 'SUPP-2026-00004', 'name' => 'Distributor ATK Sejahtera', 'phone' => '021-2221110', 'email' => 'distributor@atk.com', 'address' => 'Jl. Gunung Sahari No. 90, Jakarta', 'type' => 'company', 'status' => 'active'],
            ['code' => 'SUPP-2026-00005', 'name' => 'PT. Furniture Indah', 'phone' => '024-5554443', 'email' => 'furniture@indah.com', 'address' => 'Jl. Majapahit No. 12, Semarang', 'type' => 'company', 'status' => 'active'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['code' => $s['code']], $s);
        }
    }

    private function createProducts(): void
    {
        $warehouse = Warehouse::first();
        $whId = $warehouse?->id;

        $products = [
            ['code' => 'PROD-2026-00001', 'name' => 'Laptop Dell Latitude 3420', 'sku' => 'LAP-DELL-3420', 'category_code' => 'KOMP', 'unit_code' => 'PCS', 'purchase_price' => 8500000, 'selling_price' => 10500000, 'stock_min' => 5, 'stock_max' => 50, 'current_stock' => 25, 'type' => 'product'],
            ['code' => 'PROD-2026-00002', 'name' => 'Monitor LG 24" IPS', 'sku' => 'MON-LG-24', 'category_code' => 'ELEC', 'unit_code' => 'PCS', 'purchase_price' => 1800000, 'selling_price' => 2500000, 'stock_min' => 10, 'stock_max' => 100, 'current_stock' => 45, 'type' => 'product'],
            ['code' => 'PROD-2026-00003', 'name' => 'Mouse Wireless Logitech M240', 'sku' => 'MOU-LOG-M240', 'category_code' => 'KOMP', 'unit_code' => 'PCS', 'purchase_price' => 150000, 'selling_price' => 250000, 'stock_min' => 20, 'stock_max' => 200, 'current_stock' => 150, 'type' => 'product'],
            ['code' => 'PROD-2026-00004', 'name' => 'Keyboard Mechanical Rexus', 'sku' => 'KEY-REXUS-MECH', 'category_code' => 'KOMP', 'unit_code' => 'PCS', 'purchase_price' => 350000, 'selling_price' => 550000, 'stock_min' => 10, 'stock_max' => 100, 'current_stock' => 3, 'type' => 'product'],
            ['code' => 'PROD-2026-00005', 'name' => 'Kertas HVS A4 70gr (1 Rim)', 'sku' => 'KRT-HVS-A4', 'category_code' => 'ATK', 'unit_code' => 'PAK', 'purchase_price' => 45000, 'selling_price' => 65000, 'stock_min' => 50, 'stock_max' => 500, 'current_stock' => 200, 'type' => 'product'],
            ['code' => 'PROD-2026-00006', 'name' => 'Tinta Printer Epson L3110', 'sku' => 'TNT-EPS-L3110', 'category_code' => 'KOMP', 'unit_code' => 'PCS', 'purchase_price' => 75000, 'selling_price' => 120000, 'stock_min' => 30, 'stock_max' => 200, 'current_stock' => 75, 'type' => 'product'],
            ['code' => 'PROD-2026-00007', 'name' => 'Printer Epson L3210', 'sku' => 'PRN-EPS-L3210', 'category_code' => 'KOMP', 'unit_code' => 'PCS', 'purchase_price' => 2100000, 'selling_price' => 2800000, 'stock_min' => 5, 'stock_max' => 30, 'current_stock' => 12, 'type' => 'product'],
            ['code' => 'PROD-2026-00008', 'name' => 'Meja Kantor Minimalis 120cm', 'sku' => 'MJ-KTR-120', 'category_code' => 'FURN', 'unit_code' => 'UNIT', 'purchase_price' => 750000, 'selling_price' => 1200000, 'stock_min' => 5, 'stock_max' => 30, 'current_stock' => 8, 'type' => 'product'],
            ['code' => 'PROD-2026-00009', 'name' => 'Kursi Kantor Ergonomic', 'sku' => 'KRS-ERGONOMIC', 'category_code' => 'FURN', 'unit_code' => 'UNIT', 'purchase_price' => 1200000, 'selling_price' => 1850000, 'stock_min' => 5, 'stock_max' => 30, 'current_stock' => 15, 'type' => 'product'],
            ['code' => 'PROD-2026-00010', 'name' => 'Lemari Arsip 4 Pintu', 'sku' => 'LMR-ARSIP-4', 'category_code' => 'FURN', 'unit_code' => 'UNIT', 'purchase_price' => 2500000, 'selling_price' => 3500000, 'stock_min' => 3, 'stock_max' => 15, 'current_stock' => 6, 'type' => 'product'],
            ['code' => 'PROD-2026-00011', 'name' => 'USB Flashdisk 32GB Sandisk', 'sku' => 'USB-SD-32', 'category_code' => 'KOMP', 'unit_code' => 'PCS', 'purchase_price' => 50000, 'selling_price' => 85000, 'stock_min' => 30, 'stock_max' => 300, 'current_stock' => 120, 'type' => 'product'],
            ['code' => 'PROD-2026-00012', 'name' => 'Stapler Max HD-10', 'sku' => 'STP-MAX-HD10', 'category_code' => 'ATK', 'unit_code' => 'PCS', 'purchase_price' => 25000, 'selling_price' => 45000, 'stock_min' => 20, 'stock_max' => 100, 'current_stock' => 50, 'type' => 'product'],
            ['code' => 'PROD-2026-00013', 'name' => 'AC Split 1 PK Daikin', 'sku' => 'AC-DAIKIN-1PK', 'category_code' => 'ELEC', 'unit_code' => 'UNIT', 'purchase_price' => 3500000, 'selling_price' => 4500000, 'stock_min' => 3, 'stock_max' => 20, 'current_stock' => 7, 'type' => 'product'],
            ['code' => 'PROD-2026-00014', 'name' => 'Kabel HDMI 3 Meter', 'sku' => 'KBL-HDMI-3M', 'category_code' => 'ELEC', 'unit_code' => 'PCS', 'purchase_price' => 35000, 'selling_price' => 65000, 'stock_min' => 20, 'stock_max' => 200, 'current_stock' => 2, 'type' => 'product'],
            ['code' => 'PROD-2026-00015', 'name' => 'Jasa Instalasi Jaringan', 'sku' => 'JS-NETWORK', 'category_code' => 'JASA', 'unit_code' => 'UNIT', 'purchase_price' => 500000, 'selling_price' => 1500000, 'stock_min' => 0, 'stock_max' => 0, 'current_stock' => 0, 'type' => 'service'],
        ];

        foreach ($products as $p) {
            $category = ProductCategory::where('code', $p['category_code'])->first();
            $unit = ProductUnit::where('code', $p['unit_code'])->first();
            unset($p['category_code'], $p['unit_code']);

            Product::firstOrCreate(
                ['code' => $p['code']],
                array_merge($p, [
                    'category_id' => $category?->id,
                    'unit_id' => $unit?->id,
                    'warehouse_id' => $whId,
                    'status' => 'active',
                ])
            );
        }
    }

    private function createSettings(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'SI-ERP', 'group' => 'general', 'description' => 'Nama aplikasi'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general', 'description' => 'Zona waktu'],
            ['key' => 'currency', 'value' => 'IDR', 'group' => 'general', 'description' => 'Mata uang'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'group' => 'general', 'description' => 'Format tanggal'],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}
