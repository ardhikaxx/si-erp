@extends('layouts.app')

@section('title', 'Invoice Penjualan')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Invoice Penjualan'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="filter_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('filter_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('filter_status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="confirmed" {{ request('filter_status') == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="cancelled" {{ request('filter_status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Pembayaran</label>
                <select name="filter_payment_status" class="form-select">
                    <option value="">Semua Pembayaran</option>
                    <option value="unpaid" {{ request('filter_payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="partially_paid" {{ request('filter_payment_status') == 'partially_paid' ? 'selected' : '' }}>Dibayar Sebagian</option>
                    <option value="paid" {{ request('filter_payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="overdue" {{ request('filter_payment_status') == 'overdue' ? 'selected' : '' }}>Jatuh Tempo</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('sales.sales-invoices.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Data Invoice Penjualan</h5>
        <a href="{{ route('sales.sales-invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat Invoice
        </a>
    </div>
    <div class="card-body">
        <table id="invoiceTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Tgl. Invoice</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Status Bayar</th>
                    <th>Total</th>
                    <th>Dibayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#invoiceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sales.sales-invoices.index') }}',
            data: function (d) {
                d.filter_status = '{{ request('filter_status') }}';
                d.filter_payment_status = '{{ request('filter_payment_status') }}';
            }
        },
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'due_date', name: 'due_date' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'total', name: 'total', className: 'text-end' },
            { data: 'paid_amount', name: 'paid_amount', className: 'text-end' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']]
    });

    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        var code = $(this).data('code');
        Swal.fire({
            title: 'Hapus Invoice',
            text: 'Apakah Anda yakin ingin menghapus "' + code + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/sales/sales-invoices') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
