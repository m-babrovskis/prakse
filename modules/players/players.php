<?php
class PlayersController extends GameCore
{
    public function index(): string
    {

        $players = $this->db->getArray("SELECT id, username, email FROM players");
        
        return view('players/index', [
            'title' => 'Lietotāji',
            'players' => $players,
            'player' => $this->playerAuth(),
        ]);
    }

    public function delete(int $id)
    {
        $this->db->delete("players", "id = ?", [$id]);
        
        return redirect('players');
    }

    public function form(?int $id = null): string
    {
        if ($id) {
            $players = $this->db->getRow("SELECT * FROM players WHERE id = ?", [$id]);
        } else {
            $players = [];
        }

        return view('players/form', [
            'title' => 'Rediģēt lietotāju',
            'players' => $players,
        ]);
    }

    public function save(?int $id = null): void
    {
        $data = [
            'id' => $id ?? null,
            'username' => $_POST['username'],
            'email' => $_POST['email'],
        ];
        
        // Hashē paroli tikai, ja tā nav tukša (update gadījumā var atstāt nemainītu)
        if (!empty($_POST['password'])) {
            $data['password'] = hashPassword($_POST['password']);
        }
        
        $this->db->save("players", $data, 'id');

       redirect('players');
    }

    public function playersList(): void
    {
        $players = $this->db->getArray("SELECT id, username, email FROM players");
        
        jsonResponse($players);
    }
}
?>