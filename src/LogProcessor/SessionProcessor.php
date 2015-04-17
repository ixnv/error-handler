<?php

namespace eLama\ErrorHandler\LogProcessor;

class SessionProcessor
{
    public function __invoke(array $record)
    {
        // If $_SESSION is less than 20 KB then add it to report
        if (isset($_SESSION) && strlen(serialize($_SESSION)) < 2e4) {
            $record['extra'] = array_merge(
                $record['extra'],
                [
                    '$_SESSION' => $_SESSION
                ]
            );
        }

        return $record;
    }
}
