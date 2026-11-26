# Quorum

Aplikasi web untuk mengelola anggota, kegiatan, pendaftaran, dan kehadiran Unit Kegiatan Mahasiswa (UKM). Dibuat dengan PHP murni + MySQL (PDO), tanpa framework.

Peran: anggota, pengurus, ketua, admin. Peran anggota/pengurus/ketua disimpan per UKM di tabel `anggota_ukm`. Admin disimpan di `users.is_admin`.

## Struktur

```
public/      satu-satunya folder yang dibuka ke web (halaman, assets/)
src/         logika PHP (config, db, bootstrap, guard, helpers)
templates/   header, footer, dan menu bersama
database/    quorum.sql (skema database)
.env         kredensial database lokal (jangan di-commit)
```

## Setup

1. Pasang PHP (dengan `pdo_mysql`) dan MariaDB/MySQL.
2. Salin `.env.example` menjadi `.env`, lalu isi dengan nilai milikmu sendiri.
3. Buat database `quorum` dan impor `database/quorum.sql` (lihat bagian sesuai lingkunganmu di bawah).

Isi `.env`:

```
DB_HOST=127.0.0.1
DB_NAME=quorum
DB_USER=...
DB_PASS=...
```

Setelah `.env` diubah, restart server PHP. Nilai lama tetap dipakai sampai server dimulai ulang.

### Windows (XAMPP)

1. Buka XAMPP Control Panel, lalu klik Start pada MySQL.
2. Di PowerShell, dari folder proyek:

```
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE quorum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root quorum < database\quorum.sql"
copy .env.example .env
```

3. Isi `.env` dengan `DB_USER=root` dan `DB_PASS=` (kosong).

Jika XAMPP dipasang di folder lain, ganti `C:\xampp` sesuai lokasinya.

### GitHub Codespaces (Linux)

```
sudo apt-get update
sudo apt-get install -y php-cli php-mysql mariadb-server
sudo service mariadb start
sudo mysql -e "CREATE DATABASE quorum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'quorum_user'@'localhost' IDENTIFIED BY 'quorum_pass'; GRANT ALL PRIVILEGES ON quorum.* TO 'quorum_user'@'localhost'; FLUSH PRIVILEGES;"
sudo mysql quorum < database/quorum.sql
cp .env.example .env
```

Isi `.env` dengan `DB_USER=quorum_user` dan `DB_PASS=quorum_pass` (hanya untuk pengembangan lokal).
Setelah Codespaces dibuka kembali, jalankan lagi `sudo service mariadb start`.

## Menjalankan

Windows (XAMPP):

```
C:\xampp\php\php.exe -S 0.0.0.0:8000 -t public
```

Codespaces (Linux):

```
/usr/bin/php -S 0.0.0.0:8000 -t public
```

Lalu buka `http://127.0.0.1:8000/register.php`.

Bagian `-t public` wajib ada. Tanpa itu, `.env` dan `src/` bisa dibuka lewat URL.

## Akun Uji

Setiap orang punya database sendiri, jadi akun uji dibuat sendiri:

1. Daftar lewat `/register.php`.
2. Buat UKM dan peran lewat SQL, misalnya (ganti angka sesuai id di databasemu):

```
INSERT INTO ukm (name) VALUES ('Basket');
INSERT INTO anggota_ukm (user_id, ukm_id, role, status, joined_at) VALUES (1, 1, 'pengurus', 'active', NOW());
```

Perintah SQL di Windows dijalankan dengan `C:\xampp\mysql\bin\mysql.exe -u root quorum -e "..."`, di Codespaces dengan `sudo mysql quorum -e "..."`.

Jika skema database diubah, perbarui `database/quorum.sql` dan beri tahu tim agar semua menjalankan perubahan yang sama.

## Aturan Kerja

- Setiap halaman diawali dengan `require_once __DIR__ . '/../src/bootstrap.php';`
- Hanya gunakan prepared statement. Semua output di-escape dengan `htmlspecialchars` (lewat fungsi `e()`).
- Setiap form POST membawa token CSRF.
- Pengecekan peran dilakukan di server, pada setiap halaman yang dilindungi, dengan membaca peran dari database di setiap request.
- Halaman yang butuh login memanggil `require_user()`. Halaman khusus peran memanggil `require_ukm_role($ukm_id, [...])`.
- Kode ditulis dalam bahasa Inggris. Nama Indonesia hanya untuk `ukm`, `anggota_ukm`, `kegiatan`, `pendaftaran`, `kehadiran`, `tindakan`.
- Kerjakan di branch sendiri dan buka pull request. Jangan pernah commit `.env`.