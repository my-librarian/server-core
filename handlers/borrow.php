<?php

namespace handlers;

use lib\Handler;
use lib\mysqli;

class Borrow extends Handler {

    function get($bookid) {

        $response = $this->select(
            'SELECT b.name AS borrowerName, i.name AS issuerName, r.name AS receiverName, borrow.* FROM borrow ' .
            'JOIN users b USING(userid) ' .
            'JOIN users i ON issuerid = i.userid ' .
            'LEFT JOIN users r ON receiverid = r.userid ' .
            'WHERE bookid = ? ' .
            'ORDER BY borrowdate DESC',
            [$bookid]
        );

        $this->send($response);
    }

    function getDueDate($timespan) {

        if ($timespan === 'short') {
            return date('Y-m-d', strtotime('+4 weeks'));
        }

        return date('Y-m-d', strtotime('+12 weeks'));
    }

    function post($data) {

        $values = [
            (new User())->getUserIdFromDeptNo($data['userid']),
            (new User())->getUserIdFromDeptNo($data['issuerid']),
            $data['bookid'],
            $this->getDueDate($data['timespan'])
        ];

        $id = $this->insert('borrow', ['userid', 'issuerid', 'bookid', 'duedate'], $values);

        $this->send(['id' => $id]);
    }

    function put($borrowid, $receiverid) {

        $this->update('borrow', ['receiverid', 'returndate'], [(new User())->getUserIdFromDeptNo($receiverid), date('Y-m-d H:i:s', time() + mysqli::$timezone_offset)], 'borrowid', $borrowid);
    }
}
