<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Subject extends Handler {

    public function get($id) {

        $result = $this->select(
            'SELECT * FROM subjects WHERE subjectid = ?',
            [$id]
        );

        if (count($result) < 1) {
            (new Error('Subject Not Found'))->send();
            exit();
        }

        $result = $result[0];

        $result['books'] = $this->select(
            'SELECT bookid, title FROM books WHERE subjectid = ?',
            [$id]
        );

        $result['authors'] = $this->select(
            'SELECT DISTINCT authorid, authors.name FROM authors ' .
            'JOIN authorassoc USING(authorid) ' .
            'JOIN books USING(bookid) WHERE subjectid = ?',
            [$id]
        );

        $this->send($result);
    }
}
