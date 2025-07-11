<?php
// Simple API endpoint to serve projects data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Set cache headers for better performance
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$projects_file = '../data/projects.json';

if (file_exists($projects_file)) {
    $projects_data = file_get_contents($projects_file);
    if ($projects_data !== false) {
        $projects = json_decode($projects_data, true);
        if ($projects === null) {
            // JSON decode failed, return empty array
            echo json_encode([]);
        } else {
            echo json_encode($projects);
        }
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?> 