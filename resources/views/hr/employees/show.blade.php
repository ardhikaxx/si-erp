@extends('layouts.app')
@section('title', 'Detail Karyawan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'HR', 'url' => '#'],
    ['label' => 'Karyawan', 'url' => route('hr.employees.index')],
    ['label' => 'Detail Karyawan'],
]])
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detail Karyawan</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('hr.employees.edit', $employee->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('hr.employees.exportPdf', $employee->id) }}" class="btn btn-secondary btn-sm" target="_blank"><i class="fas fa-print"></i> PDF</a>
            <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab"><i class="fas fa-info-circle me-1"></i>Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dokumen-tab" data-bs-toggle="tab" data-bs-target="#dokumen" type="button" role="tab"><i class="fas fa-file me-1"></i>Dokumen</button>
            </li>
        </ul>
        <div class="tab-content p-3">
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Kode Karyawan</label>
                        <p class="mb-0">{{ $employee->code }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Nama Lengkap</label>
                        <p class="mb-0">{{ $employee->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Email</label>
                        <p class="mb-0">{{ $employee->email ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Telepon</label>
                        <p class="mb-0">{{ $employee->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tempat Lahir</label>
                        <p class="mb-0">{{ $employee->place_of_birth ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tanggal Lahir</label>
                        <p class="mb-0">{{ $employee->date_of_birth ? $employee->date_of_birth->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Jenis Kelamin</label>
                        <p class="mb-0">{{ $employee->gender == 'L' ? 'Laki-laki' : ($employee->gender == 'P' ? 'Perempuan' : '-') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Agama</label>
                        <p class="mb-0">{{ $employee->religion ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Status Pernikahan</label>
                        <p class="mb-0">{{ $employee->marital_status ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">No. KTP</label>
                        <p class="mb-0">{{ $employee->id_number ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">NPWP</label>
                        <p class="mb-0">{{ $employee->tax_id ?? '-' }}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold text-muted small">Alamat</label>
                        <p class="mb-0">{{ $employee->address ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Departemen</label>
                        <p class="mb-0">{{ $employee->department?->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Jabatan</label>
                        <p class="mb-0">{{ $employee->position?->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Atasan</label>
                        <p class="mb-0">{{ $employee->supervisor?->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tipe Pekerjaan</label>
                        <p class="mb-0">{{ ucfirst($employee->employment_type ?? '-') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Status</label>
                        <p class="mb-0">
                            @switch($employee->status)
                                @case('active') <span class="badge bg-success">Aktif</span> @break
                                @case('inactive') <span class="badge bg-warning">Nonaktif</span> @break
                                @case('resigned') <span class="badge bg-danger">Mengundurkan Diri</span> @break
                                @case('terminated') <span class="badge bg-dark">PHK</span> @break
                                @default {{ $employee->status }}
                            @endswitch
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Gaji</label>
                        <p class="mb-0">{{ $employee->salary ? 'Rp ' . number_format($employee->salary, 0, ',', '.') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tanggal Masuk</label>
                        <p class="mb-0">{{ $employee->join_date ? $employee->join_date->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Tanggal Keluar</label>
                        <p class="mb-0">{{ $employee->exit_date ? $employee->exit_date->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">Nama Bank</label>
                        <p class="mb-0">{{ $employee->bank_name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted small">No. Rekening</label>
                        <p class="mb-0">{{ $employee->bank_account ?? '-' }}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="fw-bold text-muted small">Catatan</label>
                        <p class="mb-0">{{ $employee->notes ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="dokumen" role="tabpanel">
                <div class="text-center py-4">
                    <i class="fas fa-file fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Dokumen karyawan akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
