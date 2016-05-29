<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Author extends Handler {

    public function get($id) {

        $result = $this->select(
            'SELECT * FROM authors WHERE authorid = ?',
            [$id]
        );

        if (count($result) < 1) {
            (new Error('Author Not Found'))->send();
            exit();
        }

        $result = $result[0];

        $result['books'] = $this->select(
            'SELECT bookid, title FROM books ' .
            'JOIN subjects USING(subjectid) ' .
            'JOIN authorassoc USING(bookid) WHERE authorassoc.authorid = ?',
            [$id]
        );

        $result['subjects'] = $this->select(
            'SELECT DISTINCT subjects.name, subjectid FROM books ' .
            'JOIN subjects USING(subjectid) ' .
            'JOIN authorassoc USING(bookid) WHERE authorassoc.authorid = ?',
            [$id]
        );

        $this->send($result);
    }
}
