<?php
header("Content-type: text/javascript; charset=UTF-8");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

$_IDC_ENGINE_ROOT = '../';
require $_IDC_ENGINE_ROOT . 'core/classes/ccore.php';
?>
var siteroot='<?= detectBaseUrl() ?>';