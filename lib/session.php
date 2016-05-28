<?php

namespace lib;

class Session {

    public static function start() {

        if (session_id() === '' || !isset($_SESSION)) {
            session_start();
        }
    }

    public static function set($key, $value) {

        self::start();

        $_SESSION[$key] = $value;
    }

    public static function get($key) {

        self::start();

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return NULL;
    }

    public static function stop() {

        self::start();

        session_unset();
        session_destroy();
    }
}
