<?php
require_once '../logic/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $message = register_user($email, $password);
}
?>
<?php include '../templates/header.php'; ?>
<h2>Register</h2>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>
<p><?= $message ?? '' ?></p>
<?php include '../templates/footer.php'; ?>