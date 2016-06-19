<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Subject extends Handler {

    public function delete($id) {

        if ($id == 0) {
            echo "here";
            (new Error('Invalid Subject ID', 406))->send();
        }

        $this->send(['success' => $this->deleteRow('subjects', 'subjectid', $id)]);
    }

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
            'SELECT bookid, title FROM books JOIN subjectassoc USING(bookid) WHERE subjectid = ?',
            [$id]
        );

        $result['authors'] = $this->select(
            'SELECT DISTINCT authors.* FROM authors ' .
            'JOIN authorassoc USING(authorid) ' .
            'JOIN books USING(bookid) ' .
            'JOIN subjectassoc USING(bookid) WHERE subjectid = ?',
            [$id]
        );

        $this->send($result);
    }

    public function insertSubject($subject, &$found = FALSE) {

        $subjectid = $this->select(
            'SELECT subjectid FROM subjects WHERE LOWER(name) LIKE LOWER(?)',
            [$subject]
        );

        if (count($subjectid) < 1) {
            return $this->insert('subjects', ['name'], [$subject]);
        } else {
            $found = TRUE;

            return $subjectid[0]['subjectid'];
        }
    }

    function post($data) {

        $id = $this->insertSubject($data['subject'], $found);

        if ($found) {
            http_response_code(302);
        }

        $this->send(['id' => $id]);
    }
}
