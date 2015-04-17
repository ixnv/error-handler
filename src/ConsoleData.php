<?php

namespace eLama\ErrorHandler;

trait ConsoleData
{
    public function getCliUsername()
    {
        $cliUserName = isset($_SERVER['USERNAME']) ? $_SERVER['USERNAME'] : null;
        if (!$cliUserName) {
            if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
                $cliUserName = posix_getpwuid(posix_geteuid());
                $cliUserName = $cliUserName['name'];
            }
        }

        return $cliUserName;
    }
}
