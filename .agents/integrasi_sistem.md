# Panduan Integrasi Sistem dengan Core Service

Dokumen ini untuk **tim pengembang sistem lain** (DAFTARU, KURIZA, LARIZAN, SIKESAN, RAPORIS, atau sistem masa depan lainnya di ekosistem Wilayah Zaid bin Tsabit Putra) yang ingin memakai **Core Service** (`sso.zabitsa.site`, "Gerbang Digital") sebagai penyedia SSO dan sumber data master tunggal.

Untuk referensi lengkap tiap endpoint (parameter, contoh request/response, kode error), lihat **`API_DOCUMENTATION.md`** — dokumen ini fokus pada **cara mengintegrasikan**, bukan katalog endpoint.

---

## 1. Dari Sisi Core Service (Apa yang Disediakan & Dijamin)

Core Service berperan sebagai **identity provider + sumber data master tunggal**. Sistem kalian **tidak boleh** punya tabel password sendiri, tabel santri/wali/unit organisasi duplikat, atau logika otorisasi role yang lepas dari data Core Service.

| Yang disediakan | Detail |
|---|---|
| Autentikasi terpusat | Satu form login (NIUP + password) untuk seluruh ekosistem |
| Token terverifikasi mandiri | JWT RS256 — sistem kalian cukup simpan public key (§3) untuk verifikasi tanda tangan **tanpa call-back** ke Core Service tiap request |
| Otorisasi granular di dalam token | Klaim `permissions` (format `"modul.aksi"`) sudah tersemat di JWT — cek lokal, tidak perlu tanya balik (§4) |
| Data master tunggal | Santri, Wali Santri, Unit Organisasi, Kamar, Jabatan, Program, Role — satu sumber kebenaran lewat REST API, supaya sistem kalian tidak duplikat/berselisih data |
| Shared session lintas subdomain | Cookie SSO di domain induk `.zabitsa.site` — user login sekali, otomatis "nyambung" ke semua aplikasi di bawah domain itu |
| Fallback lintas domain | Authorization code exchange (§5) untuk aplikasi yang tidak berada di subdomain `.zabitsa.site` |
| Kanal push notifikasi | `POST /api/notifikasi` — kirim notifikasi masuk ke bell Dashboard SSO milik pengguna terkait, tanpa perlu membangun sistem notifikasi sendiri |

**Yang dijamin (kontrak stabil, boleh diandalkan):**
- Struktur klaim JWT (§4) tidak berubah tanpa pemberitahuan — penambahan klaim baru boleh, penghapusan/perubahan makna klaim yang sudah ada dianggap *breaking change*.
- `GET /auth/public-key` selalu mengembalikan public key yang aktif saat ini; keypair tidak diganti diam-diam selama masih ada sistem yang bergantung padanya.
- Kapabilitas tulis (`can_write_riwayat_domisili`, `can_kirim_notifikasi`, dst) hanya diberikan eksplisit per baris `service_keys` — Core Service **tidak pernah** mengasumsikan semua sistem konsumen boleh menulis/mengirim.

**Yang bukan tanggung jawab Core Service (batas tanggung jawab):**
- Core Service tidak menyimpan session/state milik aplikasi kalian — begitu token diverifikasi/ditukar, urusan sesi selanjutnya sepenuhnya di sisi kalian.
- Core Service bukan proxy/gateway untuk request bisnis sistem kalian — hanya identitas, otorisasi, dan data master.
- Pencabutan izin akibat perubahan role/permission **tidak instan** ke sistem kalian (baru berlaku setelah token di-refresh) — lihat kotak peringatan di §4. Kalau sistem kalian butuh kepastian izin real-time, panggil balik API Core Service, jangan mengandalkan klaim token semata.

---

## 2. Langkah 1 — Minta API Key ke Admin Core Service

Setiap aplikasi konsumen butuh satu baris di tabel `service_keys`, dibuat lewat panel **Admin → Integrasi API → Service Key** (Web UI — bukan lagi lewat Tinker/seeder manual).

Saat membuat key baru, Admin memilih kapabilitas yang diberikan ke sistem kalian:

