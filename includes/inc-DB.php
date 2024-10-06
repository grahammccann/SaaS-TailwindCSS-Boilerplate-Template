<?php

class DB
{
    private static $instance;
    private $pdo;

    public function delete($table, $field, $value) {
        $sql = "DELETE FROM `$table` WHERE `$field` = :value";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':value' => $value]);
    }

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new DB();
        }

        return self::$instance;
    }

    public static function map($keyColumn, array $rows = [], $valueColumn = null) {
        $result = array();
        foreach ($rows as $row) {
            if (is_null($valueColumn)) {
                $result[$row[$keyColumn]] = $row;
            } else {
                $result[$row[$keyColumn]] = $row[$valueColumn];
            }
        }

        return $result;
    }

    private function __construct() {
        try {
            $this->pdo = new PDO(
                sprintf('%s:host=%s;dbname=%s',
                    DRIVER,
                    HOST,
                    DATA
                ),
                USER,
                PASS,
                array(
                    // PDO::ATTR_PERSISTENT => true,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4; SET CHARACTER SET utf8mb4;'
                )
            );
        } catch (Exception $ex) {
            throw new Exception('Cannot connect to the database.');
        }
    }

    public function execute($query, array $params = []) {
        $command = $this->pdo->prepare($query);

        foreach ($params as $paramName => $paramValue) {
            // Check if the key is numeric (positional parameter)
            if (is_int($paramName)) {
                // Positional parameters in PDO are 1-based, so we need to increment the index by 1
                $command->bindValue($paramName + 1, $paramValue);
            } else {
                // Named parameters, use the key directly
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

    public function select($query, array $params = [], $fetchType = PDO::FETCH_ASSOC) {
        $command = $this->pdo->prepare($query);

        foreach ($params as $key => $value) {
            // Check if the key is numeric (positional parameter)
            if (is_int($key)) {
                // Positional parameters in PDO are 1-based, so we need to increment the index by 1
                $command->bindValue($key + 1, $value);
            } else {
                // Named parameters, use the key directly
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

    public function selectAllByField($tableName, $fieldName, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->select(
            sprintf('
                SELECT  *
                FROM    `%s`
                WHERE   `%s` = :value',
                $tableName,
                $fieldName
            ),
            [
                ':value' => $value
            ],
            $fetchType
        );
    }

    public function selectOneByTwoFields($table, $field1, $value1, $field2, $value2, $fetchType = PDO::FETCH_ASSOC) {
        return $this->selectOne(
            sprintf('
                SELECT  *
                FROM    `%s`
                WHERE   `%s` = :value1
                AND     `%s` = :value2',
                $table,
                $field1,
                $field2
            ),
            [
                ':value1' => $value1,
                ':value2' => $value2
            ],
            $fetchType
        );
    }

    public function selectValues($query, array $params = [], $fetchType = PDO::FETCH_ASSOC) {
        $row = $this->selectOne($query, $params, $fetchType);
        if (empty($row)) {
            throw new Exception('DB::selectValues(): No values selected.');
        } else {
            return $row;
        }
    }

    public function selectValue($query, array $params = []) {
        $values = $this->selectValues($query, $params, PDO::FETCH_NUM);

        return $values[0];
    }

    public function selectAll($tableName, $fetchType = PDO::FETCH_ASSOC) {
        return $this->select(
            sprintf('
                SELECT  *
                FROM    `%s`',
                $tableName
            ),
            [],
            $fetchType
        );
    }

    public function selectByField($tableName, $fieldName, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->select(
            sprintf('
                SELECT  *
                FROM    `%s`
                WHERE   `%s` = :value',
                $tableName,
                $fieldName
            ),
            [
                ':value' => $value
            ],
            $fetchType
        );
    }

    public function selectOne($query, array $params = [], $fetchType = PDO::FETCH_ASSOC) {
        $rows = $this->select($query, $params, $fetchType);

        return array_shift($rows);
    }

    public function selectOneByField($tableName, $fieldName, $value, $fetchType = PDO::FETCH_ASSOC) {
        $rows = $this->selectByField($tableName, $fieldName, $value, $fetchType);

        return array_shift($rows);
    }

    public function get($tableName, $fieldName, $value, $fetchType = PDO::FETCH_ASSOC) {
        return $this->selectOneByField($tableName, $fieldName, $value, $fetchType);
    }

    public function insert($tableName, array $fields) {
        $normParams = $this->normalizeParams($fields);

        $paramNames = implode(', ', array_keys($normParams));
        $fieldNames = '`' . implode('`, `', array_keys($fields)) . '`';

        $command = $this->pdo->prepare(
            sprintf('
                INSERT  INTO `%s` (%s)
                VALUES  (%s)',
                $tableName,
                $fieldNames,
                $paramNames
            )
        );

        if (!$command->execute($normParams)) {
            throw new Exception('DB::insert(): Can\'t execute query.');
        }

        return $this->pdo->lastInsertId();
    }

    public function bulkInsert($tableName, array $rows = []) {
        if (empty($rows)) {
            return;
        }

        $fieldNames = array_keys($this->normalizeParams($rows[0]));

        $normParams = [];
        $paramNames = '';
        $counter = 0;
        foreach ($rows as $row) {
            $paramNames .= ((0 < $counter) ? ',' : '') . '(';

            $nextParamNames = [];
            foreach ($row as $paramKey => $paramValue) {
                $nextParamNames[] = ':' . $paramKey . $counter;
                $normParams[':' . $paramKey . $counter] = $paramValue;
            }

            $paramNames .= implode(',', $nextParamNames);
            $paramNames .= ')';

            $counter++;
        }

        $command = $this->pdo->prepare(
            sprintf('
                INSERT  INTO `%s` (%s)
                VALUES  %s',
                $tableName,
                implode(', ', $fieldNames),
                $paramNames
            )
        );

        if (!$command->execute($normParams)) {
            throw new Exception('DB::bulkInsert(): Can\'t execute query.');
        }
    }

    public function update($tableName, $fieldName, $fieldValue, array $updateFields, $updateAll = false) {
        if (is_null($fieldName)) {
            if (!$updateAll) {
                throw new SystemException('Attempt to update all table records without confirmation.');
            }

            $sqlWhere = '';
        } else {
            $sqlWhere = sprintf('WHERE `%s` = :%s', $fieldName, $fieldName);
        }

        $normUpdateFields = $this->normalizeParams($updateFields);
        $sqlSetRows = [];
        foreach ($updateFields as $updateFieldName => $updateFieldValue) {
            $sqlSetRows[] = sprintf('`%s` = :%s', $updateFieldName, $updateFieldName);
        }

        $sqlSet = implode(', ', $sqlSetRows);

        $command = $this->pdo->prepare(
            sprintf('
                UPDATE  `%s`
                SET     %s
                %s',
                $tableName,
                $sqlSet,
                $sqlWhere
            )
        );

        foreach ($normUpdateFields as $updateFieldName => $updateFieldValue) {
            if (is_array($updateFieldValue)
                && isset($updateFieldValue['type'])
                && isset($updateFieldValue['value'])) {
                $command->bindValue($updateFieldName, $updateFieldValue['value'], $updateFieldValue['type']);
            } else {
                $command->bindValue($updateFieldName, $updateFieldValue);
            }
        }

        if (!empty($sqlWhere)) {
            $command->bindValue(':' . $fieldName, $fieldValue);
        }

        if (!$command->execute()) {
            throw new Exception('DB::update(): Can\'t execute query.');
        }

        // Return true if the update was successful.
        return true;
    }

    public function remove($tableName, $fieldName = null, $value = null, $removeAll = false) {
        $isExecuted = false;

        if (is_null($fieldName)
            && is_null($value)
            && $removeAll) {
            $isExecuted = $this->execute(sprintf('DELETE FROM `%s`', $tableName));
        } else if (!is_null($fieldName)
            && !is_null($value)) {
            $isExecuted = $this->execute(
                sprintf('
                    DELETE  FROM `%s`
                    WHERE   `%s` = :value',
                    $tableName,
                    $fieldName
                ),
                [
                    ':value' => $value
                ]
            );
        }

        if (!$isExecuted) {
            throw new Exception('DB::remove(): Can\'t execute query.');
        }

        // Return the execution status.
        return $isExecuted;
    }

    protected function normalizeParams(array $params = []) {
        $normParams = [];
        foreach ($params as $paramKey => $paramValue) {
            $normParams[(strlen($paramKey) && (':' === $paramKey[0])) ? $paramKey : ':' . $paramKey] = $paramValue;
        }

        return $normParams;
    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from
     * $params are in the same order as specified in $query
     *
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    public function interpolateQuery($query, $params) {
        $keys = array();

        // build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . preg_quote($key, '/') . '/';
            } else {
                $keys[] = '/[?]/';
            }
        }

        $query = preg_replace($keys, $params, $query, 1, $count);

        return $query;
    }

    /**
     * Counts the number of records in a specified table.
     *
     * @param string $table The name of the table.
     * @return int The count of records.
     * @throws Exception If the query fails.
     */
    public function count($table) {
        $query = sprintf('SELECT COUNT(*) as count FROM `%s`', $table);
        $result = $this->selectOne($query);
        return isset($result['count']) ? (int)$result['count'] : 0;
    }
}
?>