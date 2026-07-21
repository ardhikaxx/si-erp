@extends('layouts.app')

@section('title', 'Sales Order')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Sales Order'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="filter_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('filter_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('filter_status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="approved" {{ request('filter_status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('filter_status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="processing" {{ request('filter_status') == 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="completed" {{ request('filter_status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('filter_status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
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
                <a href="{{ route('sales.sales-orders.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Data Sales Order</h5>
        <a href="{{ route('sales.sales-orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat Sales Order
        </a>
    </div>
    <div class="card-body">
        <table id="soTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Tanggal</th>
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
    $('#soTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sales.sales-orders.index') }}',
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
            { data: 'order_date', name: 'order_date' },
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
            title: 'Hapus Sales Order',
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
                form.action = '{{ url('/sales/sales-orders') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
