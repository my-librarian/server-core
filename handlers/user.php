<?php

namespace handlers;

use lib\Error;
use lib\Handler;
use lib\Session;

class User extends Handler {

    private function encrypt($plain) {

        return md5($plain);
    }

    function post($data) {

        $columns = ['name', 'deptno', 'password'];
        $values = [$data['name'], $data['deptNo'], $this->encrypt($data['pass'])];

        $id = $this->insert('users', $columns, $values);

        $this->send(['id' => $id]);
    }

    function get($deptNo, $pass, $session = FALSE) {

        $users = $this->select(
            'SELECT userid, deptno, name, level FROM users WHERE deptno=? AND password=?',
            [$deptNo, $this->encrypt($pass)]
        );

        if (count($users) > 0) {

            $user = $users[0];

            if ($session) {

                foreach ($user as $key => $value) {
                    Session::set($key, $value);
                }
            }

            $this->send();
        } else {
            (new Error('Invalid Credentials', 401))->send();
        }
    }

    function getUserIdFromDeptNo($deptNo) {

        return $this->select('SELECT userid FROM users WHERE deptno = ? OR userid = ?', [$deptNo, $deptNo])[0]['userid'];
    }
}
