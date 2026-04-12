<?php
require_once '../includes/config.php';
if (!isAdmin()) { setAlert('danger','Akses ditolak.'); redirect(BASE_URL); }

$tab = $_GET['tab'] ?? 'articles';
$pageTitle = 'Dasbor Admin';

// Stats
$totalArts  = $conn->query("SELECT COUNT(*) cnt FROM articles")->fetch_assoc()['cnt'];
$pubArts    = $conn->query("SELECT COUNT(*) cnt FROM articles WHERE status='published'")->fetch_assoc()['cnt'];
$totalUsers = $conn->query("SELECT COUNT(*) cnt FROM users")->fetch_assoc()['cnt'];
$totalCmts  = $conn->query("SELECT COUNT(*) cnt FROM comments")->fetch_assoc()['cnt'];

require_once '../includes/header.php';
?>

<h1 class="page-title">Dasbor Admin</h1>

<div class="stats-grid">
  <div class="stat-box"><div class="stat-num"><?= $totalArts ?></div><div class="stat-lbl">Total Artikel</div></div>
  <div class="stat-box"><div class="stat-num"><?= $pubArts ?></div><div class="stat-lbl">Dipublikasi</div></div>
  <div class="stat-box"><div class="stat-num"><?= $totalUsers ?></div><div class="stat-lbl">Pengguna</div></div>
  <div class="stat-box"><div class="stat-num"><?= $totalCmts ?></div><div class="stat-lbl">Komentar</div></div>
</div>

<div class="tabs">
  <a href="?tab=articles"   class="tab-link <?= $tab==='articles'?'active':'' ?>">Artikel</a>
  <a href="?tab=users"      class="tab-link <?= $tab==='users'?'active':'' ?>">Pengguna</a>
  <a href="?tab=categories" class="tab-link <?= $tab==='categories'?'active':'' ?>">Kategori</a>
  <a href="?tab=comments"   class="tab-link <?= $tab==='comments'?'active':'' ?>">Komentar</a>
</div>

