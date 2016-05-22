<?php

namespace handlers;

use lib\Handler;

class User extends Handler {

    function post($data) {

        $columns = ['name', 'deptno', 'password'];
        $values = [$data['name'], $data['deptNo'], md5($data['pass'])];

        $id = $this->insert('users', $columns, $values);

        $this->send(['id' => $id]);
    }
}
