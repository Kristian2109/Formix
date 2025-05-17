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

// Get existing fields
$fields = get_form_fields($form_id);

// Handle field operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add new field
    if ($action === 'add_field') {
        $field_type = $_POST['field_type'] ?? '';
        $field_name = $_POST['field_name'] ?? '';
        $field_order = count($fields) + 1; // Add to the end
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        
        if (empty($field_name) || empty($field_type)) {
            $message = "Field name and type are required";
        } else {
            $field_id = add_form_field($form_id, $field_type, $field_name, $field_order, $is_required);
            if ($field_id) {
                // Refresh fields list
                $fields = get_form_fields($form_id);
                $message = "Field added successfully";
            } else {
                $message = "Failed to add field";
            }
        }
    }
    
    // Delete field
    if ($action === 'delete_field' && isset($_POST['field_id'])) {
        $field_id = $_POST['field_id'];
        if (delete_form_field($field_id)) {
            // Refresh fields list
            $fields = get_form_fields($form_id);
            $message = "Field deleted successfully";
        } else {
            $message = "Failed to delete field";
        }
    }
}

// Helper function to display field type nicely
function get_field_type_label($type) {
    switch ($type) {
        case 'text':
            return 'Text Input';
        case 'number':
            return 'Number Input';
        case 'textarea':
            return 'Text Area';
        default:
            return ucfirst($type);
    }
}
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>Edit Form: <?= htmlspecialchars($form['name']) ?></h2>
    
    <?php if ($message): ?>
        <p class="<?= strpos($message, 'success') !== false ? 'success-message' : 'error-message' ?>"><?= $message ?></p>
    <?php endif; ?>
    
    <div class="form-builder">
        <div class="form-builder-header">
            <p>Add fields to your form below. You can rearrange them by dragging and dropping.</p>
        </div>
        
        <!-- Form Fields Section -->
        <div class="form-section">
            <div class="section-title">Form Fields</div>
            
            <?php if (empty($fields)): ?>
                <p>Your form has no fields yet. Add some fields below.</p>
            <?php else: ?>
                <div class="fields-list" id="fieldsContainer">
                    <?php foreach ($fields as $field): ?>
                        <div class="field-item" data-field-id="<?= $field['id'] ?>">
                            <div class="field-header">
                                <div>
                                    <span class="drag-handle"><i class="fas fa-grip-lines"></i></span>
                                    <span class="field-type"><?= get_field_type_label($field['type']) ?></span>
                                </div>
                                <div class="field-controls">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_field">
                                        <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                        <button type="submit" class="field-control-btn field-delete" onclick="return confirm('Are you sure you want to delete this field?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="field-body">
                                <strong><?= htmlspecialchars($field['name']) ?></strong>
                                <?php if ($field['is_required']): ?>
                                    <span class="field-required">*Required</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Add Field Section -->
        <div class="form-section">
            <div class="section-title">Add a New Field</div>
            
            <form method="POST" class="field-editor">
                <input type="hidden" name="action" value="add_field">
                
                <div class="editor-row">
                    <div class="editor-field">
                        <label for="field_name">Field Name</label>
                        <input type="text" id="field_name" name="field_name" required>
                        <p class="hint-text">This will be shown as the field label to your form respondents</p>
                    </div>
                    
                    <div class="editor-field">
                        <label>Field Type</label>
                        <div class="field-type-selector">
                            <div class="field-type-option">
                                <input type="radio" id="type_text" name="field_type" value="text" checked>
                                <label for="type_text">
                                    <i class="fas fa-font"></i>
                                    Text Input
                                </label>
                            </div>
                            <div class="field-type-option">
                                <input type="radio" id="type_number" name="field_type" value="number">
                                <label for="type_number">
                                    <i class="fas fa-hashtag"></i>
                                    Number
                                </label>
                            </div>
                            <div class="field-type-option">
                                <input type="radio" id="type_textarea" name="field_type" value="textarea">
                                <label for="type_textarea">
                                    <i class="fas fa-align-left"></i>
                                    Text Area
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="editor-row">
                    <div class="editor-field">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" name="is_required" id="is_required">
                            <label for="is_required">Required Field</label>
                        </div>
                        <p class="hint-text">If checked, users must fill out this field to submit the form</p>
                    </div>
                </div>
                
                <div class="preview-section">
                    <div class="preview-title">Field Preview</div>
                    <div id="fieldPreview">
                        <!-- Preview will be updated via JavaScript -->
                        <div class="form-group">
                            <label>Sample Field <span class="field-required" style="display: none;">*</span></label>
                            <input type="text" class="preview-input">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Field</button>
                </div>
            </form>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions">
            <div class="action-left">
                <a href="my_forms.php" class="btn btn-secondary">Back to My Forms</a>
                <a href="preview_form.php?id=<?= $form_id ?>" class="btn btn-secondary">Preview Form</a>
            </div>
            <a href="publish_form.php?id=<?= $form_id ?>" class="btn btn-primary">Publish Form</a>
        </div>
    </div>
</div>

<script>
// Simple preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="field_type"]');
    const isRequired = document.getElementById('is_required');
    const fieldName = document.getElementById('field_name');
    const previewContainer = document.getElementById('fieldPreview');
    const requiredMark = previewContainer.querySelector('.field-required');
    let previewInput = previewContainer.querySelector('.preview-input');
    
    // Update preview on change
    function updatePreview() {
        // Update label
        previewLabel.textContent = fieldName.value || 'Sample Field';
        
        // Show/hide required marker
        requiredMark.style.display = isRequired.checked ? 'inline' : 'none';
        
        // Get selected field type
        let selectedType = 'text'; // default
        typeRadios.forEach(radio => {
            if (radio.checked) {
                selectedType = radio.value;
            }
        });
        
        // Update input type
        if (selectedType === 'textarea') {
            if (previewInput.tagName !== 'TEXTAREA') {
                const textarea = document.createElement('textarea');
                textarea.className = 'preview-input';
                textarea.rows = 3;
                previewInput.replaceWith(textarea);
                previewInput = textarea;
            }
        } else {
            if (previewInput.tagName === 'TEXTAREA') {
                const input = document.createElement('input');
                input.type = selectedType;
                input.className = 'preview-input';
                previewInput.replaceWith(input);
                previewInput = input;
            } else {
                previewInput.type = selectedType;
            }
        }
    }
    
    // Add event listeners to all radio buttons
    typeRadios.forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });
    
    isRequired.addEventListener('change', updatePreview);
    fieldName.addEventListener('input', updatePreview);
    
    // Initialize preview
    updatePreview();
});
</script>

<?php include '../templates/footer.php'; ?> 