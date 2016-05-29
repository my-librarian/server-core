<?php

namespace handlers;

use lib\Handler;

class Subjects extends Handler {

    public function get() {

        $result = $this->select('SELECT * FROM subjects ORDER BY name');

        $this->send($result);
    }
}
