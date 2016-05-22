<?php

namespace lib;

class Handler extends Database {

    public function __construct() {

        $env = parse_ini_file('deploy.ini')['env'];
        $dbConfig = parse_ini_file('database.ini', TRUE)[$env];

        $db_host = $db_user = $db_pass = $db_name = '';
        extract($dbConfig);

        parent::__construct($db_host, $db_user, $db_pass, $db_name);
    }

    public function send($result = []) {

        echo json_encode($result);
    }
}
