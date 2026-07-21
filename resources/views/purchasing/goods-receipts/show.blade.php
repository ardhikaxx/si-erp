@extends('layouts.app')

@section('title', 'Detail Goods Receipt')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Goods Receipt', 'url' => route('purchasing.goods-receipts.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Goods Receipt</h5>
        <div>
            @if($goodsReceipt->status === 'draft')
            <button type="button" class="btn btn-success btn-sm" id="btnComplete">
                <i class="fas fa-check me-1"></i>Selesaikan
            </button>
            <a href="{{ route('purchasing.goods-receipts.edit', $goodsReceipt->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            @endif
            <a href="{{ route('purchasing.goods-receipts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode GR</label>
                <p class="mb-0 fw-medium">{{ $goodsReceipt->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Referensi PO</label>
                <p class="mb-0">{{ $goodsReceipt->purchaseOrder->code ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tanggal Terima</label>
                <p class="mb-0">{{ \Carbon\Carbon::parse($goodsReceipt->received_date)->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($goodsReceipt->status) {
                            'draft' => 'bg-secondary',
                            'completed', 'received' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-warning text-dark'
                        };
                        $label = match($goodsReceipt->status) {
                            'draft' => 'Draft',
                            'completed' => 'Selesai',
                            'received' => 'Diterima',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($goodsReceipt->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Supplier</label>
                <p class="mb-0">{{ $goodsReceipt->purchaseOrder->supplier->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Gudang</label>
                <p class="mb-0">{{ $goodsReceipt->warehouse->name ?? '-' }}</p>
            </div>
            @if($goodsReceipt->notes)
            <div class="col-12">
                <label class="fw-semibold text-muted small">Catatan</label>
                <p class="mb-0">{{ $goodsReceipt->notes }}</p>
            </div>
            @endif
        </div>

        <h6 class="fw-semibold mb-3">Item Penerimaan</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Qty PO</th>
                        <th>Qty Diterima</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goodsReceipt->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->poItem->quantity ?? 0 }}</td>
                        <td class="fw-medium text-success">{{ $item->quantity }}</td>
                        <td>{{ $item->poItem->unit ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">Tidak ada item</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="completeForm" action="{{ route('purchasing.goods-receipts.complete', $goodsReceipt->id) }}" method="POST" style="display:none">
    @csrf
</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnComplete').on('click', function () {
        Swal.fire({
            title: 'Selesaikan Goods Receipt',
            text: 'Setelah diselesaikan, stok akan ditambahkan ke gudang. Lanjutkan?',
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
