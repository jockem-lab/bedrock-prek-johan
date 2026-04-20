<?php

namespace FasadBridge\Includes\Interfaces;

interface FasadObjectHandlerInterface // todo: perhaps this should be named something else, lets see later...
{
    public function synchronize();

    public function getTitle($item, $postType);
}