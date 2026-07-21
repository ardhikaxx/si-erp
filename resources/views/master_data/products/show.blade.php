@extends('layouts.app')
@section('title', 'Detail Produk')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Produk', 'url' => route('master-data.products.index')],
    ['label' => 'Detail Produk'],
]])
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Detail Produk</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.products.edit', $product->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('master-data.products.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center mb-3">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Produk" class="img-thumbnail" style="max-height:150px">
                @else
                    <div class="border rounded p-4 text-center" style="height:150px;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-box fa-4x text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Kode Produk</label>
                        <p class="mb-0">{{ $product->code }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Nama Produk</label>
                        <p class="mb-0">{{ $product->name }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">SKU</label>
                        <p class="mb-0">{{ $product->sku ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Barcode</label>
                        <p class="mb-0">{{ $product->barcode ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Kategori</label>
                        <p class="mb-0">{{ $product->category->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Satuan</label>
                        <p class="mb-0">{{ $product->unit->name ?? '-' }} ({{ $product->unit->symbol ?? '' }})</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Tipe</label>
                        <p class="mb-0">{{ $product->type == 'service' ? 'Jasa' : 'Produk' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Merek</label>
                        <p class="mb-0">{{ $product->brand ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Harga Beli</label>
                        <p class="mb-0">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Harga Jual</label>
                        <p class="mb-0">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Stok Saat Ini</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $product->stock <= $product->min_stock ? 'danger' : 'success' }} fs-6">
                                {{ $product->stock }} {{ $product->unit->symbol ?? '' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Stok Minimal</label>
                        <p class="mb-0">{{ $product->min_stock ?? 0 }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Stok Maksimal</label>
                        <p class="mb-0">{{ $product->max_stock ?? 0 }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Berat</label>
                        <p class="mb-0">{{ $product->weight ? $product->weight . ' gram' : '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Pajak</label>
                        <p class="mb-0">{{ $product->tax->name ?? '-' }} {{ $product->tax ? '(' . $product->tax->rate . '%)' : '' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted small">Status</label>
                        <p class="mb-0">
                            @if($product->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Non-Aktif</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold text-muted small">Deskripsi</label>
                        <p class="mb-0">{{ $product->description ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Pergerakan Stok</h5></div>
    <div class="card-body">
        <table id="movementTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Referensi</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Saldo</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($m->type == 'in')
                            <span class="badge bg-success">Masuk</span>
                        @elseif($m->type == 'out')
                            <span class="badge bg-danger">Keluar</span>
                        @else
                            <span class="badge bg-info">Penyesuaian</span>
                        @endif
                    </td>
                    <td>{{ $m->reference ?? '-' }}</td>
                    <td class="text-success fw-bold">{{ $m->quantity_in ? number_format($m->quantity_in) : '-' }}</td>
                    <td class="text-danger fw-bold">{{ $m->quantity_out ? number_format($m->quantity_out) : '-' }}</td>
                    <td class="fw-bold">{{ number_format($m->balance) }}</td>
                    <td>{{ $m->description ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                        <span class="text-muted">Belum ada pergerakan stok</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    $('#movementTable').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });
});
</script>
@endpush

