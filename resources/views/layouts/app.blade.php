<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIERP - Sistem Informasi ERP')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-color: #cbd5e1;
            --sidebar-active-bg: #0ea5e9;
            --sidebar-active-color: #fff;
            --sidebar-hover-bg: #334155;
            --navbar-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width) !important;
            background: var(--sidebar-bg);
            color: var(--sidebar-color);
            border: none;
        }

        .sidebar .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            height: var(--navbar-height);
        }

        .sidebar .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .sidebar .sidebar-brand i {
            font-size: 1.5rem;
            color: #0ea5e9;
        }

        .sidebar .sidebar-body {
            padding: 0.75rem 0;
            overflow-y: auto;
            height: calc(100vh - var(--navbar-height));
        }

        .sidebar .sidebar-body::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar .sidebar-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .sidebar .nav-item {
            list-style: none;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1.25rem;
            color: var(--sidebar-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .sidebar .nav-link:hover {
            background: var(--sidebar-hover-bg);
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-color);
        }

        .sidebar .nav-link i:first-child {
            width: 1.25rem;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar .nav-link .chevron {
            margin-left: auto;
            transition: transform 0.3s;
            font-size: 0.75rem;
        }

        .sidebar .nav-link[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }

        .sidebar .nav-section-title {
            padding: 0.75rem 1.25rem 0.25rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.35);
            font-weight: 600;
        }

        .sidebar .sub-menu {
            background: rgba(0, 0, 0, 0.15);
        }

        .sidebar .sub-menu .nav-link {
            padding-left: 3.25rem;
            font-size: 0.85rem;
            padding-top: 0.55rem;
            padding-bottom: 0.55rem;
        }

        .sidebar .sub-menu .nav-link.active {
            background: rgba(14, 165, 233, 0.2);
            color: #fff;
        }

        .sidebar .sub-menu .nav-link:hover {
            background: rgba(51, 65, 85, 0.6);
        }

        @media (min-width: 992px) {
            .sidebar {
                transform: none !important;
                visibility: visible !important;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1030;
            }

            .offcanvas-backdrop.show {
                opacity: 0;
                pointer-events: none;
            }

            .main-wrapper {
                margin-left: var(--sidebar-width);
                min-height: 100vh;
            }
        }

        @media (max-width: 991.98px) {
            .sidebar .sidebar-header {
                height: auto;
            }
        }

        .top-navbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .top-navbar .navbar-brand {
            font-weight: 700;
            color: #1e293b;
            text-decoration: none;
            display: none;
        }

        @media (max-width: 991.98px) {
            .top-navbar .navbar-brand {
                display: block;
            }
        }

        .top-navbar .sidebar-toggle {
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .top-navbar .sidebar-toggle:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .top-navbar .nav-link {
            color: #64748b;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            transition: background 0.2s;
        }

        .top-navbar .nav-link:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .top-navbar .dropdown-menu {
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            padding: 0.5rem;
        }

        .top-navbar .dropdown-item {
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .top-navbar .dropdown-item:hover {
            background: #f1f5f9;
        }

        .top-navbar .dropdown-item.text-danger:hover {
            background: #fef2f2;
        }

        .top-navbar .notification-btn {
            position: relative;
        }

        .top-navbar .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: #ef4444;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.125rem 0.375rem;
            border-radius: 50%;
            min-width: 1rem;
            height: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        .top-navbar .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0ea5e9;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .content-wrapper {
            padding: 1.5rem;
            min-height: calc(100vh - var(--navbar-height) - 52px);
        }

        .main-footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 0.85rem 1.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
            text-align: center;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h4 {
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
    </style>

    @stack('styles')
</head>
<body>
    @php
        $isActive = function ($patterns) {
            if (is_array($patterns)) {
                foreach ($patterns as $pattern) {
                    if (request()->is($pattern) || request()->routeIs($pattern)) {
                        return true;
                    }
                }
                return false;
            }
            return request()->is($patterns) || request()->routeIs($patterns);
        };

        $isMaster   = request()->is('master/*');
        $isInventory = request()->is('inventory/*');
        $isPurchasing = request()->is('purchasing/*');
        $isSales    = request()->is('sales/*');
        $isFinance  = request()->is('finance/*');
        $isHr       = request()->is('hr/*');
        $isCrm      = request()->is('crm/*');
        $isReports  = request()->is('reports/*') || request()->is('laporan/*');
        $isSettings = request()->is('settings/*') || request()->is('pengaturan/*');
    @endphp

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="sidebar" data-bs-scroll="true" data-bs-backdrop="true">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="fas fa-cubes"></i>
                <span>SIERP</span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="sidebar-body">
            <div class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-section-title">Master Data</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuMaster" role="button" aria-expanded="{{ $isMaster ? 'true' : 'false' }}">
                    <i class="fas fa-database"></i>
                    <span>Master Data</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isMaster ? 'show' : '' }}" id="menuMaster">
                    <div class="sub-menu">
                        <a href="{{ url('/master/perusahaan') }}" class="nav-link {{ $isActive(['master/perusahaan', 'master.perusahaan']) ? 'active' : '' }}">Perusahaan</a>
                        <a href="{{ url('/master/cabang') }}" class="nav-link {{ $isActive(['master/cabang', 'master.cabang']) ? 'active' : '' }}">Cabang</a>
                        <a href="{{ url('/master/departemen') }}" class="nav-link {{ $isActive(['master/departemen', 'master.departemen']) ? 'active' : '' }}">Departemen</a>
                        <a href="{{ url('/master/jabatan') }}" class="nav-link {{ $isActive(['master/jabatan', 'master.jabatan']) ? 'active' : '' }}">Jabatan</a>
                        <a href="{{ url('/master/kategori-produk') }}" class="nav-link {{ $isActive(['master/kategori-produk', 'master.kategori-produk']) ? 'active' : '' }}">Kategori Produk</a>
                        <a href="{{ url('/master/satuan') }}" class="nav-link {{ $isActive(['master/satuan', 'master.satuan']) ? 'active' : '' }}">Satuan</a>
                        <a href="{{ url('/master/produk') }}" class="nav-link {{ $isActive(['master/produk', 'master.produk']) ? 'active' : '' }}">Produk</a>
                        <a href="{{ url('/master/customer') }}" class="nav-link {{ $isActive(['master/customer', 'master.customer']) ? 'active' : '' }}">Customer</a>
                        <a href="{{ url('/master/supplier') }}" class="nav-link {{ $isActive(['master/supplier', 'master.supplier']) ? 'active' : '' }}">Supplier</a>
                        <a href="{{ url('/master/pajak') }}" class="nav-link {{ $isActive(['master/pajak', 'master.pajak']) ? 'active' : '' }}">Pajak</a>
                        <a href="{{ url('/master/metode-pembayaran') }}" class="nav-link {{ $isActive(['master/metode-pembayaran', 'master.metode-pembayaran']) ? 'active' : '' }}">Metode Pembayaran</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Inventory</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuInventory" role="button" aria-expanded="{{ $isInventory ? 'true' : 'false' }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isInventory ? 'show' : '' }}" id="menuInventory">
                    <div class="sub-menu">
                        <a href="{{ url('/inventory/produk') }}" class="nav-link {{ $isActive(['inventory/produk', 'inventory.produk']) ? 'active' : '' }}">Produk</a>
                        <a href="{{ url('/inventory/stok-masuk') }}" class="nav-link {{ $isActive(['inventory/stok-masuk', 'inventory.stok-masuk']) ? 'active' : '' }}">Stok Masuk</a>
                        <a href="{{ url('/inventory/stok-keluar') }}" class="nav-link {{ $isActive(['inventory/stok-keluar', 'inventory.stok-keluar']) ? 'active' : '' }}">Stok Keluar</a>
                        <a href="{{ url('/inventory/transfer-stok') }}" class="nav-link {{ $isActive(['inventory/transfer-stok', 'inventory.transfer-stok']) ? 'active' : '' }}">Transfer Stok</a>
                        <a href="{{ url('/inventory/stock-opname') }}" class="nav-link {{ $isActive(['inventory/stock-opname', 'inventory.stock-opname']) ? 'active' : '' }}">Stock Opname</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Purchasing</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuPurchasing" role="button" aria-expanded="{{ $isPurchasing ? 'true' : 'false' }}">
                    <i class="fas fa-cart-shopping"></i>
                    <span>Purchasing</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isPurchasing ? 'show' : '' }}" id="menuPurchasing">
                    <div class="sub-menu">
                        <a href="{{ url('/purchasing/purchase-request') }}" class="nav-link {{ $isActive(['purchasing/purchase-request', 'purchasing.purchase-request']) ? 'active' : '' }}">Purchase Request</a>
                        <a href="{{ url('/purchasing/purchase-order') }}" class="nav-link {{ $isActive(['purchasing/purchase-order', 'purchasing.purchase-order']) ? 'active' : '' }}">Purchase Order</a>
                        <a href="{{ url('/purchasing/goods-receipt') }}" class="nav-link {{ $isActive(['purchasing/goods-receipt', 'purchasing.goods-receipt']) ? 'active' : '' }}">Goods Receipt</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Sales</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuSales" role="button" aria-expanded="{{ $isSales ? 'true' : 'false' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Sales</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isSales ? 'show' : '' }}" id="menuSales">
                    <div class="sub-menu">
                        <a href="{{ url('/sales/quotation') }}" class="nav-link {{ $isActive(['sales/quotation', 'sales.quotation']) ? 'active' : '' }}">Quotation</a>
                        <a href="{{ url('/sales/sales-order') }}" class="nav-link {{ $isActive(['sales/sales-order', 'sales.sales-order']) ? 'active' : '' }}">Sales Order</a>
                        <a href="{{ url('/sales/invoice') }}" class="nav-link {{ $isActive(['sales/invoice', 'sales.invoice']) ? 'active' : '' }}">Invoice Penjualan</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Finance</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuFinance" role="button" aria-expanded="{{ $isFinance ? 'true' : 'false' }}">
                    <i class="fas fa-money-bill"></i>
                    <span>Finance</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isFinance ? 'show' : '' }}" id="menuFinance">
                    <div class="sub-menu">
                        <a href="{{ url('/finance/pemasukan') }}" class="nav-link {{ $isActive(['finance/pemasukan', 'finance.pemasukan']) ? 'active' : '' }}">Pemasukan</a>
                        <a href="{{ url('/finance/pengeluaran') }}" class="nav-link {{ $isActive(['finance/pengeluaran', 'finance.pengeluaran']) ? 'active' : '' }}">Pengeluaran</a>
                        <a href="{{ url('/finance/pembayaran') }}" class="nav-link {{ $isActive(['finance/pembayaran', 'finance.pembayaran']) ? 'active' : '' }}">Pembayaran</a>
                        <a href="{{ url('/finance/chart-of-account') }}" class="nav-link {{ $isActive(['finance/chart-of-account', 'finance.chart-of-account']) ? 'active' : '' }}">Chart of Account</a>
                        <a href="{{ url('/finance/laporan-keuangan') }}" class="nav-link {{ $isActive(['finance/laporan-keuangan', 'finance.laporan-keuangan']) ? 'active' : '' }}">Laporan Keuangan</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Human Resources</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuHr" role="button" aria-expanded="{{ $isHr ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i>
                    <span>HR</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isHr ? 'show' : '' }}" id="menuHr">
                    <div class="sub-menu">
                        <a href="{{ url('/hr/karyawan') }}" class="nav-link {{ $isActive(['hr/karyawan', 'hr.karyawan']) ? 'active' : '' }}">Karyawan</a>
                        <a href="{{ url('/hr/departemen') }}" class="nav-link {{ $isActive(['hr/departemen', 'hr.departemen']) ? 'active' : '' }}">Departemen</a>
                        <a href="{{ url('/hr/jabatan') }}" class="nav-link {{ $isActive(['hr/jabatan', 'hr.jabatan']) ? 'active' : '' }}">Jabatan</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">CRM</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuCrm" role="button" aria-expanded="{{ $isCrm ? 'true' : 'false' }}">
                    <i class="fas fa-handshake"></i>
                    <span>CRM</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isCrm ? 'show' : '' }}" id="menuCrm">
                    <div class="sub-menu">
                        <a href="{{ url('/crm/customer') }}" class="nav-link {{ $isActive(['crm/customer', 'crm.customer']) ? 'active' : '' }}">Customer</a>
                        <a href="{{ url('/crm/interaksi') }}" class="nav-link {{ $isActive(['crm/interaksi', 'crm.interaksi']) ? 'active' : '' }}">Interaksi</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Laporan</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuReports" role="button" aria-expanded="{{ $isReports ? 'true' : 'false' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isReports ? 'show' : '' }}" id="menuReports">
                    <div class="sub-menu">
                        <a href="{{ url('/laporan/penjualan') }}" class="nav-link {{ $isActive(['laporan/penjualan', 'reports.penjualan']) ? 'active' : '' }}">Laporan Penjualan</a>
                        <a href="{{ url('/laporan/pembelian') }}" class="nav-link {{ $isActive(['laporan/pembelian', 'reports.pembelian']) ? 'active' : '' }}">Laporan Pembelian</a>
                        <a href="{{ url('/laporan/stok') }}" class="nav-link {{ $isActive(['laporan/stok', 'reports.stok']) ? 'active' : '' }}">Laporan Stok</a>
                        <a href="{{ url('/laporan/keuangan') }}" class="nav-link {{ $isActive(['laporan/keuangan', 'reports.keuangan']) ? 'active' : '' }}">Laporan Keuangan</a>
                    </div>
                </div>
            </div>

            <div class="nav-section-title">Pengaturan</div>

            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuSettings" role="button" aria-expanded="{{ $isSettings ? 'true' : 'false' }}">
                    <i class="fas fa-gear"></i>
                    <span>Pengaturan</span>
                    <i class="fas fa-chevron-down chevron"></i>
                </a>
                <div class="collapse {{ $isSettings ? 'show' : '' }}" id="menuSettings">
                    <div class="sub-menu">
                        <a href="{{ url('/settings/pengguna') }}" class="nav-link {{ $isActive(['settings/pengguna', 'settings.pengguna']) ? 'active' : '' }}">Pengguna</a>
                        <a href="{{ url('/settings/role-permission') }}" class="nav-link {{ $isActive(['settings/role-permission', 'settings.role-permission']) ? 'active' : '' }}">Role & Permission</a>
                        <a href="{{ url('/settings/aktivitas') }}" class="nav-link {{ $isActive(['settings/aktivitas', 'settings.aktivitas']) ? 'active' : '' }}">Aktivitas</a>
                        <a href="{{ url('/settings/pengaturan-sistem') }}" class="nav-link {{ $isActive(['settings/pengaturan-sistem', 'settings.pengaturan-sistem']) ? 'active' : '' }}">Pengaturan Sistem</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <nav class="top-navbar">
            <div class="d-flex align-items-center gap-3 w-100">
                <button class="sidebar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <button class="sidebar-toggle d-none d-lg-inline-block" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                    <i class="fas fa-bars"></i>
                </button>

                <a href="{{ route('dashboard') }}" class="navbar-brand">
                    <i class="fas fa-cubes text-primary me-1"></i> SIERP
                </a>

                <div class="ms-auto d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn notification-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 320px;">
                            <div class="px-3 py-2 border-bottom">
                                <h6 class="mb-0 fw-bold">Notifikasi</h6>
                            </div>
                            <div class="text-center py-4 text-muted small">
                                <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                                Tidak ada notifikasi baru
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <div class="user-avatar">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="d-none d-md-inline small fw-medium">{{ auth()->user()->name ?? 'User' }}</span>
                            <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="px-3 py-2 border-bottom">
                                <p class="fw-bold mb-0 small">{{ auth()->user()->name ?? 'User' }}</p>
                                <p class="text-muted small mb-0">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <a class="dropdown-item" href="{{ url('/profile') }}">
                                <i class="fas fa-user"></i> Profil
                            </a>
                            <a class="dropdown-item" href="{{ url('/settings/pengaturan-sistem') }}">
                                <i class="fas fa-gear"></i> Pengaturan
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="content-wrapper">
            @yield('content')
        </div>

        <footer class="main-footer">
            &copy; {{ date('Y') }} <strong>SIERP</strong> - Sistem Informasi ERP. All rights reserved.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                timer: 5000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '{{ session('warning') }}',
                timer: 4000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if (session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: '{{ session('info') }}',
                timer: 4000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>

    @stack('scripts')
</body>
</html>
