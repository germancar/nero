<?php

//require the needed files to bootstrap the app for testing
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/helpers.php";

global $testing;
global $container;

//set testing flag
$testing = true;

//load up the container
$container = require_once __DIR__ . "/container.php";
