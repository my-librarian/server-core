<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Session extends Handler {

    public static function verifyAuthentication($level = 1) {

        if(\lib\Session::get('level') < $level) {
            (new Error('Authentication Failed', 401))->send();
        }
    }

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
