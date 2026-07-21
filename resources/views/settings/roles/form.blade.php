@extends('layouts.app')
@section('title', $role->id ? 'Edit Role' : 'Tambah Role')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Role', 'url' => route('settings.roles.index')],
    ['label' => $role->id ? 'Edit Role' : 'Tambah Role'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>{{ $role->id ? 'Edit Role' : 'Tambah Role' }}</h5></div>
    <div class="card-body">
        <form action="{{ $role->id ? route('settings.roles.update', $role->id) : route('settings.roles.store') }}" method="POST">
            @csrf
            @if($role->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Role <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('settings.roles.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
