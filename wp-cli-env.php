<?php
$root = dirname(__FILE__);
require_once $root . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable($root);
$dotenv->load();
