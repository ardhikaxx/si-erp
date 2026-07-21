@extends('layouts.app')
@section('title', $company->id ? 'Edit Perusahaan' : 'Tambah Perusahaan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Perusahaan', 'url' => route('master-data.companies.index')],
    ['label' => $company->id ? 'Edit Perusahaan' : 'Tambah Perusahaan'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-building me-2"></i>{{ $company->id ? 'Edit Perusahaan' : 'Tambah Perusahaan' }}</h5></div>
    <div class="card-body">
        <form action="{{ $company->id ? route('master-data.companies.update', $company->id) : route('master-data.companies.store') }}" method="POST">
            @csrf
            @if($company->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Alias</label>
                    <input type="text" name="alias" class="form-control @error('alias') is-invalid @enderror" value="{{ old('alias', $company->alias) }}">
                    @error('alias')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $company->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $company->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $company->website) }}">
                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NPWP</label>
                    <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror" value="{{ old('npwp', $company->npwp) }}">
                    @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $company->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $company->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $company->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($company->logo)
                    <div class="mt-2"><img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" height="60"></div>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('master-data.companies.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

