<?php
require_once __DIR__ . '/auth.php';

function get_forms_db() {
    return get_db(); // Use the same database connection
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
        require_auth INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Check if require_auth column exists, if not add it
    $result = $db->query("PRAGMA table_info(forms)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $hasRequireAuth = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'require_auth') {
            $hasRequireAuth = true;
            break;
        }
    }
    if (!$hasRequireAuth) {
        $db->exec("ALTER TABLE forms ADD COLUMN require_auth INTEGER DEFAULT 0");
    }
    
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
function create_form($user_id, $name, $description, $password, $allow_multiple_submissions, $require_auth = 0) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("INSERT INTO forms (user_id, name, description, password, allow_multiple_submissions, require_auth) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $description, $password, $allow_multiple_submissions ? 1 : 0, $require_auth ? 1 : 0]);
    
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

// Submit a form
function submit_form($form_id, $field_values, $user_id = null) {
    $db = get_forms_db();
    
    try {
        $db->beginTransaction();
        
        // Create submission record
        $stmt = $db->prepare("INSERT INTO form_submissions (form_id, user_id) VALUES (?, ?)");
        $stmt->execute([$form_id, $user_id]);
        $submission_id = $db->lastInsertId();
        
        // Insert field values
        $stmt = $db->prepare("INSERT INTO form_field_values (submission_id, field_id, value) VALUES (?, ?, ?)");
        foreach ($field_values as $field_id => $value) {
            $stmt->execute([$submission_id, $field_id, $value]);
        }
        
        $db->commit();
        return $submission_id;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Check if a user has already submitted a form
function has_user_submitted_form($form_id, $user_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM form_submissions WHERE form_id = ? AND user_id = ?");
    $stmt->execute([$form_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

// Get form submissions
function get_form_submissions($form_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("SELECT s.*, u.email as user_email 
                         FROM form_submissions s 
                         LEFT JOIN users u ON s.user_id = u.id
                         WHERE s.form_id = ? 
                         ORDER BY s.submission_time DESC");
    $stmt->execute([$form_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all field values for a submission
function get_submission_values($submission_id) {
    $db = get_forms_db();
    
    $stmt = $db->prepare("SELECT v.*, f.name as field_name, f.type as field_type
                         FROM form_field_values v
                         JOIN form_fields f ON v.field_id = f.id
                         WHERE v.submission_id = ?
                         ORDER BY f.field_order ASC");
    $stmt->execute([$submission_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get a single submission with all its values
function get_submission($submission_id) {
    $db = get_forms_db();
    
    // Get submission info
    $stmt = $db->prepare("SELECT s.*, u.email as user_email, f.name as form_name
                         FROM form_submissions s 
                         LEFT JOIN users u ON s.user_id = u.id
                         JOIN forms f ON s.form_id = f.id
                         WHERE s.id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        return null;
    }
    
    // Get all values for this submission
    $submission['values'] = get_submission_values($submission_id);
    
    return $submission;
}

// Initialize database on include
init_forms_db();
?> 