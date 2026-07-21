@extends('layouts.app')

@section('title', 'Detail Quotation')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Quotation', 'url' => route('sales.quotations.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Quotation</h5>
        <div class="d-flex gap-2">
            @if($quotation->status === 'accepted' && !$quotation->salesOrder)
            <form action="{{ route('sales.quotations.convert', $quotation->id) }}" method="POST" style="display:inline" id="convertForm">
                @csrf
                <button type="button" class="btn btn-primary btn-sm" id="btnConvert">
                    <i class="fas fa-exchange-alt me-1"></i>Konversi ke SO
                </button>
            </form>
            @endif
            @if(in_array($quotation->status, ['draft', 'sent']))
            <a href="{{ route('sales.quotations.edit', $quotation->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            @endif
            <a href="{{ route('sales.quotations.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode Quotation</label>
                <p class="mb-0 fw-medium">{{ $quotation->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Customer</label>
                <p class="mb-0">{{ $quotation->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tanggal</label>
                <p class="mb-0">{{ $quotation->quotation_date?->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Masa Berlaku</label>
                <p class="mb-0">{{ $quotation->valid_until?->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($quotation->status) {
                            'draft' => 'bg-secondary',
                            'sent' => 'bg-info',
                            'accepted' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'expired' => 'bg-warning text-dark',
                            'converted' => 'bg-primary',
                            default => 'bg-secondary'
                        };
                        $label = match($quotation->status) {
                            'draft' => 'Draft',
                            'sent' => 'Terkirim',
                            'accepted' => 'Diterima',
                            'rejected' => 'Ditolak',
                            'expired' => 'Kedaluwarsa',
                            'converted' => 'Dikonversi',
                            default => ucfirst($quotation->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Dibuat Oleh</label>
                <p class="mb-0">{{ $quotation->creator->name ?? '-' }}</p>
            </div>
        </div>

        <h6 class="fw-semibold mb-3">Item Quotation</h6>
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
                    @forelse($quotation->items as $item)
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
                        <td class="fw-medium">Rp {{ number_format($quotation->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($quotation->discount > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Diskon</td>
                        <td class="text-danger">- Rp {{ number_format($quotation->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($quotation->tax > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Pajak</td>
                        <td>+ Rp {{ number_format($quotation->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($quotation->shipping_cost > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Biaya Kirim</td>
                        <td>+ Rp {{ number_format($quotation->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total</td>
                        <td class="fw-bold">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($quotation->notes)
        <div class="mt-3">
            <h6 class="fw-semibold">Catatan</h6>
            <p class="mb-0">{{ $quotation->notes }}</p>
        </div>
        @endif

        @if($quotation->terms_conditions)
        <div class="mt-3">
            <h6 class="fw-semibold">Syarat & Ketentuan</h6>
            <p class="mb-0">{{ $quotation->terms_conditions }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnConvert').on('click', function () {
        Swal.fire({
            title: 'Konversi ke Sales Order?',
            text: 'Quotation ini akan dikonversi menjadi Sales Order',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Konversi',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#convertForm').submit();
            }
        });
    });
});
</script>
@endpush
