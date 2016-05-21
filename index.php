<?php

function __autoload($class) {

    require_once "$class.php";
}

echo "Welcome to API\n\n";
print_r($_SERVER);

new lib\Server();


