# Dokumentasi API — LARIZAN (Layanan Perizinan Santri)

> **Bukan `API_DOCUMENTATION.md`** — file itu isinya dokumentasi API **Core Service**
> (sengaja disalin ke repo ini sebagai referensi integrasi, lihat `integrasi_sistem.md`).
> Dokumen **ini** murni tentang API milik **LARIZAN sendiri** — endpoint yang LARIZAN
> **sediakan** untuk sistem lain, dan satu endpoint yang LARIZAN **terima** dari Core Service.

Base URL (contoh lokal): `http://127.0.0.1:8001`
Base URL (produksi): domain LARIZAN yang berlaku.

---

## 1. Dua Jenis Otentikasi

LARIZAN punya dua arah komunikasi API, dengan mekanisme otentikasi yang **sama sekali berbeda** — jangan tertukar:

| Arah | Mekanisme | Dipakai untuk |
|---|---|---|
| **LARIZAN sebagai PENYEDIA** (§2–§6) | Header `X-Service-Key` | Sistem 1 (Pelaporan), RAPORIS, SIKESAN, KURIZA menarik data rekap izin/keterlambatan/izin keluar dari LARIZAN |
| **LARIZAN sebagai PENERIMA** (§7) | Tanda tangan RS256 di dalam payload (`logout_token`) | Core Service memberi tahu LARIZAN secara real-time begitu user logout/dinonaktifkan |

Tidak ada endpoint LARIZAN yang menerima JWT SSO pengguna sebagai bentuk otentikasi API — JWT SSO di LARIZAN murni dipakai untuk sesi browser (lihat `integrasi_sistem.md` §4 kalau ingin memahami mekanisme itu), bukan API service-to-service.

---

## 2. LARIZAN sebagai Penyedia — Autentikasi Service Key

Header wajib di setiap request:
```http
X-Service-Key: <api-key-plaintext>
```

Key dicocokkan lewat hash SHA-256 ke tabel `service_keys` **milik LARIZAN sendiri** (terpisah total dari Service Key Core Service) — lihat `App\Http\Middleware\VerifyServiceKey`. Tidak ada sistem kapabilitas per-key seperti di Core Service — seluruh endpoint di bawah ini **read-only**, satu key berlaku untuk semuanya begitu aktif.

**Response 401** kalau header kosong atau key tidak cocok/sudah dinonaktifkan:
```json
{ "message": "API key tidak valid." }
```

**Cara mendapatkan Service Key** (dua cara, hasilnya sama):
1. Lewat terminal server: `php artisan service-key:buat <nama_service> --label="Keterangan bebas"` — plaintext-nya cuma ditampilkan **sekali** saat itu juga, tidak bisa dilihat ulang.
2. Lewat Admin → Service Key (Web UI) — pola tampil-sekali yang sama.

Tidak ada rate limit khusus yang diberlakukan untuk endpoint-endpoint ini saat dokumen ini ditulis. Setiap panggilan tercatat di `api_access_logs` (Admin → Log Sistem → Log Akses API) lewat middleware `catat-akses-api`.

---

## 3. `GET /api/rekap-izin/{santri_id}`

Rekap ringkas izin satu santri — dikonsumsi **Sistem 1 (Pelaporan)** dan **RAPORIS**. Dihitung on-the-fly dari tabel `perizinan`, **hanya status final** (`kembali_tepat_waktu` / `kembali_tidak_tepat_waktu`) — izin yang masih berproses, ditolak, atau dibatalkan sengaja tidak ikut dihitung. Santri yang belum pernah izin sama sekali tetap dijawab `200` dengan angka nol, bukan `404`.

Punya **dua mode**, saling eksklusif (server memilih otomatis dari parameter yang dikirim):

### Mode "bulan" (dipakai Sistem 1)
```http
GET /api/rekap-izin/{santri_id}?bulan=2026-07
X-Service-Key: <api-key>
```
`bulan` opsional, format `YYYY-MM`. Default: bulan berjalan kalau tidak dikirim sama sekali.

