@extends('layouts.app')
@section('title', 'Chart of Account')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Chart of Account'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Data Chart of Account</h5>
        <a href="{{ route('finance.chart-of-accounts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Akun</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filter_type" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="asset">Aset</option>
                    <option value="liability">Kewajiban</option>
                    <option value="equity">Modal</option>
                    <option value="revenue">Pendapatan</option>
                    <option value="expense">Beban</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter_status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Akun</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Saldo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("finance.chart-of-accounts.index") }}',
            data: function(d) {
                d.filter_type = $('#filter_type').val();
                d.filter_status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'type', name: 'type' },
            { data: 'category', name: 'category' },
            { data: 'balance', name: 'balance', className: 'text-end' },
            { data: 'is_active', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#filter_type, #filter_status').on('change', function() {
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Akun',
            text: 'Apakah Anda yakin ingin menghapus akun "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("finance/chart-of-accounts") }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
