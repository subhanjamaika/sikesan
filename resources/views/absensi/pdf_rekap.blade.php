<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Kehadiran Santri</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; }
        .info-table { width: 100%; margin-bottom: 20px; font-size: 12px; }
        .info-table td { padding: 3px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #333; padding: 6px; text-align: left; }
        table.data th { background-color: #f4f4f4; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .signature { width: 100%; margin-top: 50px; }
        .signature-box { float: right; width: 300px; text-align: center; font-size: 12px; }
        .signature-line { margin-top: 60px; border-bottom: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN REKAPITULASI ABSENSI SANTRI{{ isset($kegiatan) && $kegiatan ? ' - KEGIATAN ' . strtoupper($kegiatan->nama_kegiatan) : '' }}</h1>
        <p>SIKESAN Zaid bin Tsabit</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Bulan</strong></td>
            <td width="35%">: {{ date('F', mktime(0, 0, 0, $bulan, 10)) }} {{ $tahun }}</td>
            <td width="15%"><strong>Daerah</strong></td>
            <td width="35%">: {{ $daerahId ? $daerahList->firstWhere('id', $daerahId)->nama ?? 'Semua Daerah' : 'Semua Daerah' }}</td>
        </tr>
        <tr>
            <td><strong>Total Santri</strong></td>
            <td>: {{ $totalSantri }} Santri</td>
            <td><strong>Kamar</strong></td>
            <td>: {{ request()->get('kamar_id') ? \App\Models\UnitOrganisasi::find(request()->get('kamar_id'))->nama ?? 'Semua Kamar' : 'Semua Kamar' }}</td>
        </tr>
        <tr>
            <td><strong>Rata-rata Hadir</strong></td>
            <td>: {{ $avgKehadiran }}%</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Nama Santri</th>
                <th width="12%">NIS</th>
                <th width="20%">Daerah/Kamar</th>
                <th width="6%">Hadir</th>
                <th width="6%">Izin</th>
                <th width="6%">Sakit</th>
                <th width="6%">Alpa</th>
                <th width="8%">Total</th>
                <th width="12%">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($santriList as $index => $santri)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $santri->nama }}</td>
                <td class="text-center">{{ $santri->nis_induk }}</td>
                <td>{{ $santri->unitOrganisasi->parent->nama ?? '-' }} - {{ $santri->unitOrganisasi->nama ?? '-' }}</td>
                <td class="text-center">{{ $santri->rekap->hadir }}</td>
                <td class="text-center">{{ $santri->rekap->izin }}</td>
                <td class="text-center">{{ $santri->rekap->sakit }}</td>
                <td class="text-center">{{ $santri->rekap->alpa }}</td>
                <td class="text-center">{{ $santri->rekap->total_pertemuan }}</td>
                <td class="text-center">{{ $santri->rekap->persentase }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data santri untuk filter ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature clearfix">
        <div class="signature-box">
            <p>Paiton, {{ date('d F Y') }}</p>
            <p>Pengurus / Wali Asuh</p>
            <div class="signature-line"></div>
            <p><strong>{{ auth()->user()->nama ?? '..........................' }}</strong></p>
        </div>
    </div>

</body>
</html>
