# Dokumentasi API — Core Service (Wilayah Zaid bin Tsabit Putra)

Dokumen ini menjelaskan **semua endpoint publik** yang disediakan Core Service untuk dikonsumsi sistem lain yang terintegrasi (Sistem 1: Pelaporan Perpindahan Domisili, Sistem 2: Pelaporan Capaian Santri, Sistem 3: Perizinan Santri, Sistem 4, dan seterusnya).

Base URL (contoh lokal): `http://127.0.0.1:8000`
Base URL (produksi): `https://sso.zabitsa.site` (atau domain Core Service yang berlaku)

Terakhir diperbarui: mengikuti perubahan API terbaru — payload JWT lengkap (`nama`, `role_nama`, `jabatan`), filter pencarian di `GET /api/santri`, `foto_signed_url` di daftar santri, endpoint `GET /api/santri/{id}/wali-santri`, `GET /api/roles`, `GET /api/santri/{id}/riwayat-program` (+ dukungan Program Angkatan), `POST /api/notifikasi`, rantai wilayah berjenjang `kecamatan` (kecamatan → kabupaten → provinsi → negara) disematkan di `GET /api/santri`, `GET /api/santri/{id}`, dan `GET /api/santri/{id}/wali-santri`, `pendidikan_aktif`/`program_aktif` disematkan di `GET /api/santri`/`GET /api/santri/{id}`, dan **pengerasan SSO sesuai OAuth 2.0 Security BCP** — parameter `state` (login-CSRF), rotasi refresh token (portal DAN aplikasi konsumen, aktif penuh), redirect trust diperketat ke Aplikasi terdaftar, autentikasi `client_id`/`client_secret` **wajib** di `/auth/token-exchange` & `/auth/refresh` (sejak 2026-08-20), `GET /auth/jwks`, dan TTL refresh token aplikasi konsumen kini mengikuti status "Ingat Akun Saya" sesi login asalnya (sejak 2026-09-14, sebelumnya selalu 30 hari tetap), serta `/login` kini konsisten memulihkan sesi lewat refresh token (bukan cuma access token) sebelum menampilkan form — lihat §2.1, field baru `refresh_expires_in` di respons `/auth/token-exchange` & `/auth/refresh` — lihat §2.2/§2.3, dan **logout portal kini ikut mematikan refresh token yang sudah didelegasikan ke aplikasi konsumen** (sejak 2026-09-15, jendela sisa maksimal `access_ttl`) — lihat §2.3.

> Untuk panduan **cara mengintegrasikan** (langkah demi langkah, checklist, domain tepercaya) lihat `integrasi_sistem.md`. Dokumen ini murni **referensi endpoint**.

---

## 1. Dua Jenis Otentikasi

Core Service punya **dua mekanisme otentikasi terpisah**, jangan tertukar:

| Mekanisme | Dipakai untuk | Header/Cara |
|---|---|---|
| **JWT (SSO)** | Mengetahui **siapa pengguna** yang sedang login (identitas manusia) | Cookie `zbt_access_token` (browser) atau `Authorization: Bearer <token>` (server-to-server setelah token exchange) |
| **Service Key** | Otorisasi **server-to-server** aplikasi kalian memanggil REST API data master Core Service (tanpa konteks pengguna tertentu) | Header `X-Service-Key: <key>` |

Keduanya **independen** — endpoint REST API data master (`/api/santri`, `/api/program`, dst.) hanya butuh Service Key, TIDAK butuh JWT. JWT dipakai murni untuk alur SSO (identifikasi pengguna yang login di aplikasi kalian).

---

## 2. Otentikasi SSO (JWT)

### 2.1 Alur Login

**Portal (indirect)** — pengguna login lewat Core Service dulu:
1. Pengguna buka `https://sso.zabitsa.site`, login.
2. Core Service set cookie `zbt_access_token` + `zbt_refresh_token` di domain induk (`.zabitsa.site`).
3. Pengguna klik kartu aplikasi kalian di Dashboard → cookie otomatis terbaca oleh subdomain kalian (kalau aplikasi kalian ada di subdomain `*.zabitsa.site`).

**Direct access** — pengguna buka aplikasi kalian duluan tanpa login:
1. Aplikasi kalian cek cookie `zbt_access_token` — tidak ada/invalid.
2. Redirect ke `https://sso.zabitsa.site/login?redirect_to=https://perizinan.zabitsa.site/callback&state=<nilai_acak_kalian>`.
   - `state` **opsional tapi disarankan** (proteksi login-CSRF standar OAuth2/RFC 9700 §10.12) — generate nilai acak di sisi kalian sebelum redirect, simpan di sesi kalian sendiri, lalu WAJIB dicocokkan dengan `state` yang kembali di langkah 3 sebelum mempercayai `code`-nya. Core Service **murni mengembalikannya apa adanya**, tidak pernah menyimpan/memvalidasi isinya sendiri.
   - **"Login sukses" di langkah 3 di bawah tidak selalu berarti user mengisi form** — sejak 2026-09-14, `/login` langsung memproses redirect balik (tanpa menampilkan form apa pun) kalau user yang di-redirect ke sana **masih punya sesi Core Service valid** (access token belum kedaluwarsa, ATAU refresh token-nya masih valid — termasuk sesi "Ingat Akun Saya" yang sudah melewati restart browser). Jangan asumsikan endpoint ini selalu menampilkan UI interaktif ke user.
3. Setelah login sukses:
   - Kalau origin (scheme+host+port) `redirect_to` **cocok dengan `url` salah satu Aplikasi AKTIF yang terdaftar** di Core Service → redirect balik membawa **authorization code** sekali-pakai (+ `state` kalau kalian kirim) di query string: `https://perizinan.zabitsa.site/callback?code=xxxxx&state=<nilai_kalian>`. **Aplikasi kalian harus sudah terdaftar** di Core Service (lewat Admin → Aplikasi) — subdomain `*.zabitsa.site` yang valid tapi TIDAK terdaftar sebagai Aplikasi aktif akan **ditolak**, fallback ke Dashboard (diperketat — sebelumnya cukup subdomain tepercaya saja tanpa perlu terdaftar).
   - Kalau host **DAN port** sama persis dengan Core Service sendiri (redirect internal, mis. `/admin/...`) → redirect langsung tanpa code (cookie SSO sudah cukup). Port ikut dicocokkan — dua aplikasi berbeda yang kebetulan sama-sama diakses lewat host yang sama (mis. `127.0.0.1` saat pengujian lokal, beda port) TIDAK dianggap "aplikasi yang sama".
   - Host lain yang bukan keduanya → `redirect_to` diabaikan, fallback ke Dashboard Core Service.

   > **Pengujian lokal (127.0.0.1/localhost):** Core Service punya pengecualian KHUSUS LINGKUNGAN PENGEMBANGAN (`config('jwt.redirect_dev_hosts')`, default `127.0.0.1,localhost`, diatur via env `SSO_REDIRECT_DEV_HOSTS` di **Core Service**, bukan di aplikasi kalian) — kalau `redirect_to` host-nya ada di daftar itu DAN Core Service sedang jalan di environment `local`/`testing`, authorization code tetap diterbitkan **tanpa perlu terdaftar sebagai Aplikasi**, berapa pun port-nya. Ini TIDAK PERNAH aktif di production apa pun isi config-nya (gerbang environment dicek eksplisit di kode, terpisah dari isi daftar host). Kalau kalian menguji lokal dan `redirect_to` masih diabaikan (mendarat di Dashboard, bukan balik ke aplikasi kalian), pastikan: (1) Core Service benar-benar jalan dengan `APP_ENV=local` (bukan `production`), dan (2) host `redirect_to` kalian persis salah satu dari `127.0.0.1`/`localhost` — kalau beda (mis. IP LAN atau hostname custom), minta Admin Core Service menambahkannya ke `SSO_REDIRECT_DEV_HOSTS`.
   - Kalau host TIDAK dikenal/tidak tepercaya → diabaikan, fallback ke Dashboard Core Service.