| Kapabilitas | Mengizinkan |
|---|---|
| `can_write_riwayat_domisili` | `POST`/`PUT` ke endpoint riwayat domisili (mencatat/mengoreksi perpindahan santri) |
| `can_kirim_notifikasi` | `POST /api/notifikasi` (mengirim notifikasi ke Dashboard SSO pengguna) |
| `can_register_webhook` | Dicadangkan untuk fase lanjutan, belum ada endpoint yang memakainya saat ini |

Kunci plaintext **hanya ditampilkan sekali** tepat setelah dibuat (session flash, hilang setelah refresh — pola sama seperti Stripe/GitHub). **Catat dan simpan segera** di sisi server aplikasi kalian (mis. `.env`), **tidak pernah** dikirim ke browser/klien. Kalau lupa dicatat, Admin harus mencabut key lama dan membuat yang baru (tidak ada cara melihat ulang plaintext-nya).

> Beri tahu Admin Core Service **nama service** yang jelas dan tetap (`nama_service`, mis. `larizan`, `kuriza`) — kolom ini terkunci setelah dibuat (tidak bisa diganti) karena dipakai sebagai kunci korelasi di `api_access_logs`.

---

## 3. Langkah 2 — Ambil Public Key Sekali, Simpan Lokal

```bash
curl https://sso.zabitsa.site/auth/public-key -o storage/app/zabitsa-public.pem
```

Simpan file ini di server aplikasi kalian. **Tidak perlu** memanggil endpoint ini berulang — cukup sekali saat setup awal (atau di-refresh manual kalau Core Service pernah mengganti keypair, yang akan diberitahukan lebih dulu — lihat jaminan di §1).

---

## 4. Langkah 3 — Verifikasi JWT di Sisi Aplikasi Konsumen

Kalau aplikasi kalian juga berbasis Laravel, contoh middleware verifikasi token (pola sama seperti `VerifyJwtToken` milik Core Service sendiri):

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$token = $request->cookie('zbt_access_token') ?? $request->bearerToken();
$publicKey = file_get_contents(storage_path('app/zabitsa-public.pem'));

try {
    $payload = JWT::decode($token, new Key($publicKey, 'RS256'));
    // $payload->sub                  = uuid user
    // $payload->nama                 = nama tampilan siap pakai
    // $payload->role / role_nama     = slug & label role
    // $payload->permissions          = array "modul.aksi"
    // $payload->unit_organisasi_id   = unit PRIBADI santri pemilik akun (bukan wilayah kerja jabatan)
    // $payload->santri_id / akses_wali_santri_id = salah satu terisi
    // $payload->jabatan              = array jabatan aktif [{nama, unit_organisasi, kamar}]
} catch (\Throwable $e) {
    // token invalid/kadaluarsa → redirect ke flow Langkah 5
}
```

Payload lengkap (contoh nyata + tabel field) ada di `API_DOCUMENTATION.md` §2.6. Poin yang sering salah dipakai:

- `unit_organisasi_id` adalah unit **pribadi** santri (mis. daerah pendidikan tempat dia sendiri belajar), **bukan** wilayah kerja administratif jabatannya. Untuk tahu wilayah kerja seorang pengurus, pakai `jabatan[].unit_organisasi` dari token yang sama, atau panggil `GET /api/jabatan?unit_organisasi_id=...` untuk arah sebaliknya (siapa yang menjabat di unit X).
- Access token **cuma berumur 5 menit** — jangan cache payload lama-lama, dan siapkan alur refresh (§6) atau cukup redirect ulang ke SSO kalau token kedaluwarsa.

### 4.1 Cek Otorisasi Lokal dari Klaim `permissions` (Tanpa Call-Back)

```php
function bolehkan($payload, string $modul, string $aksi): bool
{
    $perms = $payload->permissions ?? [];
    if (in_array('*', $perms, true)) {
        return true; // role admin — sentinel akses penuh
    }
    return in_array("{$modul}.{$aksi}", $perms, true);
}

