<?php
require_once 'includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL);

// Fetch article
$stmt = $conn->prepare("SELECT a.*, u.name author_name, u.id author_id, c.name cat_name, c.id cat_id
                         FROM articles a
                         LEFT JOIN users u ON a.author_id=u.id
                         LEFT JOIN categories c ON a.category_id=c.id
                         WHERE a.id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$art = $stmt->get_result()->fetch_assoc();

if (!$art || ($art['status'] !== 'published' && !isLoggedIn())) {
    redirect(BASE_URL);
}

// Increment views
$conn->query("UPDATE articles SET views=views+1 WHERE id=$id");
$art['views']++;

// Tags
$tags = $conn->query("SELECT t.name FROM tags t JOIN article_tags at2 ON t.id=at2.tag_id WHERE at2.article_id=$id")->fetch_all(MYSQLI_ASSOC);
$tags = array_column($tags, 'name');

// Comments
$comments = $conn->query("SELECT * FROM comments WHERE article_id=$id ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $cName  = trim($_POST['cmt_name']  ?? '');
    $cEmail = trim($_POST['cmt_email'] ?? '');
    $cText  = trim($_POST['cmt_text']  ?? '');
    if ($cName && $cEmail && $cText) {
        $stmt = $conn->prepare("INSERT INTO comments (article_id,name,email,text) VALUES (?,?,?,?)");
        $stmt->bind_param('isss', $id, $cName, $cEmail, $cText);
        $stmt->execute();
        setAlert('success','Komentar berhasil dikirim!');
        redirect(BASE_URL . "/article.php?id=$id#comments");
    } else {
        $cError = 'Harap isi semua field komentar.';
    }
}

$canEdit = isLoggedIn() && (currentUser()['user_id'] == $art['author_id'] || isAdmin());
$pageTitle = $art['title'];
require_once 'includes/header.php';
?>

<div style="margin-bottom:12px">
  <a href="<?= BASE_URL ?>" class="btn btn-sm">&larr; Kembali ke Beranda</a>
</div>

<div class="card">
  <!-- Meta bar -->
  <div class="tags-row">
    <?php if ($art['cat_name']): ?>
      <a href="<?= BASE_URL ?>/?cat=<?= $art['cat_id'] ?>" class="badge-cat"><?= e($art['cat_name']) ?></a>
    <?php endif; ?>
    <?php foreach ($tags as $tag): ?>
      <a href="<?= BASE_URL ?>/?tag=<?= urlencode($tag) ?>" class="badge-tag"><?= e($tag) ?></a>
    <?php endforeach; ?>
    <?php if ($art['status'] === 'draft'): ?>
      <span class="badge badge-draft">DRAFT</span>
    <?php endif; ?>
    <?php if ($canEdit): ?>
      <a href="<?= BASE_URL ?>/article-form.php?id=<?= $id ?>" class="btn btn-sm" style="margin-left:auto">Edit Artikel</a>
    <?php endif; ?>
  </div>

  <h1 style="font-size:26px;font-weight:700;margin:12px 0 8px;line-height:1.3"><?= e($art['title']) ?></h1>

  <div class="meta-bar">
    <span>Oleh <strong><?= e($art['author_name']) ?></strong></span>
    <span>&middot;</span>
    <span><?= tgl($art['created_at']) ?></span>
    <span>&middot;</span>
    <span><?= number_format($art['views']) ?> tayang</span>
    <span>&middot;</span>
    <span><?= count($comments) ?> komentar</span>
  </div>

  <hr class="divider">

  <div class="article-body"><?= nl2br(e($art['content'])) ?></div>
</div>

<!-- Comments Section -->
<div class="card mt-2" id="comments">
  <h2 class="section-title"><?= count($comments) ?> Komentar</h2>

  <?php if (empty($comments)): ?>
    <p style="font-size:14px;color:var(--gray-500)">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
  <?php else: ?>
    <?php foreach ($comments as $c): ?>
      <div class="comment-item">
        <div class="flex">
          <span class="comment-author"><?= e($c['name']) ?></span>
          <span class="comment-time" style="margin-left:auto"><?= tgl($c['created_at']) ?></span>
          <?php if (isAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/delete-comment.php?id=<?= $c['id'] ?>&article_id=<?= $id ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Hapus komentar ini?')">Hapus</a>
          <?php endif; ?>
        </div>
        <p class="comment-text"><?= nl2br(e($c['text'])) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <hr class="divider">
  <h3 class="mb-2">Tinggalkan Komentar</h3>

  <?php if (!empty($cError)): ?>
    <div class="alert alert-danger"><?= e($cError) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="grid-2">
      <div class="form-group">
        <label>Nama *</label>
        <input type="text" name="cmt_name" value="<?= isLoggedIn() ? e(currentUser()['name']) : '' ?>" placeholder="Nama Anda" required>
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="cmt_email" placeholder="email@contoh.com" required>
      </div>
    </div>
    <div class="form-group">
      <label>Komentar *</label>
      <textarea name="cmt_text" rows="4" placeholder="Tulis komentar Anda di sini..." required></textarea>
    </div>
    <button type="submit" name="submit_comment" class="btn btn-primary">Kirim Komentar</button>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
