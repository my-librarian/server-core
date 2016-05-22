<?php

namespace lib;

class Database extends \mysqli {

    public function __construct($db_host, $db_user, $db_pass, $db_name) {

        parent::__construct($db_host, $db_user, $db_pass, $db_name);
    }
}
