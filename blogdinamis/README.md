# BlogDinamis — Panduan Instalasi XAMPP

## Persyaratan
- XAMPP (PHP 7.4+ / PHP 8.x, MySQL/MariaDB)
- Browser modern

---

## Langkah Instalasi

### 1. Salin folder ke htdocs
Salin seluruh folder `blogdinamis` ke direktori htdocs XAMPP:
- **Windows:** `C:\xampp\htdocs\blogdinamis`
- **Mac/Linux:** `/Applications/XAMPP/htdocs/blogdinamis`

### 2. Import Database ke phpMyAdmin
1. Buka browser → `http://localhost/phpmyadmin`
2. Klik tab **Import**
3. Klik **Pilih File** → pilih file `database.sql` dari folder ini
4. Klik **Import** (tombol di bawah halaman)
5. Selesai! Database `blogdinamis` otomatis terbuat beserta tabel dan data sampel.

### 3. Jalankan Aplikasi
Buka browser → `http://localhost/blogdinamis`

---

## Akun Login Default
| Username | Password  | Role   |
|----------|-----------|--------|
| admin    | admin123  | Admin  |
| budi     | budi123   | Author |
| siti     | siti123   | Author |

---

## Konfigurasi Database
Jika XAMPP Anda menggunakan konfigurasi berbeda, edit file:
`includes/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // ubah sesuai user MySQL Anda
define('DB_PASS', '');        // ubah jika ada password
define('DB_NAME', 'blogdinamis');
```

---

## Struktur Folder
```
blogdinamis/
├── index.php           ← Halaman beranda
├── article.php         ← Detail artikel + komentar
├── article-form.php    ← Form tulis/edit artikel
├── login.php           ← Halaman login
├── logout.php          ← Logout
├── profile.php         ← Profil pengguna
├── database.sql        ← ← IMPORT INI KE PHPMYADMIN
├── admin/
│   ├── index.php           ← Dasbor admin
│   ├── delete-article.php
│   ├── delete-user.php
│   ├── delete-category.php
│   └── delete-comment.php
├── includes/
│   ├── config.php      ← Konfigurasi & koneksi DB
│   ├── header.php      ← Template header
│   └── footer.php      ← Template footer
└── assets/
    └── css/
        └── style.css   ← Stylesheet utama
```

---

## Fitur Aplikasi
- **CRUD Artikel** — Buat, baca, edit, hapus artikel
- **Kategori & Tag** — Kategorisasi dan tagging fleksibel
- **Komentar** — Siapa saja bisa komentar, admin bisa hapus
- **Login Admin & Author** — Hak akses berbeda per role
- **Pencarian** — Cari artikel berdasarkan judul/konten
- **Paginasi** — Navigasi halaman artikel
- **Profil** — Ubah nama dan password

---

*Password disimpan plaintext sesuai permintaan (tanpa hashing).*
