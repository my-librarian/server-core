<?php

namespace lib;

class Database {

    public function __construct() {

        $this->mysqli = mysqli::getInstance();
    }

    private function bindParams(&$stmt, $params) {

        if (!count($params)) {
            return;
        }

        $bound_params = [str_repeat('s', count($params))];

        for ($i = 0; $i < count($params); ++$i) {
            $bound_params[] = &$params[$i];
        }

        call_user_func_array([
            $stmt,
            'bind_param'
        ], $bound_params);
    }

    private function throw500Error($error) {

        if ($error) {
            $_500 = new Error($error, 500);
            $_500->send();
        }
    }

    public function beginTransaction() {

        $this->mysqli->autocommit(FALSE);
    }

    public function endTransaction() {

        if ($this->mysqli->errno) {
            $_500 = new Error($this->mysqli->error, 500);
            $_500->send();
        } else {
            $this->mysqli->commit();
        }
    }

    public function deleteRow($table, $idColumn, $idValue) {

        $stmt = $this->mysqli->prepare("DELETE FROM $table WHERE `$idColumn` = ?");

        if ($stmt) {
            $this->bindParams($stmt, [$idValue]);

            $stmt->execute();
            $this->throw500Error($stmt);

            return TRUE;
        }

        $this->throw500Error($this->mysqli->error);

        return FALSE;
    }

    public function insert($table, $columns, $values) {

        $columns = '`' . join('`,`', $columns) . '`';
        $valueHolders = substr(str_repeat('?,', count($values)), 0, -1);

        $stmt = $this->mysqli->prepare("INSERT INTO $table ($columns) values ($valueHolders)");

        if ($stmt) {
            $this->bindParams($stmt, $values);

            $stmt->execute();
            $this->throw500Error($stmt->error);
        }

        return $this->mysqli->insert_id;
    }

    public function select($sql, $params = []) {

        $stmt = $this->mysqli->prepare($sql);

        if ($stmt) {
            $this->bindParams($stmt, $params);

            $stmt->execute();
            $this->throw500Error($stmt->error);

            $result = $stmt->get_result();
            $this->throw500Error(!$result);

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    public function update($table, $columns, $values, $idColumn, $idValue) {

        $fields = array_map(function ($column) {

            return "`$column`=?";
        }, $columns);
        $fields = join(', ', $fields);

        $stmt = $this->mysqli->prepare("update $table set $fields where `$idColumn`=?");

        if ($stmt) {
            array_push($values, $idValue);
            $this->bindParams($stmt, $values);

            $stmt->execute();
            $this->throw500Error($stmt->error);

            return TRUE;
        }

        $this->throw500Error($this->mysqli->error);

        return FALSE;
    }
}
