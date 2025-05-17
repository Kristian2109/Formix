<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';
require_once '../logic/charts.php';

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

// Get date range from request or use default (30 days)
$days_range = isset($_GET['range']) && is_numeric($_GET['range']) ? (int)$_GET['range'] : 30;

// Get chart data
$chart_data = get_form_submissions_chart_data($form_id, $days_range);
?>
<?php include '../templates/header.php'; ?>

<div class="container chart-container">
    <div class="chart-header">
        <h2>Submissions for: <?= htmlspecialchars($form['name']) ?></h2>
        <div class="chart-actions">
            <a href="my_forms.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to My Forms
            </a>
            <a href="view_responses.php?id=<?= $form_id ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-list"></i> View All Responses
            </a>
            <div class="range-selector">
                <label for="range-select">Time Range:</label>
                <select id="range-select" class="form-select" onchange="changeRange(this.value)">
                    <option value="7" <?= $days_range == 7 ? 'selected' : '' ?>>Last 7 days</option>
                    <option value="30" <?= $days_range == 30 ? 'selected' : '' ?>>Last 30 days</option>
                    <option value="90" <?= $days_range == 90 ? 'selected' : '' ?>>Last 90 days</option>
                    <option value="180" <?= $days_range == 180 ? 'selected' : '' ?>>Last 180 days</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="chart-wrapper">
        <canvas id="submissionsChart"></canvas>
    </div>
    
    <?php if (array_sum($chart_data['data']) == 0): ?>
        <div class="no-data-message">
            <p>No submissions found in the selected time period.</p>
            <div class="actions">
                <a href="publish_form.php?id=<?= $form_id ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-share-alt"></i> Share Form
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.chart-container {
    max-width: 900px;
    margin: 0 auto;
    background-color: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 2rem;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.chart-header h2 {
    color: var(--primary-color);
    margin: 0;
}

.chart-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.range-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-select {
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: white;
}

.chart-wrapper {
    height: 400px;
    margin-bottom: 1rem;
}

.no-data-message {
    text-align: center;
    color: #666;
    padding: 2rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-top: 1rem;
}

.no-data-message .actions {
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .chart-actions {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }
    
    .range-selector {
        width: 100%;
    }
    
    .form-select {
        width: 100%;
    }
}
</style>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Parse chart data from PHP
const chartData = <?= json_encode($chart_data) ?>;

// Format dates for display
const formattedLabels = chartData.labels.map(date => {
    const dateObj = new Date(date);
    return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});

// Create the chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('submissionsChart').getContext('2d');
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: formattedLabels,
            datasets: [{
                label: 'Form Submissions',
                data: chartData.data,
                backgroundColor: 'rgba(156, 104, 226, 0.2)',
                borderColor: '#9c68e2',
                borderWidth: 2,
                pointBackgroundColor: '#9c68e2',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            const dateStr = chartData.labels[tooltipItems[0].dataIndex];
                            const date = new Date(dateStr);
                            return date.toLocaleDateString('en-US', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            });
                        }
                    }
                }
            }
        }
    });
});

// Function to change the date range
function changeRange(range) {
    window.location.href = 'form_submissions_chart.php?id=<?= $form_id ?>&range=' + range;
}
</script>

<?php include '../templates/footer.php'; ?> 