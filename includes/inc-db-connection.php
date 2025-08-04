<?php
// File: inc-db-connection.php

/****************************************************
 * 0) Detect Environment (Localhost or Live)
 ****************************************************/
if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    // Localhost
    define('DRIVER', 'mysql');
    define('HOST', 'localhost');
    define('DATA', 'dev_saas');
    define('USER', 'root');
    define('PASS', '');
} else {
    // Live server
    define('DRIVER', 'mysql');
    define('HOST', 'localhost');
    define('DATA', '');
    define('USER', '');
    define('PASS', '');
}

/****************************************************
 * 1) DB Class
 ****************************************************/
class DB
{
    private static $instance;
    private $pdo;

    // Usage: $db = DB::getInstance();
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    private function __construct() {
        try {
            $this->pdo = new PDO(
                sprintf('%s:host=%s;dbname=%s', DRIVER, HOST, DATA),
                USER,
                PASS,
                [
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4; SET CHARACTER SET utf8mb4;',
                ]
            );
        } catch (Exception $ex) {
            throw new Exception('Cannot connect to the database.');
        }
    }

    // Usage: $db->delete('table_name', 'id', 123);
    public function delete($table, $field, $value) {
        $sql = "DELETE FROM `$table` WHERE `$field` = :value";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':value' => $value]);
    }

    // Usage: $mapped = DB::map('id', $rows, 'name');
    public static function map($keyColumn, array $rows = [], $valueColumn = null) {
        $result = [];
        foreach ($rows as $row) {
            $result[$row[$keyColumn]] = is_null($valueColumn) ? $row : $row[$valueColumn];
        }
        return $result;
    }

    // Usage: $db->execute('UPDATE users SET name = ? WHERE id = ?', ['John', 1]);
    public function execute($query, array $params = []) {
        $command = $this->pdo->prepare($query);
        foreach ($params as $paramName => $paramValue) {
            if (is_int($paramName)) {
                $command->bindValue($paramName + 1, $paramValue);
            } else {
                if (is_array($paramValue) && isset($paramValue['type']) && isset($paramValue['value'])) {
                    $command->bindValue($paramName, $paramValue['value'], $paramValue['type']);
                } else {
                    $command->bindValue($paramName, $paramValue);
                }
            }
        }
        $status = $command->execute();
        if (!$status) {
            throw new Exception('DB::execute(): Can\'t execute query.');
        }
        return $status;
    }

    // Usage: $rows = $db->select('SELECT * FROM users WHERE active = ?', [1]);
    public function select($query, array $params = [], $fetchType = PDO::FETCH_ASSOC) {
        $command = $this->pdo->prepare($query);
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $command->bindValue($key + 1, $value);
            } else {
                if (is_array($value) && isset($value['type']) && isset($value['value'])) {
                    $command->bindValue($key, $value['value'], $value['type']);
                } else {
                    $command->bindValue($key, $value);
                }
            }
        }
        if (!$command->execute()) {
            throw new Exception('DB::select(): Can\'t execute query.');
        }
        return $command->fetchAll($fetchType);
    }

    // Usage: $row = $db->selectOne('SELECT * FROM users WHERE id = ?', [1]);
    public function selectOne($query, array $params = [], $fetchType = PDO::FETCH_ASSOC) {
        $rows = $this->select($query, $params, $fetchType);
        return array_shift($rows);
    }

    // Usage: $row = $db->selectOneByField('users', 'id', 1);
    public function selectOneByField($table, $field, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->selectOne("SELECT * FROM `$table` WHERE `$field` = :value", [':value' => $value], $fetchType);
    }

    // Usage: $row = $db->get('users', 'id', 1);
    public function get($table, $field, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->selectOneByField($table, $field, $value, $fetchType);
    }

    // Usage: $rows = $db->selectAll('users');
    public function selectAll($table, $fetchType = PDO::FETCH_ASSOC) {
        return $this->select("SELECT * FROM `$table`", [], $fetchType);
    }

    // Usage: $rows = $db->selectByField('users', 'email', 'test@example.com');
    public function selectByField($table, $field, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->select("SELECT * FROM `$table` WHERE `$field` = :value", [':value' => $value], $fetchType);
    }

    // Usage: $rows = $db->selectAllByField('users', 'role', 'admin');
    public function selectAllByField($table, $field, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->select("SELECT * FROM `$table` WHERE `$field` = :value", [':value' => $value], $fetchType);
    }

    // Usage: $row = $db->selectOneByTwoFields('users', 'id', 1, 'status', 'active');
    public function selectOneByTwoFields($table, $f1, $v1, $f2, $v2, $fetchType = PDO::FETCH_ASSOC) {
        return $this->selectOne("SELECT * FROM `$table` WHERE `$f1` = :value1 AND `$f2` = :value2", [
            ':value1' => $v1,
            ':value2' => $v2
        ], $fetchType);
    }

    // Usage: $values = $db->selectValues('SELECT name, email FROM users WHERE id = ?', [1]);
    public function selectValues($query, array $params = [], $fetchType = PDO::FETCH_ASSOC) {
        $row = $this->selectOne($query, $params, $fetchType);
        if (empty($row)) throw new Exception('DB::selectValues(): No values selected.');
        return $row;
    }

    // Usage: $name = $db->selectValue('SELECT name FROM users WHERE id = ?', [1]);
    public function selectValue($query, array $params = []) {
        $values = $this->selectValues($query, $params, PDO::FETCH_NUM);
        return $values[0];
    }

    // Usage: $id = $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
    public function insert($table, array $fields) {
        $normParams = $this->normalizeParams($fields);
        $columns = implode('`, `', array_keys($fields));
        $placeholders = implode(', ', array_keys($normParams));
        $query = "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute($normParams)) {
            throw new Exception("DB::insert(): Can't execute query.");
        }
        return $this->pdo->lastInsertId();
    }

    // Usage: $db->bulkInsert('users', [['name' => 'A'], ['name' => 'B']]);
    public function bulkInsert($table, array $rows) {
        if (empty($rows)) return;
        $columns = array_keys($this->normalizeParams($rows[0]));
        $placeholders = [];
        $params = [];
        foreach ($rows as $i => $row) {
            $rowPlaceholders = [];
            foreach ($row as $col => $val) {
                $key = ":{$col}{$i}";
                $rowPlaceholders[] = $key;
                $params[$key] = $val;
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }
        $query = sprintf("INSERT INTO `%s` (%s) VALUES %s", $table, implode(', ', $columns), implode(', ', $placeholders));
        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute($params)) {
            throw new Exception("DB::bulkInsert(): Can't execute query.");
        }
    }

    // Usage: $db->update('users', 'id', 1, ['name' => 'Jane']);
    public function update($table, $key, $val, array $fields, $updateAll = false) {
        if (is_null($key) && !$updateAll) throw new Exception("Update aborted: no key or confirmation.");
        $norm = $this->normalizeParams($fields);
        $set = implode(', ', array_map(fn($f) => "`$f` = :$f", array_keys($fields)));
        $where = $key ? "WHERE `$key` = :$key" : '';
        $query = "UPDATE `$table` SET $set $where";
        $stmt = $this->pdo->prepare($query);
        foreach ($norm as $k => $v) {
            $stmt->bindValue($k, is_array($v) ? $v['value'] : $v, $v['type'] ?? PDO::PARAM_STR);
        }
        if ($key) $stmt->bindValue(":$key", $val);
        if (!$stmt->execute()) throw new Exception("DB::update(): Can't execute query.");
        return true;
    }

    // Usage: $db->remove('users', 'id', 5);
    public function remove($table, $field = null, $value = null, $removeAll = false) {
        if (!$field && !$value && !$removeAll) throw new Exception("DB::remove(): Dangerous delete blocked.");
        if ($field && $value) {
            return $this->execute("DELETE FROM `$table` WHERE `$field` = :value", [':value' => $value]);
        }
        return $this->execute("DELETE FROM `$table`");
    }

    // Internal: Normalises parameter keys for PDO binding
    protected function normalizeParams(array $params) {
        $norm = [];
        foreach ($params as $k => $v) {
            $key = str_starts_with($k, ':') ? $k : ':' . $k;
            $norm[$key] = $v;
        }
        return $norm;
    }

    // Usage: $sql = $db->interpolateQuery('SELECT * FROM users WHERE id = :id', [':id' => 1]);
    public function interpolateQuery($query, $params) {
        $keys = [];
        foreach ($params as $k => $v) {
            $keys[] = is_string($k) ? '/:' . preg_quote($k, '/') . '/' : '/[?]/';
        }
        return preg_replace($keys, $params, $query, 1);
    }

    // Usage: $total = $db->count('users');
    public function count($table) {
        $res = $this->selectOne("SELECT COUNT(*) as count FROM `$table`");
        return isset($res['count']) ? (int)$res['count'] : 0;
    }
}
?>