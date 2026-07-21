@extends('layouts.app')
@section('title', $interaction->id ? 'Edit Interaksi' : 'Tambah Interaksi')
@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'CRM', 'url' => '#'],
    ['label' => 'Interaksi', 'url' => route('crm.interactions.index')],
    ['label' => $interaction->id ? 'Edit Interaksi' : 'Tambah Interaksi'],
]])
<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-handshake me-2"></i>{{ $interaction->id ? 'Edit Interaksi' : 'Tambah Interaksi' }}</h5></div>
    <div class="card-body">
        <form action="{{ $interaction->id ? route('crm.interactions.update', $interaction->id) : route('crm.interactions.store') }}" method="POST">
            @csrf
            @if($interaction->id) @method('PUT') @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id', $interaction->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="call" {{ old('type', $interaction->type) == 'call' ? 'selected' : '' }}>Telepon</option>
                        <option value="email" {{ old('type', $interaction->type) == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="meeting" {{ old('type', $interaction->type) == 'meeting' ? 'selected' : '' }}>Meeting</option>
                        <option value="visit" {{ old('type', $interaction->type) == 'visit' ? 'selected' : '' }}>Kunjungan</option>
                        <option value="note" {{ old('type', $interaction->type) == 'note' ? 'selected' : '' }}>Catatan</option>
                        <option value="other" {{ old('type', $interaction->type) == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Interaksi</label>
                    <input type="date" name="interaction_date" class="form-control @error('interaction_date') is-invalid @enderror" value="{{ old('interaction_date', $interaction->interaction_date ? $interaction->interaction_date->format('Y-m-d') : date('Y-m-d')) }}">
                    @error('interaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="done" {{ old('status', $interaction->status) == 'done' ? 'selected' : '' }}>Selesai</option>
                        <option value="planned" {{ old('status', $interaction->status) == 'planned' ? 'selected' : '' }}>Direncanakan</option>
                        <option value="cancelled" {{ old('status', $interaction->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $interaction->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('crm.interactions.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
