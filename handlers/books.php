<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Books extends Handler {

    private function listFilters() {

        $response = [];

        $response['authors'] = (new Authors())->getAuthorsChecklist();
        $response['subjects'] = (new Subjects())->getSubjectsChecklist();
        $response['racks'] = array_map(
            function ($index) {

                return [
                    'label' => $index,
                    'selected' => FALSE
                ];
            },
            range(1, 17)
        );
        $response['borrowed'] = FALSE;
        $response['available'] = FALSE;

        $this->send($response);
    }

    private function listBooks() {

        $result = $this->select('SELECT * FROM books');

        $this->send($result);
    }

    function get($command) {

        $commands = [
            'list' => 'listBooks',
            'filters' => 'listFilters'
        ];

        if (method_exists($this, $commands[$command])) {
            $this->$commands[$command]();
        } else {
            (new Error('API Not Implemented', 405))->send();
        }
    }

    function put($filters) {

        $this->listBooks();
    }
}
