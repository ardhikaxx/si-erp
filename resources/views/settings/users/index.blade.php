@extends('layouts.app')
@section('title', 'Pengguna')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Pengguna'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Data Pengguna</h5>
        <a href="{{ route('settings.users.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Data</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterRole" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
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
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Terakhir Login</th>
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
            url: '{{ route("settings.users.index") }}',
            data: function(d) {
                d.filter_role = $('#filterRole').val();
                d.filter_status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'role', name: 'role' },
            { data: 'is_active', name: 'is_active' },
            { data: 'last_login_at', name: 'last_login_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#filterRole, #filterStatus').on('change', function() {
        table.draw();
    });
});

function deleteConfirm(id, name) {
    Swal.fire({
        title: 'Hapus Pengguna?',
        text: "Yakin ingin menghapus " + name + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("settings.users.index") }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function() {
                    $('#dataTable').DataTable().ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Pengguna berhasil dihapus.', timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal menghapus pengguna.', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
                }
            });
        }
    });
}

function toggleActive(id, name) {
    Swal.fire({
        title: 'Ubah Status?',
        text: "Yakin ingin mengubah status pengguna " + name + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("settings.users.toggleActive", "") }}/' + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    $('#dataTable').DataTable().ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Status pengguna berhasil diubah.', timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal mengubah status.', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
                }
            });
        }
    });
}
</script>
@endpush
