@extends('layouts.app')
@section('title', 'Detail Supplier')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Supplier', 'url' => route('master-data.suppliers.index')],
    ['label' => 'Detail Supplier'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Detail Supplier</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.suppliers.edit', $supplier->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('master-data.suppliers.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Kode Supplier</label>
                <p class="mb-0">{{ $supplier->code }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Nama Supplier</label>
                <p class="mb-0">{{ $supplier->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Telepon</label>
                <p class="mb-0">{{ $supplier->phone ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Email</label>
                <p class="mb-0">{{ $supplier->email ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">NPWP</label>
                <p class="mb-0">{{ $supplier->npwp ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Contact Person</label>
                <p class="mb-0">{{ $supplier->contact_person ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Website</label>
                <p class="mb-0">{{ $supplier->website ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Term Pembayaran</label>
                <p class="mb-0">{{ $supplier->payment_term ? $supplier->payment_term . ' Hari' : '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Akun Hutang</label>
                <p class="mb-0">{{ $supplier->payable_account ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Bank</label>
                <p class="mb-0">{{ $supplier->bank_name ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Nomor Rekening</label>
                <p class="mb-0">{{ $supplier->bank_account ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Atas Nama Rekening</label>
                <p class="mb-0">{{ $supplier->bank_account_name ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Status</label>
                <p class="mb-0">
                    @if($supplier->status == 'active')
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Non-Aktif</span>
                    @endif
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="fw-bold text-muted small">Alamat</label>
                <p class="mb-0">{{ $supplier->address ?? '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold text-muted small">Provinsi</label>
                <p class="mb-0">{{ $supplier->province ?? '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold text-muted small">Kota</label>
                <p class="mb-0">{{ $supplier->city ?? '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold text-muted small">Kode Pos</label>
                <p class="mb-0">{{ $supplier->postal_code ?? '-' }}</p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="fw-bold text-muted small">Keterangan</label>
                <p class="mb-0">{{ $supplier->notes ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