<?php if ($tab === 'articles'): ?>
  <!-- ARTICLES -->
  <div class="flex mb-2">
    <h2 class="section-title" style="margin:0">Semua Artikel</h2>
    <a href="<?= BASE_URL ?>/article-form.php" class="btn btn-primary btn-sm" style="margin-left:auto">+ Artikel Baru</a>
  </div>
  <div class="table-wrap card" style="padding:0">
    <table>
      <thead>
        <tr><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Status</th><th>Tayang</th><th>Tanggal</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php
        $arts = $conn->query("SELECT a.*,u.name author_name,c.name cat_name FROM articles a LEFT JOIN users u ON a.author_id=u.id LEFT JOIN categories c ON a.category_id=c.id ORDER BY a.created_at DESC")->fetch_all(MYSQLI_ASSOC);
        foreach ($arts as $a):
        ?>
        <tr>
          <td style="max-width:220px"><a href="<?= BASE_URL ?>/article.php?id=<?= $a['id'] ?>"><?= e($a['title']) ?></a></td>
          <td><?= e($a['author_name']) ?></td>
          <td><?= e($a['cat_name'] ?? '-') ?></td>
          <td><span class="badge badge-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
          <td><?= number_format($a['views']) ?></td>
          <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
          <td>
            <div class="action-btns">
              <a href="<?= BASE_URL ?>/article-form.php?id=<?= $a['id'] ?>" class="btn btn-sm">Edit</a>
              <a href="delete-article.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Hapus artikel ini?')">Hapus</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'users'): ?>
  <!-- USERS -->
  <div class="flex mb-2">
    <h2 class="section-title" style="margin:0">Kelola Pengguna</h2>
  </div>
  <?php
  // Handle add user
  $uError = '';
  if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_user'])) {
      $uName = trim($_POST['u_name'] ?? '');
      $uUser = trim($_POST['u_username'] ?? '');
      $uPass = trim($_POST['u_password'] ?? '');
      $uRole = in_array($_POST['u_role']??'', ['admin','author']) ? $_POST['u_role'] : 'author';
      if (!$uName || !$uUser || !$uPass) $uError = 'Semua field wajib diisi.';
      else {
          $chk = $conn->prepare("SELECT id FROM users WHERE username=?");
          $chk->bind_param('s', $uUser); $chk->execute();
          if ($chk->get_result()->fetch_assoc()) $uError = 'Username sudah digunakan.';
          else {
              $stmt = $conn->prepare("INSERT INTO users (name,username,password,role) VALUES (?,?,?,?)");
              $stmt->bind_param('ssss', $uName, $uUser, $uPass, $uRole);
              $stmt->execute();
              setAlert('success','Pengguna berhasil ditambahkan!');
              redirect('?tab=users');
          }
      }
  }
  ?>
  <div class="card mb-2">
    <h3 style="margin-bottom:12px">Tambah Pengguna Baru</h3>
    <?php if ($uError): ?><div class="alert alert-danger"><?= e($uError) ?></div><?php endif; ?>
    <form method="POST">
      <div class="grid-2">
        <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="u_name" placeholder="Nama lengkap"></div>
        <div class="form-group"><label>Username *</label><input type="text" name="u_username" placeholder="username"></div>
        <div class="form-group"><label>Password *</label><input type="text" name="u_password" placeholder="password (plaintext)"></div>
        <div class="form-group"><label>Role</label>
          <select name="u_role"><option value="author">Author</option><option value="admin">Admin</option></select>
        </div>
      </div>
      <button type="submit" name="add_user" class="btn btn-primary">Tambah Pengguna</button>
    </form>
  </div>

  <div class="table-wrap card" style="padding:0">
    <table>
      <thead><tr><th>Nama</th><th>Username</th><th>Password</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $users = $conn->query("SELECT * FROM users ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td>@<?= e($u['username']) ?></td>
          <td style="font-family:monospace;font-size:13px"><?= e($u['password']) ?></td>
          <td><span class="badge badge-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
          <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['id'] != currentUser()['user_id']): ?>
              <a href="delete-user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Hapus pengguna <?= e($u['name']) ?>?')">Hapus</a>
            <?php else: ?>
              <span style="font-size:12px;color:var(--gray-500)">Anda</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'categories'): ?>
  <!-- CATEGORIES -->
  <?php
  $catError = '';
  if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_cat'])) {
      $catName = trim($_POST['cat_name'] ?? '');
      if (!$catName) $catError = 'Nama kategori tidak boleh kosong.';
      else {
          $slug = makeSlug($catName);
          $stmt = $conn->prepare("INSERT IGNORE INTO categories (name,slug) VALUES (?,?)");
          $stmt->bind_param('ss', $catName, $slug);
          $stmt->execute();
          setAlert('success','Kategori ditambahkan!');
          redirect('?tab=categories');
      }
  }
  ?>
  <div class="card mb-2">
    <h3 style="margin-bottom:12px">Tambah Kategori</h3>
    <?php if ($catError): ?><div class="alert alert-danger"><?= e($catError) ?></div><?php endif; ?>
    <form method="POST" class="flex">
      <input type="text" name="cat_name" placeholder="Nama kategori baru..." style="flex:1">
      <button type="submit" name="add_cat" class="btn btn-primary">Tambah</button>
    </form>
  </div>
  <div class="table-wrap card" style="padding:0">
    <table>
      <thead><tr><th>Nama</th><th>Slug</th><th>Jumlah Artikel</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $cats = $conn->query("SELECT c.*,COUNT(a.id) cnt FROM categories c LEFT JOIN articles a ON a.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);
        foreach ($cats as $c): ?>
        <tr>
          <td><?= e($c['name']) ?></td>
          <td style="font-family:monospace;font-size:13px;color:var(--gray-500)"><?= e($c['slug']) ?></td>
          <td><?= $c['cnt'] ?></td>
          <td><a href="delete-category.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Hapus kategori ini?')">Hapus</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'comments'): ?>
  <!-- COMMENTS -->
  <div class="table-wrap card" style="padding:0">
    <table>
      <thead><tr><th>Nama</th><th>Email</th><th>Komentar</th><th>Artikel</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $cmts = $conn->query("SELECT cm.*,a.title art_title,a.id art_id FROM comments cm LEFT JOIN articles a ON cm.article_id=a.id ORDER BY cm.created_at DESC")->fetch_all(MYSQLI_ASSOC);
        if (empty($cmts)): ?>
          <tr><td colspan="6" class="empty">Belum ada komentar.</td></tr>
        <?php else: foreach ($cmts as $c): ?>
        <tr>
          <td><?= e($c['name']) ?></td>
          <td style="font-size:12px"><?= e($c['email']) ?></td>
          <td style="max-width:200px;font-size:13px"><?= e(substr($c['text'],0,80)) ?>...</td>
          <td><a href="<?= BASE_URL ?>/article.php?id=<?= $c['art_id'] ?>" style="font-size:13px"><?= e(substr($c['art_title'],0,40)) ?>...</a></td>
          <td style="white-space:nowrap;font-size:12px"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
          <td><a href="delete-comment.php?id=<?= $c['id'] ?>&article_id=<?= $c['art_id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Hapus komentar ini?')">Hapus</a></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
