<?php

namespace lib;

class mysqli extends \mysqli {

    private static $instance;

    public static $timezone_offset;

    public static function getInstance() {

        if (!mysqli::$instance) {
            mysqli::$instance = new mysqli();
            mysqli::$timezone_offset = mysqli::$instance->query('SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP, NOW())')->fetch_row()[0];
        }

        return mysqli::$instance;
    }

    function __construct() {

        $env = parse_ini_file('deploy.ini')['env'];
        $db_host = $db_user = $db_pass = $db_name = '';

        if ($env === 'openshift') {
            $osPrefix = 'OPENSHIFT_MYSQL_DB_';
            $db_host = getenv("${osPrefix}HOST");
            $db_user = getenv("${osPrefix}USERNAME");
            $db_pass = getenv("${osPrefix}PASSWORD");
            $db_name = 'librarian';
        } else {
            $dbConfig = parse_ini_file('database.ini', TRUE)[$env];
            extract($dbConfig);
        }

        parent::__construct($db_host, $db_user, $db_pass, $db_name);
    }
}
