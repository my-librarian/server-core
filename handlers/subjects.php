<?php

namespace handlers;

use lib\Handler;

class Subjects extends Handler {

    public function get() {

        $result = $this->select('SELECT * FROM subjects ORDER BY name');

        $this->send($result);
    }

    public function getSubjectsChecklist() {

        $subjects = $this->select(
            'SELECT subjectid, name AS label FROM subjects ORDER BY name'
        );

        return array_map(
            function ($subject) {

                $subject['selected'] = FALSE;

                return $subject;
            },
            $subjects
        );
    }
}
