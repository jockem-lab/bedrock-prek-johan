<?php

namespace FasadBridge\Includes\Interfaces;

interface OutputInterface
{
    /**
     * @param string $output What to output
     * @param string $class  Only do output if this class
     */
    public function output(string $output, string $class = '');
}
