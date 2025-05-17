<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formica - Form Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Main CSS (Required) -->
    <link rel="stylesheet" href="assets/css/main.css">
    
    <?php
    // Get current page
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    
    // Load page-specific CSS
    switch ($current_page) {
        case 'index':
            echo '<link rel="stylesheet" href="assets/css/pages/home.css">';
            break;
        case 'login':
        case 'register':
        case 'create_form':
        case 'my_forms':
        case 'my_answers':
            echo '<link rel="stylesheet" href="assets/css/pages/forms.css">';
            break;
        default:
            // Default CSS if needed
            break;
    }
    ?>
</head>

<body>
    <nav>
        <div class="nav-brand">
            <a href="index.php" class="logo">Formica</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="create_form.php">Create Form</a>
                <a href="my_forms.php">My Forms</a>
                <a href="my_answers.php">My Answers</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <hr>