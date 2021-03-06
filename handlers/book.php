<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Book extends Handler {

    private function formatDate($dateTimeString) {

        return date('Y-m-d', strtotime($dateTimeString));
    }

    private function insertAuthors($data, $bookid) {

        $authors = array_map(function ($author) {

            return $author['name'];
        }, $data['authors']);

        $authors = array_unique($authors);

        foreach ($authors as $author) {
            $authorid = (new Author())->insertAuthor($author);
            $this->insert('authorassoc', ['bookid', 'authorid'], [$bookid, $authorid]);
        }
    }

    private function insertBook($data) {

        list($columns, $values) = $this->mapBookColumns($data);

        return $this->insert('books', $columns, $values);
    }

    private function insertSubjects($data, $bookid) {

        $subjects = array_map(function ($subject) {

            return $subject['name'];
        }, $data['subjects']);

        $subjects = array_unique($subjects);

        foreach ($subjects as $subject) {
            $subjectid = (new Subject())->insertSubject($subject);
            $this->insert('subjectassoc', ['bookid', 'subjectid'], [$bookid, $subjectid]);
        }
    }

    private function mapBookColumns($data) {

        $data['acquisitiondate'] = $this->formatDate($data['acquisitiondate']);

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
            'copy',
            'source',
            'condition',
            'url'
        ];

        $values = array_map(
            function ($column) use ($data) {

                return $data[$column];
            },
            $columns
        );

        return [$columns, $values];
    }

    public function delete($id) {

        Session::verifyAuthentication(2);

        $this->beginTransaction();

        $this->deleteRow('books', 'bookid', $id);
        $this->deleteRow('authorassoc', 'bookid', $id);
        $this->deleteRow('subjectassoc', 'bookid', $id);

        $this->endTransaction();

        $this->send(['success' => TRUE]);
    }

    public function get($id) {

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

        $borrowerId = \lib\Session::get('userid');

        $borrowIds = $this->select(
            'SELECT borrowid, userid FROM borrow WHERE bookid = ? AND returndate IS NULL',
            [$id]
        );

        if (count($borrowIds) > 0) {
            $result['borrowid'] = $borrowIds[0]['borrowid'];
            $result['borrowedByCurrentUser'] = $borrowIds[0]['userid'] === $borrowerId;
        }

        $this->send($result);
    }

    public function post($data) {

        Session::verifyAuthentication(2);

        $this->beginTransaction();

        $bookid = $this->insertBook($data);

        $this->insertAuthors($data, $bookid);
        $this->insertSubjects($data, $bookid);

        $this->endTransaction();

        $this->send(['id' => $bookid]);
    }

    public function put($id, $data) {

        Session::verifyAuthentication(2);

        $this->beginTransaction();

        list($columns, $values) = $this->mapBookColumns($data);

        $this->update('books', $columns, $values, 'bookid', $id);

        $this->deleteRow('authorassoc', 'bookid', $id);
        $this->deleteRow('subjectassoc', 'bookid', $id);

        $this->insertAuthors($data, $id);
        $this->insertSubjects($data, $id);

        $this->endTransaction();

        $this->send();
    }
}
