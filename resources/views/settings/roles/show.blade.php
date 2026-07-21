@extends('layouts.app')
@section('title', 'Detail Role')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Role', 'url' => route('settings.roles.index')],
    ['label' => 'Detail Role'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Detail Role: {{ $role->name }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('settings.roles.edit', $role->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('settings.roles.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Nama Role</label>
                <p class="mb-0">{{ $role->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Tipe</label>
                <p class="mb-0">
                    @if($role->is_system)
                        <span class="badge bg-danger">Sistem</span>
                    @else
                        <span class="badge bg-success">Kustom</span>
                    @endif
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="fw-bold text-muted small">Deskripsi</label>
                <p class="mb-0">{{ $role->description ?? '-' }}</p>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-key me-2"></i>Permission</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#permissionModal">
                <i class="fas fa-sync"></i> Update Permissions
            </button>
        </div>

        @foreach($permissions as $group => $items)
        <div class="card mb-3">
            <div class="card-header py-2">
                <h6 class="mb-0 text-uppercase small fw-bold">{{ $group ?? 'Lainnya' }}</h6>
            </div>
            <div class="card-body py-2">
                <div class="row">
                    @forelse($items as $perm)
                    <div class="col-md-3 mb-1">
                        <span class="badge bg-{{ $role->permissions->contains('id', $perm->id) ? 'success' : 'secondary' }} me-1">
                            {{ $perm->name }}
                        </span>
                    </div>
                    @empty
                    <div class="col-12"><small class="text-muted">Tidak ada permission</small></div>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('settings.roles.permissions', $role->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Update Permissions - {{ $role->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @foreach($permissions as $group => $items)
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input group-check" data-group="{{ $loop->index }}" id="groupCheck{{ $loop->index }}">
                                <label class="form-check-label fw-bold" for="groupCheck{{ $loop->index }}">{{ $group ?? 'Lainnya' }}</label>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                @foreach($items as $perm)
                                <div class="col-md-4 mb-1">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" class="form-check-input perm-check" value="{{ $perm->id }}" data-group="{{ $loop->parent->index }}" id="perm{{ $perm->id }}"
                                            {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm{{ $perm->id }}">{{ $perm->name }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    $('.group-check').on('change', function() {
        var group = $(this).data('group');
        $('.perm-check[data-group="' + group + '"]').prop('checked', $(this).is(':checked'));
    });

    $('.perm-check').on('change', function() {
        var group = $(this).data('group');
        var allChecked = $('.perm-check[data-group="' + group + '"]').length === $('.perm-check[data-group="' + group + '"]:checked').length;
        $('#groupCheck' + group).prop('checked', allChecked);
    });
});
</script>
@endpush