4. Aplikasi kalian **tukar code itu server-to-server** (bukan dari browser) lewat `POST /auth/token-exchange`.

### 2.2 `POST /auth/token-exchange`

Menukar authorization code (umur 30 detik, sekali pakai) jadi pasangan token asli.

**Request:**
```http
POST /auth/token-exchange
Content-Type: application/json

{
  "code": "kode_dari_query_string_redirect",
  "client_id": "modul_aplikasi_kalian",
  "client_secret": "secret_yang_diterbitkan_admin"
}
```

> **`client_id`/`client_secret` — WAJIB, sejak 2026-08-20.** `client_id` sama dengan `modul` aplikasi kalian yang terdaftar; `client_secret` diterbitkan Admin Core Service lewat Admin → Aplikasi (tampil SATU KALI saat diterbitkan/diterbitkan ulang, catat langsung). Request TANPA `client_id` sama sekali sekarang ditolak `401` — bukan lagi dianggap anonim. Kalau aplikasi kalian belum diberi kredensial, hubungi tim Core Service SEGERA (Admin → Aplikasi → "Terbitkan Secret") sebelum mencoba integrasi/pengujian lebih lanjut, endpoint ini tidak akan bisa dipakai tanpanya.

**Response 200:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refresh_token": "string_acak_80_karakter",
  "token_type": "Bearer",
  "expires_in": 300,
  "refresh_expires_in": 43200
}
```

> **`refresh_expires_in` (detik) — BARU sejak 2026-09-14, PURE ADDITIVE.** Detik PENUH kelas TTL refresh token yang baru diterbitkan — `43200` (12 jam) kalau sesi login yang menghasilkan `code` ini TANPA "Ingat Akun Saya", atau `2592000` (30 hari) kalau DENGAN. Lihat §2.3 untuk penjelasan lengkap (field yang sama, sama-sama muncul di `/auth/refresh`).

**Response 401:**
- `client_id`/`client_secret` sama sekali tidak dikirim: `{ "message": "client_id dan client_secret wajib diisi." }`
- `client_id`/`client_secret` dikirim tapi salah: `{ "message": "client_id atau client_secret tidak valid." }`
- Keduanya benar tapi `code` sendiri invalid/kadaluarsa/sudah dipakai: `{ "message": "Authorization code tidak valid atau sudah kadaluarsa." }`

> Rate limit: 30 request/menit per IP (`throttle:token`).

### 2.3 `POST /auth/refresh`

Menukar refresh token dengan access token baru (dipakai silent-refresh berkala, access token cuma berumur 5 menit). `client_id`/`client_secret` **wajib** sama seperti §2.2 di atas.

**Request** (browser — refresh token dari cookie otomatis) atau server-to-server:
```http
POST /auth/refresh
Content-Type: application/json

