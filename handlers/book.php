<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Book extends Handler {

    function get($id) {

        $result = $this->select(
            'SELECT *, subjects.name AS subject FROM books ' .
            'JOIN subjects USING(subjectid) ' .
            'WHERE bookid = ?',
            [$id]
        );

        if (count($result) < 1) {
            (new Error('Subject Not Found'))->send();
            exit();
        }

        $result = $result[0];

        $result['authors'] = $this->select(
            'SELECT authorid, name FROM authors ' .
            'JOIN authorassoc USING(authorid) WHERE bookid = ?',
            [$id]
        );

        $this->send($result);
    }

    function post($book) {
    }
}
