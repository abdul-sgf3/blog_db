-- ============================================================
--  BlogDinamis - Database Schema
--  Import ke phpMyAdmin > Import tab
--  Nama database: blogdinamis
-- ============================================================

CREATE DATABASE IF NOT EXISTS `blogdinamis`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `blogdinamis`;

-- --------------------------------------------------------
-- Tabel: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(100) NOT NULL,
  `role`       ENUM('admin','author') NOT NULL DEFAULT 'author',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(120) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: articles
-- --------------------------------------------------------
CREATE TABLE `articles` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(255) NOT NULL,
  `slug`        VARCHAR(280) NOT NULL UNIQUE,
  `content`     LONGTEXT NOT NULL,
  `category_id` INT UNSIGNED,
  `author_id`   INT UNSIGNED NOT NULL,
  `status`      ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `views`       INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`author_id`)   REFERENCES `users`(`id`)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: tags
-- --------------------------------------------------------
CREATE TABLE `tags` (
  `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: article_tags (pivot)
-- --------------------------------------------------------
CREATE TABLE `article_tags` (
  `article_id` INT UNSIGNED NOT NULL,
  `tag_id`     INT UNSIGNED NOT NULL,
  PRIMARY KEY (`article_id`, `tag_id`),
  FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`)     REFERENCES `tags`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: comments
-- --------------------------------------------------------
CREATE TABLE `comments` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `article_id` INT UNSIGNED NOT NULL,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `text`       TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed Data
-- ============================================================

-- Users (password disimpan plaintext sesuai permintaan)
INSERT INTO `users` (`name`, `username`, `password`, `role`) VALUES
('Admin Blog',    'admin', 'admin123', 'admin'),
('Budi Santoso',  'budi',  'budi123',  'author'),
('Siti Rahma',    'siti',  'siti123',  'author');

-- Categories
INSERT INTO `categories` (`name`, `slug`) VALUES
('Teknologi',   'teknologi'),
('Gaya Hidup',  'gaya-hidup'),
('Bisnis',      'bisnis'),
('Kesehatan',   'kesehatan');

-- Articles
INSERT INTO `articles` (`title`, `slug`, `content`, `category_id`, `author_id`, `status`, `views`) VALUES
(
  'Mengenal Kecerdasan Buatan di Era Modern',
  'mengenal-kecerdasan-buatan-di-era-modern',
  'Kecerdasan buatan atau AI telah mengubah cara kita bekerja dan berinteraksi. Dari asisten virtual hingga mobil otonom, AI kini ada di mana-mana.\r\n\r\nPerkembangan machine learning memungkinkan komputer belajar dari data tanpa diprogram secara eksplisit. Ini membuka peluang besar di berbagai industri mulai dari kesehatan, keuangan, hingga pendidikan.\r\n\r\nDeep learning, salah satu cabang machine learning, menggunakan jaringan saraf tiruan berlapis yang mampu mengenali pola kompleks dalam data. Teknologi inilah yang mendasari pengenalan wajah, penerjemahan bahasa otomatis, dan kendaraan otonom.\r\n\r\nKe depan, AI akan semakin terintegrasi dalam kehidupan sehari-hari. Penting bagi kita untuk memahami dasar-dasarnya agar dapat memanfaatkan dan menghadapi tantangan yang dibawanya.',
  1, 2, 'published', 142
),
(
  'Tips Produktivitas Kerja dari Rumah',
  'tips-produktivitas-kerja-dari-rumah',
  'Bekerja dari rumah membutuhkan disiplin dan manajemen waktu yang baik. Tanpa pengawasan langsung, mudah sekali tergoda untuk menunda pekerjaan.\r\n\r\nBerikut beberapa tips agar tetap produktif:\r\n\r\n1. Buat jadwal yang konsisten — Mulai dan akhiri kerja di jam yang sama setiap hari.\r\n2. Siapkan ruang kerja khusus — Pisahkan area kerja dari area istirahat.\r\n3. Batasi gangguan dari media sosial — Gunakan aplikasi pemblokir situs jika perlu.\r\n4. Lakukan istirahat rutin — Teknik Pomodoro (25 menit kerja, 5 menit istirahat) terbukti efektif.\r\n5. Komunikasikan batas waktu — Beri tahu keluarga jam kerja Anda agar tidak terganggu.',
  2, 3, 'published', 89
),
(
  'Investasi Saham untuk Pemula',
  'investasi-saham-untuk-pemula',
  'Investasi saham adalah salah satu cara terbaik untuk menumbuhkan kekayaan jangka panjang. Namun, memahami dasar-dasarnya sangat penting sebelum mulai.\r\n\r\nSaham adalah bukti kepemilikan sebagian dari sebuah perusahaan. Ketika perusahaan tumbuh dan menghasilkan laba, nilai saham Anda pun berpotensi meningkat.\r\n\r\nLangkah awal untuk berinvestasi saham:\r\n- Buka rekening di perusahaan sekuritas terpercaya\r\n- Pelajari analisis fundamental dan teknikal\r\n- Mulai dengan modal kecil yang tidak mengganggu kebutuhan pokok\r\n- Diversifikasi portofolio untuk mengurangi risiko\r\n- Investasikan hanya uang yang tidak dibutuhkan dalam jangka pendek',
  3, 2, 'published', 201
),
(
  'Rahasia Tidur Berkualitas untuk Kesehatan Optimal',
  'rahasia-tidur-berkualitas',
  'Tidur yang cukup dan berkualitas adalah fondasi kesehatan yang baik. Penelitian menunjukkan bahwa orang dewasa membutuhkan 7-9 jam tidur setiap malam.\r\n\r\nKurang tidur dikaitkan dengan berbagai masalah kesehatan termasuk obesitas, diabetes, penyakit jantung, dan gangguan mental. Sementara tidur yang baik meningkatkan fungsi kognitif, suasana hati, dan sistem imun.\r\n\r\nTips mendapatkan tidur berkualitas:\r\n- Pertahankan jadwal tidur yang konsisten, bahkan di akhir pekan\r\n- Hindari layar ponsel/komputer minimal 1 jam sebelum tidur\r\n- Jaga suhu kamar tetap sejuk (18-22°C)\r\n- Hindari kafein setelah jam 2 siang\r\n- Lakukan rutinitas relaksasi sebelum tidur seperti membaca atau meditasi',
  4, 3, 'draft', 0
);

-- Tags
INSERT INTO `tags` (`name`) VALUES
('AI'), ('Machine Learning'), ('Teknologi'), ('Produktivitas'),
('WFH'), ('Tips'), ('Investasi'), ('Saham'), ('Keuangan'),
('Kesehatan'), ('Tidur'), ('Wellness');

-- Article Tags
INSERT INTO `article_tags` (`article_id`, `tag_id`) VALUES
(1,1),(1,2),(1,3),
(2,4),(2,5),(2,6),
(3,7),(3,8),(3,9),
(4,10),(4,11),(4,12);

-- Comments
INSERT INTO `comments` (`article_id`, `name`, `email`, `text`) VALUES
(1, 'Rizky',   'rizky@email.com', 'Artikel yang sangat informatif! Terima kasih sudah menjelaskan AI dengan bahasa yang mudah dipahami.'),
(1, 'Dewi',    'dewi@email.com',  'Saya jadi lebih paham tentang perbedaan AI dan Machine Learning setelah baca ini.'),
(2, 'Andi',    'andi@email.com',  'Tips nomor 3 paling susah dilakukan haha, tapi memang penting banget!'),
(3, 'Maya',    'maya@email.com',  'Tolong bahas lebih dalam tentang reksa dana juga ya di artikel berikutnya!');
