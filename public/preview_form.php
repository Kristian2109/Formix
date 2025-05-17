<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$form_id = $_GET['id'] ?? null;

if (!$form_id) {
    header('Location: my_forms.php');
    exit;
}

// Check if the form belongs to the current user
$form = get_form($form_id);
if (!$form || $form['user_id'] != $_SESSION['user_id']) {
    header('Location: my_forms.php');
    exit;
}

// Get form fields
$fields = get_form_fields($form_id);
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <div class="preview-header">
        <h2>Form Preview: <?= htmlspecialchars($form['name']) ?></h2>
        <p class="preview-note">This is a preview of how your form will appear to users.</p>
    </div>
    
    <div class="form-preview">
        <?php if (empty($fields)): ?>
            <div class="empty-fields">
                <p>This form doesn't have any fields yet. <a href="edit_form.php?id=<?= $form_id ?>">Add some fields</a> to see a preview.</p>
            </div>
        <?php else: ?>
            <div class="form-container">
                <div class="form-header">
                    <h3><?= htmlspecialchars($form['name']) ?></h3>
                    <?php if (!empty($form['description'])): ?>
                        <p class="form-description"><?= htmlspecialchars($form['description']) ?></p>
                    <?php endif; ?>
                </div>
                
                <form class="public-form">
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
                        <button type="button" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="preview-actions">
        <a href="edit_form.php?id=<?= $form_id ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i> Back to Editor
        </a>
        <a href="publish_form.php?id=<?= $form_id ?>" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Publish Form
        </a>
    </div>
</div>

<style>
.preview-header {
    margin-bottom: 2rem;
    text-align: center;
}

.preview-note {
    color: #777;
    font-style: italic;
}

.form-preview {
    max-width: 800px;
    margin: 0 auto 2rem;
}

.empty-fields {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 2rem;
    text-align: center;
}

.form-container {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 2rem;
}

.form-header {
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--primary-light);
    padding-bottom: 1rem;
}

.form-header h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.form-description {
    color: var(--text-color);
}

.public-form .form-group {
    margin-bottom: 1.5rem;
}

.preview-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}
</style>

<?php include '../templates/footer.php'; ?> 