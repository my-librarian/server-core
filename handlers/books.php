<?php

namespace handlers;

use lib\Handler;

class Books extends Handler {

    private function listBooks() {

        $borrowed = "(SELECT count(*) FROM borrow WHERE borrow.bookid = books.bookid AND returndate IS NULL) AS borrowed";
        $result['authors'] = (new Authors())->getAuthorsChecklist();
        $result['subjects'] = (new Subjects())->getSubjectsChecklist();
        $result['languages'] = (new Languages())->getLanguagesChecklist();
        $result['books'] = $this->select("SELECT bookid, title, rackno, accessno, $borrowed, language FROM books");
        $result['authorAssoc'] = $this->select("SELECT authorid, group_concat(bookid) books FROM authorassoc GROUP BY authorid");
        $result['subjectAssoc'] = $this->select("SELECT subjectid, group_concat(bookid) books FROM subjectassoc GROUP BY subjectid");
        $result['racks'] = array_map(
            function ($index) {

                return [
                    'label' => $index,
                    'selected' => FALSE
                ];
            },
            range(1, 17)
        );

        $this->send($result);
    }

    private function listBorrowedBooks() {

        $title = '(SELECT title FROM books b WHERE b.bookid = w.bookid) AS title';

        $result = $this->select(
            "SELECT bookid, borrowid, name username, deptno userDeptNo, borrowdate, $title FROM borrow w " .
            "JOIN users USING(userid) " .
            "WHERE w.returndate IS NULL"
        );

        $this->send($result);
    }

    public function get($command) {

        $commands = [
            'all' => 'listBooks',
            'borrowed' => 'listBorrowedBooks'
        ];

        if (method_exists($this, $commands[$command])) {
            $this->$commands[$command]();
        } else {
            (new Error('API Not Implemented', 405))->send();
        }
    }
}
