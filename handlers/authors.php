<?php

namespace handlers;

use lib\Handler;

class Authors extends Handler {

    public function get() {

        $result = $this->select('SELECT * FROM authors ORDER BY name');

        $this->send($result);
    }

    public function getAuthorsChecklist() {

        $authors = $this->select(
            'SELECT authorid, name AS label FROM authors ORDER BY name'
        );

        return array_map(
            function ($author) {

                $author['selected'] = FALSE;

                return $author;
            },
            $authors
        );
    }
}
