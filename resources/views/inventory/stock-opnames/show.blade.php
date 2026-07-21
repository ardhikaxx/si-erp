@extends('layouts.app')

@section('title', 'Detail Stock Opname')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Inventory'],
    ['label' => 'Stock Opname', 'url' => route('inventory.stock-opnames.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Stock Opname</h5>
        <div>
            @if($stockOpname->status === 'draft')
            <a href="{{ route('inventory.stock-opnames.edit', $stockOpname->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <button type="button" class="btn btn-success btn-sm" id="btnComplete">
                <i class="fas fa-check me-1"></i>Selesaikan
            </button>
            @endif
            <a href="{{ route('inventory.stock-opnames.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode</label>
                <p class="mb-0 fw-medium">{{ $stockOpname->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Gudang</label>
                <p class="mb-0">{{ $stockOpname->warehouse->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tanggal</label>
                <p class="mb-0">{{ \Carbon\Carbon::parse($stockOpname->date)->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($stockOpname->status) {
                            'draft' => 'bg-secondary',
                            'completed' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-warning text-dark'
                        };
                        $label = match($stockOpname->status) {
                            'draft' => 'Draft',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($stockOpname->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            @if($stockOpname->notes)
            <div class="col-12">
                <label class="fw-semibold text-muted small">Keterangan</label>
                <p class="mb-0">{{ $stockOpname->notes }}</p>
            </div>
            @endif
        </div>

        <h6 class="fw-semibold mb-3">Item Stock Opname</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Stok Sistem</th>
                        <th>Stok Aktual</th>
                        <th>Selisih</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockOpname->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->system_qty }}</td>
                        <td>{{ $item->actual_qty }}</td>
                        <td>
                            @php $diff = ($item->actual_qty ?? 0) - ($item->system_qty ?? 0); @endphp
                            <span class="badge {{ $diff > 0 ? 'bg-success' : ($diff < 0 ? 'bg-danger' : 'bg-secondary') }}">
                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>{{ $item->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">Tidak ada item</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="completeForm" action="{{ route('inventory.stock-opnames.complete', $stockOpname->id) }}" method="POST" style="display:none">
    @csrf
</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnComplete').on('click', function () {
        Swal.fire({
            title: 'Selesaikan Stock Opname',
            text: 'Setelah diselesaikan, stok akan disesuaikan. Lanjutkan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Selesaikan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#completeForm').submit();
            }
        });
    });
});
</script>
@endpush
