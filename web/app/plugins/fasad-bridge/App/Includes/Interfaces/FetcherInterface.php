<?php

namespace FasadBridge\Includes\Interfaces;

interface FetcherInterface
{
    public function getAttribute(string $attr);

    public function getNestedAttribute(string $attr);
}