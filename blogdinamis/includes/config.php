<?php
// ============================================================
//  Konfigurasi Database - Sesuaikan jika perlu
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // user XAMPP default
define('DB_PASS', '');           // password XAMPP default (kosong)
define('DB_NAME', 'blogdinamis');
define('SITE_NAME', 'BlogDinamis');
define('BASE_URL', 'http://localhost/blogdinamis');

// Koneksi MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;color:#c0392b;background:#fdf0ef;border:1px solid #f0b8b4;border-radius:8px;margin:40px auto;max-width:600px">
        <h2>Koneksi Database Gagal</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Pastikan XAMPP MySQL sudah berjalan dan database <strong>blogdinamis</strong> sudah diimport.</p>
    </div>');
}
$conn->set_charset('utf8mb4');

// Session
if (session_status() === PHP_SESSION_NONE) session_start();

// Helper: cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function currentUser() {
    return $_SESSION ?? [];
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper: slug
function makeSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

// Helper: sanitize output
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper: excerpt
function excerpt($text, $len = 150) {
    $text = strip_tags($text);
    return strlen($text) > $len ? substr($text, 0, $len) . '...' : $text;
}

// Helper: format tanggal Indonesia
function tgl($date) {
    $bln = ['','Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'];
    $d = date_create($date);
    return date_format($d,'j') . ' ' . $bln[(int)date_format($d,'n')] . ' ' . date_format($d,'Y');
}

// Alert flash message
function setAlert($type, $msg) {
    $_SESSION['alert'] = ['type'=>$type,'msg'=>$msg];
}
function getAlert() {
    if (!empty($_SESSION['alert'])) {
        $a = $_SESSION['alert'];
        unset($_SESSION['alert']);
        $bg  = $a['type']==='success' ? '#eaf3de' : '#fcebeb';
        $col = $a['type']==='success' ? '#3b6d11'  : '#791f1f';
        $bdr = $a['type']==='success' ? '#c0dd97'  : '#f09595';
        return "<div style='background:$bg;color:$col;border:1px solid $bdr;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:14px'>{$a['msg']}</div>";
    }
    return '';
}
