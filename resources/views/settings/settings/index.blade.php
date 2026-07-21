@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Pengaturan', 'url' => '#'],
    ['label' => 'Pengaturan Sistem'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-gear me-2"></i>Pengaturan Sistem</h5></div>
    <div class="card-body">
        <form action="{{ route('settings.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Aplikasi</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                            <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $settings['app_name'] ?? 'SIERP') }}">
                            @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perusahaan</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ old('company_id', $settings['company_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-globe me-2"></i>Lokal & Regional</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zona Waktu <span class="text-danger">*</span></label>
                            <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                <option value="Asia/Jakarta" {{ old('timezone', $settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                                <option value="Asia/Makassar" {{ old('timezone', $settings['timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura" {{ old('timezone', $settings['timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                            </select>
                            @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mata Uang <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select @error('currency') is-invalid @enderror">
                                <option value="IDR" {{ old('currency', $settings['currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR - Rupiah</option>
                                <option value="USD" {{ old('currency', $settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD - Dollar</option>
                            </select>
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Format Tanggal <span class="text-danger">*</span></label>
                            <select name="date_format" class="form-select @error('date_format') is-invalid @enderror">
                                <option value="d/m/Y" {{ old('date_format', $settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (31/12/2024)</option>
                                <option value="Y-m-d" {{ old('date_format', $settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2024-12-31)</option>
                                <option value="m/d/Y" {{ old('date_format', $settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (12/31/2024)</option>
                                <option value="d F Y" {{ old('date_format', $settings['date_format'] ?? '') == 'd F Y' ? 'selected' : '' }}>DD Month YYYY (31 Desember 2024)</option>
                            </select>
                            @error('date_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
