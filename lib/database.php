<?php

namespace lib;

class Database extends \mysqli {

    public function __construct($db_host, $db_user, $db_pass, $db_name) {

        parent::__construct($db_host, $db_user, $db_pass, $db_name);

        if ($this->connect_error) {
            (new Error($this->connect_error, 502))->send();
        }
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

    public function select($sql, $params = []) {

        $stmt = $this->prepare($sql);

        if (count($params)) {
            $this->bindParams($stmt, $params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            $_500 = new Error($stmt->error, 500);
            $_500->send();
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
