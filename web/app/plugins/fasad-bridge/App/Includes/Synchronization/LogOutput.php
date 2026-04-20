<?php

namespace FasadBridge\Includes\Synchronization;

use FasadBridge\Includes\Interfaces\OutputInterface;

class LogOutput implements OutputInterface
{
    public function output(string $output, string $class = '')
    {
        if ($class && strpos(static::class, $class) === false) {
            return;
        }
        error_log($output . PHP_EOL);
    }

}