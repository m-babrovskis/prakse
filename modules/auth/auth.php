<?php
class AuthController
{
    public function index(): string
    {
        return view('auth/visual', [
            'title' => 'Login',
            ],'login'
        );
    }

    public function auth(): void
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $db = new Database();
        $user = $db->getRow("SELECT * FROM players WHERE email = ?", [$email]);

        if ($user && verifyPassword($password, $user['password'])) {
            // Lietotājs veiksmīgi autorizējies
            session_start();
            $_SESSION['player_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            redirect('players');
        } else {
            // Neizdevusies autorizācija
            $_SESSION['error'] = 'Nekorekts e-pasts vai parole.';
            redirect('auth');
        }
    }
}
?>