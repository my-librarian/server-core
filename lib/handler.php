<?php

namespace lib;

class Handler extends Database {

    public function send($result = [], $array = FALSE) {

        if(count($result) === 0 && !$array) {
            $result = new \stdClass();
        }
        
        echo json_encode($result);
    }
}
