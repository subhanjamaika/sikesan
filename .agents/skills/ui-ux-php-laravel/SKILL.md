---
name: ui-ux-php-laravel
description: Panduan lengkap untuk merancang, mereview, dan membangun UI/UX yang elegan, realistis, dan production-ready untuk project web PHP native maupun Laravel (Blade). Gunakan skill ini SETIAP KALI user meminta membuat halaman/tampilan baru, form, dashboard, tabel CRUD, landing page; mereview atau mengaudit UI/UX yang sudah ada; membuat wireframe/mockup; atau minta rekomendasi best practice UX untuk sebuah fitur — bahkan jika mereka tidak secara eksplisit menyebut kata "UI/UX", "desain", atau "tampilan". Selalu konsultasikan skill ini untuk permintaan apa pun yang menyentuh styling, layout, komponen form, navigasi, atau pengalaman pengguna pada aplikasi PHP/Laravel, termasuk saat kode akan dijalankan/dieksekusi di tool lain (mis. Antigravity, XAMPP, php artisan serve).
---

# UI/UX untuk PHP & Laravel

Berperan sebagai UI/UX designer sekaligus front-end engineer yang membantu pelajar PHP/Laravel membangun tampilan yang terasa buatan produk sungguhan — bukan tampilan "tutorial" dengan Bootstrap default dan data `Lorem Ipsum` / `John Doe`.

## Langkah 0 — Kenali mode permintaan

Tentukan dulu user sedang minta yang mana (boleh gabungan lebih dari satu):

| Mode | Ciri permintaan | Output |
|---|---|---|
| **Review/Audit** | "cek dong tampilan ini", "kenapa form saya berantakan", ada kode/screenshot yang dilampirkan | Daftar temuan + saran perbaikan, prioritas tinggi→rendah |
| **Wireframe/Mockup** | "buatkan gambaran tampilan", "sketsa dulu sebelum coding", "kira-kira layoutnya gimana" | ASCII wireframe cepat, lalu mockup visual (lihat Langkah 3) |
| **Build/Kode** | "buatkan halaman login", "bikinkan tabel data siswa", "styling-in form ini" | Kode lengkap (Blade/PHP native + CSS/Tailwind), siap dijalankan |
| **Rekomendasi UX** | "gimana UX yang baik untuk fitur hapus data", "best practice-nya apa" | Penjelasan singkat + contoh pola yang sudah terbukti |

Kalau ambigu, defaultnya adalah **Build/Kode** — pelajar biasanya ingin lihat hasil konkret, bukan hanya teori.

## Langkah 1 — Cek konteks project dulu

Sebelum menulis kode, cek dengan cepat (jangan tanya balik kalau bisa disimpulkan sendiri):
- **PHP native atau Laravel?** Kalau ada file `.blade.php`, `routes/web.php`, `composer.json` dengan `laravel/framework` → Laravel. Kalau ada `<?php` polos dengan `mysqli`/`PDO` manual → PHP native.
- **Ada styling library yang sudah dipakai?** Cek apakah project sudah pakai Tailwind (ada `tailwind.config.js` atau CDN), Bootstrap, atau CSS manual. Ikuti yang sudah ada — jangan mencampur library baru ke project yang sudah pakai library lain kecuali diminta.
- **Kalau project baru/kosong**, defaultkan ke **Tailwind CSS via CDN** untuk PHP native (paling cepat dicoba di XAMPP tanpa build step), atau **Tailwind + Vite** kalau Laravel (sudah bawaan `laravel/breeze`-style setup).

## Langkah 2 — Prinsip desain (jangan bikin tampilan "AI banget")

Adaptasi dari studio desain profesional — hindari default yang langsung ketahuan buatan AI:

- **Hindari 3 pola generik ini** kecuali memang diminta: (1) krem hangat + serif kontras + aksen terracotta, (2) hitam pekat + satu aksen neon, (3) layout koran dengan garis tipis dan sudut lancip di semua elemen. Pilih palet & tipografi yang cocok dengan *isi* halaman (misal: dashboard sekolah beda nuansa dengan landing page produk digital).
- **Konten harus realistis**, bukan placeholder mentah. Ganti `Lorem Ipsum`, `John Doe`, `Data 1/2/3` dengan nama, alamat, dan angka yang masuk akal untuk konteks Indonesia (mis. tabel siswa: nama asli Indonesia, NIS, kelas nyata seperti "XI IPA 2", nilai realistis 60–95, bukan 0 atau 999).
- **Satu elemen "signature"** yang membuat halaman diingat (warna aksen konsisten, ikon khas, micro-interaction saat hover/submit) — sisanya tenang dan rapi. Jangan taruh animasi di mana-mana, itu justru terasa murahan.
- **State harus lengkap**: setiap halaman punya kondisi *loading*, *empty* (belum ada data), *error* (gagal submit/gagal koneksi DB), dan *success* (data berhasil disimpan) — jangan cuma desain kondisi "happy path".
- **Copy/teks UI**: pakai kata kerja aktif dan konsisten. Tombol "Simpan Data" harus memicu notifikasi "Data berhasil disimpan", bukan "Submit" lalu notifikasi "Success". Pesan error jelas menyebut apa yang salah dan cara memperbaikinya (mis. "Email sudah terdaftar, gunakan email lain" — bukan "Terjadi kesalahan").

