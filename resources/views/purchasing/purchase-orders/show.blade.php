@extends('layouts.app')

@section('title', 'Detail Purchase Order')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Purchase Order', 'url' => route('purchasing.purchase-orders.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Purchase Order</h5>
        <div class="d-flex gap-2">
            @if($purchaseOrder->status === 'draft')
            <button type="button" class="btn btn-warning btn-sm" id="btnSubmit">
                <i class="fas fa-paper-plane me-1"></i>Submit
            </button>
            @endif
            @if($purchaseOrder->status === 'approved' || $purchaseOrder->status === 'submitted')
            <a href="{{ route('purchasing.goods-receipts.create', ['po_id' => $purchaseOrder->id]) }}" class="btn btn-success btn-sm">
                <i class="fas fa-truck-loading me-1"></i>Terima Barang
            </a>
            @endif
            <a href="{{ route('purchasing.purchase-orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode PO</label>
                <p class="mb-0 fw-medium">{{ $purchaseOrder->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Supplier</label>
                <p class="mb-0">{{ $purchaseOrder->supplier->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Referensi PR</label>
                <p class="mb-0">{{ $purchaseOrder->purchaseRequest->code ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($purchaseOrder->status) {
                            'draft' => 'bg-secondary',
                            'submitted', 'pending_approval' => 'bg-warning text-dark',
                            'approved' => 'bg-info',
                            'ordered' => 'bg-primary',
                            'partially_received' => 'bg-warning text-dark',
                            'completed', 'received' => 'bg-success',
                            'rejected', 'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $label = match($purchaseOrder->status) {
                            'draft' => 'Draft',
                            'submitted' => 'Submitted',
                            'pending_approval' => 'Menunggu Approve',
                            'approved' => 'Disetujui',
                            'ordered' => 'Diproses',
                            'partially_received' => 'Diterima Sebagian',
                            'completed' => 'Selesai',
                            'received' => 'Diterima',
                            'rejected' => 'Ditolak',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($purchaseOrder->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tanggal PO</label>
                <p class="mb-0">{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tgl. Diharapkan</label>
                <p class="mb-0">{{ $purchaseOrder->expected_date ? \Carbon\Carbon::parse($purchaseOrder->expected_date)->format('d/m/Y') : '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Gudang</label>
                <p class="mb-0">{{ $purchaseOrder->warehouse->name ?? '-' }}</p>
            </div>
        </div>

        <h6 class="fw-semibold mb-3">Item Purchase Order</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Diterima</th>
                        <th>Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrder->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="fw-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        <td>{{ $item->received_qty ?? 0 }}</td>
                        <td>{{ ($item->quantity ?? 0) - ($item->received_qty ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-3 text-muted">Tidak ada item</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-semibold">Subtotal</td>
                        <td class="fw-medium" colspan="3">Rp {{ number_format($purchaseOrder->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($purchaseOrder->discount > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Diskon</td>
                        <td class="text-danger" colspan="3">- Rp {{ number_format($purchaseOrder->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($purchaseOrder->tax > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Pajak</td>
                        <td colspan="3">+ Rp {{ number_format($purchaseOrder->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total</td>
                        <td class="fw-bold" colspan="3">Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($purchaseOrder->notes)
        <div class="mt-3">
            <h6 class="fw-semibold">Catatan</h6>
            <p class="mb-0">{{ $purchaseOrder->notes }}</p>
        </div>
        @endif

        @if($purchaseOrder->goodsReceipts && $purchaseOrder->goodsReceipts->count() > 0)
        <h6 class="fw-semibold mt-4 mb-3">Riwayat Penerimaan Barang</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode GR</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->goodsReceipts as $gr)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-medium">{{ $gr->code }}</td>
                        <td>{{ \Carbon\Carbon::parse($gr->received_date)->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $grBadge = $gr->status === 'completed' ? 'bg-success' : ($gr->status === 'draft' ? 'bg-secondary' : 'bg-warning text-dark');
                                $grLabel = $gr->status === 'completed' ? 'Selesai' : ($gr->status === 'draft' ? 'Draft' : ucfirst($gr->status));
                            @endphp
                            <span class="badge {{ $grBadge }}">{{ $grLabel }}</span>
                        </td>
                        <td>
                            <a href="{{ route('purchasing.goods-receipts.show', $gr->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<form id="actionForm" method="POST" style="display:none">@csrf</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnSubmit').on('click', function () {
        Swal.fire({
            title: 'Submit PO',
            text: 'Submit purchase order untuk diapprove?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Submit',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#actionForm').attr('action', '{{ route('purchasing.purchase-orders.submit', $purchaseOrder->id) }}').submit();
            }
        });
    });
});
</script>
@endpush
