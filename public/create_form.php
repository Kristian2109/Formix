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
    <h2>Create a New Form</h2>
    <p>Form creation page is under development.</p>
</div>

<?php include '../templates/footer.php'; ?> 