{ "refresh_token": "string_acak_80_karakter" }
```

**Response 200 (sesi PORTAL — browser, cookie SSO — DAN sesi APLIKASI KONSUMEN — server-to-server, hasil `/auth/token-exchange`):**
```json
{
  "access_token": "eyJ0eXAiOi...",
  "refresh_token": "string_acak_80_karakter_BARU",
  "refresh_expires_in": 43200
}
```

> **Refresh token DI-ROTASI tiap panggilan berhasil, standar OAuth 2.0 Security BCP — AKTIF untuk KEDUA jenis sesi** (diaktifkan penuh 2026-08-20, setelah dikoordinasikan & dikonfirmasi dengan aplikasi konsumen yang sudah terintegrasi). `refresh_token` di respons ini SELALU nilai BARU, BEDA dari yang kalian kirim — **WAJIB simpan & timpa nilai lama dengan yang baru ini setiap kali**. `refresh_token` yang sudah dipakai LANGSUNG tidak valid lagi begitu dirotasi (memanggil refresh dua kali dengan token yang sama akan gagal `401` di panggilan kedua), KECUALI dipanggil lagi dalam hitungan detik dengan token yang PERSIS sama seperti barusan (jendela toleransi ~10 detik untuk beberapa proses/tab yang kebetulan memanggil nyaris bersamaan — bukan izin memakai ulang token lama sesudahnya). TTL sesi tidak berubah oleh rotasi ini — tetap mengikuti kelas TTL yang sudah ditentukan sejak sesi ini pertama dibuat (lihat baris berikutnya), diperpanjang lagi (sliding) tiap kali berhasil dirotasi selama dipakai aktif.
>
> **TTL mengikuti status "Ingat Akun Saya" — UNTUK KEDUA jenis sesi, sejak 2026-09-14**: portal sudah begitu sejak awal (30 hari dicentang / 12 jam tidak); sesi aplikasi konsumen (hasil `/auth/token-exchange`) SEBELUMNYA selalu 30 hari tetap terlepas dari pilihan itu — bug nyata dilaporkan LARIZAN (sesi tanpa "Ingat Akun Saya" tetap bertahan 11+ jam lewat refresh berulang) — sekarang ikut mengikuti status "Ingat Akun Saya" sesi login yang melahirkan `code`-nya: TIDAK dicentang → TTL 12 jam (sama seperti umur sesi Core Service sendiri); DICENTANG → tetap 30 hari. Kelas ini ditentukan sekali saat `code` ditukar dan dipertahankan apa adanya di setiap rotasi berikutnya, tidak terpengaruh login lain milik user yang sama setelahnya. Belum ada batas umur ABSOLUT terlepas dari status ini (sesi yang terus di-refresh aktif bisa bertahan tanpa batas waktu) — dicatat sebagai kemungkinan pengerasan tambahan ke depan, belum diterapkan.
>
> **`refresh_expires_in` (detik) — BARU sejak 2026-09-14, PURE ADDITIVE.** Detik PENUH kelas TTL di atas untuk `refresh_token` yang BARU SAJA diterbitkan di respons ini — `43200` atau `2592000`, dua nilai itu saja, tergantung kelasnya. **SELALU nilai penuh** (persis seperti `expires_in` access token), **BUKAN sisa waktu dari grant pertama** — karena TTL-nya sliding/rolling (lihat paragraf rotasi di atas), field ini melaporkan ulang nilai penuh di SETIAP respons refresh, bukan hitung mundur. Berguna kalau aplikasi kalian perlu tahu kelas sesi secara lokal (mis. untuk housekeeping baris sesi kalian sendiri) tanpa harus menyimpan/menebak status "Ingat Akun Saya" terpisah. Field lama (`access_token`/`refresh_token`) tidak berubah — kode yang belum membaca field ini tetap jalan normal.
>
> **Logout di Core Service ikut mematikan refresh token yang sudah didelegasikan ke aplikasi kalian — SEJAK 2026-09-15.** Sebelumnya logout portal (`POST /logout`, dipanggil dari browser Core Service) TIDAK PERNAH menyentuh refresh token yang sudah kalian pegang hasil `/auth/token-exchange` — sesi delegasi tetap hidup sampai TTL-nya sendiri habis (12 jam/30 hari), walau user sudah logout. Sekarang: begitu user logout, refresh token yang sudah didelegasikan ke kalian (SEMUA aplikasi konsumen, bukan cuma yang sedang dibuka) ditolak paling lambat di panggilan `/auth/refresh` **berikutnya** — respons **`401` generik yang SAMA PERSIS** dengan kasus kadaluarsa biasa (`{ "message": "Refresh token invalid atau kadaluarsa." }`, tidak ada field/kode error baru yang membedakan "logout" dari "kadaluarsa"). **Tidak ada perubahan kontrak apa pun di sisi kalian** — penanganan `401` yang sudah ada otomatis menangani kasus ini juga.
> - **Jendela yang masih tersisa setelah logout**: access token yang SUDAH TERLANJUR beredar (didapat SEBELUM user logout) tetap valid sampai `exp`-nya sendiri habis — **maksimal `access_ttl`, default 5 menit** — karena access token diverifikasi 100% lokal oleh Core Service maupun (kalau kalian verifikasi RS256 sendiri) aplikasi kalian, tanpa call-back per-request. Ini batas presisi invalidasi yang berlaku SAAT INI.
> - **Roadmap ke depan (BELUM diimplementasikan)**: back-channel logout bergaya OpenID Connect (Core Service memanggil webhook terdaftar milik aplikasi kalian secara real-time saat logout, membawa Logout Token bertanda tangan) — akan butuh pendaftaran URL webhook + endpoint penerima baru di sisi kalian kalau nanti benar-benar dikerjakan, dikoordinasikan terpisah, bukan breaking change dadakan.
>
> **Aplikasi baru yang belum pernah integrasi**: kontrak di atas berlaku sejak awal, tidak ada masa tenggang lagi.

**Response 401:** `{ "message": "client_id dan client_secret wajib diisi." }` (tidak dikirim) / `{ "message": "client_id atau client_secret tidak valid." }` (dikirim tapi salah) / `{ "message": "Refresh token tidak ditemukan." }` / `{ "message": "Refresh token invalid atau kadaluarsa." }`

### 2.4 `GET /auth/public-key`

Ambil **public key RSA** (format PEM) untuk verifikasi signature JWT secara **lokal** — panggil sekali saat setup, simpan/cache di sisi kalian, TIDAK perlu dipanggil setiap request.

```http
GET /auth/public-key
```

**Response 200** (`Content-Type: application/x-pem-file`):
```
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...
-----END PUBLIC KEY-----
```

Algoritma: **RS256**. Verifikasi token pakai library JWT standar (`firebase/php-jwt`, `jsonwebtoken` di Node, dll) dengan public key ini — **tidak perlu call-back ke Core Service tiap request**, itulah intinya SSO stateless.

### 2.5 `GET /auth/jwks` — **BARU**

Bentuk **JWK standar** (RFC 7517) dari public key yang sama seperti §2.4 — alternatif tambahan, BUKAN pengganti `GET /auth/public-key` (keduanya tetap dipertahankan, pilih sesuai library JWT yang kalian pakai — banyak library modern mengonsumsi JWKS langsung).

```http
GET /auth/jwks
```

**Response 200:**
```json
{
  "keys": [
    {
      "kty": "RSA",
      "use": "sig",
      "alg": "RS256",
      "kid": "fd341a886db2",
      "n": "base64url...",
      "e": "base64url..."
    }
  ]
}
```

Field `kid` (key ID) sekarang juga disematkan di header tiap `access_token` yang diterbitkan — murni **fondasi rotasi key yang graceful di masa depan** (belum ada rotasi apa pun terjadi hari ini, cuma satu key aktif seperti sebelumnya). Kalau verifier kalian memakai satu public key tunggal (pola paling umum), `kid` **boleh diabaikan sepenuhnya** — tidak mengubah cara verifikasi token yang sudah berjalan.

### 2.6 Payload JWT Lengkap

Setelah didekode, `access_token` berisi:

```json
{
  "sub": "019fa1b1-22ac-73b6-80dc-c9f42dd5d299",
  "nama": "Muhammad Ilfan Asykuri",
  "role": "admin",
  "role_nama": "Admin",
  "permissions": ["*"],
  "unit_organisasi_id": "019fb8b3-3b27-726f-a06b-2c3f3942c3bb",
  "santri_id": "019fa1a4-c3bd-715c-8848-6a5a16b51af1",
  "akses_wali_santri_id": null,
  "jabatan": [
    { "nama": "Kepala Daerah", "unit_organisasi": "Kamtib", "kamar": null }
  ],
  "generation": 0,
  "iat": 1785931750,
  "exp": 1785932050
}
```

| Field | Tipe | Keterangan |
|---|---|---|
| `sub` | uuid | ID akun (`users.id`) — identitas utama pemegang token |
| `nama` | string | Nama tampilan siap pakai. Diambil dari `santri.nama` (akun pengurus) → `wali_santri.nama` (akun portal wali) → email (fallback terakhir, mis. akun admin murni tanpa data santri/wali) |
| `role` | string | Slug role (mis. `kepala-daerah`, `pengurus-wilayah`, `wali-santri`, `admin`) — dipakai untuk logika kondisional di kode |
| `role_nama` | string | Nama role yang enak dibaca (mis. `"Kepala Daerah"`) — dipakai untuk **tampilan UI**, jangan hardcode terjemahan sendiri |
| `permissions` | array\<string\> | Daftar `"modul.aksi"` yang dimiliki role ini (mis. `"santri.view"`). Role `admin` selalu dapat sentinel `["*"]` (akses penuh) |
| `unit_organisasi_id` | uuid\|null | Unit organisasi **pribadi** santri pemilik akun (kalau akun pengurus) — **BUKAN** wilayah kerja jabatannya, lihat catatan di bawah |
| `santri_id` | uuid\|null | Terisi kalau akun ini **milik santri sendiri** (pengurus, login pakai username) |
| `akses_wali_santri_id` | uuid\|null | Terisi kalau akun ini **portal wali** — nilainya adalah ID santri yang diwakili (login pakai NIUP santri, bukan NIUP wali) |
| `jabatan` | array\<object\> | Semua jabatan yang **sedang aktif** dipegang santri pemilik akun. Array kosong `[]` kalau bukan pengurus atau sedang tidak menjabat apa pun. Tiap entri: `{nama, unit_organisasi, kamar}` (salah satu dari `unit_organisasi`/`kamar` akan `null` tergantung lingkup jabatannya) |
| `generation` | int | **Murni informasional, sejak 2026-09-15** — counter yang dibump tiap user logout. **JANGAN dicek/divalidasi sendiri oleh aplikasi kalian** — Core Service TIDAK PERNAH mencocokkan field ini saat verifikasi access token (baik miliknya sendiri maupun yang kalian lakukan via RS256), invalidasi lintas sesi terjadi di titik `/auth/refresh`, bukan di titik ini. Aman diabaikan sepenuhnya. |
| `iat` / `exp` | int (unix timestamp) | Diterbitkan / kedaluwarsa. Access token **cuma berumur 5 menit** — WAJIB refresh berkala, jangan simpan lama-lama |

> ⚠️ **Catatan penting soal `unit_organisasi_id`:** field ini adalah unit organisasi **pribadi santri** (mis. program pendidikan/daerah tempat dia sendiri tinggal), **BUKAN** unit yang dia **awasi secara jabatan**. Untuk tahu wilayah kerja administratif seorang pengurus (mis. "Kepala Daerah ini bertanggung jawab atas unit mana"), gunakan field `jabatan[].unit_organisasi` di atas, atau panggil `GET /api/jabatan?unit_organisasi_id=...` untuk arah sebaliknya (siapa yang menjabat di unit X).

### 2.6 Logout

`POST /logout` (dari browser, form Core Service) — invalidate refresh token pemilik sesi. Tidak ada endpoint logout server-to-server terpisah; kalau aplikasi kalian ingin "logout global", arahkan pengguna ke `https://sso.zabitsa.site/logout`.

