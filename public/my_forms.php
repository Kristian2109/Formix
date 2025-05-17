<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>My Forms</h2>
    <p>Your forms will appear here once you create them.</p>
</div>

<?php include '../templates/footer.php'; ?> 