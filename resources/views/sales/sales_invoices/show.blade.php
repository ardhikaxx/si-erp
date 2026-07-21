@extends('layouts.app')

@section('title', 'Detail Invoice Penjualan')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Invoice Penjualan', 'url' => route('sales.sales-invoices.index')],
    ['label' => 'Detail'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Detail Invoice Penjualan</h5>
        <div class="d-flex gap-2">
            @if($salesInvoice->payment_status !== 'paid')
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-money-bill me-1"></i>Tambah Pembayaran
            </button>
            @endif
            @if($salesInvoice->payment_status !== 'paid' && $salesInvoice->status !== 'cancelled')
            <a href="{{ route('sales.sales-invoices.edit', $salesInvoice->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            @endif
            <a href="{{ route('sales.sales-invoices.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Kode Invoice</label>
                <p class="mb-0 fw-medium">{{ $salesInvoice->code }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Customer</label>
                <p class="mb-0">{{ $salesInvoice->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Ref. SO</label>
                <p class="mb-0">{{ $salesInvoice->salesOrder->code ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Ref. Quotation</label>
                <p class="mb-0">{{ $salesInvoice->quotation->code ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Tgl. Invoice</label>
                <p class="mb-0">{{ $salesInvoice->invoice_date?->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Jatuh Tempo</label>
                <p class="mb-0">{{ $salesInvoice->due_date?->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status</label>
                <p class="mb-0">
                    @php
                        $badge = match($salesInvoice->status) {
                            'draft' => 'bg-secondary',
                            'sent' => 'bg-info',
                            'confirmed' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $label = match($salesInvoice->status) {
                            'draft' => 'Draft',
                            'sent' => 'Terkirim',
                            'confirmed' => 'Dikonfirmasi',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($salesInvoice->status)
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $label }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Status Pembayaran</label>
                <p class="mb-0">
                    @php
                        $psBadge = match($salesInvoice->payment_status) {
                            'unpaid' => 'bg-danger',
                            'partially_paid' => 'bg-warning text-dark',
                            'paid' => 'bg-success',
                            'overdue' => 'bg-dark',
                            default => 'bg-secondary'
                        };
                        $psLabel = match($salesInvoice->payment_status) {
                            'unpaid' => 'Belum Dibayar',
                            'partially_paid' => 'Dibayar Sebagian',
                            'paid' => 'Lunas',
                            'overdue' => 'Jatuh Tempo',
                            default => ucfirst($salesInvoice->payment_status)
                        };
                    @endphp
                    <span class="badge {{ $psBadge }}">{{ $psLabel }}</span>
                </p>
            </div>
            <div class="col-md-3">
                <label class="fw-semibold text-muted small">Dibuat Oleh</label>
                <p class="mb-0">{{ $salesInvoice->creator->name ?? '-' }}</p>
            </div>
        </div>

        <h6 class="fw-semibold mb-3">Item Invoice</h6>
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
                    @forelse($salesInvoice->items as $item)
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
                        <td class="fw-medium">Rp {{ number_format($salesInvoice->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($salesInvoice->discount > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Diskon</td>
                        <td class="text-danger">- Rp {{ number_format($salesInvoice->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($salesInvoice->tax > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Pajak</td>
                        <td>+ Rp {{ number_format($salesInvoice->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($salesInvoice->shipping_cost > 0)
                    <tr>
                        <td colspan="6" class="text-end text-muted">Biaya Kirim</td>
                        <td>+ Rp {{ number_format($salesInvoice->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total</td>
                        <td class="fw-bold">Rp {{ number_format($salesInvoice->total, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-success">
                        <td colspan="6" class="text-end fw-bold">Total Dibayar</td>
                        <td class="fw-bold">Rp {{ number_format($salesInvoice->paid_amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end fw-bold text-danger">Sisa</td>
                        <td class="fw-bold text-danger">Rp {{ number_format(max(0, ($salesInvoice->total - ($salesInvoice->paid_amount ?? 0))), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($salesInvoice->notes)
        <div class="mt-3">
            <h6 class="fw-semibold">Catatan</h6>
            <p class="mb-0">{{ $salesInvoice->notes }}</p>
        </div>
        @endif

        <h6 class="fw-semibold mt-4 mb-3">Riwayat Pembayaran</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th>Referensi</th>
                        <th>Jumlah</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-medium">{{ $payment->code }}</td>
                        <td>{{ $payment->payment_date?->format('d/m/Y') }}</td>
                        <td>{{ $payment->paymentMethod->name ?? '-' }}</td>
                        <td>{{ $payment->reference ?? '-' }}</td>
                        <td class="fw-medium text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>{{ $payment->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted">Belum ada pembayaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($salesInvoice->payment_status !== 'paid')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('sales.sales-invoices.payment', $salesInvoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sisa Tagihan</label>
                        <input type="text" class="form-control" value="Rp {{ number_format(max(0, ($salesInvoice->total - ($salesInvoice->paid_amount ?? 0))), 0, ',', '.') }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pembayaran <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required min="0.01" step="any" max="{{ max(0, ($salesInvoice->total - ($salesInvoice->paid_amount ?? 0))) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method_id" class="form-select" required>
                            <option value="">-- Pilih Metode --</option>
                            @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referensi</label>
                        <input type="text" name="reference" class="form-control" placeholder="No. referensi pembayaran">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<form id="actionForm" method="POST" style="display:none">@csrf</form>
@endsection
