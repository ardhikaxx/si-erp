@extends('layouts.app')
@section('title', $user->id ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Pengguna', 'url' => route('settings.users.index')],
    ['label' => $user->id ? 'Edit Pengguna' : 'Tambah Pengguna'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ $user->id ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h5></div>
    <div class="card-body">
        <form action="{{ $user->id ? route('settings.users.update', $user->id) : route('settings.users.store') }}" method="POST">
            @csrf
            @if($user->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password @if(!$user->id) <span class="text-danger">*</span> @endif</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($user->id) <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small> @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Aktif</label>
                    </div>
                    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <div class="row">
                    @foreach($roles as $r)
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="roles[]" class="form-check-input" value="{{ $r->id }}" id="role{{ $r->id }}"
                                {{ in_array($r->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role{{ $r->id }}">{{ $r->name }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('roles')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('settings.users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
