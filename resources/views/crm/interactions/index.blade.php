@extends('layouts.app')
@section('title', 'Interaksi Customer')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'CRM', 'url' => '#'],
    ['label' => 'Interaksi'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Data Interaksi</h5>
        <a href="{{ route('crm.interactions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Data</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="filterCustomer" class="form-select form-select-sm">
                    <option value="">Semua Customer</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterType" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="call">Telepon</option>
                    <option value="email">Email</option>
                    <option value="meeting">Meeting</option>
                    <option value="visit">Kunjungan</option>
                    <option value="note">Catatan</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
        </div>
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Tipe</th>
                    <th>Deskripsi</th>
                    <th>Tanggal</th>
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
            url: '{{ route("crm.interactions.index") }}',
            data: function(d) {
                d.filter_customer = $('#filterCustomer').val();
                d.filter_type = $('#filterType').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'customer_id', name: 'customer_id' },
            { data: 'type', name: 'type' },
            { data: 'description', name: 'description' },
            { data: 'interaction_date', name: 'interaction_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[4, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#filterCustomer, #filterType').on('change', function() {
        table.draw();
    });
});

function deleteConfirm(id, name) {
    Swal.fire({
        title: 'Hapus Interaksi?',
        text: "Yakin ingin menghapus interaksi ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("crm.interactions.index") }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function() {
                    $('#dataTable').DataTable().ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Interaksi berhasil dihapus.', timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal menghapus interaksi.', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
                }
            });
        }
    });
}
</script>
@endpush
