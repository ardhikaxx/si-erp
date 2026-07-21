@extends('layouts.app')
@section('title', $permission->id ? 'Edit Permission' : 'Tambah Permission')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Permission', 'url' => route('settings.permissions.index')],
    ['label' => $permission->id ? 'Edit Permission' : 'Tambah Permission'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-key me-2"></i>{{ $permission->id ? 'Edit Permission' : 'Tambah Permission' }}</h5></div>
    <div class="card-body">
        <form action="{{ $permission->id ? route('settings.permissions.update', $permission->id) : route('settings.permissions.store') }}" method="POST">
            @csrf
            @if($permission->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Permission <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $permission->name) }}" placeholder="contoh: create-users">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Grup</label>
                    <div class="input-group">
                        <input type="text" name="group" class="form-control @error('group') is-invalid @enderror" value="{{ old('group', $permission->group) }}" placeholder="contoh: users" list="groupList">
                        <datalist id="groupList">
                            @foreach($groups as $g)
                            <option value="{{ $g }}">
                            @endforeach
                        </datalist>
                        @error('group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="text-muted">Gunakan grup yang sudah ada atau ketik grup baru</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('settings.permissions.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
