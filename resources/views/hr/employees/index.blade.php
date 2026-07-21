@extends('layouts.app')
@section('title', 'Karyawan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'HR', 'url' => '#'],
    ['label' => 'Karyawan'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Data Karyawan</h5>
        <a href="{{ route('hr.employees.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Data</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterDepartment" class="form-select form-select-sm">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                    <option value="resigned">Mengundurkan Diri</option>
                    <option value="terminated">PHK</option>
                </select>
            </div>
        </div>
        <table id="dataTable" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Telepon</th>
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
            url: '{{ route("hr.employees.index") }}',
            data: function(d) {
                d.filter_department = $('#filterDepartment').val();
                d.filter_status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'department_id', name: 'department_id' },
            { data: 'position_id', name: 'position_id' },
            { data: 'status', name: 'status' },
            { data: 'phone', name: 'phone' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        responsive: true,
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });

    $('#filterDepartment, #filterStatus').on('change', function() {
        table.draw();
    });
});

function deleteConfirm(id, name) {
    Swal.fire({
        title: 'Hapus Karyawan?',
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
                url: '{{ route("hr.employees.index") }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function() {
                    $('#dataTable').DataTable().ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Karyawan berhasil dihapus.', timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal menghapus karyawan.', timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
                }
            });
        }
    });
}
</script>
@endpush