// contoh pemakaian di controller Sistem 3 (Perizinan):
if (! bolehkan($payload, 'perizinan', 'approve')) {
    abort(403, 'Anda tidak berwenang menyetujui perizinan.');
}
```

`modul`/`aksi` di sini adalah **milik sistem kalian sendiri** (mis. `perizinan.approve`) — Core Service tidak tahu dan tidak perlu tahu daftar modul internal kalian; ia hanya menyimpan & menyematkan apa pun grant yang Admin berikan lewat panel Role kalau kalian minta modul kalian didaftarkan ke sana, ATAU kalian bisa membangun sistem permission sendiri di aplikasi kalian yang independen dari Core Service dan hanya memakai `role`/`role_nama` dari token sebagai dasarnya. Kedua pendekatan valid — sepakati dengan tim Core Service pendekatan mana yang dipakai sistem kalian.

> ⚠️ **Konsekuensi penting (bukan bug):** karena permission dibaca dari isi token, perubahan permission yang dilakukan Admin lewat panel Core Service **tidak langsung berlaku seketika** untuk user yang sedang login di sistem kalian. Perubahan baru tercermin setelah access token user tersebut di-refresh (atau ia login ulang) — paling lambat dalam 5 menit (umur access token). Ini konsekuensi sadar dari desain JWT stateless (supaya sistem kalian tidak perlu call-back ke Core Service tiap request). Jangan laporkan ini sebagai bug; kalau butuh pencabutan izin yang benar-benar seketika, cek izin itu lewat panggilan API real-time ke Core Service, bukan dari klaim token.

---

## 5. Langkah 4 — Alur Redirect Login (Direct Access) + Authorization Code Exchange

Jalur utama = **shared cookie domain induk**. Untuk aplikasi di dalam domain `.zabitsa.site`, cookie SSO otomatis terbaca — **tidak perlu langkah tambahan apa pun**. Alur di bawah ini adalah fallback khusus untuk aplikasi **di luar** domain induk (atau di dalamnya tapi butuh alur eksplisit, mis. saat cookie belum ada).

1. Kalau verifikasi token di Langkah 3 gagal (cookie tidak ada/kadaluarsa), redirect user ke:
   ```
   https://sso.zabitsa.site/login?redirect_to=https://sistemkalian.example.com/halaman-tujuan&state=<nilai_acak_kalian>
   ```
   `state` **opsional tapi disarankan** — proteksi login-CSRF standar (RFC 9700 §10.12). Generate nilai acak di sisi kalian, simpan di sesi kalian sendiri sebelum redirect, cocokkan dengan `state` yang kembali di langkah 2 sebelum mempercayai `code`-nya. Core Service murni mengembalikannya apa adanya, tidak menyimpan/memvalidasi isinya.
2. Setelah user login sukses, Core Service redirect balik ke URL tujuan **membawa authorization code sekali-pakai**, bukan token asli:
   ```
   https://sistemkalian.example.com/halaman-tujuan?code=<authorization-code-acak>&state=<nilai_kalian>
   ```
   (Kalau `redirect_to` tidak dikenali sebagai origin Aplikasi terdaftar & aktif, code **tidak** diterbitkan — user jatuh ke Dashboard Core Service sebagai fallback aman. Lihat §8 soal domain/aplikasi tepercaya — **aplikasi kalian harus terdaftar** di Core Service, bukan cukup berada di subdomain `.zabitsa.site` saja.)

   > **Catatan penting (sejak 2026-09-14)**: kalau user yang di-redirect ke `/login` ini **SUDAH** punya sesi Core Service yang masih valid — baik lewat access token yang belum kedaluwarsa, MAUPUN lewat refresh token yang masih valid (termasuk sesi "Ingat Akun Saya" yang sudah melewati restart browser) — Core Service akan **langsung** memproses langkah 2 (mengembalikan `code`) **tanpa menampilkan form login sama sekali** ke user. Jangan asumsikan user SELALU harus mengetik ulang kredensial di titik ini; alur redirect ini juga berfungsi sebagai pengecekan "apakah user sudah login" yang silent. Sebelum tanggal ini ada gap: sesi "Ingat Akun Saya" yang sudah lewat restart browser SEMPAT salah diminta login ulang di titik ini padahal refresh token-nya masih valid — sudah diperbaiki, disebutkan di sini kalau kalian sempat menulis workaround untuk gap itu di sisi kalian sendiri, boleh dicabut.
3. Aplikasi kalian menukar kode ini **dari sisi server** (server-to-server, **jangan** dari browser) ke `POST /auth/token-exchange`:

```php
$resp = Illuminate\Support\Facades\Http::asJson()->post(
    'https://sso.zabitsa.site/auth/token-exchange',
    [
        'code' => $request->query('code'),
        // WAJIB sejak 2026-08-20 — lihat §9. client_id = modul aplikasi
        // kalian, client_secret diterbitkan Admin lewat Admin → Aplikasi.
        // Tanpa ini, request ditolak 401 sebelum sempat memeriksa `code`.
        'client_id' => config('services.core.client_id'),
        'client_secret' => config('services.core.client_secret'),
    ]
);
// { "access_token": "...", "refresh_token": "...", "token_type": "Bearer", "expires_in": 300 }
$accessToken = $resp->json('access_token');
$refreshToken = $resp->json('refresh_token'); // simpan aman di sisi server kalian
```

**Sifat authorization code (keamanan):**
- Bukan token asli — kode acak yang tidak berguna sendiri; token asli hanya keluar lewat kanal server-to-server, tidak pernah lewat URL browser.
- **Sekali-pakai** — langsung invalid begitu ditukar; percobaan tukar kedua kalinya dengan kode sama gagal `401`.
- Berumur sangat pendek — **maksimal 30 detik**. Lewat itu, user harus login ulang.
- Disimpan sementara di cache server Core Service (bukan tabel permanen), key-nya di-hash sebelum disimpan, dan operasi baca+hapusnya atomic (lihat `README.md` §5.6) — dua percobaan tukar yang datang bersamaan tidak bisa sama-sama berhasil.

---

## 6. Langkah 5 — Menjaga Sesi Tetap Hidup (Refresh)

Access token berumur pendek (5 menit) by design. Dua pendekatan yang bisa dipakai sistem kalian:

- **Redirect ulang ke SSO** setiap kali token kedaluwarsa — paling sederhana, cocok kalau interaksi user dengan sistem kalian singkat.
- **Implementasikan silent-refresh sendiri**, memanggil `POST /auth/refresh` (server-to-server, bawa `refresh_token`) sebelum token kedaluwarsa — cocok kalau sistem kalian butuh sesi panjang tanpa mengganggu user. Simpan `refresh_token` dengan aman (setara menyimpan password — jangan expose ke JavaScript sisi klien).

```http
POST /auth/refresh
Content-Type: application/json

