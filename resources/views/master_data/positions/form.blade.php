@extends('layouts.app')
@section('title', $position->id ? 'Edit Jabatan' : 'Tambah Jabatan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Jabatan', 'url' => route('master-data.positions.index')],
    ['label' => $position->id ? 'Edit Jabatan' : 'Tambah Jabatan'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>{{ $position->id ? 'Edit Jabatan' : 'Tambah Jabatan' }}</h5></div>
    <div class="card-body">
        <form action="{{ $position->id ? route('master-data.positions.update', $position->id) : route('master-data.positions.store') }}" method="POST">
            @csrf
            @if($position->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Jabatan <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $position->code) }}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $position->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departemen <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ old('department_id', $position->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-select @error('level') is-invalid @enderror">
                        <option value="">-- Pilih Level --</option>
                        <option value="staff" {{ old('level', $position->level) == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="supervisor" {{ old('level', $position->level) == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="manager" {{ old('level', $position->level) == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="general_manager" {{ old('level', $position->level) == 'general_manager' ? 'selected' : '' }}>General Manager</option>
                        <option value="director" {{ old('level', $position->level) == 'director' ? 'selected' : '' }}>Director</option>
                    </select>
                    @error('level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $position->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $position->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $position->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('master-data.positions.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

