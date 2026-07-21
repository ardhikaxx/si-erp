@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @include('components.breadcrumb', ['items' => [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Dashboard'],
    ]])

    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Pendapatan</p>
                            <h5 class="fw-bold mb-0 text-success">
                                Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                            </h5>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-3 p-2">
                            <i class="fas fa-money-bill-trend-up text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Pengeluaran</p>
                            <h5 class="fw-bold mb-0 text-danger">
                                Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
                            </h5>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-3 p-2">
                            <i class="fas fa-money-bill-wave text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Invoice</p>
                            <h5 class="fw-bold mb-0 text-primary">
                                {{ $totalInvoice ?? 0 }}
                                @if(($unpaidInvoices ?? 0) > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $unpaidInvoices }} Belum</span>
                                @endif
                            </h5>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                            <i class="fas fa-file-invoice text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Piutang</p>
                            <h5 class="fw-bold mb-0 text-warning">
                                Rp {{ number_format($totalReceivables ?? 0, 0, ',', '.') }}
                            </h5>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-2">
                            <i class="fas fa-hand-holding-dollar text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Customer</p>
                            <h5 class="fw-bold mb-0 text-purple" style="color: #6f42c1;">
                                {{ $totalCustomers ?? 0 }}
                            </h5>
                        </div>
                        <div class="bg-purple bg-opacity-10 rounded-3 p-2" style="background-color: rgba(111, 66, 193, 0.1);">
                            <i class="fas fa-users text-purple fs-4" style="color: #6f42c1;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Stok Menipis</p>
                            <h5 class="fw-bold mb-0 text-warning">
                                {{ $lowStockProducts ?? 0 }}
                            </h5>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-3 p-2">
                            <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">Pendapatan & Pengeluaran</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueExpenseChart" height="200"></canvas>
                </div>
                @if(empty($revenueData) && empty($expenseData))
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-line text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">Belum ada data pendapatan dan pengeluaran</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">Status Pembayaran</h6>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <canvas id="paymentStatusChart" height="200" style="max-width: 280px;"></canvas>
                </div>
                @if(empty($paymentStatus))
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-pie text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">Belum ada data status pembayaran</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">Penjualan Per Bulan</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="200"></canvas>
                </div>
                @if(empty($monthlySales))
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-bar text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">Belum ada data penjualan</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-semibold mb-0">Produk Terlaris</h6>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" height="200"></canvas>
                </div>
                @if(empty($topProducts))
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">Belum ada data produk terjual</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-semibold mb-0">Transaksi Terbaru</h6>
                </div>
                <div class="card-body p-0">
                    @if(!empty($recentTransactions) && count($recentTransactions) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tipe</th>
                                        <th>Nomor</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th class="text-end pe-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $trx)
                                        <tr>
                                            <td class="ps-3">
                                                @if($trx['type'] === 'sales')
                                                    <span class="badge bg-primary">Penjualan</span>
                                                @else
                                                    <span class="badge bg-secondary">Pembelian</span>
                                                @endif
                                            </td>
                                            <td class="fw-medium">{{ $trx['code'] }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($trx['status']) {
                                                        'selesai', 'completed', 'received' => 'bg-success',
                                                        'diproses', 'processing', 'approved' => 'bg-info',
                                                        'menunggu', 'pending' => 'bg-warning text-dark',
                                                        'dibatalkan', 'cancelled' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    $statusLabel = match($trx['status']) {
                                                        'completed' => 'Selesai',
                                                        'processing' => 'Diproses',
                                                        'pending' => 'Menunggu',
                                                        'cancelled' => 'Dibatalkan',
                                                        'received' => 'Diterima',
                                                        'approved' => 'Disetujui',
                                                        default => ucfirst($trx['status'])
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($trx['date'])->format('d/m/Y') }}</td>
                                            <td class="text-end pe-3 fw-medium">
                                                Rp {{ number_format($trx['total'] ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-0">Belum ada transaksi terbaru</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white pt-3">
                    <h6 class="fw-semibold mb-0">Aktivitas Terbaru</h6>
                </div>
                <div class="card-body p-0">
                    @if(!empty($recentActivities) && count($recentActivities) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Pengguna</th>
                                        <th>Modul</th>
                                        <th>Aksi</th>
                                        <th class="pe-3">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivities as $log)
                                        <tr>
                                            <td class="ps-3">{{ $log['user'] }}</td>
                                            <td>{{ $log['module'] }}</td>
                                            <td>{{ $log['action'] }}</td>
                                            <td class="pe-3 text-muted small">
                                                {{ \Carbon\Carbon::parse($log['time'])->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clock-rotate-left text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-0">Belum ada aktivitas terbaru</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chartLabels = @json($chartLabels);
    var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    var gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
    var textColor = isDark ? '#e0e0e0' : '#666';

    var revenueData = @json($revenueData);
    var expenseData = @json($expenseData);
    var hasRevenueExpense = revenueData.some(function(v) { return v > 0; }) || expenseData.some(function(v) { return v > 0; });
    if (hasRevenueExpense) {
        new Chart(document.getElementById('revenueExpenseChart'), {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Pendapatan',
                    data: revenueData,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Pengeluaran',
                    data: expenseData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: textColor }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    var paymentStatus = @json($paymentStatus ?? [0, 0, 0, 0]);
    var hasPaymentData = paymentStatus.some(function(v) { return v > 0; });
    if (hasPaymentData) {
        new Chart(document.getElementById('paymentStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum Dibayar', 'Terlambat', 'Dibayar Sebagian'],
                datasets: [{
                    data: paymentStatus,
                    backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0d6efd'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, padding: 12 }
                    }
                }
            }
        });
    }

    var monthlySales = @json($monthlySales ?? [0, 0, 0, 0, 0, 0]);
    var hasMonthlySales = monthlySales.some(function(v) { return v > 0; });
    if (hasMonthlySales) {
        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Penjualan',
                    data: monthlySales,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    var topProductLabels = @json($topProductLabels ?? []);
    var topProductData = @json($topProductData ?? []);
    var hasTopProducts = topProductLabels.length > 0;
    if (hasTopProducts) {
        new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topProductLabels,
                datasets: [{
                    label: 'Terjual',
                    data: topProductData,
                    backgroundColor: [
                        'rgba(111, 66, 193, 0.7)',
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        '#6f42c1', '#0d6efd', '#198754', '#ffc107', '#dc3545'
                    ],
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    }
                }
            }
        });
    }
});
</script>
@endpush
