<?php
require_once '../includes/config.php';
if (!isLoggedIn()) redirect(BASE_URL . '/login.php');

$id        = (int)($_GET['id']         ?? 0);
$articleId = (int)($_GET['article_id'] ?? 0);

if ($id && isAdmin()) {
    $conn->query("DELETE FROM comments WHERE id=$id");
    setAlert('success', 'Komentar berhasil dihapus.');
}

if ($articleId) redirect(BASE_URL . '/article.php?id=' . $articleId . '#comments');
redirect(BASE_URL . '/admin/index.php?tab=comments');
