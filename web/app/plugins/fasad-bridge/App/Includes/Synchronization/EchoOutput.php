<?php

namespace FasadBridge\Includes\Synchronization;

use FasadBridge\Includes\Interfaces\OutputInterface;

class EchoOutput implements OutputInterface
{
    public function output(string $output, string $class = '')
    {
        if ($class && strpos(static::class, $class) === false) {
            return;
        }
        echo $output . PHP_EOL;
        ob_flush();
        flush();
    }

}