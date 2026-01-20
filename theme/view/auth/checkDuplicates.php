<?php
require_once '../../../config.php';
require_once '../../../core/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        
        $db = new Database();
        $result = $db->getRow("SELECT username, email FROM players WHERE username = ? OR email = ?", [$username, $email]);
        
        if ($result) {
            if ($result['username'] === $username) {
                echo json_encode(['exists' => true, 'field' => 'username']);
            } else {
                echo json_encode(['exists' => true, 'field' => 'email']);
            }
        } else {
            echo json_encode(['exists' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>