> **Sejak 2026-09-15, logout ini juga mematikan refresh token yang sudah didelegasikan ke aplikasi kalian** (hasil `/auth/token-exchange`), bukan cuma sesi portal — lihat §2.3 untuk detail lengkap (jendela sisa maksimal `access_ttl`, respons `401` generik yang sama seperti kadaluarsa biasa, tidak ada perubahan kontrak di sisi kalian).

---

## 3. REST API Data Master (Service Key)

Semua endpoint di bagian ini **wajib** header:

```http
X-Service-Key: <api_key_kalian>
Accept: application/json
```

Kunci didapat dari Admin Core Service lewat menu **Service Key**. Satu key = satu sistem (`nama_service`), bisa dicabut/di-nonaktifkan kapan saja dari panel Admin.

### 3.1 Format Respons Umum

**Daftar (paginated)** — memakai format bawaan Laravel paginator:
```json
{
  "current_page": 1,
  "data": [ /* array item */ ],
  "first_page_url": "...",
  "from": 1,
  "last_page": 23,
  "last_page_url": "...",
  "links": [ /* ... */ ],
  "next_page_url": "http://.../api/santri?page=2",
  "path": "http://.../api/santri",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 45
}
```
Parameter umum: `?page=1&per_page=20` (default `per_page` bervariasi per endpoint, lihat masing-masing).

**Item tunggal / daftar tanpa paginasi** — langsung array/objek JSON, tanpa pembungkus.

### 3.2 Format Error Umum

| Status | Kapan | Contoh body |
|---|---|---|
| `401` | Header `X-Service-Key` tidak ada, atau key tidak valid/nonaktif | `{"message": "Header X-Service-Key wajib diisi."}` atau `{"message": "API key tidak valid."}` |
| `403` | Key valid tapi tidak punya kapabilitas untuk aksi ini (mis. coba `POST riwayat-domisili` tanpa hak tulis) | `{"message": "Service 'sistem2' tidak punya akses untuk aksi ini."}` |
| `404` | Resource (ID) tidak ditemukan | `{"message": "No query results for model [App\\Models\\Santri] <uuid>"}` |
| `422` | Validasi gagal (endpoint tulis) | `{"message": "The given data was invalid.", "errors": {"field": ["pesan"]}}` |
| `429` | Rate limit terlampaui | `{"message": "Terlalu banyak permintaan. Silakan coba lagi beberapa saat lagi."}` |

### 3.3 Rate Limit

**120 request/menit per Service Key** (bukan per IP — semua sistem kalian boleh memanggil dari server yang sama tanpa saling menghabiskan jatah). Header `X-RateLimit-Limit` / `X-RateLimit-Remaining` disertakan di setiap respons supaya kalian bisa memantau sisa jatah sendiri.

Rate limit lain yang relevan buat integrasi (bukan bagian dari REST API data master, tapi tetap dipanggil sistem kalian):

| Endpoint | Batas | Kunci |
|---|---|---|
| `POST /login` | 5/menit | IP + NIUP |
| `POST /auth/refresh`, `POST /auth/token-exchange` | 30/menit | IP |

Endpoint-endpoint ini **tidak** menyertakan header `X-RateLimit-*` (sengaja, supaya penyerang tidak tahu sisa jatah percobaan login) — hanya `Retry-After` saat kena `429`.

### 3.4 CORS

Header CORS **hanya** diaktifkan untuk path `api/*` (endpoint di bagian ini) — `supports_credentials` dikunci `false`, dan origin yang diizinkan dibatasi ke subdomain resmi `*.zabitsa.site` (`config/cors.php`, override lewat `CORS_ALLOWED_ORIGINS` di server Core Service). Kalau aplikasi kalian punya komponen browser yang memanggil REST API ini **langsung dari JavaScript** (bukan lewat backend kalian), pastikan origin kalian sudah masuk daftar — hubungi tim Core Service kalau perlu ditambahkan. Endpoint berbasis cookie (`/login`, `/auth/*`, `/admin/*`) **sengaja tidak** diberi header CORS sama sekali dan tidak dimaksudkan dipanggil lintas origin dari browser.

---

## 4. Endpoint: Santri

### `GET /api/santri`

Daftar santri, dengan filter & pencarian.

**Query params:**
| Param | Tipe | Keterangan |
|---|---|---|
| `search` | string | Cocok di `nama` **atau** `niup` (LIKE, case-insensitive tergantung collation DB) |
| `status` | string | `aktif` atau `alumni` |
| `unit_organisasi_id` | uuid | Filter unit organisasi (persis, bukan termasuk turunan) |
| `kamar_id` | uuid | Filter kamar |
| `per_page` | int | Default `20` |
| `page` | int | Default `1` |

**Contoh:**
```http
GET /api/santri?search=Ahmad&status=aktif&per_page=20
X-Service-Key: <key>
```

