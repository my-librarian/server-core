<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Profile extends Handler {

    public function get($userid) {

        $users = $this->select('SELECT deptno, name, level, userid FROM users WHERE userid = ?', [$userid]);

        if (count($users) < 1) {
            (new Error('User Not Found'))->send();
            exit();
        }

        $user = $users[0];

        $user['borrowed'] = (new Borrow())->getBorrowedBooks($userid);
        $user['history'] = (new Borrow())->getBorrowHistory($userid);

        $this->send($user);
    }
}