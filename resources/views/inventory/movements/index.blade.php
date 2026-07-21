@extends('layouts.app')

@section('title', 'Mutasi Stok')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Inventory'],
    ['label' => 'Mutasi Stok'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Produk</label>
                <select name="product_id" class="form-select">
                    <option value="">Semua Produk</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Gudang</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stok Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stok Keluar</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">Data Mutasi Stok</h5>
    </div>
    <div class="card-body">
        <table id="movementsTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Stok Sebelum</th>
                    <th>Stok Sesudah</th>
                    <th>Referensi</th>
                    <th>Pengguna</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mv)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($mv->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $mv->product->name ?? '-' }}</td>
                    <td>
                        @php
                            $typeBadge = match($mv->type) {
                                'in' => 'bg-success',
                                'out' => 'bg-danger',
                                'transfer' => 'bg-info',
                                'adjustment' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            $typeLabel = match($mv->type) {
                                'in' => 'Stok Masuk',
                                'out' => 'Stok Keluar',
                                'transfer' => 'Transfer',
                                'adjustment' => 'Penyesuaian',
                                default => $mv->type
                            };
                        @endphp
                        <span class="badge {{ $typeBadge }}">{{ $typeLabel }}</span>
                    </td>
                    <td class="fw-medium {{ $mv->quantity > 0 ? 'text-success' : ($mv->quantity < 0 ? 'text-danger' : '') }}">
                        {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}
                    </td>
                    <td>{{ $mv->stock_before }}</td>
                    <td>{{ $mv->stock_after }}</td>
                    <td>{{ $mv->reference_no ?? '-' }}</td>
                    <td>{{ $mv->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Belum ada data mutasi stok</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#movementsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: [7, 8] }
        ],
        order: [[1, 'desc']]
    });
});
</script>
@endpush
