<?php
require_once '../../../config.php';
require_once '../../../core/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $db = new Database();
        $id = $db->insert('players', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);
        
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>