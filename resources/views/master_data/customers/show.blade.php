@extends('layouts.app')
@section('title', 'Detail Customer')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Customer', 'url' => route('master-data.customers.index')],
    ['label' => 'Detail Customer'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detail Customer</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.customers.edit', $customer->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('master-data.customers.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="customerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab"><i class="fas fa-info-circle me-1"></i>Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="quotations-tab" data-bs-toggle="tab" data-bs-target="#quotations" type="button" role="tab"><i class="fas fa-file-invoice me-1"></i>Quotations</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab"><i class="fas fa-shopping-cart me-1"></i>Orders</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab"><i class="fas fa-file-invoice-dollar me-1"></i>Invoices</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab"><i class="fas fa-money-bill me-1"></i>Payments</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="interactions-tab" data-bs-toggle="tab" data-bs-target="#interactions" type="button" role="tab"><i class="fas fa-handshake me-1"></i>Interaksi</button>
            </li>
        </ul>
        <div class="tab-content p-3">
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Kode Customer</label>
                        <p class="mb-0">{{ $customer->code }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Nama Customer</label>
                        <p class="mb-0">{{ $customer->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tipe</label>
                        <p class="mb-0">{{ $customer->type == 'company' ? 'Perusahaan' : 'Perorangan' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Telepon</label>
                        <p class="mb-0">{{ $customer->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Email</label>
                        <p class="mb-0">{{ $customer->email ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">NPWP</label>
                        <p class="mb-0">{{ $customer->npwp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Contact Person</label>
                        <p class="mb-0">{{ $customer->contact_person ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">WhatsApp</label>
                        <p class="mb-0">{{ $customer->whatsapp ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Website</label>
                        <p class="mb-0">{{ $customer->website ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Plafon Piutang</label>
                        <p class="mb-0">{{ $customer->credit_limit ? 'Rp ' . number_format($customer->credit_limit, 0, ',', '.') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Term Pembayaran</label>
                        <p class="mb-0">{{ $customer->payment_term ? $customer->payment_term . ' Hari' : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Status</label>
                        <p class="mb-0">
                            @if($customer->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Non-Aktif</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold text-muted small">Alamat</label>
                        <p class="mb-0">{{ $customer->address ?? '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-muted small">Provinsi</label>
                        <p class="mb-0">{{ $customer->province ?? '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-muted small">Kota</label>
                        <p class="mb-0">{{ $customer->city ?? '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-muted small">Kode Pos</label>
                        <p class="mb-0">{{ $customer->postal_code ?? '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="fw-bold text-muted small">Negara</label>
                        <p class="mb-0">{{ $customer->country ?? 'Indonesia' }}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold text-muted small">Keterangan</label>
                        <p class="mb-0">{{ $customer->notes ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="quotations" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Data quotations akan ditampilkan di sini</p>
                </div>
            </div>
            <div class="tab-pane fade" id="orders" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Data orders akan ditampilkan di sini</p>
                </div>
            </div>
            <div class="tab-pane fade" id="invoices" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Data invoices akan ditampilkan di sini</p>
                </div>
            </div>
            <div class="tab-pane fade" id="payments" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-money-bill fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Data payments akan ditampilkan di sini</p>
                </div>
            </div>
            <div class="tab-pane fade" id="interactions" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-handshake fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Data interaksi akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

