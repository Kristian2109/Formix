<?php
require_once '../logic/auth.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $message = login_user($email, $password);
    if (!$message) {
        header('Location: index.php');
        exit;
    }
}
?>
<?php include '../templates/header.php'; ?>
<h2>Login</h2>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
<p><?= $message ?? '' ?></p>
<?php include '../templates/footer.php'; ?>
