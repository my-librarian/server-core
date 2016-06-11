<?php

namespace lib;

class Database {

    public function __construct() {

        $this->mysqli = mysqli::getInstance();
    }

    private function bindParams(&$stmt, $params) {

        $bound_params = [str_repeat('s', count($params))];

        for ($i = 0; $i < count($params); ++$i) {
            $bound_params[] = &$params[$i];
        }

        call_user_func_array([
            $stmt,
            'bind_param'
        ], $bound_params);
    }

    public function deleteRow($table, $idColumn, $idValue) {

        $stmt = $this->mysqli->prepare("DELETE FROM $table WHERE `$idColumn` = ?");

        if ($stmt) {
            $this->bindParams($stmt, [$idValue]);

            $stmt->execute();

            if ($stmt->error) {
                $_500 = new Error($stmt->error, 500);
                $_500->send();
            }
        }
        
        return TRUE;
    }

    public function insert($table, $columns, $values) {

        $columns = '`' . join('`,`', $columns) . '`';
        $valueHolders = substr(str_repeat('?,', count($values)), 0, -1);

        $stmt = $this->mysqli->prepare("INSERT INTO $table ($columns) values ($valueHolders)");

        if ($stmt) {
            $this->bindParams($stmt, $values);

            $stmt->execute();

            if ($stmt->error) {
                $_500 = new Error($stmt->error, 500);
                $_500->send();
            }
        }

        return $this->mysqli->insert_id;
    }

    public function select($sql, $params = []) {

        $stmt = $this->mysqli->prepare($sql);

        if (count($params)) {
            $this->bindParams($stmt, $params);
        }

        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();

            if (!$result) {
                $_500 = new Error($stmt->error, 500);
                $_500->send();
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}
