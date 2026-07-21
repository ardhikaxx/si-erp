@extends('layouts.app')
@section('title', 'Aktivitas')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Aktivitas'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Data Aktivitas</h5></div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <select id="filterUser" class="form-select form-select-sm">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select id="filterModule" class="form-select form-select-sm">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select id="filterAction" class="form-select form-select-sm">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $a)
                    <option value="{{ $a }}">{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <input type="date" id="filterDateFrom" class="form-control form-control-sm" placeholder="Dari Tanggal">
            </div>
            <div class="col-md-2 mb-2">
                <input type="date" id="filterDateTo" class="form-control form-control-sm" placeholder="Sampai Tanggal">
            </div>
            <div class="col-md-1 mb-2">
                <button id="resetFilter" class="btn btn-sm btn-secondary w-100"><i class="fas fa-undo"></i></button>
            </div>
        </div>
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Modul</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
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
            url: '{{ route("settings.activity-logs") }}',
            data: function(d) {
                d.filter_user = $('#filterUser').val();
                d.filter_module = $('#filterModule').val();
                d.filter_action = $('#filterAction').val();
                d.filter_date_from = $('#filterDateFrom').val();
                d.filter_date_to = $('#filterDateTo').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'user_id', name: 'user_id' },
            { data: 'module', name: 'module' },
            { data: 'action', name: 'action' },
            { data: 'description', name: 'description' },
            { data: 'ip_address', name: 'ip_address', defaultContent: '-' }
        ],
        responsive: true,
        order: [[1, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#filterUser, #filterModule, #filterAction, #filterDateFrom, #filterDateTo').on('change', function() {
        table.draw();
    });

    $('#resetFilter').on('click', function() {
        $('#filterUser, #filterModule, #filterAction').val('');
        $('#filterDateFrom, #filterDateTo').val('');
        table.draw();
    });
});
</script>
@endpush
