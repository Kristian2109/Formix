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

// Get form fields - we need these for the CSV headers and structure
$fields = get_form_fields($form_id);
if (empty($fields)) {
    // If no fields exist, redirect back
    header('Location: view_responses.php?id=' . $form_id);
    exit;
}

// Get all submissions for this form
$submissions = get_form_submissions($form_id);

// Prepare CSV filename (sanitize form name for filename)
$filename = 'form_responses_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($form['name'])) . '_' . date('Y-m-d') . '.csv';

// Set headers for file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Prepare headers row
$headers = array();

// Add submission date as first column
$headers[] = 'Submission Date';

// Add email column if form requires authentication
if ($form['require_auth']) {
    $headers[] = 'Email';
}

// Add field names as headers
foreach ($fields as $field) {
    $headers[] = $field['name'];
}

// Write headers row
fputcsv($output, $headers, ',', '"', '\\');

// If there are submissions
if (!empty($submissions)) {
    // Write data rows
    foreach ($submissions as $submission) {
        $row = array();
        
        // Add submission date
        $row[] = date('Y-m-d H:i:s', strtotime($submission['submission_time']));
        
        // Add email if form requires authentication
        if ($form['require_auth']) {
            $row[] = $submission['user_email'] ?? 'Anonymous';
        }
        
        // Get all values for this submission
        $values = get_submission_values($submission['id']);
        
        // Create a lookup array for quick access to values by field_id
        $valuesByFieldId = array();
        foreach ($values as $value) {
            $valuesByFieldId[$value['field_id']] = $value['value'];
        }
        
        // Add field values in the same order as headers
        foreach ($fields as $field) {
            // Use empty string if no value exists for this field
            $row[] = $valuesByFieldId[$field['id']] ?? '';
        }
        
        // Write the row
        fputcsv($output, $row, ',', '"', '\\');
    }
} else {
    // If no submissions, write a message
    fputcsv($output, ['No submissions available for this form'], ',', '"', '\\');
}

// Close the output stream
fclose($output);
exit;
?> 