<?php
require_once 'includes/config.php';
if (!isLoggedIn()) redirect(BASE_URL . '/login.php');

$editId = (int)($_GET['id'] ?? 0);
$art    = null;
$artTags = [];

if ($editId) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $art = $stmt->get_result()->fetch_assoc();
    if (!$art) redirect(BASE_URL);
    // Only author or admin can edit
    if (currentUser()['user_id'] != $art['author_id'] && !isAdmin()) {
        setAlert('danger', 'Anda tidak punya izin mengedit artikel ini.');
        redirect(BASE_URL);
    }
    $artTags = array_column(
        $conn->query("SELECT t.name FROM tags t JOIN article_tags at2 ON t.id=at2.tag_id WHERE at2.article_id=$editId")->fetch_all(MYSQLI_ASSOC),
        'name'
    );
}

$cats  = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$error = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $content  = trim($_POST['content']  ?? '');
    $catId    = (int)($_POST['category_id'] ?? 0);
    $status   = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';
    $tagsRaw  = trim($_POST['tags_hidden'] ?? '');
    $tagList  = $tagsRaw ? array_filter(array_map('trim', explode(',', $tagsRaw))) : [];

    if (!$title || !$content || !$catId) {
        $error = 'Harap isi semua field yang wajib (judul, konten, kategori).';
    } else {
        $slug = makeSlug($title);
        // Make slug unique
        $slugBase = $slug;
        $suffix = 1;
        while (true) {
            $chk = $conn->prepare("SELECT id FROM articles WHERE slug=? AND id!=?");
            $tmpId = $editId ?: 0;
            $chk->bind_param('si', $slug, $tmpId);
            $chk->execute();
            if (!$chk->get_result()->fetch_assoc()) break;
            $slug = $slugBase . '-' . $suffix++;
        }
        $authorId = currentUser()['user_id'];

        if ($editId) {
            $stmt = $conn->prepare("UPDATE articles SET title=?,slug=?,content=?,category_id=?,status=?,updated_at=NOW() WHERE id=?");
            $stmt->bind_param('sssisi', $title, $slug, $content, $catId, $status, $editId);
            $stmt->execute();
            $artId = $editId;
            $conn->query("DELETE FROM article_tags WHERE article_id=$artId");
        } else {
            $stmt = $conn->prepare("INSERT INTO articles (title,slug,content,category_id,author_id,status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('sssiis', $title, $slug, $content, $catId, $authorId, $status);
            $stmt->execute();
            $artId = $conn->insert_id;
        }

        // Sync tags
        foreach ($tagList as $tagName) {
            $tagName = substr($tagName, 0, 80);
            $ts = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
            $ts->bind_param('s', $tagName);
            $ts->execute();
            $tagId = $conn->insert_id ?: $conn->query("SELECT id FROM tags WHERE name='".addslashes($tagName)."'")->fetch_assoc()['id'];
            $ins = $conn->prepare("INSERT IGNORE INTO article_tags (article_id,tag_id) VALUES (?,?)");
            $ins->bind_param('ii', $artId, $tagId);
            $ins->execute();
        }

        setAlert('success', $editId ? 'Artikel berhasil diperbarui!' : 'Artikel berhasil disimpan!');
        redirect(BASE_URL . '/article.php?id=' . $artId);
    }
}

$pageTitle = $editId ? 'Edit Artikel' : 'Tulis Artikel Baru';
require_once 'includes/header.php';
?>

<h1 class="page-title"><?= $pageTitle ?></h1>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
  <form method="POST">
    <div class="form-group">
      <label>Judul Artikel *</label>
      <input type="text" name="title" value="<?= e($art['title'] ?? '') ?>" placeholder="Judul artikel..." required>
    </div>

    <div class="grid-2">
      <div class="form-group">
        <label>Kategori *</label>
        <select name="category_id" required>
          <option value="">Pilih kategori...</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (($art['category_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="draft"     <?= (($art['status'] ?? '') !== 'published') ? 'selected' : '' ?>>Draft</option>
          <option value="published" <?= (($art['status'] ?? '') === 'published') ? 'selected' : '' ?>>Publikasi</option>
        </select>
      </div>
    </div>

    <!-- Tag input -->
    <div class="form-group">
      <label>Tag (ketik lalu tekan Enter atau koma)</label>
      <div class="tag-input-wrap" id="tagWrap">
        <?php foreach ($artTags as $t): ?>
          <span class="tag-chip"><?= e($t) ?><span class="tag-remove" data-tag="<?= e($t) ?>">&times;</span></span>
        <?php endforeach; ?>
        <input class="tag-bare" id="tagInput" placeholder="Tambah tag...">
      </div>
      <input type="hidden" name="tags_hidden" id="tagsHidden" value="<?= e(implode(',', $artTags)) ?>">
    </div>

    <div class="form-group">
      <label>Konten Artikel *</label>
      <textarea name="content" rows="14" placeholder="Tulis konten artikel di sini..." required><?= e($art['content'] ?? '') ?></textarea>
    </div>

    <div class="flex">
      <button type="submit" class="btn btn-primary"><?= $editId ? 'Simpan Perubahan' : 'Simpan Artikel' ?></button>
      <a href="<?= BASE_URL ?>" class="btn">Batal</a>
      <?php if ($editId && isAdmin()): ?>
        <a href="<?= BASE_URL ?>/admin/delete-article.php?id=<?= $editId ?>"
           class="btn btn-danger" style="margin-left:auto"
           onclick="return confirm('Hapus artikel ini secara permanen?')">Hapus Artikel</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<script>
(function(){
  const wrap  = document.getElementById('tagWrap');
  const input = document.getElementById('tagInput');
  const hidden= document.getElementById('tagsHidden');
  let tags = hidden.value ? hidden.value.split(',').map(t=>t.trim()).filter(Boolean) : [];

  function sync() { hidden.value = tags.join(','); }

  function addTag(val) {
    val = val.trim();
    if (val && !tags.includes(val)) {
      tags.push(val);
      const chip = document.createElement('span');
      chip.className = 'tag-chip';
      chip.innerHTML = `${val}<span class="tag-remove" data-tag="${val}">&times;</span>`;
      chip.querySelector('.tag-remove').addEventListener('click', removeTag);
      wrap.insertBefore(chip, input);
      sync();
    }
  }

  function removeTag(e) {
    const tag = e.target.dataset.tag;
    tags = tags.filter(t => t !== tag);
    e.target.parentElement.remove();
    sync();
  }

  document.querySelectorAll('.tag-remove').forEach(btn => btn.addEventListener('click', removeTag));

  input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      addTag(input.value);
      input.value = '';
    } else if (e.key === 'Backspace' && !input.value && tags.length) {
      tags.pop();
      wrap.querySelectorAll('.tag-chip').forEach(c => c.remove());
      tags.forEach(t => {
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `${t}<span class="tag-remove" data-tag="${t}">&times;</span>`;
        chip.querySelector('.tag-remove').addEventListener('click', removeTag);
        wrap.insertBefore(chip, input);
      });
      sync();
    }
  });

  wrap.addEventListener('click', () => input.focus());
})();
</script>

<?php require_once 'includes/footer.php'; ?>
