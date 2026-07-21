@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Laporan', 'url' => route('reports.index')],
    ['label' => 'Keuangan'],
]])

<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h4>Laporan Keuangan</h4>
    </div>
    <a href="{{ route('reports.export', 'finance') }}?start_date={{ $startDate }}&end_date={{ $endDate }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small">Tanggal Awal</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-5">
                <label class="form-label small">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
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
                        <p class="text-muted small mb-1">Total Pemasukan</p>
                        <h4 class="fw-bold mb-0 text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-arrow-down text-success fs-4"></i>
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
                        <p class="text-muted small mb-1">Total Pengeluaran</p>
                        <h4 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-arrow-up text-danger fs-4"></i>
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
                        <p class="text-muted small mb-1">Selisih (Laba / Rugi)</p>
                        <h4 class="fw-bold mb-0 {{ $difference >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($difference, 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="{{ $difference >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 rounded-3 p-2">
                        <i class="fas {{ $difference >= 0 ? 'fa-chart-line text-success' : 'fa-chart-line text-danger' }} fs-4"></i>
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
                <h6 class="fw-semibold mb-0">Perbandingan Pemasukan & Pengeluaran Per Bulan</h6>
            </div>
            <div class="card-body">
                <canvas id="financeChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pt-3">
                <h6 class="fw-semibold mb-0">Ringkasan</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="text-muted small mb-1">Total Pemasukan</p>
                    <div class="progress" style="height: 10px;">
                        @php
                            $maxVal = max($totalRevenue, $totalExpense, 1);
                            $revPct = ($totalRevenue / $maxVal) * 100;
                            $expPct = ($totalExpense / $maxVal) * 100;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $revPct }}%">{{ $revPct >= 10 ? number_format($revPct, 0) . '%' : '' }}</div>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="text-muted small mb-1">Total Pengeluaran</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-danger" style="width: {{ $expPct }}%">{{ $expPct >= 10 ? number_format($expPct, 0) . '%' : '' }}</div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">Selisih</span>
                    <span class="fw-bold {{ $difference >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($difference, 0, ',', '.') }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted small">Status</span>
                    <span class="badge {{ $difference >= 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ $difference >= 0 ? 'Laba' : 'Rugi' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pt-3">
        <h6 class="fw-semibold mb-0">Data Transaksi Keuangan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="financeTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tipe</th>
                        <th>Kode</th>
                        <th>Akun</th>
                        <th>Tanggal</th>
                        <th class="text-end pe-3">Jumlah</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($revenues as $r)
                        <tr>
                            <td class="ps-3"><span class="badge bg-success">Pemasukan</span></td>
                            <td class="fw-medium">{{ $r->code }}</td>
                            <td>{{ $r->account?->name ?? '-' }}</td>
                            <td>{{ $r->revenue_date?->format('d/m/Y') }}</td>
                            <td class="text-end pe-3 text-success fw-bold">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                            <td>{{ $r->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                    @forelse ($expenses as $e)
                        <tr>
                            <td class="ps-3"><span class="badge bg-danger">Pengeluaran</span></td>
                            <td class="fw-medium">{{ $e->code }}</td>
                            <td>{{ $e->account?->name ?? '-' }}</td>
                            <td>{{ $e->expense_date?->format('d/m/Y') }}</td>
                            <td class="text-end pe-3 text-danger fw-bold">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                            <td>{{ $e->description ?? '-' }}</td>
                        </tr>
                    @empty
                        @if ($revenues->isEmpty() && $expenses->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada data keuangan</td>
                            </tr>
                        @endif
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
    $('#financeTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [[3, 'desc']]
    });
});

var ctx = document.getElementById('financeChart').getContext('2d');
var chartData = @json($chartData);
var hasData = chartData.revenues.some(function(v) { return v > 0; }) || chartData.expenses.some(function(v) { return v > 0; });

if (hasData) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Pemasukan',
                data: chartData.revenues,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.3,
                fill: true
            }, {
                label: 'Pengeluaran',
                data: chartData.expenses,
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
                legend: { position: 'top' }
            },
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
    document.getElementById('financeChart').parentElement.innerHTML =
        '<div class="text-center py-5"><i class="fas fa-chart-line text-muted fs-1 mb-2"></i><p class="text-muted mb-0">Belum ada data keuangan</p></div>';
}
</script>
@endpush
