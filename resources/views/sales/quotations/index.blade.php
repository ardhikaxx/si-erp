@extends('layouts.app')

@section('title', 'Quotation')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Quotation'],
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
                    <option value="accepted" {{ request('filter_status') == 'accepted' ? 'selected' : '' }}>Diterima</option>
                    <option value="rejected" {{ request('filter_status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="expired" {{ request('filter_status') == 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                    <option value="converted" {{ request('filter_status') == 'converted' ? 'selected' : '' }}>Dikonversi</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer</label>
                <select name="filter_customer" class="form-select">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('filter_customer') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('sales.quotations.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Data Quotation</h5>
        <a href="{{ route('sales.quotations.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat Quotation
        </a>
    </div>
    <div class="card-body">
        <table id="quotationTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Tanggal</th>
                    <th>Masa Berlaku</th>
                    <th>Status</th>
                    <th>Total</th>
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
    $('#quotationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sales.quotations.index') }}',
            data: function (d) {
                d.filter_status = '{{ request('filter_status') }}';
                d.filter_customer = '{{ request('filter_customer') }}';
            }
        },
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'quotation_date', name: 'quotation_date' },
            { data: 'valid_until', name: 'valid_until' },
            { data: 'status', name: 'status' },
            { data: 'total', name: 'total', className: 'text-end' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']]
    });

    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        var code = $(this).data('code');
        Swal.fire({
            title: 'Hapus Quotation',
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
                form.action = '{{ url('/sales/quotations') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