### Mode "rentang tanggal bebas" (dipakai RAPORIS)
```http
GET /api/rekap-izin/{santri_id}?dari=2026-08-15&sampai=2026-09-14
X-Service-Key: <api-key>
```
`dari` dan `sampai` **wajib dikirim bersamaan** — kalau salah satu dikirim sendirian, itu dianggap invalid (`422`). Mode ini menang kalau keduanya dikirim, terlepas dari `bulan` ikut dikirim atau tidak.

**Response 200** (mode "bulan"):
```json
{
  "santri_id": "019fa1a4-c3bd-715c-8848-6a5a16b51af1",
  "bulan": "2026-07",
  "total_izin": 5,
  "rincian": {
    "bermalam": 2,
    "tidak_bermalam": 1,
    "rombongan": 0,
    "tidak_sekolah": 2
  },
  "rincian_hari": {
    "bermalam": 4,
    "tidak_bermalam": 0,
    "rombongan": 0,
    "tidak_sekolah": 2
  },
  "total_hari_izin": 6,
  "jumlah_terlambat": 1
}
```
**Response 200** (mode rentang tanggal) — bentuknya sama persis, cuma `bulan` diganti `dari`/`sampai`:
```json
{
  "santri_id": "...",
  "dari": "2026-08-15",
  "sampai": "2026-09-14",
  "total_izin": 5,
  "rincian": { "...": "..." },
  "rincian_hari": { "...": "..." },
  "total_hari_izin": 6,
  "jumlah_terlambat": 1
}
```

**Field:**

| Field | Tipe | Keterangan |
|---|---|---|
| `total_izin` | int | Jumlah KEJADIAN izin (bukan hari) berstatus final dalam periode |
| `rincian` | object | Jumlah kejadian PER JENIS. Selalu memuat 4 jenis bawaan (`bermalam`, `tidak_bermalam`, `rombongan`, `tidak_sekolah`) meski nilainya nol; jenis custom (mis. `tidak_kuliah`) ikut muncul sebagai key tambahan HANYA kalau memang ada kejadiannya — tidak diprapopulasi nol |
| `rincian_hari` | object | Sama seperti `rincian`, tapi satuan HARI (dipakai RAPORIS untuk narasi "izin Bermalam 3x/5 hari") — jenis berbasis jam (Tidak Bermalam/Rombongan) selalu nol di sini |
| `total_hari_izin` | int | Jumlah seluruh `rincian_hari` |
| `jumlah_terlambat` | int | Berapa dari `total_izin` yang berstatus `kembali_tidak_tepat_waktu` |

**Response 422** kalau `bulan` bukan format `YYYY-MM`, atau `dari`/`sampai` dikirim tidak berpasangan/`sampai` lebih awal dari `dari`.

Santri yang tercatat lewat keanggotaan Rombongan (bukan pemohon tunggal) ikut terhitung di sini — kecuali kalau dia ditolak sebagian dari rombongan itu (lihat penolakan sebagian Rombongan di alur approval), dia tidak pernah dianggap benar-benar berangkat.

---

## 4. `GET /api/rekap-libur/{santri_id}`

Rekap hari libur massal (Libur Maulid/Ramadhan, dst) satu santri dalam rentang tanggal — dikonsumsi **RAPORIS** untuk memangkas durasi program aktif santri dengan hari-hari yang kebetulan beririsan libur pondok, supaya perbandingan "lama di program vs target" tidak salah menghukum.

```http
GET /api/rekap-libur/{santri_id}?dari=2026-08-01&sampai=2026-10-31
X-Service-Key: <api-key>
```
`dari` dan `sampai` **keduanya wajib**.

**Response 200:**
```json
{
  "santri_id": "019fa1a4-c3bd-715c-8848-6a5a16b51af1",
  "dari": "2026-08-01",
  "sampai": "2026-10-31",
  "total_hari_libur": 12,
  "rincian": [
    {
      "periode_libur_id": "01a0...",
      "jenis": "libur_maulid",
      "tanggal_mulai": "2026-08-22",
      "tanggal_selesai": "2026-09-01",
      "hari_terhitung": 8
    }
  ]
}
```
`hari_terhitung` per baris sudah dipotong ke batas `dari`/`sampai` yang diminta (kalau periode liburnya sebagian di luar rentang, cuma bagian yang beririsan yang dihitung). Santri yang dikecualikan dari suatu Deklarasi Libur, atau yang pengajuan liburnya dibatalkan belakangan, otomatis tidak muncul di sini — sesuai kenyataan.

