<?php

namespace handlers;

use lib\Handler;

class Books extends Handler {

    function get() {

        $result = $this->select('select * from books');

        $this->send($result);
    }
}
