<?php
require_once '../includes/config.php';
if (!isAdmin()) { setAlert('danger','Akses ditolak.'); redirect(BASE_URL); }

$id = (int)($_GET['id'] ?? 0);
if ($id && $id !== currentUser()['user_id']) {
    $conn->query("DELETE FROM users WHERE id=$id");
    setAlert('success', 'Pengguna berhasil dihapus.');
}
redirect(BASE_URL . '/admin/index.php?tab=users');
