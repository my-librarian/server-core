<?php

function __autoload($class) {

    $class = strtolower($class);
    $filename = str_replace('\\', '/', $class).".php";

    require_once $filename;
}

new lib\Server();
