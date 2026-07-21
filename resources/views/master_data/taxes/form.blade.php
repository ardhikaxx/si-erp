@extends('layouts.app')
@section('title', $tax->id ? 'Edit Pajak' : 'Tambah Pajak')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Pajak', 'url' => route('master-data.taxes.index')],
    ['label' => $tax->id ? 'Edit Pajak' : 'Tambah Pajak'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-percent me-2"></i>{{ $tax->id ? 'Edit Pajak' : 'Tambah Pajak' }}</h5></div>
    <div class="card-body">
        <form action="{{ $tax->id ? route('master-data.taxes.update', $tax->id) : route('master-data.taxes.store') }}" method="POST">
            @csrf
            @if($tax->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Pajak <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $tax->code) }}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Pajak <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tax->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tarif (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', $tax->rate) }}">
                    @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe Pajak</label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        <option value="ppn" {{ old('type', $tax->type) == 'ppn' ? 'selected' : '' }}>PPN</option>
                        <option value="pph" {{ old('type', $tax->type) == 'pph' ? 'selected' : '' }}>PPh</option>
                        <option value="other" {{ old('type', $tax->type) == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Akun Pajak</label>
                    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" value="{{ old('account_code', $tax->account_code) }}">
                    @error('account_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $tax->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $tax->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $tax->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('master-data.taxes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