**Response 422** kalau `dari`/`sampai` tidak dikirim, atau `sampai` lebih awal dari `dari`.

---

## 5. Feed Keterlambatan — untuk SIKESAN

Dua endpoint terpisah: satu untuk kondisi **sekarang** (masih terlambat, belum kembali), satu untuk kejadian yang **sudah final** (sudah kembali, terlambatnya sudah pasti). Cakupan jenis kedua endpoint ini SELALU jenis yang punya `butuh_konfirmasi_kembali=true` (Bermalam, Tidak Bermalam, Rombongan, Libur Maulid, Libur Ramadhan — dicek dari katalog `jenis_perizinan`, bukan hardcode) — Tidak Sekolah/Tidak Kuliah tidak pernah muncul di sini karena jenis itu tidak pernah mencapai status "terlambat" sama sekali.

### 5.1 `GET /api/keterlambatan/aktif`
Snapshot SEMUA santri yang **saat ini** berstatus `terlambat`.
```http
GET /api/keterlambatan/aktif
X-Service-Key: <api-key>
```
```json
{
  "pada": "2026-09-27T10:15:00+07:00",
  "data": [
    {
      "perizinan_id": "01a0...",
      "santri_id": "019fcd30-b114-739e-97fc-c05f8654faf7",
      "jenis": "rombongan",
      "status": "terlambat",
      "batas_waktu": "2026-09-21T17:00:00+07:00",
      "waktu_kembali_aktual": null,
      "lama_terlambat_menit": 8760,
      "lama_terlambat_label": "6 hari, 2 jam"
    }
  ]
}
```
`lama_terlambat_menit`/`lama_terlambat_label` di endpoint ini dihitung **hidup** (live, dari `now()`), bukan angka tersimpan — akan terus membesar tiap kali endpoint ini dipanggil ulang selama santrinya belum dikonfirmasi kembali.

### 5.2 `GET /api/keterlambatan/aktif/{santri_id}`
Sama persis, disaring ke satu santri saja:
```json
{
  "santri_id": "019fcd30-b114-739e-97fc-c05f8654faf7",
  "pada": "2026-09-27T10:15:00+07:00",
  "data": [ { "...": "sama seperti di atas" } ]
}
```
`data` berupa array (bisa lebih dari satu baris kalau santri itu kebetulan terlambat di lebih dari satu izin sekaligus, walau jarang terjadi), atau array kosong kalau santri itu sedang tidak terlambat sama sekali.

### 5.3 `GET /api/keterlambatan/riwayat?dari=&sampai=`
Kejadian keterlambatan yang **sudah final** (`kembali_tidak_tepat_waktu`) dalam rentang tanggal. **Disaring dari `waktu_kembali_aktual`** (kapan pelanggarannya tercatat — santri benar-benar kembali), **bukan** tanggal izin diajukan.
```http
GET /api/keterlambatan/riwayat?dari=2026-09-01&sampai=2026-09-30
X-Service-Key: <api-key>
```
`dari`/`sampai` keduanya wajib.
```json
{
  "dari": "2026-09-01",
  "sampai": "2026-09-30",
  "data": [
    {
      "perizinan_id": "01a0...",
      "santri_id": "...",
      "jenis": "bermalam",
      "status": "kembali_tidak_tepat_waktu",
      "batas_waktu": "2026-09-20T17:00:00+07:00",
      "waktu_kembali_aktual": "2026-09-20T19:26:00+07:00",
      "lama_terlambat_menit": 146,
      "lama_terlambat_label": "2 jam, 26 menit"
    }
  ]
}
```
Di sini `lama_terlambat_menit` adalah angka **final** yang sudah tersimpan (bukan hitungan hidup) — tidak akan berubah lagi meski dipanggil ulang kapan pun.

### 5.4 `GET /api/keterlambatan/riwayat/{santri_id}?dari=&sampai=`
Sama seperti 5.3, ditambah `"santri_id"` di root respons, disaring ke satu santri.

**Response 422** (kedua endpoint riwayat) kalau `dari`/`sampai` tidak dikirim atau `sampai` lebih awal dari `dari`.

---

