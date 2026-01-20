<?php
/**
 * Vienkārša mysqli iesaiņošana ar prepared statements un ērtām metodēm.
 * Atbalsta lasīšanu, inserta/update/delete, transakcijas un upsert.
 *
 * Ātri piemēri:
 *   $db = new Database(); // automātiski ielādē globālo $config['db']
 *   $rows = $db->getArray("SELECT * FROM players WHERE score > ?", [100]);
 *   $id   = $db->insert('players', ['name' => 'John', 'score' => 50]);
 *   $db->upsert('players', ['id' => 1, 'score' => 200]); // insert vai update
 *   $db->update('players', ['id' => 1, 'score' => 120], 'id');
 *   $db->delete('players', 'id = ?', [2]);
 *   // transakcija
 *   $db->begin();
 *   try { $db->insertMany('scores', $batch); $db->commit(); }
 *   catch (Throwable $e) { $db->rollback(); throw $e; }
 *   $query = $db->execute("CREATE TABLE users (
 *       id INT AUTO_INCREMENT PRIMARY KEY, 
 *       username VARCHAR(255), 
 *       email VARCHAR(255), 
 *       password VARCHAR(255)
 *   )"); // create table
 *   // tabulu pārbaudes un pārvaldība
 *   if ($db->tableExists('users')) { ... }
 *   $tables = $db->getTables(); // visu tabulu saraksts
 *   $columns = $db->getTableColumns('users'); // tabulas kolonnas
 *   if ($db->tableHasColumn('users', 'email')) { ... }
 *   $count = $db->count('users'); // ierakstu skaits
 *   $db->dropTable('old_table'); // dzēš tabulu
 */
class Database
{
    /** @var mysqli|null */
    private $conn = null;

    /** @var array<string,mixed> */
    private $cfg;

    /** @var bool */
    private $inTransaction = false;

    /**
     * Konstruktors automātiski ielādē globālo $config['db'] mainīgo.
     * Ja vēlies padot savu config, var padot kā parametru.
     */
    public function __construct(?array $cfg = null)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        if ($cfg === null) {
            // Mēģina ielādēt globālo $config mainīgo
            global $config;
            if (!isset($config) || !isset($config['db'])) {
                throw new RuntimeException('Globālais $config["db"] nav definēts. Padod config kā parametru vai definē globālo $config.');
            }
            $this->cfg = $config['db'];
        } else {
            $this->cfg = $cfg;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /** @return array<int,array<string,mixed>> */
    public function getArray(string $sql, array $params = []): array
    {
        try {
            return $this->fetchAll($sql, $params);
        } finally {
            $this->closeIfAuto();
        }
    }

    /** @return array<string,mixed>|null */
    public function getRow(string $sql, array $params = []): ?array
    {
        try {
            $rows = $this->fetchAll($sql, $params);
            return $rows[0] ?? null;
        } finally {
            $this->closeIfAuto();
        }
    }

    /** @return mixed|null */
    public function getValue(string $sql, array $params = [])
    {
        try {
            $row = $this->getRow($sql, $params);
            return $row ? array_values($row)[0] : null;
        } finally {
            $this->closeIfAuto();
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->prepareAndExecute($sql, $params);
            return $stmt->affected_rows;
        } finally {
            $this->closeIfAuto();
        }
    }

    public function insert(string $table, array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Insert data ir tukšs');
        }
        try {
            $this->ensureConnection();
            [$sql, $params] = $this->buildInsert($table, [$data]);
            $stmt = $this->prepareAndExecute($sql, $params);
            $insertId = $this->conn->insert_id;
            return $insertId;
        } finally {
            $this->closeIfAuto();
        }
    }

