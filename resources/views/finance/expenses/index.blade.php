@extends('layouts.app')
@section('title', 'Pengeluaran')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Pengeluaran'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Data Pengeluaran</h5>
        <a href="{{ route('finance.expenses.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Pengeluaran</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label form-label-sm">Tanggal Awal</label>
                <input type="date" id="filter_start_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm">Tanggal Akhir</label>
                <input type="date" id="filter_end_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm">Akun</label>
                <select id="filter_account_id" class="form-select form-select-sm">
                    <option value="">Semua Akun</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="btn_filter" class="btn btn-sm btn-primary me-2"><i class="fas fa-search"></i> Filter</button>
                <button id="btn_reset" class="btn btn-sm btn-secondary"><i class="fas fa-undo"></i> Reset</button>
            </div>
        </div>
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Supplier</th>
                    <th>Akun</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
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
    $.ajax({
        url: '{{ url("finance/chart-of-accounts") }}',
        data: { filter_type: 'expense' },
        dataType: 'json',
        success: function(resp) {
            if (resp.data) {
                var select = $('#filter_account_id');
                resp.data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.code + ' - ' + item.name + '</option>');
                });
            }
        }
    });

    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("finance.expenses.index") }}',
            data: function(d) {
                d.filter_start_date = $('#filter_start_date').val();
                d.filter_end_date = $('#filter_end_date').val();
                d.filter_account_id = $('#filter_account_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'supplier_id', name: 'supplier_id' },
            { data: 'account_id', name: 'account_id' },
            { data: 'amount', name: 'amount', className: 'text-end' },
            { data: 'expense_date', name: 'expense_date' },
            { data: 'description', name: 'description' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[5, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#btn_filter').on('click', function() { table.ajax.reload(); });
    $('#btn_reset').on('click', function() {
        $('#filter_start_date, #filter_end_date, #filter_account_id').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Pengeluaran',
            text: 'Apakah Anda yakin ingin menghapus pengeluaran "' + name + '"?',
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
                form.action = '{{ url("finance/expenses") }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
