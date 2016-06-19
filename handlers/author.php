<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Author extends Handler {

    public function delete($id) {

        $this->send(['success' => $this->deleteRow('authors', 'authorid', $id)]);
    }

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
            'JOIN authorassoc USING(bookid) WHERE authorassoc.authorid = ?',
            [$id]
        );

        $result['subjects'] = $this->select(
            'SELECT DISTINCT subjects.* FROM subjects ' .
            'JOIN subjectassoc USING(subjectid) ' .
            'JOIN books USING(bookid) ' .
            'JOIN authorassoc USING(bookid) WHERE authorassoc.authorid = ?',
            [$id]
        );

        $this->send($result);
    }

    public function insertAuthor($author, &$found = FALSE) {

        $authorid = $this->select(
            'SELECT authorid FROM authors WHERE LOWER(name) LIKE LOWER(?)',
            [$author]
        );

        if (count($authorid) < 1) {
            return $this->insert('authors', ['name'], [$author]);
        } else {
            $found = TRUE;

            return $authorid[0]['authorid'];
        }
    }

    function post($data) {

        $id = $this->insertAuthor($data['author'], $found);

        if ($found) {
            http_response_code(302);
        }

        $this->send(['id' => $id]);
    }
}
