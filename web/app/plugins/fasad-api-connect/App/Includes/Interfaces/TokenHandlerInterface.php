<?php

namespace FasadApiConnect\Includes\Interfaces;

interface TokenHandlerInterface
{
    public function set($data, $expires);
    public function get();
    public function delete();
}