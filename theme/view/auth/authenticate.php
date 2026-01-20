<?php
require_once '../../../config.php';
require_once '../../../core/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Missing username or password']);
            exit;
        }
        
        $db = new Database();
        $user = $db->getRow("SELECT id, username, password FROM players WHERE username = ?", [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['player_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>