{ "refresh_token": "..." }
```
Response `200`: `{ "access_token": "...", "refresh_token": "...", "refresh_expires_in": 43200 }`. Response `401` kalau refresh token invalid/kadaluarsa/user dinonaktifkan.

> **Refresh token DI-ROTASI tiap panggilan berhasil (standar OAuth 2.0 Security BCP) — AKTIF sepenuhnya sejak 2026-08-20**, untuk sesi portal maupun sesi aplikasi konsumen. `refresh_token` di respons SELALU nilai BARU, BEDA dari yang kalian kirim — **kode kalian WAJIB membaca field ini dan MENYIMPAN ULANG (menimpa yang lama)** tiap kali refresh berhasil, jangan lagi berasumsi nilainya tetap sama selamanya. `refresh_token` LAMA langsung tidak valid begitu dirotasi (panggilan kedua dengan token yang sama gagal `401`), kecuali dipanggil ulang dalam hitungan detik dengan token yang PERSIS sama (jendela toleransi ~10 detik untuk beberapa proses yang kebetulan memanggil nyaris bersamaan). Detail lengkap di `API_DOCUMENTATION.md` §2.3.
>
> **TTL refresh token aplikasi konsumen MENGIKUTI status "Ingat Akun Saya" sesi login yang melahirkan `code`-nya — SEJAK 2026-09-14 (perbaikan bug nyata dilaporkan LARIZAN)**. Sebelumnya refresh token yang didelegasikan ke klien SELALU dapat TTL penuh 30 hari terlepas dari pilihan itu, sehingga user yang sengaja TIDAK mencentang "Ingat Akun Saya" tetap bisa "diam-diam" punya sesi berjam-jam di aplikasi kalian lewat refresh berulang, walau sesi browser Core Service-nya sendiri sudah wajar berakhir. Sekarang:
> - Login **TANPA** "Ingat Akun Saya" dicentang → refresh token yang kalian terima dari `/auth/token-exchange` (dan tiap hasil rotasinya) ber-TTL **sama seperti umur sesi Core Service sendiri saat itu** (12 jam) — BUKAN lagi 30 hari.
> - Login **DENGAN** "Ingat Akun Saya" dicentang → tetap seperti sebelumnya, TTL penuh 30 hari.
> - Kelas TTL ini ditentukan SEKALI, di titik `code` ditukar, dan dipertahankan apa adanya di setiap rotasi berikutnya — TIDAK berubah walau user yang sama login ulang ke Core Service dengan pilihan "Ingat Akun Saya" yang berbeda setelahnya (sesi delegasi lama tidak ikut naik/turun kelas).
> - **Konsekuensi buat kode kalian**: kalau user login tanpa "Ingat Akun Saya", jangan asumsikan sesi di aplikasi kalian bisa bertahan sampai 30 hari cukup dengan refresh berulang — sesi itu akan berakhir (refresh mulai gagal `401`) sekitar 12 jam sejak terakhir aktif, sama seperti sesi Core Service itu sendiri. Kalau produk kalian butuh sesi lebih panjang dari itu, minta user login dengan "Ingat Akun Saya" dicentang.
>
> **Field `refresh_expires_in` (detik) — SEJAK 2026-09-14, PURE ADDITIVE** — ditambahkan ke respons `/auth/token-exchange` DAN `/auth/refresh`, supaya kalian bisa tahu SECARA LOKAL kelas TTL refresh token yang sedang kalian pegang (12 jam = `43200`, atau 30 hari = `2592000`) tanpa perlu menebak dari status "Ingat Akun Saya" yang kalian sendiri tidak tahu. Berguna terutama untuk **housekeeping** (job pembersih baris sesi lokal kalian) — sebelumnya kalau ingin akurat, kalian terpaksa memakai ambang konservatif (31 hari) untuk SEMUA baris karena tidak bisa membedakan kelasnya. **Nilainya SELALU detik PENUH kelasnya (mis. selalu `43200`, bukan hitung mundur), SAMA seperti `expires_in` (access token) yang sudah begini sejak awal** — karena TTL refresh token bersifat **sliding/rolling** (diperpanjang lagi ke nilai penuh tiap berhasil dirotasi, lihat paragraf di atas), field ini BUKAN representasi "sisa waktu sampai kadaluarsa sejak grant pertama". Field lama (`access_token`/`refresh_token`/`token_type`/`expires_in`) tidak berubah sama sekali — kode kalian yang belum membaca field baru ini tetap jalan normal.
>
> **Kebijakan 1-perangkat-aktif HANYA berlaku untuk sesi PORTAL** (login lewat browser/halaman `sso.zabitsa.site` langsung) — login browser baru menimpa sesi browser lama. **Sesi aplikasi konsumen (hasil `/auth/token-exchange`) independen dari sesi portal DAN independen antar aplikasi konsumen lain** — menukar kode dari aplikasi kalian TIDAK menggusur sesi browser user yang mungkin sedang aktif, dan sebaliknya (diperbaiki 2026-08-19, sebelumnya keduanya salah berbagi satu slot yang sama). Kalau aplikasi kalian tiba-tiba mendapat `401` saat refresh, penyebabnya sekarang: refresh token benar-benar kadaluarsa (mengikuti kelas TTL-nya masing-masing di atas, tanpa pernah di-refresh sama sekali), user dinonaktifkan Admin, user **logout dari Core Service** (lihat paragraf berikutnya), atau refresh token itu sudah dirotasi (kalian belum menyimpan yang baru, atau memang sungguh dipakai ulang setelah jendela toleransi lewat).
>
> **Logout di Core Service sekarang juga mematikan sesi yang sudah didelegasikan ke aplikasi kalian — SEJAK 2026-09-15 (perbaikan bug nyata dilaporkan LARIZAN).** Sebelumnya, independensi penyimpanan portal/aplikasi-konsumen (paragraf di atas) punya efek samping tak diinginkan: logout di portal Core Service **TIDAK PERNAH** menyentuh refresh token yang sudah kalian pegang — begitu user klik "Logout", sesi PORTAL berakhir tapi refresh token di aplikasi kalian tetap hidup sampai TTL-nya sendiri habis (bisa 12 jam/30 hari). Nyata jadi masalah untuk skenario "satu perangkat dipakai bergantian beberapa user" — user berikutnya yang login masih melihat identitas user sebelumnya di aplikasi kalian. Sekarang diperbaiki: logout membuat SEMUA refresh token yang pernah didelegasikan ke aplikasi kalian (termasuk ke aplikasi konsumen lain) mati paling lambat di panggilan `/auth/refresh` berikutnya — kode kalian **tidak perlu berubah sama sekali**, penanganan `401` yang sudah kalian punya untuk kasus lain (kadaluarsa, dinonaktifkan) otomatis berlaku sama untuk ini, responsnya `401` generik yang sama (`{ "message": "Refresh token invalid atau kadaluarsa." }`), TIDAK ada alasan spesifik "logout" yang dibocorkan ke respons.
> - **Batas jendela yang masih tersisa**: access token yang SUDAH TERLANJUR beredar di aplikasi kalian (didapat dari `/auth/token-exchange` atau `/auth/refresh` SEBELUM user logout) tetap valid sampai `exp`-nya sendiri — **maksimal ~5 menit** (`access_ttl`) — karena access token diverifikasi 100% lokal (RS256 tanda tangan + `exp`), Core Service tidak pernah dikontak ulang untuk memvalidasinya per-request. Kalau sistem kalian punya kebutuhan lebih ketat dari jendela 5 menit ini (mis. verifikasi real-time tanpa toleransi sama sekali), itu di luar cakupan mekanisme ini — hubungi tim Core Service untuk mendiskusikan kebutuhan spesifik kalian.
>
> **Catatan (belum diterapkan, dipertimbangkan sebagai roadmap ke depan)**: mekanisme di atas menutup celah lewat jalur `/auth/refresh` (paling lambat ~5 menit setelah logout). Untuk invalidasi BENAR-BENAR real-time (Core Service memberi tahu aplikasi kalian secara aktif, bukan menunggu kalian memanggil refresh), sedang dipertimbangkan implementasi **back-channel logout** bergaya OpenID Connect — Core Service memanggil webhook yang kalian daftarkan begitu user logout, membawa Logout Token bertanda tangan. Ini butuh skema pendaftaran URL webhook per aplikasi + endpoint penerima baru di sisi kalian, jadi TIDAK akan jadi breaking change dadakan — akan dikoordinasikan dulu kalau benar-benar diimplementasikan.
>
> Terpisah dari itu: saat ini TIDAK ada batas umur ABSOLUT untuk refresh token — selama terus di-refresh secara rutin (sliding/rolling expiration), sesi user (baik "Ingat Akun Saya" maupun tidak) bisa bertahan tanpa batas waktu selama tidak logout. Batas absolut (mis. wajib re-login penuh tiap 90 hari walau terus dipakai aktif) belum ada di kedua jalur (portal maupun aplikasi konsumen) — kalau ke depan diterapkan, akan diumumkan di sini terlebih dahulu.

**Rekomendasi pola untuk sistem kalian sendiri (pelajaran dari bug nyata yang baru diperbaiki di Core Service)**: jangan simpulkan "user belum/tidak login" hanya karena indikator LOKAL kalian (access token lokal, cache singkat, dsb) sudah kedaluwarsa/kosong. Sebelum menampilkan UI "harus login", coba dulu `POST /auth/refresh` pakai `refresh_token` yang tersimpan — kalau berhasil, lanjutkan sesi secara silent; baru kalau refresh itu SENDIRI gagal (`401`), simpulkan user memang harus login ulang. Core Service sendiri sempat punya bug persis kebalikannya di halaman `/login` (lihat catatan §5 di atas) — satu titik masuk memutuskan "belum login" hanya dari indikator pendek-umur tanpa mencoba refresh dulu, padahal sesi user (terutama yang "Ingat Akun Saya") sebenarnya masih valid. Kalau sistem kalian punya pola serupa (mis. halaman awal/dashboard kalian sendiri langsung redirect ke Core Service login begitu access token lokal kadaluarsa, tanpa mencoba refresh dulu), user kalian akan mengalami gejala yang sama: terasa seperti "Ingat Akun Saya" tidak berfungsi padahal sebenarnya berfungsi di sisi Core Service.

---

## 7. Langkah 6 — Memanggil Data Master via REST API

Gunakan API key dari Langkah 1, panggil endpoint publik sesuai daftar lengkap di `API_DOCUMENTATION.md`. Contoh pakai Laravel HTTP client:

```php
$response = Illuminate\Support\Facades\Http::withHeaders([
    'X-Service-Key' => config('services.core.api_key'),
])->get('https://sso.zabitsa.site/api/santri', ['status' => 'aktif']);

