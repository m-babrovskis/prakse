<?php
//vienkāršo darbu ar MariaDB / MySQL.
class Database //definē jaunu PHP klasi
{
    private $pdo;//mainīgais, kurā tiks glabāts PDO savienojums (objekts, kas runā ar DB)

    public function __construct() {
       $host = "localhost";
       $dbname = "gamecore";
       $user = "root";
       $pass = "";
        try 
        {
            $this->pdo = new PDO//izveido jaunu PDO objektu
            (
                "mysql:host=$host;dbname=$dbname;charset=utf8",//nosaka DB serveri, datubāzi un kodējumu
                $user,
                $pass
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//PDO kļūdu režīms
        } catch(PDOException $e) //izvada kludas zinojumu
        {
            die("DB savienojums neizdevās: " . $e->getMessage());
        }
    }

//izpilda SQL komandas, kas NENODOD datus atpakaļ (INSERT, UPDATE, DELETE)
    public function execute($sql, $params = []) 
    {
       $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount(); // atgriež ietekmēto rindu skaitu
    }
//izpilda SQL, kas atgriež datus (SELECT)
    public function query($sql, $params = []) 
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
//Pārbauda, vai datubāzē eksistē tabula ar konkrētu nosaukumu
    public function tableExists($table) 
    {
        $result = $this->pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        return $result !== false;
    }
//Skaita, cik rindu tabulā
    public function isTableEmpty($table) 
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as c FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
        return $count == 0;
    }
}
?>
