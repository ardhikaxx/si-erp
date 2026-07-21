@extends('layouts.app')
@section('title', 'Pembayaran')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Pembayaran'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-money-check me-2"></i>Data Pembayaran</h5>
        <a href="{{ route('finance.payments.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Pembayaran</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label form-label-sm">Tipe</label>
                <select id="filter_type" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="incoming">Pemasukan</option>
                    <option value="outgoing">Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm">Tanggal Awal</label>
                <input type="date" id="filter_start_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm">Tanggal Akhir</label>
                <input type="date" id="filter_end_date" class="form-control form-control-sm">
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
                    <th>Tipe</th>
                    <th>Pelanggan/Supplier</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Referensi</th>
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
            url: '{{ route("finance.payments.index") }}',
            data: function(d) {
                d.filter_type = $('#filter_type').val();
                d.filter_start_date = $('#filter_start_date').val();
                d.filter_end_date = $('#filter_end_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'type', name: 'type' },
            { data: 'party', name: 'party' },
            { data: 'payment_method_id', name: 'payment_method_id' },
            { data: 'amount', name: 'amount', className: 'text-end' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'reference', name: 'reference' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[6, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        columnDefs: [
            { render: function(data, type, row) {
                return data || '-';
            }, targets: [4, 7] }
        ]
    });

    $('#btn_filter').on('click', function() { table.ajax.reload(); });
    $('#btn_reset').on('click', function() {
        $('#filter_type, #filter_start_date, #filter_end_date').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        Swal.fire({
            title: 'Hapus Pembayaran',
            text: 'Apakah Anda yakin ingin menghapus pembayaran "' + name + '"?',
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
                form.action = '{{ url("finance/payments") }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
