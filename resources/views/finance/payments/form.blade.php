@extends('layouts.app')
@section('title', $payment->id ? 'Edit Pembayaran' : 'Tambah Pembayaran')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Finance', 'url' => '#'],
    ['label' => 'Pembayaran', 'url' => route('finance.payments.index')],
    ['label' => $payment->id ? 'Edit Pembayaran' : 'Tambah Pembayaran'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-money-check me-2"></i>{{ $payment->id ? 'Edit Pembayaran' : 'Tambah Pembayaran' }}</h5></div>
    <div class="card-body">
        <form action="{{ $payment->id ? route('finance.payments.update', $payment->id) : route('finance.payments.store') }}" method="POST">
            @csrf
            @if($payment->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="incoming" {{ old('type', $payment->type) == 'incoming' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="outgoing" {{ old('type', $payment->type) == 'outgoing' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Akun <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('account_id', $payment->account_id) == $account->id ? 'selected' : '' }}>[{{ $account->code }}] {{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $payment->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $payment->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method_id" class="form-select @error('payment_method_id') is-invalid @enderror">
                        <option value="">-- Pilih Metode --</option>
                        @foreach($paymentMethods as $method)
                        <option value="{{ $method->id }}" {{ old('payment_method_id', $payment->payment_method_id) == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_method_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $payment->amount) }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', $payment->payment_date ? $payment->payment_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Referensi</label>
                    <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $payment->reference) }}" placeholder="Nomor referensi">
                    @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe Referensi</label>
                    <select name="reference_type" class="form-select @error('reference_type') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="invoice" {{ old('reference_type', $payment->reference_type) == 'invoice' ? 'selected' : '' }}>Invoice</option>
                        <option value="sales_order" {{ old('reference_type', $payment->reference_type) == 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                        <option value="purchase_order" {{ old('reference_type', $payment->reference_type) == 'purchase_order' ? 'selected' : '' }}>Purchase Order</option>
                        <option value="other" {{ old('reference_type', $payment->reference_type) == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('reference_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $payment->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('finance.payments.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
