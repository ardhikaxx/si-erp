@extends('layouts.app')
@section('title', $expense->id ? 'Edit Pengeluaran' : 'Tambah Pengeluaran')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Pengeluaran', 'url' => route('finance.expenses.index')],
    ['label' => $expense->id ? 'Edit Pengeluaran' : 'Tambah Pengeluaran'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>{{ $expense->id ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}</h5></div>
    <div class="card-body">
        <form action="{{ $expense->id ? route('finance.expenses.update', $expense->id) : route('finance.expenses.store') }}" method="POST">
            @csrf
            @if($expense->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $expense->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Akun Beban <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id', $expense->account_id) == $account->id ? 'selected' : '' }}>[{{ $account->code }}] {{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', $expense->expense_date ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $expense->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('finance.expenses.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
