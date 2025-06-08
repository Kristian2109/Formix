<?php
function get_db() {
    $dataDir = __DIR__ . '/../data';

    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }

    $dbPath = $dataDir . '/formix.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

function init_auth_db() {
    $db = get_db();
    
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

function register_user($email, $password) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Invalid email.";
    if (strlen($password) < 6) return "Password too short.";

    $db = get_db();
    
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) return "Email already registered.";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $hash]);
        
        return "Registration successful.";
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return "An error occurred during registration.";
    }
}

function login_user($email, $password) {
    $db = get_db();
    try {
        $stmt = $db->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return null;
        }
        return "Invalid credentials.";
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return "An error occurred during login.";
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_email($user_id) {
    if (!$user_id) return null;
    
    $db = get_db();
    try {
        $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['email'] : null;
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

init_auth_db();
?>