<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Author extends Handler {

    public function delete($id) {

        Session::verifyAuthentication(2);

        $this->beginTransaction();

        $this->deleteRow('authors', 'authorid', $id);
        $this->deleteRow('authorassoc', 'authorid', $id);

        $this->endTransaction();

        $this->send(['success' => TRUE]);
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

        Session::verifyAuthentication(2);

        $id = $this->insertAuthor($data['author'], $found);

        if ($found) {
            http_response_code(302);
        }

        $this->send(['id' => $id]);
    }

    function put($author) {

        Session::verifyAuthentication(2);

        $this->update('authors', ['name'], [$author['name']], 'authorid', $author['authorid']);
    }
}
