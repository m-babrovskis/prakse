<?php

class GameCore {
    protected $db;
    public function __construct() {
        $this->db = new Database();
    }

    public function playerAuth(){
        if (isset($_SESSION['user_id'])) {
            return $this->db->getRow("SELECT id, username, email FROM players WHERE id = ?", [$_SESSION['user_id']]);
        }
        return null;
    }

    public function playerId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
}
?>