$santriList = $response->json('data');
```

Endpoint yang tersedia (ringkas — detail lengkap di `API_DOCUMENTATION.md`):

| Kebutuhan data | Endpoint |
|---|---|
| Daftar/detail santri, termasuk foto siap-pakai (`foto_signed_url`), pendidikan formal & program diniyah yang SEDANG BERJALAN (`pendidikan_aktif`, `program_aktif`) | `GET /api/santri`, `GET /api/santri/{id}` |
| Kontak wali santri (untuk approval/pemberitahuan) | `GET /api/santri/{id}/wali-santri` |
| Riwayat jabatan / program / domisili satu santri | `GET /api/santri/{id}/riwayat-jabatan`, `.../riwayat-program`, `.../riwayat-domisili` |
| Catat/koreksi perpindahan domisili (khusus kapabilitas tulis) | `POST /api/santri/{id}/riwayat-domisili`, `PUT /api/riwayat-domisili/{id}` |
| Struktur organisasi (tree) | `GET /api/unit-organisasi` |
| Katalog kamar | `GET /api/kamar` |
| Siapa yang **sedang menjabat** di suatu unit/kamar | `GET /api/jabatan?unit_organisasi_id=...` |
| Katalog program akademik | `GET /api/program` |
| Katalog role (menerjemahkan slug → nama) | `GET /api/roles` |
| Kirim notifikasi ke Dashboard SSO pengguna | `POST /api/notifikasi` (khusus kapabilitas `can_kirim_notifikasi`) |

**Tidak tersedia lewat REST API publik** (khusus internal Core Service, tidak relevan untuk sistem konsumen): Kuesioner/survei — modul ini murni untuk pengisian internal lewat Dashboard SSO Core Service sendiri, tidak punya kegunaan lintas sistem sehingga sengaja tidak diekspos.

---

## 8. Domain/Aplikasi Tepercaya untuk Redirect

Authorization code (§5) hanya diterbitkan kalau `redirect_to` mengarah ke:
- Host/port **persis sama** dengan Core Service sendiri (dianggap internal, redirect langsung tanpa code sama sekali).
- **Origin (scheme+host+port) yang cocok dengan `url` salah satu baris Aplikasi AKTIF yang terdaftar** di Core Service (Admin → Aplikasi). **Sekadar berada di subdomain `*.zabitsa.site` TIDAK LAGI cukup** (diperketat 2026-08-20, sebelumnya cukup subdomain tepercaya tanpa perlu terdaftar) — aplikasi kalian **wajib** punya baris Aplikasi terdaftar & aktif dengan `url` yang originnya cocok persis (termasuk scheme — `https://` terdaftar tidak bisa "dijangkau" lewat `redirect_to` yang dipalsukan `http://`).
- Kalau sistem kalian **di luar** domain `zabitsa.site` (mis. domain sendiri), minta tim Core Service mendaftarkan aplikasi kalian (Admin → Aplikasi, bukan lagi sekadar daftar host tepercaya terpisah) sebelum integrasi bisa berjalan — hubungi tim Core Service, bukan mengandalkan trial-and-error, karena mekanisme ini sengaja dikunci ketat untuk mencegah celah open-redirect (lihat `README.md` §5.7).

