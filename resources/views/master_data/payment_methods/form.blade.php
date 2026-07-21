@extends('layouts.app')
@section('title', $paymentMethod->id ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Metode Pembayaran', 'url' => route('master-data.payment-methods.index')],
    ['label' => $paymentMethod->id ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>{{ $paymentMethod->id ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' }}</h5></div>
    <div class="card-body">
        <form action="{{ $paymentMethod->id ? route('master-data.payment-methods.update', $paymentMethod->id) : route('master-data.payment-methods.store') }}" method="POST">
            @csrf
            @if($paymentMethod->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $paymentMethod->code) }}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Metode <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $paymentMethod->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        <option value="cash" {{ old('type', $paymentMethod->type) == 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="bank_transfer" {{ old('type', $paymentMethod->type) == 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="credit_card" {{ old('type', $paymentMethod->type) == 'credit_card' ? 'selected' : '' }}>Kartu Kredit</option>
                        <option value="debit_card" {{ old('type', $paymentMethod->type) == 'debit_card' ? 'selected' : '' }}>Kartu Debit</option>
                        <option value="e_wallet" {{ old('type', $paymentMethod->type) == 'e_wallet' ? 'selected' : '' }}>E-Wallet</option>
                        <option value="other" {{ old('type', $paymentMethod->type) == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Akun Kas/Bank</label>
                    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" value="{{ old('account_code', $paymentMethod->account_code) }}">
                    @error('account_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $paymentMethod->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $paymentMethod->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $paymentMethod->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('master-data.payment-methods.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

