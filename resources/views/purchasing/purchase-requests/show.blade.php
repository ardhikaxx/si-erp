@extends('layouts.app')

@section('title', 'Detail Purchase Request')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Purchase Request', 'url' => route('purchasing.purchase-requests.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Purchase Request</h5>
        <div class="d-flex gap-2">
            @if($purchaseRequest->status === 'draft')
            <button type="button" class="btn btn-warning btn-sm" id="btnSubmit">
                <i class="fas fa-paper-plane me-1"></i>Submit
            </button>
            @endif
            @if($purchaseRequest->status === 'submitted')
            <button type="button" class="btn btn-info btn-sm" id="btnApprove">
                <i class="fas fa-check me-1"></i>Approve
            </button>
            <button type="button" class="btn btn-danger btn-sm" id="btnReject">
                <i class="fas fa-times me-1"></i>Tolak
            </button>
            @endif
            <a href="{{ route('purchasing.purchase-requests.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode PR</label>
                <p class="mb-0 fw-medium">{{ $purchaseRequest->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Supplier</label>
                <p class="mb-0">{{ $purchaseRequest->supplier->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Departemen</label>
                <p class="mb-0">{{ $purchaseRequest->department->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($purchaseRequest->status) {
                            'draft' => 'bg-secondary',
                            'submitted', 'pending_approval' => 'bg-warning text-dark',
                            'approved' => 'bg-info',
                            'ordered' => 'bg-primary',
                            'partially_received' => 'bg-warning text-dark',
                            'completed', 'received' => 'bg-success',
                            'rejected', 'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $label = match($purchaseRequest->status) {
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
                            default => ucfirst($purchaseRequest->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tanggal Request</label>
                <p class="mb-0">{{ \Carbon\Carbon::parse($purchaseRequest->request_date)->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tgl. Diharapkan</label>
                <p class="mb-0">{{ $purchaseRequest->expected_date ? \Carbon\Carbon::parse($purchaseRequest->expected_date)->format('d/m/Y') : '-' }}</p>
            </div>
        </div>

        <h6 class="fw-semibold mb-3">Item Purchase Request</h6>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequest->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="fw-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted">Tidak ada item</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-semibold">Subtotal</td>
                        <td class="fw-medium">Rp {{ number_format($purchaseRequest->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($purchaseRequest->discount > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Diskon</td>
                        <td class="text-danger">- Rp {{ number_format($purchaseRequest->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($purchaseRequest->tax > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Pajak</td>
                        <td>+ Rp {{ number_format($purchaseRequest->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total</td>
                        <td class="fw-bold">Rp {{ number_format($purchaseRequest->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($purchaseRequest->notes)
        <div class="mt-3">
            <h6 class="fw-semibold">Catatan</h6>
            <p class="mb-0">{{ $purchaseRequest->notes }}</p>
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
            title: 'Submit PR',
            text: 'Submit purchase request untuk diapprove?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Submit',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#actionForm').attr('action', '{{ route('purchasing.purchase-requests.submit', $purchaseRequest->id) }}').submit();
            }
        });
    });

    $('#btnApprove').on('click', function () {
        Swal.fire({
            title: 'Approve PR',
            text: 'Setujui purchase request ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Approve',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#actionForm').attr('action', '{{ route('purchasing.purchase-requests.approve', $purchaseRequest->id) }}').submit();
            }
        });
    });

    $('#btnReject').on('click', function () {
        Swal.fire({
            title: 'Tolak PR',
            input: 'textarea',
            inputLabel: 'Alasan Penolakan',
            inputPlaceholder: 'Masukkan alasan...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',
            inputValidator: function (value) {
                if (!value) return 'Alasan penolakan harus diisi';
            }
        }).then(function (result) {
            if (result.isConfirmed) {
                var form = $('#actionForm');
                $('<input>').attr({type: 'hidden', name: 'rejection_reason', value: result.value}).appendTo(form);
                form.attr('action', '{{ route('purchasing.purchase-requests.reject', $purchaseRequest->id) }}').submit();
            }
        });
    });
});
</script>
@endpush
