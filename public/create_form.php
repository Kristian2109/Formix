<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$form_id = null;

// Handle form creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_form') {
    $name = $_POST['form_name'] ?? '';
    $description = $_POST['form_description'] ?? '';
    $password = $_POST['form_password'] ?? '';
    $allow_multiple = isset($_POST['allow_multiple']) ? true : false;
    
    if (empty($name)) {
        $message = "Form name is required";
    } else {
        $form_id = create_form($_SESSION['user_id'], $name, $description, $password, $allow_multiple);
        if ($form_id) {
            header("Location: edit_form.php?id={$form_id}");
            exit;
        } else {
            $message = "Failed to create the form";
        }
    }
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>Create a New Form</h2>
    
    <?php if ($message): ?>
        <p class="error-message"><?= $message ?></p>
    <?php endif; ?>
    
    <div class="form-builder">
        <div class="form-builder-header">
            <p>Start by providing the basic information about your form. Once created, you'll be able to add fields.</p>
        </div>
        
        <form method="POST" action="create_form.php">
            <input type="hidden" name="action" value="create_form">
            
            <div class="form-section">
                <div class="section-title">Form Information</div>
                
                <div class="form-group">
                    <label for="form_name">Form Name</label>
                    <input type="text" id="form_name" name="form_name" required>
                    <p class="hint-text">This will be displayed as the title of your form</p>
                </div>
                
                <div class="form-group">
                    <label for="form_description">Description (Optional)</label>
                    <textarea id="form_description" name="form_description" rows="3"></textarea>
                    <p class="hint-text">Provide instructions or context for your form</p>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">Form Settings</div>
                
                <div class="form-group">
                    <label for="form_password">Password Protection (Optional)</label>
                    <input type="password" id="form_password" name="form_password">
                    <p class="hint-text">If set, users will need this password to access your form</p>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="allow_multiple" id="allow_multiple">
                        Allow multiple submissions from the same user
                    </label>
                    <p class="hint-text">If checked, users can submit the form multiple times</p>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Form</button>
            </div>
        </form>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 