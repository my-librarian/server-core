<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Book extends Handler {

    private function formatDate($dateTimeString) {

        return date('Y-m-d', strtotime($dateTimeString));
    }

    private function insertAuthors($data, $bookid) {

        foreach ($data['authors'] as $author) {
            $authorid = (new Author())->insertAuthor($author['name']);
            $this->insert('authorassoc', ['bookid', 'authorid'], [$bookid, $authorid]);
        }
    }

    private function insertBook($data) {

        $data['adddate'] = $this->formatDate($data['adddate']);

        $columns = ['title', 'accessno', 'adddate', 'rackno', 'subjectid'];
        $values = array_map(
            function ($column) use ($data) {

                return $data[$column];
            },
            $columns
        );

        return $this->insert('books', $columns, $values);
    }

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

    function post($data) {

        $this->mysqli->autocommit(FALSE);

        $data['subjectid'] = (new Subject())->insertSubject($data['subject']);

        $bookid = $this->insertBook($data);

        $this->insertAuthors($data, $bookid);

        if ($this->mysqli->errno) {
            $_500 = new Error($this->mysqli->error, 500);
            $_500->send();
        } else {
            $this->mysqli->commit();
        }

        $this->send(['id' => $bookid]);
    }
}
