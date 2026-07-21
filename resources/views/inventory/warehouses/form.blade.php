@extends('layouts.app')

@section('title', $warehouse ? 'Edit Gudang' : 'Tambah Gudang')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Inventory'],
    ['label' => 'Gudang', 'url' => route('inventory.warehouses.index')],
    ['label' => $warehouse ? 'Edit' : 'Tambah'],
]])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">{{ $warehouse ? 'Edit Gudang' : 'Tambah Gudang' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ $warehouse ? route('inventory.warehouses.update', $warehouse->id) : route('inventory.warehouses.store') }}" method="POST">
            @csrf
            @if($warehouse) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode Gudang <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $warehouse->code ?? '') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Gudang <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $warehouse->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cabang</label>
                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $warehouse->phone ?? '') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $warehouse->address ?? '') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $warehouse->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1"
                               {{ old('is_active', $warehouse->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Aktif</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ $warehouse ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
