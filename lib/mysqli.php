<?php

namespace lib;

class mysqli extends \mysqli {

    private static $instance;

    public static function getInstance() {

        if (!mysqli::$instance) {
            mysqli::$instance = new mysqli();
        }

        return mysqli::$instance;
    }

    function __construct() {

        $env = parse_ini_file('deploy.ini')['env'];
        $dbConfig = parse_ini_file('database.ini', TRUE)[$env];

        $db_host = $db_user = $db_pass = $db_name = '';
        extract($dbConfig);

        parent::__construct($db_host, $db_user, $db_pass, $db_name);
    }
}
