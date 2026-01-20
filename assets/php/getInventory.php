<?php
require_once '../../config.php';
require_once '../../core/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $playerId = $_GET['playerId'] ?? null;
        
        if (!$playerId) {
            echo json_encode(['success' => false, 'error' => 'Player ID required']);
            exit;
        }
        
        $db = new Database();
        $inventory = $db->getArray("
            SELECT pi.quantity, pi.cell, i.name, i.categoryId, i.price, i.timeUse, i.energyUse
            FROM playerInventory pi
            JOIN items i ON pi.itemId = i.id
            WHERE pi.playerId = ?
        ", [$playerId]);
        
        echo json_encode(['success' => true, 'inventory' => $inventory]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>