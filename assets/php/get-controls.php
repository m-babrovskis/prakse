<?php
session_start();
require_once '../../config.php';
require_once '../../core/database.php';

// Debug logging
error_log('get-controls.php: Starting execution');
error_log('get-controls.php: Session data: ' . print_r($_SESSION, true));

// Get player controls from database
if (!isset($_SESSION['player_id'])) {
    error_log('get-controls.php: No player_id in session');
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$db = new Database();
$playerId = $_SESSION['player_id'];
error_log('get-controls.php: Player ID: ' . $playerId);

// Get player controls or default controls (playerId = 0)
$controls = $db->getRow(
    "SELECT * FROM playerSettings WHERE playerId = ? OR playerId = 0 ORDER BY playerId DESC LIMIT 1",
    [$playerId]
);

error_log('get-controls.php: Database query result: ' . print_r($controls, true));

if ($controls) {
    $keyBindings = [
        'navigateLeft' => $controls['navigateLeft'],
        'navigateRight' => $controls['navigateRight'],
        'navigateUp' => $controls['navigateUp'],
        'navigateDown' => $controls['navigateDown'],
        'openInventory' => $controls['openInventory'],
        'openShop' => $controls['openShop'],
        'openSettings' => $controls['openSettings'],
        'confirm' => $controls['comfirm'],
        'category1' => $controls['category1'],
        'category2' => $controls['category2'],
        'category3' => $controls['category3'],
        'category4' => $controls['category4'],
        'moveUp' => $controls['moveUp'],
        'moveDown' => $controls['moveDown'],
        'moveLeft' => $controls['moveLeft'],
        'moveRight' => $controls['moveRight'],
        'close' => $controls['close']
    ];
    
    error_log('get-controls.php: Prepared keyBindings: ' . print_r($keyBindings, true));
    
    header('Content-Type: application/json');
    echo json_encode($keyBindings);
    error_log('get-controls.php: JSON response sent successfully');
} else {
    error_log('get-controls.php: No controls found in database');
    http_response_code(404);
    echo json_encode(['error' => 'No controls found']);
}
?>