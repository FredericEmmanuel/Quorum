# Quorum

Aplikasi web untuk mengelola anggota, kegiatan, pendaftaran, dan kehadiran Unit Kegiatan Mahasiswa (UKM). Dibuat dengan PHP murni + MySQL (PDO), tanpa framework.

Peran: anggota, pengurus, ketua, admin.

## Struktur

```
public/      satu-satunya folder yang dibuka ke web (halaman, assets/)
src/         logika PHP (config, db, bootstrap, guard)
templates/   header, footer, dan menu bersama
database/    quorum.sql (skema database)
.env         kredensial database lokal (jangan di-commit)
```

## Setup

1. Salin `.env.example` menjadi `.env`, lalu isi dengan nilai milikmu sendiri.
2. Pasang PHP (dengan `pdo_mysql`) dan MariaDB/MySQL.

**GitHub Codespaces**

```
sudo apt-get update
sudo apt-get install -y php-cli php-mysql mariadb-server
sudo service mariadb start
sudo mysql -e "CREATE DATABASE quorum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'quorum_user'@'localhost' IDENTIFIED BY 'quorum_pass'; GRANT ALL PRIVILEGES ON quorum.* TO 'quorum_user'@'localhost'; FLUSH PRIVILEGES;"
sudo mysql quorum < database/quorum.sql
```

Isi `.env` dengan `DB_USER=quorum_user` dan `DB_PASS=quorum_pass` (hanya untuk pengembangan lokal).
Setelah Codespaces dibuka kembali, jalankan lagi `sudo service mariadb start`.

**Lokal (XAMPP)**

Jalankan MySQL di XAMPP, buat database `quorum`, lalu impor `database/quorum.sql`. Isi `.env` bawaan: user `root`, password kosong.

## Menjalankan

```
/usr/bin/php -S 0.0.0.0:8000 -t public
```

Bagian `-t public` wajib ada. Tanpa itu, `.env` dan `src/` bisa dibuka lewat URL.

## Aturan Kerja

- Setiap halaman diawali dengan `require_once __DIR__ . '/../src/bootstrap.php';`
- Hanya gunakan prepared statement. Semua output di-escape dengan `htmlspecialchars`.
- Setiap form POST membawa token CSRF.
- Pengecekan peran dilakukan di server, pada setiap halaman yang dilindungi.
- Kode ditulis dalam bahasa Inggris. Nama Indonesia hanya untuk `ukm`, `anggota_ukm`, `kegiatan`, `pendaftaran`, `kehadiran`, `tindakan`.
- Kerjakan di branch sendiri dan buka pull request. Jangan pernah commit `.env`.
