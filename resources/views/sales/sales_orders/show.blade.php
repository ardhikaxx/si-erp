@extends('layouts.app')

@section('title', 'Detail Sales Order')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Sales Order', 'url' => route('sales.sales-orders.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Sales Order</h5>
        <div class="d-flex gap-2">
            @if($salesOrder->status === 'draft')
            <button type="button" class="btn btn-warning btn-sm" id="btnSubmit">
                <i class="fas fa-paper-plane me-1"></i>Submit
            </button>
            <a href="{{ route('sales.sales-orders.edit', $salesOrder->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            @endif
            @if($salesOrder->status === 'submitted')
            <form action="{{ route('sales.sales-orders.approve', $salesOrder->id) }}" method="POST" style="display:inline" id="approveForm">
                @csrf
                <button type="button" class="btn btn-success btn-sm" id="btnApprove">
                    <i class="fas fa-check me-1"></i>Setujui
                </button>
            </form>
            <form action="{{ route('sales.sales-orders.reject', $salesOrder->id) }}" method="POST" style="display:inline" id="rejectForm">
                @csrf
                <button type="button" class="btn btn-danger btn-sm" id="btnReject">
                    <i class="fas fa-times me-1"></i>Tolak
                </button>
            </form>
            @endif
            @if(in_array($salesOrder->status, ['approved', 'processing']))
            <a href="{{ route('sales.sales-invoices.create', ['so_id' => $salesOrder->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-invoice me-1"></i>Buat Invoice
            </a>
            @endif
            <a href="{{ route('sales.sales-orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode SO</label>
                <p class="mb-0 fw-medium">{{ $salesOrder->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Customer</label>
                <p class="mb-0">{{ $salesOrder->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Ref. Quotation</label>
                <p class="mb-0">{{ $salesOrder->quotation->code ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($salesOrder->status) {
                            'draft' => 'bg-secondary',
                            'submitted' => 'bg-info',
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'processing' => 'bg-warning text-dark',
                            'completed' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $label = match($salesOrder->status) {
                            'draft' => 'Draft',
                            'submitted' => 'Submitted',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'processing' => 'Diproses',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($salesOrder->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tanggal Order</label>
                <p class="mb-0">{{ $salesOrder->order_date?->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tgl. Diharapkan</label>
                <p class="mb-0">{{ $salesOrder->expected_date?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Gudang</label>
                <p class="mb-0">{{ $salesOrder->warehouse->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Dibuat Oleh</label>
                <p class="mb-0">{{ $salesOrder->creator->name ?? '-' }}</p>
            </div>
            @if($salesOrder->approved_by)
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Disetujui Oleh</label>
                <p class="mb-0">{{ $salesOrder->approver->name ?? '-' }} ({{ $salesOrder->approved_at?->format('d/m/Y H:i') }})</p>
            </div>
            @endif
        </div>

        <h6 class="fw-semibold mb-3">Item Sales Order</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Diskon %</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesOrder->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->discount }}%</td>
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
                        <td class="fw-medium">Rp {{ number_format($salesOrder->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($salesOrder->discount > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Diskon</td>
                        <td class="text-danger">- Rp {{ number_format($salesOrder->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($salesOrder->tax > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Pajak</td>
                        <td>+ Rp {{ number_format($salesOrder->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($salesOrder->shipping_cost > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Biaya Kirim</td>
                        <td>+ Rp {{ number_format($salesOrder->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total</td>
                        <td class="fw-bold">Rp {{ number_format($salesOrder->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($salesOrder->notes)
        <div class="mt-3">
            <h6 class="fw-semibold">Catatan</h6>
            <p class="mb-0">{{ $salesOrder->notes }}</p>
        </div>
        @endif

        @if($salesOrder->invoices && $salesOrder->invoices->count() > 0)
        <h6 class="fw-semibold mt-4 mb-3">Riwayat Invoice</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Invoice</th>
                        <th>Tanggal</th>
                        <th>Status Pembayaran</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesOrder->invoices as $inv)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-medium">{{ $inv->code }}</td>
                        <td>{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $psLabel = match($inv->payment_status) {
                                    'unpaid' => 'Belum Dibayar',
                                    'partially_paid' => 'Dibayar Sebagian',
                                    'paid' => 'Lunas',
                                    'overdue' => 'Jatuh Tempo',
                                    default => ucfirst($inv->payment_status)
                                };
                                $psBadge = match($inv->payment_status) {
                                    'unpaid' => 'bg-danger',
                                    'partially_paid' => 'bg-warning text-dark',
                                    'paid' => 'bg-success',
                                    'overdue' => 'bg-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $psBadge }}">{{ $psLabel }}</span>
                        </td>
                        <td>Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('sales.sales-invoices.show', $inv->id) }}" class="btn btn-info btn-sm">
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
            title: 'Submit Sales Order?',
            text: 'Submit Sales Order untuk diapprove?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Submit',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#actionForm').attr('action', '{{ route('sales.sales-orders.submit', $salesOrder->id) }}').submit();
            }
        });
    });

    $('#btnApprove').on('click', function () {
        Swal.fire({
            title: 'Setujui Sales Order?',
            text: 'Sales Order ini akan disetujui',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#approveForm').submit();
            }
        });
    });

    $('#btnReject').on('click', function () {
        Swal.fire({
            title: 'Tolak Sales Order?',
            text: 'Sales Order ini akan ditolak',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Alasan Penolakan',
                    input: 'textarea',
                    inputPlaceholder: 'Masukkan alasan penolakan...',
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    cancelButtonText: 'Batal',
                    preConfirm: function (reason) {
                        $('#rejectForm').append('<input type="hidden" name="reason" value="' + reason + '">');
                        $('#rejectForm').submit();
                    }
                });
            }
        });
    });
});
</script>
@endpush