**Response 200** (`data[]`, tiap item):
```json
{
  "id": "019fcd30-2853-73ed-a425-9e962b920e4c",
  "niup": "12320718026",
  "nis": "2320717853",
  "nik": "3515161307110001",
  "nama": "Ahmad Syarif Hidayatullah",
  "jenis_kelamin": "L",
  "tempat_lahir": "Probolinggo",
  "tanggal_lahir": "2011-07-12T17:00:00.000000Z",
  "alamat_asal": "Dusun Kentrung RT/RW : 001/002 Ketompen Pajarakan Probolinggo",
  "kecamatan_id": "019fb8a1-5b7c-7347-aa6d-f84e69427bcf",
  "foto_url": null,
  "foto_path": "santri/ba06a062-99b4-4179-b0b5-b48ac812e8e0/foto.jpg",
  "status": "aktif",
  "kamar_id": "019fcd6b-9a67-73c4-9df0-a2f2a7cef4da",
  "unit_organisasi_id": "019fb8b3-3ade-7309-ae95-6bbc137eab15",
  "tanggal_masuk": "2023-06-17T17:00:00.000000Z",
  "tanggal_keluar": null,
  "pedatren_id": "ba06a062-99b4-4179-b0b5-b48ac812e8e0",
  "sumber_data": "pedatren",
  "synced_at": "2026-08-04T23:54:26.000000Z",
  "created_at": "2026-08-04T14:31:53.000000Z",
  "updated_at": "2026-08-05T00:27:22.000000Z",
  "deleted_at": null,
  "kamar": { "id": "...", "nama": "A.8 (Ali Bin Abi Thalib)", "daerah_pendidikan_id": "...", "is_active": true, "...": "..." },
  "unit_organisasi": { "id": "...", "nama": "Tahsinul Qiro'ah", "tipe": "daerah_pendidikan", "parent_id": "...", "...": "..." },
  "kecamatan": {
    "id": "019fb8a1-5b7c-7347-aa6d-f84e69427bcf",
    "nama": "Pejarakan",
    "tipe": "kecamatan",
    "parent_id": "...",
    "pedatren_id": "...",
    "parent": {
      "id": "...",
      "nama": "Probolinggo",
      "tipe": "kabupaten",
      "parent_id": "...",
      "pedatren_id": "...",
      "parent": {
        "id": "...",
        "nama": "Jawa Timur",
        "tipe": "provinsi",
        "parent_id": "...",
        "pedatren_id": "...",
        "parent": {
          "id": "...",
          "nama": "Indonesia",
          "tipe": "negara",
          "parent_id": null,
          "pedatren_id": "...",
          "parent": null
        }
      }
    }
  },
  "pendidikan_aktif": [
    {
      "id": "019fcde4-9bdb-70a6-bb8f-468e40eaf881",
      "santri_id": "019fcd30-2853-73ed-a425-9e962b920e4c",
      "lembaga": "SMP-NJ",
      "id_lembaga_pedatren": "22",
      "jurusan": "Reguler",
      "kelas": "VII",
      "rombel": "C",
      "tanggal_mulai": "2026-07-02T17:00:00.000000Z",
      "tanggal_akhir": null,
      "pedatren_id": "31936",
      "sumber_data": "pedatren"
    }
  ],
  "program_aktif": [
    {
      "id": "019fdb55-d41d-7167-8809-4320cf14654b",
      "santri_id": "019fcd30-2853-73ed-a425-9e962b920e4c",
      "program_id": "019fb653-74dd-7370-ae88-257e197190ba",
      "program_angkatan_id": "019fdd81-2233-70a7-9991-62b8ac724186",
      "jenis": "peserta",
      "tanggal_mulai": "2026-06-30T17:00:00.000000Z",
      "tanggal_selesai": null,
      "keterangan": null,
      "program": { "id": "019fb653-74dd-7370-ae88-257e197190ba", "nama": "Sifir", "keterangan": null, "target_bulan": 3, "is_active": true },
      "program_angkatan": { "id": "019fdd81-2233-70a7-9991-62b8ac724186", "program_id": "019fb653-74dd-7370-ae88-257e197190ba", "nama": "Angkatan 1", "tanggal_mulai": "2026-06-30T17:00:00.000000Z", "tanggal_selesai": null, "keterangan": null }
    }
  ],
  "foto_signed_url": "http://.../media/tertanda/santri/019fcd30.../foto?expires=1785935542&signature=..."
}
```

> **Jangan pakai `foto_url` maupun `foto_path` langsung** — keduanya bukan URL yang bisa diakses browser (`foto_url` biasanya `null`, `foto_path` adalah path internal ke disk privat). **Selalu pakai `foto_signed_url`** untuk `<img src>` — sudah ditandatangani, berlaku 1 jam, dan otomatis `null` kalau santri belum punya foto sama sekali.
>
> **Signature-nya SUDAH CUKUP jadi bukti akses — tidak butuh sesi login Core Service apa pun untuk memakainya.** Aman ditempel langsung sebagai `<img src>` di halaman kalian dan diminta oleh BROWSER pengguna akhir (bukan server kalian) — permintaan itu TIDAK akan pernah dicek ulang terhadap sesi/izin login siapa pun, satu-satunya syarat adalah signature valid & belum melewati `expires`. **Implikasinya: siapa pun yang memegang URL ini (selama belum expired) bisa mengaksesnya** — jangan dicatat/dibagikan di tempat publik (log, screenshot, tautan permanen), teruskan hanya ke pengguna yang memang berhak melihatnya (keputusan itu ada di tangan kalian sebagai pemanggil API, bukan di rute media Core Service ini).

#### `kecamatan` — rantai wilayah berjenjang **BARU**

`kecamatan_id` sendiri (field flat, UUID) sudah lama ada, tapi sebelumnya tidak bisa diterjemahkan lewat API — konsumen hanya dapat ID mentah tanpa cara mengetahui nama kecamatan/kabupaten/provinsinya. Sekarang objek `kecamatan` disematkan langsung berisi rantai lengkap sampai ke negara, dibaca dengan menelusuri `parent` berulang sampai `null`:

- Setiap level punya field `tipe` (`kecamatan`/`kabupaten`/`provinsi`/`negara`) — **pakai ini untuk mengidentifikasi level, bukan kedalaman nesting**, supaya kode kalian tidak salah asumsi kalau suatu saat rantainya lebih pendek.
- `kecamatan` bisa bernilai **`null`** kalau data wilayah santri itu belum pernah ter-resolve dari Pedatren (mis. santri input manual tanpa data alamat lengkap) — selalu cek null sebelum menelusuri `parent`.
- Ini tabel yang **tumbuh organik**, bukan referensi wilayah Indonesia yang lengkap — hanya berisi wilayah yang benar-benar pernah muncul di data santri/wali yang tersinkron dari Pedatren. Jangan jadikan sumber pencarian wilayah umum di luar konteks data santri.
- Field bebas `alamat_asal` (di atas) dan `wali_santri.alamat` (§5) **tidak dirangkai dari `kecamatan`** — keduanya teks bebas apa adanya dari Pedatren, formatnya tidak selalu konsisten mencantumkan label "Kec./Kab./Prov.". Kalau butuh label wilayah yang konsisten untuk dicetak (mis. surat resmi), susun sendiri dari objek `kecamatan` ini, bukan dari field teks bebas tersebut.

