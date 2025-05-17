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
<div class="container">
    <h2>Login to Your Account</h2>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Sign In</button>
        
        <?php if (isset($message)): ?>
            <p class="error-message"><?= $message ?></p>
        <?php endif; ?>
        
        <p class="form-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </form>
</div>
<?php include '../templates/footer.php'; ?>
