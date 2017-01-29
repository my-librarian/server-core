<?php

namespace handlers;

use lib\Error;
use lib\Handler;

class Books extends Handler {

    private function getAuthorQuery($filters) {

        $authors = array_values(array_filter($filters['authors'], function ($author) {

            return $author['selected'];
        }));

        $authorIds = array_map(function ($author) {

            return $author['authorid'];
        }, $authors);

        $authorQuery = array_fill(0, count($authorIds), 'authorid=?');
        $authorQuery = join(' OR ', $authorQuery);
        $authorQuery = $this->wrapQuery($authorQuery);

        return array($authorIds, $authorQuery);
    }

    private function getAvailabilityQuery($availability) {

        $borrowQuery = NULL;
        $in = 'IN (SELECT bookid FROM borrow WHERE returndate IS NULL)';

        if ($availability == 'borrowed') {
            $borrowQuery = "bookid $in";
        } elseif ($availability == 'available') {
            $borrowQuery = "bookid NOT $in";
        }

        return $borrowQuery;
    }

    private function getLanguageQuery($filters) {

        $languages = array_values(array_filter($filters['languages'], function ($language) {

            return $language['selected'];
        }));

        $languages = array_map(function ($language) {

            return $language['label'];
        }, $languages);

        $languageQuery = array_fill(0, count($languages), 'language LIKE ?');
        $languageQuery = join(' OR ', $languageQuery);
        $languageQuery = $this->wrapQuery($languageQuery);

        return array($languages, $languageQuery);
    }

    private function fuzzyCompare($books, $searchString) {

        $search = preg_replace('/\s/', '', $searchString);
        $similarities = [];

        for ($i = 0; $i < count($books); ++$i) {
            $title = $books[$i]['title'];
            $title = preg_replace('/\s/', '', $title);

            $similarities[$i] = similar_text($title, $search);
        }

        $avgSimilarity = 0;
        foreach ($similarities as $l) {
            $avgSimilarity += $l;
        }

        if ($avgSimilarity === 0) {
            return [];
        }

        $avgSimilarity = $avgSimilarity / count($similarities);

        return array_filter($books, function ($i) use ($similarities, $avgSimilarity) {

            return $similarities[$i] >= $avgSimilarity;
        }, ARRAY_FILTER_USE_KEY);
    }

    private function getPageLimit($page) {

        $PAGE_SIZE = 20;
        $page = $page - 1;

        return [$PAGE_SIZE * $page, $PAGE_SIZE];
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
        $rackNoQuery = $this->wrapQuery($rackNoQuery);

        return array($racks, $rackNoQuery);
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
        $subjectQuery = $this->wrapQuery($subjectQuery);

        return array($subjectIds, $subjectQuery);
    }

    private function listBorrowedBooks() {

        $title = '(SELECT title FROM books b WHERE b.bookid = w.bookid) AS title';

        $result = $this->select(
            "SELECT bookid, borrowid, name username, deptno userDeptNo, borrowdate, $title FROM borrow w " .
            "JOIN users USING(userid) " .
            "WHERE w.returndate IS NULL"
        );

        $this->send($result);
    }

    private function listFilters() {

        $response = [];

        $response['authors'] = (new Authors())->getAuthorsChecklist();
        $response['subjects'] = (new Subjects())->getSubjectsChecklist();
        $response['languages'] = (new Languages())->getLanguagesChecklist();
        $response['racks'] = array_map(
            function ($index) {

                return [
                    'label' => $index,
                    'selected' => FALSE
                ];
            },
            range(1, 17)
        );
        $response['availability'] = 'all';
        $response['page'] = 1;

        $this->send($response);
    }

    private function wrapQuery($query) {

        return strlen(trim($query)) ? "($query)" : $query;
    }

    public function get($command) {

        $commands = [
            'borrowed' => 'listBorrowedBooks',
            'filters' => 'listFilters'
        ];

        if (method_exists($this, $commands[$command])) {
            $this->$commands[$command]();
        } else {
            (new Error('API Not Implemented', 405))->send();
        }
    }

    public function put($filters) {

        list($authorIds, $authorQuery) = $this->getAuthorQuery($filters);
        list($subjectIds, $subjectQuery) = $this->getSubjectQuery($filters);
        list($racks, $rackNoQuery) = $this->getRackNoQuery($filters);
        list($languages, $languageQuery) = $this->getLanguageQuery($filters);
        list($start, $length) = $this->getPageLimit($filters['page']);

        $searchString = $filters['searchString'];
        $borrowQuery = $this->getAvailabilityQuery($filters['availability']);

        $queries = [
            $authorQuery,
            $subjectQuery,
            $rackNoQuery,
            $languageQuery,
            $borrowQuery
        ];

        $params = array_merge(
            $authorIds,
            $subjectIds,
            $racks,
            $languages
        );

        $WHERE = join(' AND ', array_filter($queries));

        if (!empty($WHERE)) {

            $WHERE = "WHERE $WHERE";
        }

        $books = $this->select(
            "SELECT bookid, title, rackno, accessno FROM books " .
            "LEFT JOIN authorassoc USING(bookid) " .
            "LEFT JOIN subjectassoc USING(bookid) " .
            "$WHERE GROUP BY bookid ORDER BY title DESC",
            $params
        );

        $booksAccession = array_filter($books, function ($book) use ($searchString) {

            return preg_match("/$searchString/", $book['accessno']);
        });

        $booksTitle = $this->fuzzyCompare($books, $searchString);

        $books = array_merge($booksAccession, $booksTitle);
        $books = array_map("unserialize", array_unique(array_map("serialize", $books)));

        usort($books, function ($book1, $book2) {

            return strcmp($book1['title'], $book2['title']);
        });

        $response = [];
        $response['list'] = array_slice($books, $start, $length);
        $response['count'] = count($books);
        $this->send($response, TRUE);
    }
}
