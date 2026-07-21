@extends('layouts.app')
@section('title', 'Detail Perusahaan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Master Data', 'url' => '#'],
    ['label' => 'Perusahaan', 'url' => route('master-data.companies.index')],
    ['label' => 'Detail Perusahaan'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Detail Perusahaan</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.companies.edit', $company->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('master-data.companies.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Nama Perusahaan</label>
                <p class="mb-0">{{ $company->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Alias</label>
                <p class="mb-0">{{ $company->alias ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Telepon</label>
                <p class="mb-0">{{ $company->phone ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Email</label>
                <p class="mb-0">{{ $company->email ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Website</label>
                <p class="mb-0">{{ $company->website ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">NPWP</label>
                <p class="mb-0">{{ $company->npwp ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Status</label>
                <p class="mb-0">
                    @if($company->status == 'active')
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Non-Aktif</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted small">Logo</label>
                <p class="mb-0">
                    @if($company->logo)
                        <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" height="80">
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="fw-bold text-muted small">Alamat</label>
                <p class="mb-0">{{ $company->address ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

