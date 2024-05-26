<?php
function _faili_row_file_exists($rowId) {
    include 'config/core.php';

    $path = $_IDC_CONFIG['rowimage.directory'];
    $fileName = $rowId . '.jpg';

    $fullPath = $_IDC_CONFIG['filelist.root'] . '/' . $path . '/' . $fileName;
    if (file_exists($fullPath)) {
        return '/faili/xml/rowimage.php?cmd=get&rowid=' . $rowId . '&randomstuff=' . rand(0, 100000) . '.' . rand(0, 1000);
    } else {
        return false;
    }
}

function _faili_create_order_directory_ex($orderName) {
    include 'config/core.php';
    $rootDir =  $_IDC_CONFIG['filelist.root'] . '/' . $orderName;

    if (!file_exists($rootDir)) {
        @mkdir($rootDir, 0777, true);
        @chmod($rootDir, 0777);
    }
}
