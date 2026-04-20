<?php

namespace PrekWebHelper\Includes;

use PrekWeb\Includes\Helpers;

class PrekWebHelperHandler
{
    protected $loader;

    public function __construct(\PrekWebHelper\Includes\Loader $loader)
    {
        $this->loader = $loader;
    }

    public function run()
    {
    }
}
