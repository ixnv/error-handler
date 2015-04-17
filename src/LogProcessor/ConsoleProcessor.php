<?php

namespace eLama\ErrorHandler\LogProcessor;

use eLama\ErrorHandler\ConsoleData;

class ConsoleProcessor
{
    use ConsoleData;

    public function __invoke(array $record)
    {
        if (php_sapi_name() != 'cli') {
            return $record;
        }

        $hostName = php_uname('n');
        $cliUsername = $this->getCliUsername();

        $record['extra'] = array_merge(
            $record['extra'],
            [
                'argv'          => $GLOBALS['argv'],
                'cliUserName'   => $cliUsername,
                'cliUserDomain' => $hostName,
            ]
        );

        return $record;
    }
}
