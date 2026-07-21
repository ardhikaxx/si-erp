@extends('layouts.app')
@section('title', $revenue->id ? 'Edit Pemasukan' : 'Tambah Pemasukan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Pemasukan', 'url' => route('finance.revenues.index')],
    ['label' => $revenue->id ? 'Edit Pemasukan' : 'Tambah Pemasukan'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-money-bill-trend-up me-2"></i>{{ $revenue->id ? 'Edit Pemasukan' : 'Tambah Pemasukan' }}</h5></div>
    <div class="card-body">
        <form action="{{ $revenue->id ? route('finance.revenues.update', $revenue->id) : route('finance.revenues.store') }}" method="POST">
            @csrf
            @if($revenue->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $revenue->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Akun Pendapatan <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id', $revenue->account_id) == $account->id ? 'selected' : '' }}>[{{ $account->code }}] {{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $revenue->amount) }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="revenue_date" class="form-control @error('revenue_date') is-invalid @enderror" value="{{ old('revenue_date', $revenue->revenue_date ? $revenue->revenue_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    @error('revenue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe Referensi</label>
                    <input type="text" name="reference_type" class="form-control @error('reference_type') is-invalid @enderror" value="{{ old('reference_type', $revenue->reference_type) }}" placeholder="Contoh: invoice, sales_order">
                    @error('reference_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ID Referensi</label>
                    <input type="number" name="reference_id" class="form-control @error('reference_id') is-invalid @enderror" value="{{ old('reference_id', $revenue->reference_id) }}">
                    @error('reference_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $revenue->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('finance.revenues.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