## 6. `GET /api/izin-keluar` — untuk KURIZA (sejak 2026-10-01)

Izin keluar pondok yang **beririsan** dengan suatu rentang waktu, untuk satu atau banyak santri sekaligus. Dikonsumsi **KURIZA** (Kunjungan Santri Zabitsa) untuk mengecek apakah santri yang akan disambang sedang atau akan berada di luar pondok pada jam sesi sambang. Dipanggil saat reservasi dibuat, berkala menjelang sesi, dan saat QR dipindai.

Beda dari `rekap-izin` (§3), yang hanya menghitung izin yang sudah **final**: endpoint ini justru mengembalikan izin yang **masih berjalan**, termasuk yang masih diajukan atau berproses.

```http
GET /api/izin-keluar?santri_ids[]=<uuid>&santri_ids[]=<uuid>&dari=2026-10-06T08:00:00+07:00&sampai=2026-10-06T11:00:00+07:00
X-Service-Key: <api-key>
```

| Param | Wajib | Keterangan |
|---|---|---|
| `santri_ids[]` | ya | 1–200 UUID santri (Core Service) |
| `dari` | ya | Awal rentang (tanggal + jam) |
| `sampai` | ya | Akhir rentang, harus setelah `dari` |

**Izin yang dihitung:**
- Jenisnya punya **`butuh_konfirmasi_kembali = true`** (dibaca dari katalog jenis, bukan hardcode slug). Saat ini: Bermalam, Tidak Bermalam, Rombongan, Libur Maulid, Libur Ramadhan, dan jenis custom lain yang ditandai sama. Tidak Sekolah/Tidak Kuliah tidak ikut, karena santrinya tetap di pondok.
- Statusnya **belum final**: `diajukan`, `proses_persetujuan`, `disetujui`, `dicetak`, `terlambat`. Izin `ditolak`, `dibatalkan`, dan yang sudah kembali tidak ikut.
- Santri tercatat sebagai pemohon tunggal **atau** anggota Rombongan yang tidak ditolak sebagian.
- Rentang keluarnya beririsan dengan `[dari, sampai)`:
  - **Mulai** = tanggal pelaksanaan (`tanggal_mulai`, lalu tanggal surat dicetak, lalu tanggal pengajuan) + `jam_mulai` kalau ada.
  - **Selesai** = batas kembali yang sama dengan yang dipakai penandaan terlambat. Ini sudah termasuk perpanjangan yang disetujui dan izin jam yang melewati tengah malam.
  - Status **`terlambat`**: selesai **terbuka** (`null`), karena santri belum kembali walau batasnya sudah lewat.

**Response 200:**
```json
{
  "dari": "2026-10-06T08:00:00+07:00",
  "sampai": "2026-10-06T11:00:00+07:00",
  "data": [
    {
      "santri_id": "019fcd30-2853-73ed-a425-9e962b920e4c",
      "perizinan_id": "01a0b1c2-...",
      "jenis": "bermalam",
      "jenis_label": "Bermalam (Pulang)",
      "status": "dicetak",
      "status_label": "Dicetak",
      "sudah_disetujui": true,
      "mulai": "2026-10-05T00:00:00+07:00",
      "selesai": "2026-10-08T23:59:59+07:00"
    }
  ]
}
```

- **Satu baris per santri per izin.** Rombongan yang mencakup beberapa santri yang diminta dipecah jadi beberapa baris. Santri tanpa izin yang beririsan tidak muncul sama sekali, jadi `data: []` berarti semua santri yang diminta ada di pondok pada rentang itu.
- **`sudah_disetujui`** — `true` untuk `disetujui`/`dicetak`/`terlambat`, `false` untuk yang masih diajukan/berproses. Konsumen yang memutuskan perlakuannya. KURIZA hanya menandai bentrok untuk izin yang sudah disetujui, dan memberi peringatan untuk yang belum.

**Response 422** kalau `santri_ids` kosong atau lebih dari 200, atau `dari`/`sampai` tidak dikirim atau `sampai` tidak setelah `dari`.

---

## 7. LARIZAN sebagai Penerima — Webhook Logout dari Core Service

```http
POST /api/webhook/core-logout
Content-Type: application/json

{ "logout_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..." }
```

