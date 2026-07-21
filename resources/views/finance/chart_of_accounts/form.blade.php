@extends('layouts.app')
@section('title', $account->id ? 'Edit Akun' : 'Tambah Akun')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Chart of Account', 'url' => route('finance.chart-of-accounts.index')],
    ['label' => $account->id ? 'Edit Akun' : 'Tambah Akun'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-book me-2"></i>{{ $account->id ? 'Edit Akun' : 'Tambah Akun' }}</h5></div>
    <div class="card-body">
        <form action="{{ $account->id ? route('finance.chart-of-accounts.update', $account->id) : route('finance.chart-of-accounts.store') }}" method="POST">
            @csrf
            @if($account->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Akun</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $account->code) }}" placeholder="Kosongkan untuk auto-generate">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Biarkan kosong untuk generate otomatis (AC-YYYY-XXXXX)</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Akun <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="asset" {{ old('type', $account->type) == 'asset' ? 'selected' : '' }}>Aset</option>
                        <option value="liability" {{ old('type', $account->type) == 'liability' ? 'selected' : '' }}>Kewajiban</option>
                        <option value="equity" {{ old('type', $account->type) == 'equity' ? 'selected' : '' }}>Modal</option>
                        <option value="revenue" {{ old('type', $account->type) == 'revenue' ? 'selected' : '' }}>Pendapatan</option>
                        <option value="expense" {{ old('type', $account->type) == 'expense' ? 'selected' : '' }}>Beban</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $account->category) }}" placeholder="Contoh: Kas, Bank, Piutang">
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Akun Induk</label>
                    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">-- Tidak Ada (Akun Utama) --</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ old('parent_id', $account->parent_id) == $acc->id ? 'selected' : '' }}>
                            [{{ $acc->code }}] {{ $acc->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Saldo Awal</label>
                    <input type="number" step="0.01" min="0" name="balance" class="form-control @error('balance') is-invalid @enderror" value="{{ old('balance', $account->balance) }}">
                    @error('balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Aktif</label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $account->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('finance.chart-of-accounts.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
