<?php
function get_db() {
    return new PDO('sqlite:' . __DIR__ . '/../data/formica_auth.db');
}

function register_user($email, $password) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Invalid email.";
    if (strlen($password) < 6) return "Password too short.";

    $db = get_db();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return "Email already registered.";

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hash]);
    return "Registration successful.";
}

function login_user($email, $password) {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        return null;
    }
    return "Invalid credentials.";
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}
?>