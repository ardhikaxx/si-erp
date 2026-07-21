@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Laporan', 'url' => route('reports.index')],
    ['label' => 'Penjualan'],
]])

<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h4>Laporan Penjualan</h4>
    </div>
    <a href="{{ route('reports.export', 'sales') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&customer_id={{ $customerId }}&status={{ $status }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Tanggal Awal</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">Semua Customer</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Total Penjualan</p>
                        <h4 class="fw-bold mb-0 text-primary">{{ number_format($summary->total_count, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-shopping-cart text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Total Pendapatan</p>
                        <h4 class="fw-bold mb-0 text-success">Rp {{ number_format($summary->total_revenue, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-money-bill-trend-up text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Rata-rata Pesanan</p>
                        <h4 class="fw-bold mb-0 text-info">Rp {{ number_format($summary->average_order, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-chart-simple text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-semibold mb-0">Penjualan Per Hari</h6>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-semibold mb-0">Status Penjualan</h6>
            </div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="statusChart" height="200" style="max-width: 220px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pt-3">
        <h6 class="fw-semibold mb-0">Data Penjualan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="salesTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Subtotal</th>
                        <th>Diskon</th>
                        <th>Pajak</th>
                        <th class="text-end pe-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $inv->code }}</td>
                            <td>{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                            <td>{{ $inv->customer?->name ?? '-' }}</td>
                            <td>Rp {{ number_format($inv->subtotal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($inv->discount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($inv->tax, 0, ',', '.') }}</td>
                            <td class="text-end pe-3 fw-bold">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data penjualan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#salesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [[1, 'desc']]
    });
});

var ctx = document.getElementById('salesChart').getContext('2d');
var chartLabels = @json(array_keys($chartData));
var chartValues = @json(array_values($chartData));
var hasData = chartValues.some(function(v) { return v > 0; });

if (hasData) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels.map(function(d) {
                var parts = d.split('-');
                return parts[2] + '/' + parts[1];
            }),
            datasets: [{
                label: 'Penjualan',
                data: chartValues,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); }
                    }
                }
            }
        }
    });
} else {
    document.getElementById('salesChart').parentElement.innerHTML =
        '<div class="text-center py-5"><i class="fas fa-chart-bar text-muted fs-1 mb-2"></i><p class="text-muted mb-0">Belum ada data penjualan</p></div>';
}

var statusCtx = document.getElementById('statusChart').getContext('2d');
var statusCounts = @json($invoices->groupBy('status')->map->count());
var statusLabels = [];
var statusData = [];
var statusColors = {
    'draft': '#6c757d',
    'confirmed': '#0d6efd',
    'completed': '#198754',
    'cancelled': '#dc3545'
};
var statusNames = {
    'draft': 'Draft',
    'confirmed': 'Dikonfirmasi',
    'completed': 'Selesai',
    'cancelled': 'Dibatalkan'
};

statusCounts.each(function(count, status) {
    statusLabels.push(statusNames[status] || status);
    statusData.push(count);
});

if (statusData.length > 0) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusLabels.map(function(l) {
                    return statusColors[Object.keys(statusNames).find(function(k) { return statusNames[k] === l; })] || '#6c757d';
                }),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 12 } } }
        }
    });
} else {
    document.getElementById('statusChart').parentElement.innerHTML =
        '<div class="text-center py-4"><p class="text-muted mb-0">Tidak ada data</p></div>';
}
</script>
@endpush
