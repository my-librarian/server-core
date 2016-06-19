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

        $data['acquisitiondate'] = $this->formatDate($data['acquisitiondate']);
        $data['original'] = $data['original'] ? 1 : 0;

        $columns = [
            'title',
            'accessno',
            'acquisitiondate',
            'rackno',
            'description',
            'pages',
            'year',
            'language',
            'isbn',
            'cost',
            'binding',
            'original',
            'source',
            'condition'
        ];

        $values = array_map(
            function ($column) use ($data) {

                return $data[$column];
            },
            $columns
        );

        return $this->insert('books', $columns, $values);
    }

    private function insertSubjects($data, $bookid) {

        foreach ($data['subjects'] as $subject) {
            $subjectid = (new Subject())->insertSubject($subject['name']);
            $this->insert('subjectassoc', ['bookid', 'subjectid'], [$bookid, $subjectid]);
        }
    }

    public function delete($id) {

        $this->send(['success' => $this->deleteRow('books', 'bookid', $id)]);
    }

    function get($id) {

        $result = $this->select(
            'SELECT * FROM books ' .
            'WHERE bookid = ?',
            [$id]
        );

        if (count($result) < 1) {
            (new Error('Book Not Found'))->send();
            exit();
        }

        $result = $result[0];

        $result['description'] = $result['description'] ?: '';

        $result['authors'] = $this->select(
            'SELECT authorid, name FROM authors ' .
            'JOIN authorassoc USING(authorid) WHERE bookid = ?',
            [$id]
        );

        $result['subjects'] = $this->select(
            'SELECT subjectid, name FROM subjects ' .
            'JOIN subjectassoc USING(subjectid) WHERE bookid = ?',
            [$id]
        );

        $this->send($result);
    }

    function post($data) {

        $this->mysqli->autocommit(FALSE);

        $bookid = $this->insertBook($data);

        $this->insertAuthors($data, $bookid);
        $this->insertSubjects($data, $bookid);

        if ($this->mysqli->errno) {
            $_500 = new Error($this->mysqli->error, 500);
            $_500->send();
        } else {
            $this->mysqli->commit();
        }

        $this->send(['id' => $bookid]);
    }
}
