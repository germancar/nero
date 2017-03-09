<?php

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/helpers.php";

global $testing;
global $container;

$testing = true;
$container = require_once __DIR__ . "/container.php";
