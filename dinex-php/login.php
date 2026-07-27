<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $error = 'Incorrect username or password.';
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header('Location: index.php');
        exit;
    }
}

$active = '';
$hidePreloader = true;
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><img src="assets/logo.png" alt="DineX"></div>
    <div class="auth-title">Log in</div>
    <div class="auth-sub">Welcome back — log in to your dashboard.</div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <div class="auth-field">
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="auth-field">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button class="auth-submit" type="submit">Log in</button>
    </form>

    <div class="auth-switch">
      New to DineX? <a href="signup.php">Create an account</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
