<?php

$_IDC_ENGINE_ROOT = '../';
require $_IDC_ENGINE_ROOT . 'core/classes/ccore.php';
$_core = new CCore();
$_core->initialize();

if (P('filemanager.charset.convert', 0)) {
    define('CONVERT', true);
} else {
    define('CONVERT', false);
}

$cmd = get_param('cmd', 'G');

$rowID = get_param_int('rowid');
$order = get_param('order');

switch ($cmd) {
    default: {
            $path = $order . '';

            foreach (@$_FILES['files']['name'] as $idx => $dummy) {
                $fileData['error'] = $_FILES['files']['error'][$idx];
                $fileData['name'] = $_FILES['files']['name'][$idx];
                $fileData['type'] = $_FILES['files']['type'][$idx];
                $fileData['tmp_name'] = $_FILES['files']['tmp_name'][$idx];
                $fileData['size'] = $_FILES['files']['size'][$idx];

                $error = $fileData['error'];
                if ($error == UPLOAD_ERR_OK) {
                    if (@$fileData['size'] == 0) {
                        break;
                    }

                    $fileName = @$fileData['name'];
                    $tmpname = @$fileData['tmp_name'];
                    $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
                    $type = strtolower(@$fileData['type']);

                    @chmod(C('filelist.root') . '/' . $path . '/', 0777);
                    @move_uploaded_file($tmpname, C('filelist.root') . '/' . $path . '/' . $fileName);
                    @chmod(C('filelist.root') . '/' . $path . '/' . $fileName, 0777);
                }
            }

            header('Location: /lv/Data');
            break;
        }
}
header('Location: /lv/Data');
echo "done!";