#### `pendidikan_aktif` & `program_aktif` **BARU**

Dua array baru, disematkan langsung di respons ini (tanpa panggilan terpisah) — dibuat khusus supaya konsumen yang memproses BANYAK santri sekaligus (mis. alur cetak surat massal) tidak perlu memanggil sistem lain satu-satu per santri hanya demi data pendidikan/program.

- **`pendidikan_aktif`** — baris `pendidikan_formal` (sekolah/madrasah reguler hasil sinkronisasi Pedatren) yang **masih berjalan** (`tanggal_akhir` masih `null`). `lembaga` adalah nama institusinya (mis. `"SMP-NJ"`, `"UNUJA"`) — bukan ID, sudah nama siap pakai. Array **bisa berisi lebih dari satu baris** — satu santri boleh terdaftar di lebih dari satu pendidikan formal sekaligus (data nyata) — dan **bisa array kosong `[]`** kalau tidak ada yang sedang berjalan (BUKAN `null`, selalu array).
- **`program_aktif`** — baris `riwayat_program` (program diniyah pondok, mis. Tahfidz, Kaligrafi) yang **masih berjalan** (`tanggal_selesai` masih `null`), masing-masing sudah membawa objek `program` (nama program) dan `program_angkatan` (kelompok angkatan, bisa `null` kalau penugasannya tidak terhubung ke angkatan). Array **bisa berisi lebih dari satu baris** — satu santri boleh aktif di lebih dari satu program berbeda sekaligus (mis. Tahfidz + Kaligrafi bersamaan, atau `jenis: "peserta"` di satu program + `jenis: "pengurus"` di program lain, keduanya data nyata) — dan **bisa array kosong `[]`** kalau tidak ada yang sedang diikuti.
- **Beda dari `GET /api/santri/{id}/riwayat-program`** (§4 di bawah): endpoint terpisah itu mengembalikan **SELURUH riwayat** (termasuk yang sudah selesai), sedangkan `program_aktif` di sini **HANYA yang masih berjalan**. Field-nya sengaja diberi nama BEDA (`program_aktif`, bukan `riwayat_program`) justru supaya keduanya tidak tertukar — kalau butuh histori lengkap satu santri, tetap pakai endpoint `riwayat-program` terpisah.

### `GET /api/santri/{id}`

Detail satu santri — field sama seperti di atas (termasuk `kecamatan`, `pendidikan_aktif`, `program_aktif`), PLUS relasi `wali_santri` (daftar lengkap wali/ortu, masing-masing dengan `kecamatan`-nya sendiri, lihat §5) ikut di-*load*.

### `GET /api/santri/{id}/foto`

Unduh berkas foto langsung, **server-to-server** (pakai Service Key). Untuk ditampilkan ke pengguna akhir di halaman kalian, **gunakan `foto_signed_url`**, bukan endpoint ini — endpoint ini akan menolak request browser biasa kalau tidak membawa Service Key yang valid.

Response: binary image (`Content-Type: image/...`) atau `404` kalau belum ada foto.

### `GET /api/santri/{id}/wali-santri` — **BARU**

Daftar wali (ayah/ibu/wali lain) satu santri, lengkap kontak & alamat — untuk sistem yang perlu menghubungi/memberitahu wali (mis. **persetujuan izin pulang di Perizinan Santri**).

**Response 200:**
```json
[
  {
    "id": "019fcdb0-62f9-7173-a2f6-72a7e1f65566",
    "nama": "Uswatun Hasanah",
    "niup": null,
    "hubungan": "ibu",
    "nik": "3513165511920003",
    "kontak_telepon": "+6281360998688",
    "kontak_whatsapp": null,
    "alamat": "Dusun Kentrung RT 001 RW 022 Desa Ketompen Kec. Pejarakan Kab. Probolinggo Prov. Jawa Timur",
    "kecamatan_id": "019fb8a1-5b7c-7347-aa6d-f84e69427bcf",
    "kecamatan": {
      "id": "019fb8a1-5b7c-7347-aa6d-f84e69427bcf",
      "nama": "Pejarakan",
      "tipe": "kecamatan",
      "parent_id": "...",
      "pedatren_id": "...",
      "parent": { "id": "...", "nama": "Probolinggo", "tipe": "kabupaten", "parent_id": "...", "pedatren_id": "...", "parent": { "...": "provinsi & negara, struktur sama seperti §4" } }
    },
    "pekerjaan": "Wiraswasta",
    "pedatren_id": "1e51f51e-295a-40d7-abd0-460e80c5748f",
    "sumber_data": "pedatren",
    "pivot": {
      "santri_id": "019fcd30-2853-73ed-a425-9e962b920e4c",
      "wali_santri_id": "019fcdb0-62f9-7173-a2f6-72a7e1f65566",
      "is_wali_utama": true
    }
  }
]
```
Diurutkan: wali utama (`is_wali_utama: true`) muncul lebih dulu. `hubungan` bernilai salah satu dari `ayah`, `ibu`, `wali_lain`. `kontak_telepon`/`kontak_whatsapp`/`niup` bisa `null` kalau belum diisi datanya di Pedatren. `kecamatan` mengikuti struktur berjenjang yang sama persis dengan milik santri (lihat penjelasan di §4) — **alamat wali dan alamat santri bisa merujuk kecamatan yang BERBEDA**, masing-masing di-resolve dari `kecamatan_id` miliknya sendiri.

### `GET /api/santri/{id}/riwayat-jabatan`

Histori lengkap pelantikan/pemberhentian jabatan santri ini (array, urut terbaru dulu), tiap entri sudah membawa relasi `jabatan`, `unit_organisasi`, `kamar`. `tanggal_selesai: null` = masih menjabat.

### `GET /api/santri/{id}/riwayat-program`

Histori **LENGKAP** keikutsertaan program diniyah (Tahfidz, Kaligrafi, dst) — dipakai **Sistem Pelaporan Capaian Santri**. Tiap entri membawa relasi `program` dan `program_angkatan`. `tanggal_selesai: null` = masih aktif di program itu.

> Cuma butuh yang **sedang berjalan saat ini** (bukan histori lengkap), untuk BANYAK santri sekaligus? Pakai field `program_aktif` yang sudah disematkan langsung di `GET /api/santri`/`GET /api/santri/{id}` (§4 di atas) — tidak perlu memanggil endpoint ini satu-satu per santri.

**BARU:** setiap penugasan bisa terhubung ke satu **Angkatan** (`program_angkatan`) — kelompok santri yang masuk ke program itu bersamaan (mis. beberapa santri mulai Tahsin di Program Awaliyah bareng). `program_angkatan_id`/`program_angkatan` bernilai `null` kalau penugasan itu dibuat satuan tanpa memilih Angkatan. Perhatikan nama kuncinya **snake_case** (`program_angkatan`, bukan `programAngkatan`) — mengikuti konversi otomatis Eloquent, sama seperti field lain di respons ini.

