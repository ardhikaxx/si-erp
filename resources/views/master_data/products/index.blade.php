@extends('layouts.app')
@section('title', 'Produk')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Produk'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Data Produk</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.products.import') }}" class="btn btn-info btn-sm"><i class="fas fa-file-import"></i> Import</a>
            <a href="{{ route('master-data.products.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Data</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterCategory" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterType" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="product">Produk</option>
                    <option value="service">Jasa</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Non-Aktif</option>
                </select>
            </div>
        </div>
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>SKU</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("master-data.products.index") }}',
            data: function(d) {
                d.category = $('#filterCategory').val();
                d.type = $('#filterType').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'sku', name: 'sku' },
            { data: 'category', name: 'category' },
            { data: 'purchase_price', name: 'purchase_price' },
            { data: 'selling_price', name: 'selling_price' },
            { data: 'stock', name: 'stock' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#filterCategory, #filterType, #filterStatus').on('change', function() {
        table.draw();
    });
});
</script>
@endpush

