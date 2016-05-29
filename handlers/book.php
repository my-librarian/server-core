<?php

namespace handlers;

use lib\Handler;

class Book extends Handler {

    function get($id) {

        $result = $this->select(
            'SELECT * FROM books WHERE bookid = ?',
            [$id]
        );

        $this->send($result[0]);
    }
}
