<?php
require_once __DIR__ . '/auth.php';

function get_forms_db() {
    return new PDO('sqlite:' . __DIR__ . '/../data/formica_forms.db');
}

function init_forms_db() {
    $db = get_forms_db();
    
    // Create forms table
    $db->exec("CREATE TABLE IF NOT EXISTS forms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        password TEXT,
        allow_multiple_submissions INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create form fields table
    $db->exec("CREATE TABLE IF NOT EXISTS form_fields (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        form_id INTEGER NOT NULL,
        type TEXT NOT NULL,
        name TEXT NOT NULL,
        field_order INTEGER NOT NULL,
        is_required INTEGER DEFAULT 0,
        FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
    )");
    
    // Create form submissions table
    $db->exec("CREATE TABLE IF NOT EXISTS form_submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        form_id INTEGER NOT NULL,
        user_id INTEGER,
        submission_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create form field values table
    $db->exec("CREATE TABLE IF NOT EXISTS form_field_values (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        submission_id INTEGER NOT NULL,
        field_id INTEGER NOT NULL,
        value TEXT,
        FOREIGN KEY (submission_id) REFERENCES form_submissions(id) ON DELETE CASCADE,
        FOREIGN KEY (field_id) REFERENCES form_fields(id) ON DELETE CASCADE
    )");
}

// Create a new form
function create_form($user_id, $name, $description, $password, $allow_multiple_submissions) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("INSERT INTO forms (user_id, name, description, password, allow_multiple_submissions) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $description, $password, $allow_multiple_submissions ? 1 : 0]);
    
    return $db->lastInsertId();
}

// Add a field to a form
function add_form_field($form_id, $type, $name, $field_order, $is_required) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("INSERT INTO form_fields (form_id, type, name, field_order, is_required) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$form_id, $type, $name, $field_order, $is_required ? 1 : 0]);
    
    return $db->lastInsertId();
}

// Get a form by ID
function get_form($form_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("SELECT * FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all forms for a user
function get_user_forms($user_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("SELECT * FROM forms WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all fields for a form
function get_form_fields($form_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY field_order ASC");
    $stmt->execute([$form_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Delete a form field
function delete_form_field($field_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("DELETE FROM form_fields WHERE id = ?");
    $stmt->execute([$field_id]);
    
    return $stmt->rowCount() > 0;
}

// Update form field order
function update_field_order($field_id, $new_order) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("UPDATE form_fields SET field_order = ? WHERE id = ?");
    $stmt->execute([$new_order, $field_id]);
    
    return $stmt->rowCount() > 0;
}

// Initialize database on include
init_forms_db();
?> 