## Langkah 3 — Wireframe & mockup

1. Mulai dengan **ASCII wireframe singkat** di chat untuk menyepakati layout sebelum menulis kode penuh (kotak-kotak sederhana: header, sidebar, konten, form field). Lewati langkah ini kalau user sudah sangat spesifik atau minta langsung kode.
2. Untuk mockup visual interaktif, gunakan tool Visualizer (`read_me` modul `mockup`, lalu `show_widget`) agar user bisa melihat tampilan langsung di chat sebelum kode final ditulis ke file project.
3. Setelah disetujui, baru tulis kode final ke file project (Blade component / file PHP) mengikuti struktur di Langkah 4.

## Langkah 4 — Checklist kode yang elegan & siap eksekusi

Sebelum menyerahkan kode, pastikan:

- [ ] **Semantic HTML** — `<nav>`, `<main>`, `<table>` untuk data tabular (bukan `<div>` semua), `<label for>` terhubung ke `<input id>`.
- [ ] **Responsif** — minimal 3 breakpoint dicek (mobile, tablet, desktop) via Tailwind (`sm:`, `md:`, `lg:`). Jangan hanya desain untuk layar lebar.
- [ ] **Validasi & feedback jelas terlihat** — pesan error di bawah field yang salah (bukan alert generik di atas), styling border merah pada input invalid.
- [ ] **Aksesibilitas dasar** — kontras warna teks cukup, ada `alt` pada gambar, tombol bisa di-*focus* dengan keyboard (outline terlihat).
- [ ] **Konsisten dengan pola project (untuk Laravel)** — pakai `@extends`/`@section`, Blade component (`<x-...>`), CSRF token (`@csrf`) di semua form POST, `old()` untuk re-populate form setelah validasi gagal, flash session (`session('success')`) untuk notifikasi.
- [ ] **Konsisten untuk PHP native** — `htmlspecialchars()` di setiap output data ke HTML (cegah XSS, sesuai kebiasaan yang sudah dipelajari), prepared statement untuk query, struktur folder header/footer di-`include` agar tidak duplikasi.
- [ ] **Langsung bisa dijalankan** — tidak ada kode setengah jadi/`// TODO`, import CSS/JS (CDN atau file lokal) sudah lengkap di `<head>`, sehingga begitu file dibuka di browser (via XAMPP, `php artisan serve`, atau dieksekusi lewat tool coding lain) hasilnya langsung terlihat rapi tanpa langkah tambahan.

## Langkah 5 — Pola UX untuk kasus umum CRUD (relevan untuk project belajar)

- **Tabel data (index)**: search box + filter di atas tabel, pagination di bawah, kolom aksi (Edit/Hapus) rata kanan dengan ikon+label, baris zebra-striping untuk keterbacaan.
- **Hapus data**: SELALU pakai konfirmasi (modal atau `confirm()` minimal) sebelum eksekusi delete — jangan langsung hapus dari satu klik.
- **Form tambah/edit**: satu form dipakai ulang untuk create & edit (bedakan lewat kondisi `isset($data)`), tombol submit berlabel sesuai aksi ("Tambah Siswa" vs "Simpan Perubahan").
- **Notifikasi**: gunakan flash message session (bukan `alert()` browser) yang otomatis hilang setelah beberapa detik, warna hijau untuk sukses/merah untuk gagal.
- **Breadcrumb/navigasi balik**: setiap halaman form/detail punya jalan jelas untuk kembali ke halaman index.

## Langkah 6 — Saat mereview/audit tampilan yang sudah ada

Gunakan checklist heuristik ringkas (adaptasi 10 Usability Heuristics Nielsen), sebutkan maksimal 5-7 temuan paling berdampak, urut dari prioritas tertinggi:

1. Apakah user selalu tahu status sistem saat ini? (loading, sudah tersimpan, dsb.)
2. Apakah istilah di UI sesuai bahasa user (bukan istilah teknis database)?
3. Apakah ada jalan keluar/undo untuk aksi yang tidak sengaja (terutama hapus data)?
4. Konsistensi — tombol/warna/istilah yang sama dipakai untuk aksi yang sama di semua halaman?
5. Apakah desain mencegah error (validasi sebelum submit) sebelum sekadar menampilkan error?
6. Apakah elemen penting terlihat tanpa harus mengingat (label jelas, bukan hanya ikon tanpa teks)?
7. Efisiensi untuk pengguna berulang (shortcut, default value cerdas) tanpa merepotkan pemula?

Untuk tiap temuan: sebutkan masalahnya, dampaknya ke user, dan contoh perbaikan konkret (potongan kode kalau relevan) — bukan cuma teori.

## Catatan tentang tool eksekusi kode lain (mis. Antigravity)

Skill ini berjalan di sisi Claude (Claude.ai, Claude Code, Cowork) — bukan di dalam IDE/agent pihak lain seperti Google Antigravity. Prinsip desain dan checklist di atas bersifat tool-agnostic: kode yang dihasilkan Claude di sini sudah bisa langsung dijalankan di XAMPP/`php artisan serve`. Kalau instruksi ini ingin dipakai sebagai acuan gaya saat bekerja di tool lain, salin bagian Langkah 2–5 sebagai panduan gaya di sana secara manual.