    /**
     * Saglabā ierakstu: ja ir $idColumn vērtība → UPDATE, citādi → INSERT.
     * Atgriež ieraksta id (ja insert, ņem insert_id; ja update, atgriež padoto id).
     */
    public function save(string $table, array $data, string $idColumn = 'id'): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Save data ir tukšs');
        }

        $hasId = array_key_exists($idColumn, $data) && $data[$idColumn] !== null && $data[$idColumn] !== '';
        $id = $hasId ? $data[$idColumn] : null;

        // noņem id no SET datiem, lai netiktu mainīts
        unset($data[$idColumn]);

        if ($hasId) {
            if (empty($data)) {
                // Ja padots tikai id bez datiem, nav ko atjaunināt
                return (int)$id;
            }
            $this->update($table, $data, "`{$idColumn}` = ?", [$id]);
            return (int)$id;
        }

        return $this->insert($table, $data);
    }

    public function insertMany(string $table, array $rows): void
    {
        if (empty($rows)) {
            throw new InvalidArgumentException('InsertMany dati ir tukši');
        }
        $this->begin();
        try {
            [$sql, $params] = $this->buildInsert($table, $rows);
            $this->execute($sql, $params);
            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        } finally {
            $this->closeIfAuto();
        }
    }

    public function update(string $table, array $data, string $where, array $params = []): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data ir tukšs');
        }
        if (trim($where) === '') {
            throw new InvalidArgumentException('Update WHERE ir obligāts');
        }
        try {
            $setParts = [];
            $values = [];
            foreach ($data as $col => $val) {
                $setParts[] = "`{$col}` = ?";
                $values[] = $val;
            }
            $sql = "UPDATE `{$table}` SET " . implode(', ', $setParts) . " WHERE {$where}";
            return $this->execute($sql, array_merge($values, $params));
        } finally {
            $this->closeIfAuto();
        }
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        if (trim($where) === '') {
            throw new InvalidArgumentException('Delete WHERE ir obligāts');
        }
        try {
            $sql = "DELETE FROM `{$table}` WHERE {$where}";
            return $this->execute($sql, $params);
        } finally {
            $this->closeIfAuto();
        }
    }

    /**
     * Upsert (INSERT ... ON DUPLICATE KEY UPDATE).
     * $updateData - kolonnas ko atjaunināt konfliktā; ja tukšs, izmantos ievades laukus.
     */
    public function upsert(string $table, array $data, array $updateData = []): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Upsert data ir tukšs');
        }
        $updateData = $updateData ?: $data;

        try {
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');

            $updateParts = [];
            foreach ($updateData as $col => $_) {
                $updateParts[] = "`{$col}` = VALUES(`{$col}`)";
            }

            $sql = "INSERT INTO `{$table}` (`" . implode('`,`', $columns) . "`) "
                 . "VALUES (" . implode(',', $placeholders) . ") "
                 . "ON DUPLICATE KEY UPDATE " . implode(', ', $updateParts);

            $params = array_values($data);
            $this->execute($sql, $params);
            return $this->conn->affected_rows;
        } finally {
            $this->closeIfAuto();
        }
    }

    public function begin(): void
    {
        $this->ensureConnection();
        $this->conn->begin_transaction();
        $this->inTransaction = true;
    }

    public function commit(): void
    {
        $this->conn->commit();
        $this->inTransaction = false;
        $this->closeIfAuto();
    }

    public function rollback(): void
    {
        $this->conn->rollback();
        $this->inTransaction = false;
        $this->closeIfAuto();
    }

    /**
     * Pārbauda, vai tabula eksistē datubāzē.
     * 
     * Piemēri:
     *   if ($db->tableExists('users')) { ... }
     * 
     * @param string $tableName Tabulas nosaukums
     * @return bool True, ja tabula eksistē, false citādi 
     **/
    public function tableExists(string $tableName): bool
    {
        try {
            $result = $this->getValue(
                "SELECT COUNT(*) FROM information_schema.tables 
                 WHERE table_schema = DATABASE() 
                 AND table_name = ?",
                [$tableName]
            );
            return (int)$result > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Iegūst visu tabulu sarakstu datubāzē.
     * 
     * Piemēri:
     *   $tables = $db->getTables();
     *   // ['users', 'posts', 'comments']
     * 
     * @return array<int,string> Tabulu nosaukumu masīvs
     */
    public function getTables(): array
    {
        try {
            $rows = $this->getArray(
                "SELECT table_name FROM information_schema.tables 
                 WHERE table_schema = DATABASE() 
                 ORDER BY table_name"
            );
            return array_column($rows, 'table_name');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Dzēš tabulu no datubāzes.
     * 
     * Piemēri:
     *   $db->dropTable('old_table');
     * 
     * @param string $tableName Tabulas nosaukums
     * @return int Ietekmēto rindu skaits (parasti 0)
     */
    public function dropTable(string $tableName): int
    {
        try {
            return $this->execute("DROP TABLE IF EXISTS `{$tableName}`");
        } finally {
            $this->closeIfAuto();
        }
    }

    /**
     * Iegūst tabulas kolonnu informāciju.
     * 
     * Piemēri:
     *   $columns = $db->getTableColumns('users');
     *   // [['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', ...], ...]
     * 
     * @param string $tableName Tabulas nosaukums
     * @return array<int,array<string,mixed>> Kolonnu informācijas masīvs
     */
    public function getTableColumns(string $tableName): array
    {
        try {
            return $this->getArray("SHOW COLUMNS FROM `{$tableName}`");
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Pārbauda, vai tabulā eksistē konkrēta kolonna.
     * 
     * Piemēri:
     *   if ($db->tableHasColumn('users', 'email')) { ... }
     * 
     * @param string $tableName Tabulas nosaukums
     * @param string $columnName Kolonnas nosaukums
     * @return bool True, ja kolonna eksistē, false citādi
     */
    public function tableHasColumn(string $tableName, string $columnName): bool
    {
        try {
            $result = $this->getValue(
                "SELECT COUNT(*) FROM information_schema.columns 
                 WHERE table_schema = DATABASE() 
                 AND table_name = ? 
                 AND column_name = ?",
                [$tableName, $columnName]
            );
            return (int)$result > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Iegūst ierakstu skaitu tabulā.
     * 
     * Piemēri:
     *   $count = $db->count('users');
     *   $count = $db->count('users', 'status = ?', ['active']);
     * 
     * @param string $table Tabulas nosaukums
     * @param string $where WHERE nosacījums (nav obligāts)
     * @param array<int,mixed> $params WHERE parametri
     * @return int Ierakstu skaits
     */
    public function count(string $table, string $where = '', array $params = []): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM `{$table}`";
            if (trim($where) !== '') {
                $sql .= " WHERE {$where}";
            }
            $result = $this->getValue($sql, $params);
            return (int)$result;
        } catch (Exception $e) {
            return 0;
        }
    }

    /** @return array<int,array<string,mixed>> */
    private function fetchAll(string $sql, array $params): array
    {
        $stmt = $this->prepareAndExecute($sql, $params);
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function prepareAndExecute(string $sql, array $params): mysqli_stmt
    {
        $this->ensureConnection();
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $types = $this->detectTypes($params);
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    private function detectTypes(array $params): string
    {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_float($p)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array{0:string,1:array<int,mixed>}
     */
    private function buildInsert(string $table, array $rows): array
    {
        $first = $rows[0];
        $columns = array_keys($first);
        $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';

        $valuesPart = [];
        $params = [];
        foreach ($rows as $row) {
            // nodrošina, ka visiem ir vienādas atslēgas
            $row = array_replace(array_fill_keys($columns, null), $row);
            $valuesPart[] = $placeholders;
            foreach ($columns as $col) {
                $params[] = $row[$col];
            }
        }

        $sql = "INSERT INTO `{$table}` (`" . implode('`,`', $columns) . "`) VALUES "
             . implode(', ', $valuesPart);

        return [$sql, $params];
    }

    private function ensureConnection(): void
    {
        if ($this->conn instanceof mysqli) {
            return;
        }
        $this->conn = new mysqli(
            $this->cfg['host'] ?? 'localhost',
            $this->cfg['username'] ?? '',
            $this->cfg['password'] ?? '',
            $this->cfg['database'] ?? ''
        );
        $this->conn->set_charset('utf8mb4');
    }

    private function disconnect(): void
    {
        if ($this->conn instanceof mysqli) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    private function closeIfAuto(): void
    {
        if (!$this->inTransaction) {
            $this->disconnect();
        }
    }

    //check if table is empty
    public function isTableEmpty(string $tableName): bool
    {
        try {
            $result = $this->getValue(
                "SELECT COUNT(*) FROM `{$tableName}`"
            );
            return (int)$result === 0;
        } catch (Exception $e) {
            return true;
        }
    }

    //get all categories from database
    public function getCategories(): array
    {
        try {
            return $this->getArray("SELECT * FROM categories ORDER BY id");
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
