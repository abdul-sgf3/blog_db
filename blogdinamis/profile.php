<?php
require_once 'includes/config.php';
if (!isLoggedIn()) redirect(BASE_URL . '/login.php');

$uid = currentUser()['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$myArts = $conn->query("SELECT a.*, c.name cat_name, (SELECT COUNT(*) FROM comments cm WHERE cm.article_id=a.id) cmts FROM articles a LEFT JOIN categories c ON a.category_id=c.id WHERE a.author_id=$uid ORDER BY a.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (!$name) { $error = 'Nama tidak boleh kosong.'; }
    else {
        if ($pass) {
            $stmt = $conn->prepare("UPDATE users SET name=?, password=? WHERE id=?");
            $stmt->bind_param('ssi', $name, $pass, $uid);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->bind_param('si', $name, $uid);
        }
        $stmt->execute();
        $_SESSION['name'] = $name;
        setAlert('success','Profil berhasil disimpan!');
        redirect(BASE_URL . '/profile.php');
    }
}

$pageTitle = 'Profil Saya';
require_once 'includes/header.php';
?>

<h1 class="page-title">Profil Saya</h1>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="grid-2" style="gap:20px;align-items:start">
  <div class="card">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
      <div style="width:52px;height:52px;border-radius:50%;background:var(--primary-lt);color:var(--primary-dk);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700">
        <?= strtoupper(substr($user['name'],0,1)) ?>
      </div>
      <div>
        <div style="font-weight:700;font-size:16px"><?= e($user['name']) ?></div>
        <div style="font-size:13px;color:var(--gray-500)">@<?= e($user['username']) ?></div>
        <span class="badge badge-<?= $user['role'] ?>"><?= $user['role'] ?></span>
      </div>
    </div>
    <form method="POST">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="name" value="<?= e($user['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" value="<?= e($user['username']) ?>" disabled style="background:var(--gray-100);color:var(--gray-500)">
      </div>
      <div class="form-group">
        <label>Password Baru <span style="color:var(--gray-500);font-weight:400">(kosongkan jika tidak diubah)</span></label>
        <input type="text" name="password" placeholder="Password baru...">
        <div style="font-size:11px;color:var(--gray-500);margin-top:4px">Password disimpan tanpa enkripsi (plaintext)</div>
      </div>
      <button type="submit" class="btn btn-primary">Simpan Profil</button>
    </form>
  </div>

  <div class="card">
    <h3 class="section-title">Artikel Saya (<?= count($myArts) ?>)</h3>
    <?php if (empty($myArts)): ?>
      <p class="empty" style="padding:20px 0">Belum ada artikel.</p>
    <?php else: ?>
      <?php foreach ($myArts as $a): ?>
        <div style="padding:10px 0;border-bottom:1px solid var(--gray-100)">
          <a href="<?= BASE_URL ?>/article.php?id=<?= $a['id'] ?>" style="font-weight:600;font-size:14px;color:var(--gray-900)"><?= e($a['title']) ?></a>
          <div style="font-size:12px;color:var(--gray-500);margin-top:2px">
            <span class="badge badge-<?= $a['status'] ?>"><?= $a['status'] ?></span>
            &middot; <?= number_format($a['views']) ?> tayang &middot; <?= $a['cmts'] ?> komentar
            &middot; <a href="<?= BASE_URL ?>/article-form.php?id=<?= $a['id'] ?>">Edit</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
