<?php

namespace handlers;

use lib\Handler;

class Session extends Handler {

    public function get() {

        \lib\Session::start();

        $this->send($_SESSION);
    }

    public function put($command) {

        if($command === 'stop') {
            \lib\Session::stop();
        }

        $this->send();
    }
}
