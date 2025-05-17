<?php
require_once '../logic/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $message = register_user($email, $password);
}
?>
<?php include '../templates/header.php'; ?>
<div class="container">
    <h2>Create Your Account</h2>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Register</button>
        
        <?php if (isset($message)): ?>
            <p class="error-message"><?= $message ?></p>
        <?php endif; ?>
        
        <p class="form-footer">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </form>
</div>
<?php include '../templates/footer.php'; ?>