<?php
require_once 'includes/config.php';
$pageTitle = 'Beranda';

// Filter
$catFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$tagFilter  = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$search     = isset($_GET['q'])   ? trim($_GET['q'])   : '';

// Pagination
$perPage = 6;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Build WHERE
$where = ["a.status = 'published'"];
$params = [];
$types  = '';

if ($catFilter) {
    $where[]  = 'a.category_id = ?';
    $params[] = $catFilter;
    $types   .= 'i';
}
if ($tagFilter !== '') {
    $where[]  = 'EXISTS (SELECT 1 FROM article_tags at2 JOIN tags t2 ON at2.tag_id=t2.id WHERE at2.article_id=a.id AND t2.name=?)';
    $params[] = $tagFilter;
    $types   .= 's';
}
if ($search !== '') {
    $where[]  = '(a.title LIKE ? OR a.content LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count
$countSQL = "SELECT COUNT(*) as total FROM articles a $whereSQL";
$stmt = $conn->prepare($countSQL);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = (int)ceil($total / $perPage);

// Articles
$sql = "SELECT a.*, u.name author_name, c.name cat_name, c.id cat_id,
               (SELECT COUNT(*) FROM comments cm WHERE cm.article_id=a.id) comment_count
        FROM articles a
        LEFT JOIN users u ON a.author_id=u.id
        LEFT JOIN categories c ON a.category_id=c.id
        $whereSQL
        ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
$allParams  = array_merge($params, [$perPage, $offset]);
$allTypes   = $types . 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tags for each article
foreach ($articles as &$art) {
    $ts = $conn->prepare("SELECT t.name FROM tags t JOIN article_tags at2 ON t.id=at2.tag_id WHERE at2.article_id=?");
    $ts->bind_param('i', $art['id']);
    $ts->execute();
    $art['tags'] = array_column($ts->get_result()->fetch_all(MYSQLI_ASSOC), 'name');
}
unset($art);

// Sidebar: categories
$cats = $conn->query("SELECT c.*, COUNT(a.id) cnt FROM categories c LEFT JOIN articles a ON a.category_id=c.id AND a.status='published' GROUP BY c.id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);

// Sidebar: all tags
$allTags = $conn->query("SELECT t.name, COUNT(at2.article_id) cnt FROM tags t JOIN article_tags at2 ON t.id=at2.tag_id JOIN articles a ON at2.article_id=a.id AND a.status='published' GROUP BY t.id ORDER BY cnt DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);

// Recent articles
$recent = $conn->query("SELECT id,title,created_at FROM articles WHERE status='published' ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<div class="page-grid">
  <!-- Articles -->
  <div>
    <!-- Search bar -->
    <form method="GET" action="" style="margin-bottom:16px;display:flex;gap:8px">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari artikel..." style="flex:1">
      <button type="submit" class="btn btn-primary">Cari</button>
      <?php if ($search || $catFilter || $tagFilter): ?>
        <a href="<?= BASE_URL ?>" class="btn">Reset</a>
      <?php endif; ?>
    </form>

    <?php if ($catFilter): $catName = $conn->query("SELECT name FROM categories WHERE id=$catFilter")->fetch_assoc()['name'] ?? ''; ?>
      <p class="mb-2" style="font-size:14px;color:var(--gray-500)">Kategori: <strong><?= e($catName) ?></strong></p>
    <?php endif; ?>
    <?php if ($tagFilter): ?>
      <p class="mb-2" style="font-size:14px;color:var(--gray-500)">Tag: <strong><?= e($tagFilter) ?></strong></p>
    <?php endif; ?>

    <h1 class="page-title">
      <?= $search ? 'Hasil pencarian: "'.e($search).'"' : 'Artikel Terbaru' ?>
    </h1>

    <?php if (empty($articles)): ?>
      <div class="empty">Tidak ada artikel yang ditemukan.</div>
    <?php else: ?>
      <?php foreach ($articles as $art): ?>
        <div class="article-card">
          <div class="tags-row">
            <?php if ($art['cat_name']): ?>
              <a href="?cat=<?= $art['cat_id'] ?>" class="badge-cat"><?= e($art['cat_name']) ?></a>
            <?php endif; ?>
            <?php foreach (array_slice($art['tags'],0,3) as $tag): ?>
              <a href="?tag=<?= urlencode($tag) ?>" class="badge-tag"><?= e($tag) ?></a>
            <?php endforeach; ?>
          </div>
          <h2><a href="<?= BASE_URL ?>/article.php?id=<?= $art['id'] ?>"><?= e($art['title']) ?></a></h2>
          <p class="article-excerpt"><?= e(excerpt($art['content'], 160)) ?></p>
          <div class="article-meta">
            <strong><?= e($art['author_name']) ?></strong> &middot;
            <?= tgl($art['created_at']) ?> &middot;
            <?= number_format($art['views']) ?> tayang &middot;
            <?= $art['comment_count'] ?> komentar
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($i=1; $i<=$totalPages; $i++): ?>
            <?php
              $qp = $_GET;
              $qp['page'] = $i;
              $qs = http_build_query($qp);
            ?>
            <?php if ($i === $page): ?>
              <span class="current"><?= $i ?></span>
            <?php else: ?>
              <a href="?<?= $qs ?>"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <aside class="sidebar">
    <?php if (isLoggedIn()): ?>
    <div class="card-sm">
      <a href="<?= BASE_URL ?>/article-form.php" class="btn btn-primary btn-full">+ Tulis Artikel Baru</a>
    </div>
    <?php endif; ?>

    <div class="card-sm widget">
      <h3>Kategori</h3>
      <div class="cat-list">
        <?php foreach ($cats as $c): ?>
          <a href="?cat=<?= $c['id'] ?>">
            <span><?= e($c['name']) ?></span>
            <span><?= $c['cnt'] ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card-sm widget">
      <h3>Tag Populer</h3>
      <div class="tags-row" style="margin-top:4px">
        <?php foreach ($allTags as $t): ?>
          <a href="?tag=<?= urlencode($t['name']) ?>" class="badge-tag"><?= e($t['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card-sm widget">
      <h3>Artikel Terbaru</h3>
      <?php foreach ($recent as $r): ?>
        <div style="padding:6px 0;border-bottom:1px solid var(--gray-100)">
          <a href="<?= BASE_URL ?>/article.php?id=<?= $r['id'] ?>" style="font-size:13px;color:var(--gray-900)"><?= e($r['title']) ?></a>
          <div style="font-size:11px;color:var(--gray-500)"><?= tgl($r['created_at']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </aside>
</div>

<?php require_once 'includes/footer.php'; ?>
