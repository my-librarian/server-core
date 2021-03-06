<?php

namespace lib;

class Server {

    private $_url = "";
    private $_method = "";
    private static $sep = DIRECTORY_SEPARATOR;

    public function __construct() {

        $this->parseApi();
        $this->serveApi();
    }

    private function getData() {

        $data = array();

        switch ($this->_method) {
            case 'delete':
            case 'get':
                $data = $_GET;
                break;
            case 'post':
            case 'put':
                $data = json_decode(file_get_contents("php://input"), TRUE);
                break;
        }

        return $data;
    }

    private function parseApi() {

        $_400 = new Error('Invalid Endpoint', 400);

        $url = explode('?', $_SERVER['REQUEST_URI'])[0];
        $url = preg_replace('/api/', '', $url);
        $url = explode('/', trim($url, '/ '));

        if ($url[0] === '') {
            $_400->send();
        }

        $this->_method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->_url = $url;
    }

    private function serveApi() {

        $_404 = new Error('API Not Found', 404);
        $_405 = new Error('API Not Implemented', 405);

        $url = $this->_url;
        $method = $this->_method;

        $filename = join(self::$sep, [__DIR__, '..', 'handlers', $url[0] . '.php']);

        if (file_exists($filename)) {

            $Handler = "handlers\\" . $url[0];
            $handler = new $Handler();
            $params = array_slice($url, 1);

            $params[] = $this->getData();

            if (method_exists($handler, $method)) {
                call_user_func_array([$handler, $method], $params);
            } else {
                $_405->send();
            }
        } else {
            $_404->send();
        }
    }
}
