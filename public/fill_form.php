<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

$form_id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';
$password_required = false;
$form_authenticated = false;

if (!$form_id) {
    // No form ID provided
    $error_message = "Form not found";
} else {
    // Get the form
    $form = get_form($form_id);
    
    if (!$form) {
        $error_message = "Form not found";
    } else {
        // Check if the form requires authentication
        if ($form['require_auth'] && !isset($_SESSION['user_id'])) {
            // Authentication required, but user is not logged in
            header("Location: login.php?redirect=fill_form.php?id=$form_id");
            exit;
        }
        
        // Check if the user has already submitted this form and it doesn't allow multiple submissions
        if (isset($_SESSION['user_id']) && !$form['allow_multiple_submissions'] && 
            has_user_submitted_form($form_id, $_SESSION['user_id'])) {
            $error_message = "You have already submitted this form.";
        }
        
        // Check if the form requires a password
        if (!empty($form['password'])) {
            $password_required = true;
            
            // Check if password has been submitted and is correct
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_password'])) {
                if ($_POST['form_password'] === $form['password']) {
                    $form_authenticated = true;
                    
                    // Store in session that this form is authenticated
                    $_SESSION['form_authenticated_' . $form_id] = true;
                } else {
                    $error_message = "Incorrect password.";
                }
            }
            
            // Check if the form is already authenticated in the session
            if (isset($_SESSION['form_authenticated_' . $form_id]) && $_SESSION['form_authenticated_' . $form_id]) {
                $form_authenticated = true;
            }
        } else {
            // No password required
            $form_authenticated = true;
        }
        
        // If authenticated and form submitted, process the submission
        if ($form_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_form') {
            $field_values = [];
            $fields = get_form_fields($form_id);
            $validation_errors = [];
            
            // Validate and collect field values
            foreach ($fields as $field) {
                $field_id = $field['id'];
                $field_name = "field_{$field_id}";
                $value = $_POST[$field_name] ?? '';
                
                // Check if required field is empty
                if ($field['is_required'] && empty($value)) {
                    $validation_errors[] = "The field '{$field['name']}' is required.";
                }
                
                // Add to field values
                $field_values[$field_id] = $value;
            }
            
            // If no validation errors, submit the form
            if (empty($validation_errors)) {
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                $submission_id = submit_form($form_id, $field_values, $user_id);
                
                if ($submission_id) {
                    $success_message = "Form submitted successfully!";
                } else {
                    $error_message = "There was an error submitting the form. Please try again.";
                }
            } else {
                $error_message = implode("<br>", $validation_errors);
            }
        }
    }
}

// Get form fields if form exists and is authenticated
$fields = [];
if (isset($form) && $form && $form_authenticated && empty($success_message)) {
    $fields = get_form_fields($form_id);
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <?php if ($error_message): ?>
        <div class="error-message"><?= $error_message ?></div>
        <?php if (strpos($error_message, "already submitted") !== false): ?>
            <div class="action-buttons" style="margin-top: 20px; text-align: center;">
                <a href="index.php" class="btn btn-primary">Return to Home</a>
            </div>
        <?php endif; ?>
    <?php elseif ($success_message): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h2>Thank You!</h2>
            <p><?= $success_message ?></p>
            <div class="action-buttons">
                <?php if ($form['allow_multiple_submissions']): ?>
                    <a href="fill_form.php?id=<?= $form_id ?>" class="btn btn-secondary">Submit Another Response</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary">Return to Home</a>
            </div>
        </div>
    <?php elseif ($password_required && !$form_authenticated): ?>
        <div class="form-password-container">
            <h2>This form is password protected</h2>
            <p>Please enter the password to access this form.</p>
            
            <form method="POST" class="password-form">
                <div class="form-group">
                    <label for="form_password">Password</label>
                    <input type="password" id="form_password" name="form_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Continue</button>
            </form>
        </div>
    <?php elseif (isset($form) && $form): ?>
        <div class="form-container">
            <div class="form-header">
                <h2><?= htmlspecialchars($form['name']) ?></h2>
                <?php if (!empty($form['description'])): ?>
                    <p class="form-description"><?= htmlspecialchars($form['description']) ?></p>
                <?php endif; ?>
                
                <?php if ($form['require_auth'] && isset($_SESSION['user_id'])): ?>
                    <div class="auth-info">
                        <p><i class="fas fa-user"></i> You are submitting this form as <?= htmlspecialchars($_SESSION['email'] ?? 'an authenticated user') ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="public-form">
                <input type="hidden" name="action" value="submit_form">
                
                <?php foreach ($fields as $field): ?>
                    <div class="form-group">
                        <label for="field_<?= $field['id'] ?>">
                            <?= htmlspecialchars($field['name']) ?>
                            <?php if ($field['is_required']): ?>
                                <span class="field-required">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($field['type'] === 'textarea'): ?>
                            <textarea 
                                id="field_<?= $field['id'] ?>" 
                                name="field_<?= $field['id'] ?>" 
                                rows="4"
                                <?= $field['is_required'] ? 'required' : '' ?>
                            ></textarea>
                        <?php else: ?>
                            <input 
                                type="<?= $field['type'] ?>" 
                                id="field_<?= $field['id'] ?>" 
                                name="field_<?= $field['id'] ?>"
                                <?= $field['is_required'] ? 'required' : '' ?>
                            >
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
.form-container {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 2rem;
    max-width: 800px;
    margin: 0 auto;
}

.form-header {
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--primary-light);
    padding-bottom: 1rem;
}

.form-description {
    color: var(--text-color);
    margin-top: 0.5rem;
}

.auth-info {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background-color: #f4f0ff;
    border-radius: 4px;
    font-size: 0.9rem;
}

.auth-info i {
    color: var(--primary-color);
    margin-right: 0.3rem;
}

.form-password-container {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 2rem;
    max-width: 500px;
    margin: 0 auto;
    text-align: center;
}

.password-form {
    max-width: 300px;
    margin: 2rem auto 0;
}

.success-message {
    background-color: #e8f5e9;
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 3rem 2rem;
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.success-message i {
    color: #4caf50;
    font-size: 3rem;
    margin-bottom: 1rem;
}

.success-message h2 {
    color: #2e7d32;
    margin-bottom: 1rem;
}

.action-buttons {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.error-message {
    background-color: #ffebee;
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 1.5rem;
    color: #c62828;
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}
</style>

<?php include '../templates/footer.php'; ?> 