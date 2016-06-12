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

        $result = $this->select('SELECT * FROM books ORDER BY title');

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

    private function getAuthorQuery($filters) {

        $authors = array_values(array_filter($filters['authors'], function ($author) {

            return $author['selected'];
        }));

        $authorIds = array_map(function ($author) {

            return $author['authorid'];
        }, $authors);

        $authorQuery = array_fill(0, count($authorIds), 'authorid=?');
        $authorQuery = join(' OR ', $authorQuery);

        return array($authorIds, $authorQuery);
    }

    private function getSubjectQuery($filters) {

        $subjects = array_values(array_filter($filters['subjects'], function ($subject) {

            return $subject['selected'];
        }));

        $subjectIds = array_map(function ($subject) {

            return $subject['subjectid'];
        }, $subjects);

        $subjectQuery = array_fill(0, count($subjectIds), 'subjectid=?');
        $subjectQuery = join(' OR ', $subjectQuery);

        return array($subjectIds, $subjectQuery);
    }

    private function getRackNoQuery($filters) {

        $racks = array_values(array_filter($filters['racks'], function ($rack) {

            return $rack['selected'];
        }));

        $racks = array_map(function ($rack) {

            return '^r-' . $rack['label'] . '-';
        }, $racks);

        $rackNoQuery = array_fill(0, count($racks), 'LOWER(rackno) REGEXP ?');
        $rackNoQuery = join(' OR ', $rackNoQuery);

        return array($racks, $rackNoQuery);
    }

    function put($filters) {

        list($authorIds, $authorQuery) = $this->getAuthorQuery($filters);
        list($subjectIds, $subjectQuery) = $this->getSubjectQuery($filters);
        list($racks, $rackNoQuery) = $this->getRackNoQuery($filters);

        $WHERE = 'WHERE ' . join(' AND ', array_filter([$authorQuery, $subjectQuery, $rackNoQuery, 1]));

        $response = $this->select(
            "SELECT * FROM books JOIN authorassoc USING(bookid) $WHERE GROUP BY bookid ORDER BY title",
            array_merge($authorIds, $subjectIds, $racks)
        );

        $this->send($response, TRUE);
    }
}
