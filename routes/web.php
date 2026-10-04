<?php

use App\Http\Controllers\AbsensiKegiatanController;
use App\Http\Controllers\AbsensiPuasaController;
use App\Http\Controllers\BigDataDaerahController;
use App\Http\Controllers\BigDataWilayahController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DummyAuthController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\PembinaanController;
use App\Http\Controllers\PenilaianKeseharianController;
use App\Http\Controllers\PenugasanDaerahController;
use App\Http\Controllers\PenugasanWaliAsuhController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SsoAuthController;
use App\Http\Controllers\WaliAsuhController;
use App\Http\Controllers\WaliSantriController;
use App\Http\Controllers\Api\SsoSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// ─── SSO Dummy Simulator (Legacy) ───
Route::get('/dev/debug-user', function() {
    $user = auth()->user();
    if(!$user) return 'Not logged in';
    return [
        'id' => $user->id,
        'nama' => $user->nama,
        'role' => $user->role->nama_role ?? null,
        'unit_organisasi_id' => $user->unit_organisasi_id,
        'unit' => $user->unitOrganisasi,
        'allowed' => $user->getAllowedUnitIds(),
    ];
});
Route::get('/dev/login', [DummyAuthController::class, 'loginSimulator'])->name('dev.login');

// ─── SSO Cangkang Penerima — Main Core Zabitsa ───
Route::get('/dev/sso-login', [SsoAuthController::class, 'dummyLogin'])->name('dev.sso-login');
Route::post('/auth/sso-callback', [SsoAuthController::class, 'callback'])->name('auth.sso-callback');

// ─── Dashboard ───
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ─── API Webhook SSO ───
Route::patch('/api/sso/sync-santri', [SsoSyncController::class, 'sync'])->name('api.sso.sync-santri');

// ─── Authenticated Routes ───
Route::middleware(['auth', 'scope.wilayah'])->group(function () {

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Big Data Wilayah (Admin & Pengurus Daerah) ───
    Route::middleware('role:admin,pengurus_wilayah,pengurus_daerah,dewan_pemangku')->group(function () {
        Route::get('/admin/big-data', [BigDataWilayahController::class, 'index'])
            ->name('big-data.index');
        Route::patch('/admin/big-data/santri/{santri}/program', [BigDataWilayahController::class, 'updateProgram'])
            ->name('santri.update-program');
    });

    // ─── Absensi Terpadu (Khusus Pengurus — Wali Santri dialihkan ke route terpisah) ───
    Route::middleware('role:admin,pengurus_wilayah,pengurus_daerah,pengurus_sektor,dewan_pemangku')->group(function () {
        Route::get('/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi/kegiatan', [\App\Http\Controllers\AbsensiController::class, 'store'])->name('absensi.store');
        Route::post('/absensi/puasa', [\App\Http\Controllers\AbsensiController::class, 'storePuasa'])->name('absensi.store-puasa');
        Route::post('/absensi/puasa/{id}/toggle-wajib', [\App\Http\Controllers\AbsensiController::class, 'togglePuasaWajib'])->name('absensi.toggle-puasa-wajib');
        
        // Rekapitulasi Absensi
        Route::get('/absensi/rekap', [\App\Http\Controllers\AbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::get('/absensi/rekap/detail', [\App\Http\Controllers\AbsensiController::class, 'detailRekap'])->name('absensi.rekap.detail');
        Route::get('/absensi/rekap/riwayat-santri', [\App\Http\Controllers\AbsensiController::class, 'detailRiwayatSantri'])->name('absensi.rekap.riwayat-santri');
        Route::get('/absensi/export/excel', [\App\Http\Controllers\AbsensiController::class, 'exportExcel'])->name('absensi.export.excel');
        Route::get('/absensi/export/pdf', [\App\Http\Controllers\AbsensiController::class, 'exportPdf'])->name('absensi.export.pdf');

        // Tutup & Buka Buku
        Route::post('/absensi/tutup-buku', [\App\Http\Controllers\AbsensiController::class, 'tutupBuku'])->name('absensi.tutup-buku');
        Route::post('/absensi/buka-buku', [\App\Http\Controllers\AbsensiController::class, 'bukaBuku'])->name('absensi.buka-buku');

        // Arsip Absensi
        Route::get('/arsip-absensi', [\App\Http\Controllers\ArsipAbsensiController::class, 'index'])->name('arsip-absensi.index');
        Route::get('/arsip-absensi/{bulan}/{tahun}', [\App\Http\Controllers\ArsipAbsensiController::class, 'kegiatan'])->name('arsip-absensi.kegiatan');
        Route::get('/arsip-absensi/detail-kegiatan', [\App\Http\Controllers\ArsipAbsensiController::class, 'detailKegiatan'])->name('arsip-absensi.detail-kegiatan');

        // CRUD Kegiatan Daerah
        Route::resource('kegiatan-daerah', \App\Http\Controllers\KegiatanDaerahController::class)->except(['create', 'edit', 'show']);
        
        // CRUD Aspek Penilaian (Master Penilaian)
        Route::resource('aspek-penilaian', \App\Http\Controllers\AspekPenilaianController::class)->except(['create', 'edit', 'show']);
    });

    // ─── Pelanggaran ───
    Route::middleware('role:admin,pengurus_wilayah,pengurus_daerah,pengurus_sektor,dewan_pemangku,wali_santri')->group(function () {
        Route::resource('pelanggaran', PelanggaranController::class)->only(['index', 'create', 'store', 'show']);
    });

    // ─── Pembinaan ───
    Route::middleware('role:admin,pengurus_wilayah,pengurus_daerah,dewan_pemangku')->group(function () {
        Route::get('pembinaan', [PembinaanController::class, 'index'])->name('pembinaan.index');
        Route::post('pembinaan', [PembinaanController::class, 'store'])->name('pembinaan.store');
    });

    // ─── Penilaian Keseharian ───
    Route::middleware('role:admin,pengurus_wilayah,pengurus_daerah,pengurus_sektor,dewan_pemangku,wali_santri')->group(function () {
        Route::resource('penilaian-keseharian', PenilaianKeseharianController::class)
            ->parameters(['penilaian-keseharian' => 'penilaian_keseharian'])
            ->only(['index', 'create', 'store', 'show']);
            
        Route::get('penilaian-keseharian/{penilaian_keseharian}/export-pdf', [PenilaianKeseharianController::class, 'exportPdf'])
            ->name('penilaian-keseharian.export-pdf');
            
        Route::post('penilaian-keseharian/{penilaian_keseharian}/catatan-pemangku', [PenilaianKeseharianController::class, 'storeCatatanPemangku'])
            ->name('penilaian-keseharian.catatan-pemangku');
    });

    // ─── Pengurus Sektor ───
    Route::middleware('role:pengurus_sektor')->group(function () {
        Route::get('wali-asuh/santri-asuhan', [WaliAsuhController::class, 'santriAsuhan'])
            ->name('wali-asuh.santri-asuhan');
    });

    // ─── Wali Santri ───
    Route::middleware('role:wali_santri')->group(function () {
        Route::get('wali-santri/riwayat-izin', [WaliSantriController::class, 'riwayatIzin'])
            ->name('wali-santri.riwayat-izin');

        Route::get('wali-santri/absensi/hari-ini', [\App\Http\Controllers\AbsensiController::class, 'rekapHarianWali'])
            ->name('wali-santri.absensi.hari-ini');
    });
});

require __DIR__.'/auth.php';
