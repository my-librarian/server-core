<?php

namespace handlers;

use lib\Error;
use lib\Handler;

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

    function get($deptNo, $pass) {

        $ids = $this->select(
            'SELECT userid FROM users WHERE deptno=? AND password=?',
            [$deptNo, $this->encrypt($pass)]
        );

        if(count($ids) > 0) {
            $this->send();
        } else {
            (new Error('Invalid Credentials', 401))->send();
        }
    }
}
