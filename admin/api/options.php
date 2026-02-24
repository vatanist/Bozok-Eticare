<?php
require_once __DIR__ . '/../../config/config.php';
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Yetkisiz']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'search') {
    $q = trim($_GET['q'] ?? '');
    $options = Database::fetchAll("SELECT id, name, type FROM options WHERE name LIKE ? ORDER BY name LIMIT 10", ["%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($options);
    exit;
}

if ($action === 'get_values') {
    $optionId = intval($_GET['option_id']);
    $values = Database::fetchAll("SELECT id, name FROM option_values WHERE option_id = ? ORDER BY sort_order, name", [$optionId]);
    header('Content-Type: application/json');
    echo json_encode($values);
    exit;
}
