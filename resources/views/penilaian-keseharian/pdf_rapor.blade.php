<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Penilaian Keseharian Santri</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; }
        .info-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .info-table td { padding: 3px; }
        
        .section-title { font-size: 12px; font-weight: bold; background: #eee; padding: 5px; border: 1px solid #000; margin-top: 15px; margin-bottom: 0;}
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px; text-align: left; }
        table.data th { background-color: #f4f4f4; text-align: center; font-weight: bold; }
        table.data tfoot td { font-weight: bold; }
        .text-center { text-align: center; }
        
        .notes-box { border: 1px solid #000; padding: 8px; margin-bottom: 10px; border-top: none; }
        .notes-title { font-weight: bold; margin-bottom: 5px; font-size: 11px; }
        .notes-content { white-space: pre-wrap; font-size: 11px; }
        
        .signature { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .signature-box { float: right; width: 250px; text-align: center; font-size: 11px; }
        .signature-box-left { float: left; width: 250px; text-align: center; font-size: 11px; }
        .signature-line { margin-top: 50px; border-bottom: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN PENILAIAN KESEHARIAN SANTRI</h1>
        <p>SIKESAN Terpadu - Wilayah Zaid bin Tsabit</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Nama Santri</strong></td>
            <td width="35%">: {{ $penilaian->santri->nama }}</td>
            <td width="15%"><strong>Periode</strong></td>
            <td width="35%">: {{ \Carbon\Carbon::parse($penilaian->periode)->translatedFormat('F Y') }}</td>
        </tr>
        <tr>
            <td><strong>NIS Induk</strong></td>
            <td>: {{ $penilaian->santri->nis_induk }}</td>
            <td><strong>Tgl Penilaian</strong></td>
            <td>: {{ $penilaian->tanggal_penilaian->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td><strong>Unit Daerah</strong></td>
            <td>: {{ $penilaian->santri->unitOrganisasi->parent->nama ?? '-' }}</td>
            <td><strong>Wali Asuh</strong></td>
            <td>: {{ $penilaian->waliAsuh->nama }}</td>
        </tr>
        <tr>
            <td><strong>Kamar</strong></td>
            <td>: {{ $penilaian->santri->unitOrganisasi->nama }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="section-title" style="border-bottom: none;">A. SKOR ASPEK PENILAIAN</div>
    <table class="data" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="80%">Aspek yang Dinilai</th>
                <th width="15%">Skor (1-4)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penilaian->detail as $item)
            <tr>
                <td class="text-center">{{ $item->aspek->nomor_urut }}</td>
                <td>
                    <strong>{{ $item->aspek->nama_aspek }}</strong><br>
                    <span style="font-size: 9px; color: #555;">{{ $item->aspek->indikator ?? $item->aspek->deskripsi }}</span>
                </td>
                <td class="text-center" style="font-size: 14px; font-weight: bold;">{{ $item->skor }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right;">Nilai Akhir (Skala 100)</td>
                <td class="text-center" style="font-size: 14px;">{{ $penilaian->nilai_akhir }}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;">Kategori Predikat</td>
                <td class="text-center">{{ $penilaian->kategori }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title" style="border-bottom:none; margin-top: 15px;">B. CATATAN PERKEMBANGAN</div>
    <div class="notes-box" style="border-top: 1px solid #000;">
        <div class="notes-title">Kelebihan & Hal Positif:</div>
        <div class="notes-content">{{ $penilaian->catatan_kelebihan }}</div>
    </div>
    <div class="notes-box">
        <div class="notes-title">Area Perlu Peningkatan:</div>
        <div class="notes-content">{{ $penilaian->catatan_peningkatan }}</div>
    </div>
    <div class="notes-box">
        <div class="notes-title">Rencana Tindak Lanjut Wali Asuh:</div>
        <div class="notes-content">{{ $penilaian->rencana_tindak_lanjut }}</div>
    </div>
    
    @if($penilaian->catatan_pemangku)
    <div class="notes-box">
        <div class="notes-title">Catatan Khusus Dewan Pemangku:</div>
        <div class="notes-content">{{ $penilaian->catatan_pemangku }}</div>
    </div>
    @endif

    <div class="signature clearfix">
        <div class="signature-box-left">
            <p>Mengetahui,</p>
            <p>Kepala Bagian Daerah</p>
            <div class="signature-line"></div>
            <p>(............................................)</p>
        </div>
        <div class="signature-box">
            <p>Paiton, {{ date('d F Y') }}</p>
            <p>Wali Asuh</p>
            <div class="signature-line"></div>
            <p><strong>{{ $penilaian->waliAsuh->nama }}</strong></p>
        </div>
    </div>

</body>
</html>
