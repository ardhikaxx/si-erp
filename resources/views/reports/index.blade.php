@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Laporan'],
]])

<div class="page-header">
    <h4>Laporan</h4>
    <p class="text-muted mb-0">Pilih jenis laporan yang ingin Anda lihat</p>
</div>

<div class="row g-3">
    <div class="col-lg-3 col-md-6">
        <a href="{{ route('reports.sales') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="fas fa-chart-line text-primary fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Laporan Penjualan</h5>
                    <p class="text-muted small mb-0">Lihat ringkasan penjualan, pendapatan, dan tren penjualan.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('reports.purchases') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="fas fa-cart-shopping text-success fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Laporan Pembelian</h5>
                    <p class="text-muted small mb-0">Lihat ringkasan pembelian, biaya, dan tren pembelian.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('reports.stock') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="fas fa-boxes text-warning fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Laporan Stok</h5>
                    <p class="text-muted small mb-0">Pantau stok produk, barang menipis, dan kelebihan stok.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('reports.finance') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="fas fa-money-bill-wave text-info fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Laporan Keuangan</h5>
                    <p class="text-muted small mb-0">Bandingkan pemasukan dan pengeluaran serta laba/rugi.</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
