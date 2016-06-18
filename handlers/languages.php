<?php

namespace handlers;

use lib\Handler;

class Languages extends Handler {

    public function get() {

        $result = $this->select('SELECT DISTINCT language FROM books WHERE language IS NOT NULL ORDER BY language');

        $this->send($result, TRUE);
    }

    public function getLanguagesChecklist() {

        $languages = $this->select('SELECT DISTINCT language as label FROM books WHERE language IS NOT NULL ORDER BY language');

        return array_map(
            function ($language) {

                $language['selected'] = FALSE;

                return $language;
            },
            $languages
        );
    }
}
