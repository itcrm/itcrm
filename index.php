<?php

//$start = microtime(1);

define('_DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('_DB_USER', getenv('DB_USER') ?: 'YOUR_MARIADB_USER');
define('_DB_PASS', getenv('DB_PASSWORD') ?: 'YOUR_MARIADB_PASSWORD');
define('_DB_NAME', getenv('DB_DATABASE') ?: 'YOUR_MARIADB_DATABASE');

spl_autoload_register(function ($class) {
    require_once './classes/' . $class . '.class.php';
});

mb_regex_encoding('UTF-8');
mb_internal_encoding('UTF-8');

session_start();
header('Content-type:text/html; charset=utf-8');
$System = new System();
$System->Run();

//$end = microtime(1);
//echo $start.'-'.$end.'='.($end-$start);
