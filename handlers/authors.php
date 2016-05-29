<?php

namespace handlers;

use lib\Handler;

class Authors extends Handler {

    public function get() {

        $result = $this->select('SELECT * FROM authors ORDER BY name');

        $this->send($result);
    }
}
