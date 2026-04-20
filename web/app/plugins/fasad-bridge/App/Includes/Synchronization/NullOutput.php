<?php

namespace FasadBridge\Includes\Synchronization;

use FasadBridge\Includes\Interfaces\OutputInterface;

class NullOutput implements OutputInterface
{
    public function output(string $output, string $class = '')
    {
    }

}