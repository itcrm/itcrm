<?php

//$start = microtime(1);

define('_DB_PATH', getenv('DB_PATH') ?: __DIR__ . '/data/database.sqlite');

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