Dipanggil **Core Service** (bukan Sistem 1/RAPORIS/SIKESAN) secara server-to-server, otomatis, begitu seorang user logout atau dinonaktifkan Admin — bagian dari mekanisme "logout real-time" (menutup jendela ~5 menit bawaan JWT stateless, lihat `integrasi_sistem.md` §6.1 untuk penjelasan lengkap mekanismenya).

**Otentikasi**: BUKAN `X-Service-Key` — endpoint ini di luar middleware `service-key` sama sekali. Keasliannya dijamin murni oleh **tanda tangan RS256** `logout_token` itu sendiri, diverifikasi pakai public key Core Service yang sama dengan yang dipakai memverifikasi JWT SSO biasa (tersimpan di `pengaturan_integrasi`, diisi lewat wizard `/setup` atau Admin → Integrasi Core Service).

**Response 200:**
```json
{ "status": "ok" }
```

**Response 401** (tanda tangan tidak valid/rusak):
```json
{ "message": "logout_token tidak valid: ..." }
```

**Response 422** (payload kosong/tidak lengkap):
```json
{ "message": "logout_token wajib diisi." }
```
atau
```json
{ "message": "Payload logout_token tidak lengkap." }
```

Endpoint ini **hanya menerima**, tidak pernah dipanggil balik oleh sistem lain, dan tidak punya cara untuk "ditanya" (tidak ada `GET` untuk cek status). Kalau Core Service gagal memanggilnya (LARIZAN down, dsb), Core Service akan retry beberapa kali lalu menyerah senyap — tidak ada efek ke alur logout di sisi Core Service itu sendiri.

---

## 8. Format Error Umum

Semua endpoint di dokumen ini menjawab error sebagai JSON `{ "message": "..." }`, dengan kode HTTP:

| Kode | Arti |
|---|---|
| `401` | `X-Service-Key` kosong/tidak valid (§2–§6), atau `logout_token` gagal diverifikasi (§7) |
| `422` | Parameter query tidak valid/tidak lengkap (lihat validasi tiap endpoint) |
| `503` | LARIZAN sendiri tidak bisa menjawab (jarang — endpoint di dokumen ini semua baca dari database LARIZAN sendiri, bukan proxy ke Core Service, jadi status ini praktis tidak pernah muncul di sini) |

---

## 9. Ringkasan Semua Endpoint

| Method | Path | Auth | Arah |
|---|---|---|---|
| `GET` | `/api/rekap-izin/{santri_id}` | Service Key | LARIZAN → konsumen |
| `GET` | `/api/rekap-libur/{santri_id}` | Service Key | LARIZAN → konsumen |
| `GET` | `/api/keterlambatan/aktif` | Service Key | LARIZAN → konsumen |
| `GET` | `/api/keterlambatan/aktif/{santri_id}` | Service Key | LARIZAN → konsumen |
| `GET` | `/api/keterlambatan/riwayat` | Service Key | LARIZAN → konsumen |
| `GET` | `/api/keterlambatan/riwayat/{santri_id}` | Service Key | LARIZAN → konsumen |
| `GET` | `/api/izin-keluar` | Service Key | LARIZAN → konsumen (KURIZA) |
| `POST` | `/api/webhook/core-logout` | Tanda tangan RS256 (`logout_token`) | Core Service → LARIZAN |

---

## 10. Catatan Keamanan

- **Service Key** LARIZAN setara password server-to-server — jangan pernah expose ke JavaScript sisi klien/browser, jangan commit ke repository.
- Seluruh endpoint §2–§6 **read-only** — tidak ada satu pun yang mengubah data LARIZAN. Kalau suatu saat sistem konsumen butuh kemampuan tulis, itu perlu endpoint & keputusan desain baru, bukan sekadar mencentang kapabilitas (LARIZAN tidak punya sistem kapabilitas per-key seperti Core Service).
- Endpoint §7 sengaja TIDAK memakai `X-Service-Key` — jangan tergoda menambahkannya "supaya konsisten": Core Service tidak (dan tidak akan pernah) memiliki Service Key LARIZAN, keasliannya sudah cukup terjamin lewat tanda tangan RS256 yang sudah dipercaya di seluruh alur SSO.
- Semua komunikasi wajib **HTTPS** di produksi.
