<?php

require_once "classes/FileSessionHandler.php";

header("Content-type: text/html; charset=UTF-8");

$handler = new FileSessionHandler();
session_set_save_handler(
    array($handler, 'open'),
    array($handler, 'close'),
    array($handler, 'read'),
    array($handler, 'write'),
    array($handler, 'destroy'),
    array($handler, 'gc')
);

// the following prevents unexpected effects when using objects as save handlers
register_shutdown_function('session_write_close');

$_IDC_ENGINE_ROOT = '';
include_once('profiling.php');
PS('global');
require $_IDC_ENGINE_ROOT . 'core/classes/ccore.php';
$_core = new CCore();
PS('init');
$_core->initialize();
PE('init');
PS('render');
$_core->render();
PE('render');
PE('global');
//micro_display();
