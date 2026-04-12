<?php
require_once 'includes/config.php';
if (isLoggedIn()) redirect(BASE_URL);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['name']     = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        setAlert('success', 'Selamat datang, ' . $user['name'] . '!');
        redirect(BASE_URL);
    } else {
        $error = 'Username atau password salah.';
    }
}

$pageTitle = 'Masuk';
require_once 'includes/header.php';
?>

<div class="login-wrap">
  <div class="login-card card">
    <h2 style="font-size:22px;font-weight:700;margin-bottom:4px">Masuk ke <?= SITE_NAME ?></h2>
    <p style="font-size:13px;color:var(--gray-500);margin-bottom:20px">Login sebagai admin atau author</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="Masukkan username" autofocus required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="text" name="password" placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:4px">Masuk</button>
    </form>

    <div class="demo-box">
      <strong>Akun Demo:</strong>
      admin &nbsp;/ admin123 &nbsp;— Admin<br>
      budi &nbsp;&nbsp;/ budi123 &nbsp;— Author<br>
      siti &nbsp;&nbsp;/ siti123 &nbsp;— Author
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
