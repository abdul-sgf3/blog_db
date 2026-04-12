<?php
require_once '../includes/config.php';
if (!isAdmin()) { setAlert('danger','Akses ditolak.'); redirect(BASE_URL); }

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $conn->query("DELETE FROM articles WHERE id=$id");
    setAlert('success', 'Artikel berhasil dihapus.');
}
redirect(BASE_URL . '/admin/index.php?tab=articles');
