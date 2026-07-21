<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Karyawan - {{ $employee->name }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table td, table th { padding: 6px 8px; border: 1px solid #ddd; }
        table th { background: #f5f5f5; text-align: left; font-weight: 600; width: 30%; }
        .section-title { background: #1e293b; color: #fff; padding: 8px 10px; font-weight: 700; font-size: 13px; margin: 15px 0 10px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>DATA KARYAWAN</h2>
        <p>{{ $employee->code }}</p>
    </div>

    <div class="section-title">Informasi Pribadi</div>
    <table>
        <tr><th>Nama Lengkap</th><td>{{ $employee->name }}</td></tr>
        <tr><th>Email</th><td>{{ $employee->email ?? '-' }}</td></tr>
        <tr><th>Telepon</th><td>{{ $employee->phone ?? '-' }}</td></tr>
        <tr><th>Tempat / Tanggal Lahir</th><td>{{ $employee->place_of_birth ?? '-' }} / {{ $employee->date_of_birth ? $employee->date_of_birth->format('d/m/Y') : '-' }}</td></tr>
        <tr><th>Jenis Kelamin</th><td>{{ $employee->gender == 'L' ? 'Laki-laki' : ($employee->gender == 'P' ? 'Perempuan' : '-') }}</td></tr>
        <tr><th>Agama</th><td>{{ $employee->religion ?? '-' }}</td></tr>
        <tr><th>Status Pernikahan</th><td>{{ $employee->marital_status ?? '-' }}</td></tr>
        <tr><th>No. KTP</th><td>{{ $employee->id_number ?? '-' }}</td></tr>
        <tr><th>NPWP</th><td>{{ $employee->tax_id ?? '-' }}</td></tr>
        <tr><th>Alamat</th><td>{{ $employee->address ?? '-' }}</td></tr>
    </table>

    <div class="section-title">Informasi Pekerjaan</div>
    <table>
        <tr><th>Departemen</th><td>{{ $employee->department?->name ?? '-' }}</td></tr>
        <tr><th>Jabatan</th><td>{{ $employee->position?->name ?? '-' }}</td></tr>
        <tr><th>Atasan</th><td>{{ $employee->supervisor?->name ?? '-' }}</td></tr>
        <tr><th>Status</th><td>{{ ucfirst($employee->status) }}</td></tr>
        <tr><th>Tipe Pekerjaan</th><td>{{ ucfirst($employee->employment_type ?? '-') }}</td></tr>
        <tr><th>Gaji</th><td>{{ $employee->salary ? 'Rp ' . number_format($employee->salary, 0, ',', '.') : '-' }}</td></tr>
        <tr><th>Tanggal Masuk</th><td>{{ $employee->join_date ? $employee->join_date->format('d/m/Y') : '-' }}</td></tr>
        <tr><th>Tanggal Keluar</th><td>{{ $employee->exit_date ? $employee->exit_date->format('d/m/Y') : '-' }}</td></tr>
    </table>

    <div class="section-title">Informasi Bank</div>
    <table>
        <tr><th>Nama Bank</th><td>{{ $employee->bank_name ?? '-' }}</td></tr>
        <tr><th>No. Rekening</th><td>{{ $employee->bank_account ?? '-' }}</td></tr>
    </table>

    <div class="section-title">Catatan</div>
    <p>{{ $employee->notes ?? '-' }}</p>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i:s') }} | SIERP - Sistem Informasi ERP
    </div>
</body>
</html>
