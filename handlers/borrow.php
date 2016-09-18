<?php

namespace handlers;

use lib\Handler;
use lib\mysqli;

class Borrow extends Handler {

    function get($bookid) {

        Session::verifyAuthentication(2);

        $response = $this->select(
            'SELECT b.name AS borrowerName, i.name AS issuerName, r.name AS receiverName, borrow.* FROM borrow ' .
            'JOIN users b USING(userid) ' .
            'JOIN users i ON issuerid = i.userid ' .
            'LEFT JOIN users r ON receiverid = r.userid ' .
            'WHERE bookid = ? ' .
            'ORDER BY borrowdate DESC',
            [$bookid]
        );

        $this->send($response, TRUE);
    }

    function getDueDate($timespan) {

        if ($timespan === 'short') {
            return date('Y-m-d', strtotime('+4 weeks'));
        }

        return date('Y-m-d', strtotime('+12 weeks'));
    }

    function post($data) {

        Session::verifyAuthentication(1);

        $values = [
            (new User())->getUserIdFromDeptNo($data['userid']),
            (new User())->getUserIdFromDeptNo($data['issuerid']),
            $data['bookid'],
            $this->getDueDate($data['timespan']),
            gmdate('Y-m-d H:i:s')
        ];

        $id = $this->insert('borrow', ['userid', 'issuerid', 'bookid', 'duedate', 'borrowdate'], $values);

        $this->send(['id' => $id]);
    }

    function put($borrowid, $receiverid, $data) {

        Session::verifyAuthentication(1);

        $this->update(
            'borrow',
            ['receiverid', 'returndate', 'penalty'],
            [
                (new User())->getUserIdFromDeptNo($receiverid),
                gmdate('Y-m-d H:i:s'),
                $data['penalty']
            ],
            'borrowid',
            $borrowid
        );
    }

    function getBorrowedBooks($userid) {

        return $this->select(
            'SELECT title, borrowdate, bookid, duedate FROM borrow ' .
            'JOIN books USING (bookid) ' .
            'WHERE userid = ? AND returndate IS NULL',
            [$userid]
        );
    }

    function getBorrowHistory($userid) {

        return $this->select(
            'SELECT b.name borrowerName, i.name issuerName, r.name receiverName, borrow.*, title bookTitle FROM borrow ' .
            'JOIN users b USING(userid) ' .
            'JOIN users i ON issuerid = i.userid ' .
            'JOIN users r ON receiverid = r.userid ' .
            'JOIN books USING(bookid) ' .
            'WHERE borrow.userid = ? ' .
            'ORDER BY borrowdate DESC',
            [$userid]
        );
    }
}