**Response 200:**
```json
[
  {
    "id": "019fdd01-edf5-72d8-86cf-69540ffb6e4a",
    "santri_id": "019fcd30-2853-73ed-a425-9e962b920e4c",
    "program_id": "019fb653-b3f5-719e-88bf-64ed9e069cb5",
    "program_angkatan_id": "019fdd00-eb10-71a0-a307-1c67ba256773",
    "jenis": "peserta",
    "tanggal_mulai": "2026-08-07",
    "tanggal_selesai": null,
    "keterangan": null,
    "program": { "id": "019fb653-b3f5-719e-88bf-64ed9e069cb5", "nama": "Awaliyah", "keterangan": "...", "target_bulan": 6 },
    "program_angkatan": {
      "id": "019fdd00-eb10-71a0-a307-1c67ba256773",
      "program_id": "019fb653-b3f5-719e-88bf-64ed9e069cb5",
      "nama": "Angkatan 12",
      "tanggal_mulai": "2026-08-07",
      "tanggal_selesai": null,
      "keterangan": null
    }
  }
]
```
Kalau `program_angkatan_id` bernilai `null` (penugasan satuan tanpa Angkatan), `program_angkatan` ikut bernilai `null` juga.

### `GET /api/santri/{id}/riwayat-domisili`

Histori perpindahan daerah/kamar santri ini (array, urut terbaru dulu).

### `POST /api/santri/{id}/riwayat-domisili` — **khusus Service Key berkapabilitas `can_write_riwayat_domisili`**

Mencatat perpindahan domisili baru (dipakai Sistem 1 saat modul Pelaporan mencatat perpindahan langsung dari sistemnya).

```json
{
  "unit_organisasi_id": "uuid",       // wajib
  "kamar_id": "uuid atau null",       // opsional
  "tanggal_mulai": "2026-08-05",      // wajib
  "keterangan": "string atau null"    // opsional
}
```
Response `201` — baris riwayat_domisili yang baru dibuat. **Efek samping:** kolom snapshot `santri.kamar_id`/`santri.unit_organisasi_id` ikut diperbarui otomatis dalam transaksi yang sama.

### `PUT /api/riwayat-domisili/{id}` — **khusus Service Key berkapabilitas `can_write_riwayat_domisili`**

Koreksi baris riwayat domisili yang sudah ada. Semua field `sometimes` (opsional, kirim yang mau diubah saja):
```json
{
  "unit_organisasi_id": "uuid",
  "kamar_id": "uuid atau null",
  "tanggal_mulai": "2026-08-05",
  "tanggal_selesai": "2026-08-10 atau null",
  "keterangan": "string atau null"
}
```

---

## 5. Endpoint: Wali Santri (via Santri)

Tidak ada `GET /api/wali-santri` mandiri — akses wali santri **selalu lewat santri terkait**: `GET /api/santri/{id}/wali-santri` (§4). Ini disengaja: wali santri baru berarti sesuatu kalau terhubung ke santri tertentu.

---

## 6. Endpoint: Program

### `GET /api/program`

Katalog program diniyah yang aktif (Tahfidz, Kaligrafi, dst). Di-cache 1 jam, otomatis dibuang begitu ada perubahan lewat panel Admin.

**Response 200** (array, tanpa paginasi):
```json
[
  { "id": "uuid", "nama": "Tahfidz Al-Qur'an", "keterangan": "...", "target_bulan": 24 }
]
```

---

## 7. Endpoint: Unit Organisasi

### `GET /api/unit-organisasi`

Struktur organisasi **lengkap berbentuk tree** (bukan flat list + paginasi). Di-cache 1 jam.

**Response 200:**
```json
[
  {
    "id": "uuid-wilayah",
    "nama": "Pengurus Wilayah",
    "tipe": "wilayah",
    "kode": null,
    "is_active": true,
    "children": [
      {
        "id": "uuid-daerah",
        "nama": "Kamtib",
        "tipe": "daerah_pembantu",
        "kode": null,
        "is_active": true,
        "children": []
      }
    ]
  }
]
```
`tipe` adalah kode mentah (mis. `daerah_pembantu`, `daerah_pendidikan`, `wilayah`, `sektor`, `bkwa`, `unit_usaha`) — tipe BUKAN daftar tetap, bisa bertambah lewat panel Admin (menu **Tipe Unit Organisasi**), jadi jangan hardcode enum di sisi kalian.

---

## 8. Endpoint: Kamar

### `GET /api/kamar`

**Query params:** `daerah_pendidikan_id` (uuid, filter), `per_page` (default `50`), `page`.

Paginated, tiap item field mentah tabel `kamar` (`id`, `nama`, `daerah_pendidikan_id`, `kapasitas`, `keterangan`, `is_active`, `pedatren_id`, `sumber_data`, `synced_at`, timestamps).

---

## 9. Endpoint: Jabatan (Roster Pengurus Aktif)

### `GET /api/jabatan`

⚠️ **BUKAN** katalog statis tabel `jabatan` — ini adalah **roster siapa yang SEDANG menjabat** (`riwayat_jabatan` dengan `tanggal_selesai IS NULL`). Berguna untuk, misalnya, "siapa Kepala Daerah unit X sekarang" saat mau merutekan persetujuan izin.

**Query params:** `unit_organisasi_id`, `kamar_id` (salah satu atau keduanya), `per_page` (default `50`), `page`.

**Response 200** (paginated), tiap item riwayat_jabatan lengkap relasi `santri`, `jabatan`, `unit_organisasi`, `kamar`:
```json
{
  "data": [
    {
      "id": "uuid",
      "santri_id": "uuid",
      "jabatan_id": "uuid",
      "unit_organisasi_id": "uuid",
      "kamar_id": null,
      "tanggal_mulai": "2025-01-01",
      "tanggal_selesai": null,
      "santri": { "id": "...", "nama": "...", "niup": "..." },
      "jabatan": { "id": "...", "nama": "Kepala Daerah", "lingkup": "unit_organisasi", "tingkat_hierarki": 3 },
      "unit_organisasi": { "id": "...", "nama": "Kamtib" },
      "kamar": null
    }
  ]
}
```
Di-cache per kombinasi filter, otomatis invalid begitu ada pelantikan/pemberhentian baru.

---

## 10. Endpoint: Roles — **BARU**

### `GET /api/roles`

Katalog role aktif — untuk menerjemahkan `role` (slug, dari payload JWT §2.6) jadi nama yang enak dibaca **tanpa hardcode tabel terjemahan sendiri**.