---

## 9. Catatan Keamanan Wajib Diperhatikan

- **Private key JWT** tidak pernah meninggalkan Core Service — sistem kalian hanya perlu public key untuk verifikasi.
- **API key Service** bersifat rahasia server-to-server — jangan pernah expose ke JavaScript sisi klien/browser, jangan commit ke repository publik.
- Semua komunikasi wajib **HTTPS** di produksi.
- Sistem 2-4/dst secara default hanya mendapat akses **read-only** ke data master. Kalau sistem kalian butuh akses tulis (riwayat domisili) atau kirim notifikasi, kapabilitas itu perlu ditambahkan eksplisit ke baris `service_keys` kalian oleh Admin — minta secara eksplisit, jangan asumsikan.
- **Refresh token** setara password — simpan aman di server (terenkripsi at-rest kalau memungkinkan), jangan pernah log-kan nilainya mentah-mentah.
- **`client_secret`** (§5) — **wajib sejak 2026-08-20** untuk memanggil `/auth/token-exchange` & `/auth/refresh` (request tanpa ini ditolak `401`). Perlakukan SAMA seperti API key Service: rahasia server-to-server, jangan expose ke JavaScript sisi klien, jangan commit ke repository. Diterbitkan/diterbitkan-ulang Admin lewat Admin → Aplikasi, plaintext-nya tampil **satu kali saja** — catat segera, tidak bisa dilihat ulang. Belum punya `client_secret`? Hubungi tim Core Service SEKARANG — integrasi kalian tidak akan berfungsi tanpanya.
- **Rate limit** REST API: 120 request/menit per Service Key (bukan per IP). Kalau sistem kalian memanggil dalam jumlah besar (batch), pertimbangkan paginasi bertahap alih-alih satu ledakan request. `POST /auth/refresh` & `POST /auth/token-exchange`: 30/menit **per IP** (bukan per aplikasi) — kalau backend kalian memanggil endpoint ini dari satu server untuk banyak user sekaligus, hindari memanggil refresh di tiap request tanpa cek dulu apakah access token yang kalian pegang masih berlaku, supaya user lain tidak ikut kehabisan jatah.

