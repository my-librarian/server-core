<?php

namespace lib;

class Error {

    public function __construct($message, $code = 404) {

        $this->code = $code;
        $this->message = $message;
    }

    public function send() {

        http_response_code($this->code);

        echo json_encode(
            [
                "message" => $this->message
            ]
        );

        exit();
    }
}
