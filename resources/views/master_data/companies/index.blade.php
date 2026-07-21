@extends('layouts.app')
@section('title', 'Perusahaan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Perusahaan'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Data Perusahaan</h5>
        <a href="{{ route('master-data.companies.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Data</a>
    </div>
    <div class="card-body">
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Alias</th>
                    <th>Telepon</th>
                    <th>Email</th>
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
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("master-data.companies.index") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'alias', name: 'alias' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[1, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});
</script>
@endpush

