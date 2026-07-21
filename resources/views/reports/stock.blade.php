@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Laporan', 'url' => route('reports.index')],
    ['label' => 'Stok'],
]])

<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h4>Laporan Stok</h4>
    </div>
    <a href="{{ route('reports.export', 'stock') }}?warehouse_id={{ $warehouseId }}&category_id={{ $categoryId }}" class="btn btn-success">
        <i class="fas fa-download me-1"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small">Gudang</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Semua Gudang</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" {{ $warehouseId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
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
                        <p class="text-muted small mb-1">Total Produk</p>
                        <h4 class="fw-bold mb-0 text-primary">{{ number_format($summary->total_products, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-box text-primary fs-4"></i>
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
                        <p class="text-muted small mb-1">Stok Menipis</p>
                        <h4 class="fw-bold mb-0 text-warning">{{ number_format($summary->low_stock_count, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-exclamation-triangle text-warning fs-4"></i>
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
                        <p class="text-muted small mb-1">Kelebihan Stok</p>
                        <h4 class="fw-bold mb-0 text-info">{{ number_format($summary->overstock_count, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-3 p-2">
                        <i class="fas fa-arrow-trend-up text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pt-3">
        <h6 class="fw-semibold mb-0">Data Stok Produk</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="stockTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th class="text-center">Stok Min</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Stok Max</th>
                        <th class="text-center pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        @php
                            $stockStatus = 'normal';
                            $rowClass = '';
                            if ($p->stock_min > 0 && $p->current_stock <= $p->stock_min) {
                                $stockStatus = 'menipis';
                                $rowClass = 'table-danger';
                            } elseif ($p->stock_min > 0 && $p->current_stock <= $p->stock_min * 2) {
                                $stockStatus = 'sedang';
                                $rowClass = 'table-warning';
                            } elseif ($p->stock_max > 0 && $p->current_stock >= $p->stock_max) {
                                $stockStatus = 'berlebih';
                                $rowClass = 'table-info';
                            } else {
                                $rowClass = 'table-success';
                            }

                            $statusBadge = match($stockStatus) {
                                'menipis' => '<span class="badge bg-danger">Menipis</span>',
                                'sedang' => '<span class="badge bg-warning text-dark">Sedang</span>',
                                'berlebih' => '<span class="badge bg-info text-dark">Berlebih</span>',
                                default => '<span class="badge bg-success">Normal</span>',
                            };
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="ps-3">{{ $p->code }}</td>
                            <td class="fw-medium">{{ $p->name }}</td>
                            <td>{{ $p->category?->name ?? '-' }}</td>
                            <td>{{ $p->warehouse?->name ?? '-' }}</td>
                            <td class="text-center">{{ $p->stock_min > 0 ? number_format($p->stock_min, 0, ',', '.') : '-' }}</td>
                            <td class="text-center fw-bold">{{ number_format($p->current_stock, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $p->stock_max > 0 ? number_format($p->stock_max, 0, ',', '.') : '-' }}</td>
                            <td class="text-center pe-3">{!! $statusBadge !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada data stok</td>
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
    $('#stockTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [[1, 'asc']]
    });
});
</script>
@endpush
