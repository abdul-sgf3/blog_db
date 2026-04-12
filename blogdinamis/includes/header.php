<?php
// includes/header.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? SITE_NAME) ?> | <?= SITE_NAME ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container nav-inner">
    <a href="<?= BASE_URL ?>" class="brand"><?= SITE_NAME ?></a>
    <div class="nav-links">
      <a href="<?= BASE_URL ?>" class="<?= $currentPage==='index.php'?'active':'' ?>">Beranda</a>
      <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/article-form.php" class="<?= $currentPage==='article-form.php'&&empty($_GET['id'])?'active':'' ?>">Tulis Artikel</a>
        <?php if (isAdmin()): ?>
          <a href="<?= BASE_URL ?>/admin/index.php" class="<?= strpos($currentPage,'admin')!==false?'active':'' ?>">Dasbor Admin</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="nav-user">
      <?php if (isLoggedIn()): $u = currentUser(); ?>
        <span class="user-name"><?= e($u['name']) ?></span>
        <span class="badge badge-<?= $u['role'] ?>"><?= $u['role'] ?></span>
        <a href="<?= BASE_URL ?>/profile.php" class="btn btn-sm">Profil</a>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline">Keluar</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-primary">Masuk</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="main-wrap">
<div class="container">
<?= getAlert() ?>
