<?php

error_reporting(0);
header('Content-type: application/json');

function __autoload($class) {

    $class = strtolower($class);
    $filename = str_replace('\\', '/', $class).".php";

    require_once $filename;
}

new lib\Server();