---

## 10. Checklist Integrasi Baru

1. [ ] Minta Admin Core Service membuat `service_keys` baru untuk sistem kalian (§2) — sepakati kapabilitas yang dibutuhkan.
2. [ ] Simpan API key plaintext dengan aman di `.env`/secret manager sisi server kalian.
3. [ ] Minta Admin Core Service mendaftarkan aplikasi kalian di Admin → Aplikasi (`url` origin yang benar) — **wajib** sejak redirect trust diperketat ke Aplikasi terdaftar (§8), bukan lagi cukup subdomain `.zabitsa.site` saja.
4. [ ] Minta Admin Core Service menerbitkan `client_id`/`client_secret` untuk aplikasi kalian (§5, §9) — **wajib sejak 2026-08-20**, tanpa ini `POST /auth/token-exchange` & `POST /auth/refresh` langsung ditolak `401`.
5. [ ] Unduh & simpan public key JWT (§3, atau `GET /auth/jwks` kalau library kalian mendukung JWK).
6. [ ] Implementasikan verifikasi JWT + cek `permissions` lokal (§4).
7. [ ] Implementasikan alur redirect login untuk kasus direct-access (§5), termasuk `state` (proteksi login-CSRF).
8. [ ] Putuskan strategi menjaga sesi (redirect ulang vs. silent-refresh, §6) — **wajib** kode kalian membaca & menyimpan ulang `refresh_token` dari tiap respons `/auth/refresh` (rotasi sudah aktif penuh sejak 2026-08-20, §6) — kalau tidak, sesi akan gagal refresh di panggilan kedua.
9. [ ] Integrasikan pemanggilan REST API data master yang relevan (§7).
10. [ ] Uji ulang seluruh alur di lingkungan staging sebelum go-live, termasuk skenario token kedaluwarsa dan login dari device lain (kebijakan 1-perangkat-aktif KHUSUS sesi portal, §6).
11. [ ] Konfirmasikan ke tim Core Service kalau sistem kalian sudah live — supaya tercatat sebagai konsumen aktif untuk keperluan audit `api_access_logs`.