**Response 200** (array, tanpa paginasi):
```json
[
  { "id": "uuid", "nama": "Admin", "slug": "admin" },
  { "id": "uuid", "nama": "Kepala Daerah", "slug": "kepala-daerah" },
  { "id": "uuid", "nama": "Pengurus Wilayah", "slug": "pengurus-wilayah" }
]
```
> Catatan: payload JWT sudah menyematkan `role_nama` langsung (§2.6) — endpoint ini untuk kasus lain, mis. menampilkan daftar semua role yang mungkin (dropdown penerima notifikasi, dsb), bukan untuk kebutuhan "role milik token yang sedang login" (itu sudah cukup dari JWT-nya sendiri).

---

## 11. Endpoint: Notifikasi — **BARU**

### `POST /api/notifikasi` — **khusus Service Key berkapabilitas `can_kirim_notifikasi`**

Push notifikasi masuk ke Dashboard SSO (Gerbang Digital) — tampil di bell notifikasi milik penerima. Kapabilitas ini **tidak aktif secara default**; minta Admin Core Service mengaktifkan `can_kirim_notifikasi` di Service Key kalian dulu lewat panel Admin → Service Key.

```json
{
  "judul": "string, wajib, maks 150 karakter",
  "pesan": "string, wajib, maks 1000 karakter",
  "kategori": "string, opsional — default 'aplikasi_eksternal' kalau tidak diisi",
  "url_tujuan": "url, opsional — dibuka saat notifikasi diklik",
  "target": {
    "tipe": "user | role | semua — wajib",
    "niup": "string — salah satu (dengan user_id) kalau tipe=user",
    "user_id": "uuid Core Service — salah satu (dengan niup) kalau tipe=user",
    "role_slug": "string — wajib kalau tipe=role, lihat GET /api/roles (§10)"
  }
}
```

Kategori yang tersedia: `jabatan`, `akun`, `sinkronisasi`, `akses_aplikasi`, `pengumuman`, `aplikasi_eksternal`.

**Response `201`** — baris notifikasi yang baru dibuat. `sumber` di database SELALU diisi otomatis dari nama Service Key kalian (bukan dari field di body) — satu sistem tidak bisa menyamar mengirim atas nama sistem lain.

**Response `422`** — target tidak ditemukan (niup/user_id/role_slug tidak cocok akun/role yang ada) atau kategori di luar daftar.

**Contoh — beri tahu wali santri lewat NIUP santrinya:**
```json
{
  "judul": "Izin pulang disetujui",
  "pesan": "Pengajuan izin pulang untuk Ahmad telah disetujui Wali Asuh.",
  "kategori": "aplikasi_eksternal",
  "url_tujuan": "https://larizan.zabitsa.site/perizinan/123",
  "target": { "tipe": "user", "niup": "30225381026" }
}
```

---

## 12. Ringkasan Semua Endpoint

| Method | Path | Auth | Keterangan |
|---|---|---|---|
| `POST` | `/auth/token-exchange` | client_id + client_secret | Tukar authorization code → token pair |
| `POST` | `/auth/refresh` | client_id + client_secret | Perpanjang access token, rotasi refresh token |
| `GET` | `/auth/public-key` | — | Ambil public key verifikasi JWT (PEM) |
| `GET` | `/auth/jwks` | — | Ambil public key format JWK **(baru)** |
| `GET` | `/api/santri` | Service Key | Daftar santri (search, filter) |
| `GET` | `/api/santri/{id}` | Service Key | Detail satu santri |
| `GET` | `/api/santri/{id}/foto` | Service Key | Unduh foto (server-to-server) |
| `GET` | `/api/santri/{id}/wali-santri` | Service Key | Daftar wali santri ini **(baru)** |
| `GET` | `/api/santri/{id}/riwayat-jabatan` | Service Key | Histori jabatan santri ini |
| `GET` | `/api/santri/{id}/riwayat-program` | Service Key | Histori program santri ini |
| `GET` | `/api/santri/{id}/riwayat-domisili` | Service Key | Histori domisili santri ini |
| `POST` | `/api/santri/{id}/riwayat-domisili` | Service Key + `can_write_riwayat_domisili` | Catat perpindahan baru |
| `PUT` | `/api/riwayat-domisili/{id}` | Service Key + `can_write_riwayat_domisili` | Koreksi riwayat domisili |
| `GET` | `/api/program` | Service Key | Katalog program |
| `GET` | `/api/unit-organisasi` | Service Key | Struktur organisasi (tree) |
| `GET` | `/api/kamar` | Service Key | Daftar kamar |
| `GET` | `/api/jabatan` | Service Key | Roster pengurus aktif |
| `GET` | `/api/roles` | Service Key | Katalog role **(baru)** |
| `POST` | `/api/notifikasi` | Service Key + `can_kirim_notifikasi` | Push notifikasi ke Dashboard SSO **(baru)** |

---

## 13. Catatan untuk Sistem Perizinan Santri (dan sistem serupa)

Kalau alur kalian butuh:
- **Foto santri untuk ditampilkan** → pakai `foto_signed_url` dari `GET /api/santri` (sudah ada di daftar, tidak perlu panggil `show()` satu-satu lagi).
- **Nama/role/jabatan pengguna yang sedang login** → sudah lengkap di payload JWT (§2.6), tidak perlu call-back apa pun ke Core Service.
- **Kontak wali untuk approval izin** → `GET /api/santri/{id}/wali-santri` (baru).
- **Siapa atasan yang berwenang menyetujui** (mis. Kabag Daerah/Wali Asuh unit tempat santri berada) → `GET /api/jabatan?unit_organisasi_id=...` atau `?kamar_id=...`, ambil baris pertama (atau semua kalau `multi_slot`).
- **Riwayat izin/perpindahan sebelumnya terkait domisili** → `GET /api/santri/{id}/riwayat-domisili`.
- **Memberi tahu santri/wali kalau pengajuan izin disetujui/ditolak** → `POST /api/notifikasi` (§11, minta kapabilitas `can_kirim_notifikasi` diaktifkan dulu) — muncul di bell Dashboard SSO milik yang bersangkutan, `target.niup` cukup pakai NIUP santri yang sudah kalian punya.

Kalau ternyata masih ada data yang dibutuhkan tapi belum tersedia lewat API manapun di atas, jangan buat workaround/tebak-tebak di sisi kalian — laporkan field/endpoint apa yang kurang, supaya bisa ditambahkan resmi di sini.

---

## 14. Modul yang Sengaja TIDAK Diekspos

- **Kuesioner/Survei** — murni fitur internal Core Service, diisi lewat Dashboard SSO sendiri oleh pengguna terkait. Tidak ada endpoint publik untuk membaca/menulis data kuesioner, karena tidak ada kebutuhan lintas sistem atas data ini.
- **Log sistem** (`activity_logs`, `error_logs`, `api_access_logs`) — internal audit Core Service, tidak diekspos ke Sistem 1-5 lewat API apa pun.
- **Konfigurasi integrasi Pedatren** — kredensial & pengaturan sinkronisasi murni internal, tidak ada endpoint publik untuknya.

Kalau sistem kalian ternyata butuh salah satu di atas untuk kasus penggunaan yang sah, diskusikan dengan tim Core Service — jangan mengakses database secara langsung.
