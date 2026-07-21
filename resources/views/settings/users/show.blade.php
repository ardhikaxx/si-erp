@extends('layouts.app')
@section('title', 'Detail Pengguna')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Pengguna', 'url' => route('settings.users.index')],
    ['label' => 'Detail Pengguna'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detail Pengguna</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('settings.users.edit', $user->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('settings.users.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Nama</label>
                <p class="mb-0">{{ $user->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Email</label>
                <p class="mb-0">{{ $user->email }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Telepon</label>
                <p class="mb-0">{{ $user->phone ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Status</label>
                <p class="mb-0">
                    @if($user->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Nonaktif</span>
                    @endif
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="fw-bold text-muted small">Role</label>
                <p class="mb-0">
                    @foreach($user->roles as $role)
                    <span class="badge bg-info me-1">{{ $role->name }}</span>
                    @endforeach
                    @if($user->roles->isEmpty()) - @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
