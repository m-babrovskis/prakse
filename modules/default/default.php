<?php
class DefaultController extends GameCore
{
    public function index(): string
    {
        // Check if player is logged in
        if (!isset($_SESSION['player_id'])) {
            header('Location: /auth');
            exit;
        }
        
        // Atgriež tikai skata saturu (bez layout), Router ielādēs layout
        return renderView('default/index', [
            'title' => 'Sveicināti GameCore!',
            'lead' => 'Šis ir noklusētais moduļa skats, kas tiek ielādēts caur Router + layout.',
            'player' => $this->playerAuth(),
        ]);
    }
}
?>