@extends('layouts.app')
@section('title', 'Role & Permission')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Role & Permission'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Data Role</h5>
        <a href="{{ route('settings.roles.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Data</a>
    </div>
    <div class="card-body">
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Role</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Pengguna</th>
                    <th>Jumlah Permission</th>
                    <th>Tipe</th>
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
        ajax: '{{ route("settings.roles.index") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description', defaultContent: '-' },
            { data: 'users_count', name: 'users_count' },
            { data: 'permissions_count', name: 'permissions_count' },
            { data: 'is_system', name: 'is_system' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });
});

function deleteConfirm(id, name) {
    Swal.fire({
        title: 'Hapus Role?',
        text: "Yakin ingin menghapus role " + name + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("settings.roles.index") }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function() {
                    $('#dataTable').DataTable().ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Role berhasil dihapus.', timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal menghapus role.', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
                }
            });
        }
    });
}
</script>
@endpush
