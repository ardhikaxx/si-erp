@extends('layouts.app')
@section('title', $employee->id ? 'Edit Karyawan' : 'Tambah Karyawan')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'HR', 'url' => '#'],
    ['label' => 'Karyawan', 'url' => route('hr.employees.index')],
    ['label' => $employee->id ? 'Edit Karyawan' : 'Tambah Karyawan'],
]])
<form action="{{ $employee->id ? route('hr.employees.update', $employee->id) : route('hr.employees.store') }}" method="POST">
    @csrf
    @if($employee->id) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Pribadi</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="place_of_birth" class="form-control @error('place_of_birth') is-invalid @enderror" value="{{ old('place_of_birth', $employee->place_of_birth) }}">
                    @error('place_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '') }}">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('gender', $employee->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $employee->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Agama</label>
                    <select name="religion" class="form-select @error('religion') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="Islam" {{ old('religion', $employee->religion) == 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ old('religion', $employee->religion) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ old('religion', $employee->religion) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('religion', $employee->religion) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('religion', $employee->religion) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ old('religion', $employee->religion) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        <option value="Lainnya" {{ old('religion', $employee->religion) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('religion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Pernikahan</label>
                    <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="Belum Menikah" {{ old('marital_status', $employee->marital_status) == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('marital_status', $employee->marital_status) == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="Cerai" {{ old('marital_status', $employee->marital_status) == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                    </select>
                    @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $employee->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. KTP</label>
                    <input type="text" name="id_number" class="form-control @error('id_number') is-invalid @enderror" value="{{ old('id_number', $employee->id_number) }}">
                    @error('id_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NPWP</label>
                    <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id', $employee->tax_id) }}">
                    @error('tax_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Informasi Pekerjaan</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Departemen</label>
                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ old('department_id', $employee->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jabatan</label>
                    <select name="position_id" class="form-select @error('position_id') is-invalid @enderror">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($positions as $p)
                        <option value="{{ $p->id }}" {{ old('position_id', $employee->position_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('position_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Atasan</label>
                    <select name="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror">
                        <option value="">-- Pilih Atasan --</option>
                        @foreach($supervisors as $s)
                        <option value="{{ $s->id }}" {{ old('supervisor_id', $employee->supervisor_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supervisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Karyawan <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="resigned" {{ old('status', $employee->status) == 'resigned' ? 'selected' : '' }}>Mengundurkan Diri</option>
                        <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>PHK</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe Pekerjaan</label>
                    <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="tetap" {{ old('employment_type', $employee->employment_type) == 'tetap' ? 'selected' : '' }}>Tetap</option>
                        <option value="kontrak" {{ old('employment_type', $employee->employment_type) == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                        <option value="magang" {{ old('employment_type', $employee->employment_type) == 'magang' ? 'selected' : '' }}>Magang</option>
                        <option value="harian" {{ old('employment_type', $employee->employment_type) == 'harian' ? 'selected' : '' }}>Harian</option>
                    </select>
                    @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gaji</label>
                    <input type="number" step="0.01" min="0" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', $employee->salary) }}">
                    @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date', $employee->join_date ? $employee->join_date->format('Y-m-d') : '') }}">
                    @error('join_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Keluar</label>
                    <input type="date" name="exit_date" class="form-control @error('exit_date') is-invalid @enderror" value="{{ old('exit_date', $employee->exit_date ? $employee->exit_date->format('Y-m-d') : '') }}">
                    @error('exit_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $employee->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-university me-2"></i>Informasi Bank</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Bank</label>
                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $employee->bank_name) }}">
                    @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Rekening</label>
                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account', $employee->bank_account) }}">
                    @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="{{ route('hr.employees.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</form>
@endsection
