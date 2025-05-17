<?php
session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$form_id = $_GET['id'] ?? null;

if (!$form_id) {
    header('Location: my_forms.php');
    exit;
}

$form = get_form($form_id);
if (!$form || $form['user_id'] != $_SESSION['user_id']) {
    header('Location: my_forms.php');
    exit;
}

$fields = get_form_fields($form_id);
if (empty($fields)) {
    header('Location: view_responses.php?id=' . $form_id);
    exit;
}

$submissions = get_form_submissions($form_id);

$filename = 'form_responses_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($form['name'])) . '_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

$headers = array();

$headers[] = 'Submission Date';

if ($form['require_auth']) {
    $headers[] = 'Email';
}

foreach ($fields as $field) {
    $headers[] = $field['name'];
}

fputcsv($output, $headers, ',', '"', '\\');

if (!empty($submissions)) {
    foreach ($submissions as $submission) {
        $row = array();
        
        $row[] = date('Y-m-d H:i:s', strtotime($submission['submission_time']));
        
        if ($form['require_auth']) {
            $row[] = $submission['user_email'] ?? 'Anonymous';
        }
        
        $values = get_submission_values($submission['id']);
        
        $valuesByFieldId = array();
        foreach ($values as $value) {
            $valuesByFieldId[$value['field_id']] = $value['value'];
        }
        
        foreach ($fields as $field) {
            $row[] = $valuesByFieldId[$field['id']] ?? '';
        }
        
        fputcsv($output, $row, ',', '"', '\\');
    }
} else {
    fputcsv($output, ['No submissions available for this form'], ',', '"', '\\');
}

fclose($output);
exit;
?> 