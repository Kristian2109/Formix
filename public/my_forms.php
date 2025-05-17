<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get all forms for the user
$forms = get_user_forms($_SESSION['user_id']);
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <h2>My Forms</h2>
    
    <div class="my-forms-header">
        <a href="create_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Form
        </a>
    </div>
    
    <?php if (empty($forms)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>No Forms Yet</h3>
            <p>You haven't created any forms yet. Click the button above to get started.</p>
        </div>
    <?php else: ?>
        <div class="forms-list">
            <?php foreach ($forms as $form): ?>
                <div class="form-card">
                    <div class="form-card-header">
                        <h3><?= htmlspecialchars($form['name']) ?></h3>
                        <div class="form-card-date">
                            Created: <?= date('M j, Y', strtotime($form['created_at'])) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($form['description'])): ?>
                        <div class="form-card-description">
                            <?= htmlspecialchars($form['description']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-card-actions">
                        <a href="edit_form.php?id=<?= $form['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="preview_form.php?id=<?= $form['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <a href="view_responses.php?id=<?= $form['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Responses
                        </a>
                        <a href="form_submissions_chart.php?id=<?= $form['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-chart-line"></i> Analytics
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Additional styles for the forms list */
.my-forms-header {
    margin-bottom: 2rem;
    display: flex;
    justify-content: flex-end;
}

.forms-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-card {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow);
    padding: 1.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.form-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.form-card-header {
    border-bottom: 1px solid var(--primary-light);
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}

.form-card-header h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.form-card-date {
    color: #888;
    font-size: 0.9rem;
}

.form-card-description {
    min-height: 60px;
    margin-bottom: 1rem;
    color: var(--text-color);
    font-size: 0.95rem;
}

.form-card-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
}

/* Empty state styling */
.empty-state {
    text-align: center;
    padding: 3rem;
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: var(--shadow);
}

.empty-state-icon {
    font-size: 4rem;
    color: var(--primary-light);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.empty-state p {
    color: var(--text-color);
    max-width: 500px;
    margin: 0 auto;
}
</style>

<?php include '../templates/footer.php'; ?> 