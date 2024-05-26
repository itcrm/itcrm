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

function readfile_chunked($filename, $retbytes = true) {
    $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
    $buffer = '';
    $cnt = 0;
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        @set_time_limit(90);
        $buffer = fread($handle, $chunksize);
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
        unset($buffer);     // ?? let's help PHPs garbage collector a little bit...
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}

$rowID = get_param_int('rowid');

$width = get_param_int('width');
$height = get_param_int('height');
if ($width == 0) $width = 800;
if ($height == 0) $height = 600;

switch ($cmd) {
    case 'get': {
            $path = C('rowimage.directory');
            $fileName = $rowID . '.jpg';

            $fullPath = C('filelist.root') . '/' . $path . '/' . $fileName;

            if (@file_exists($fullPath)) {
                header("Content-type: image/jpeg");
                header("Content-Disposition: inline");

                $thumb = CThumbCache::getThumb($fullPath, $width, $height);
                if ($thumb) {
                    readfile_chunked($_IDC_ENGINE_ROOT . C('filelist.thumbcache.root') . '/' . $thumb->id . '.jpg');
                }
                die();
            } else {
                header("Content-type: text/plain");
                echo 'EPIC FAIL!';
                die();
            }
            break;
        }

    default: {
            $path = C('rowimage.directory');

            $error = @$_FILES["r_bilde"]['error'];
            if ($error == UPLOAD_ERR_OK) {
                if (@$_FILES['r_bilde']['size'] == 0) {
                    break;
                }

                $filename = @$_FILES["r_bilde"]['name'];
                $tmpname = @$_FILES["r_bilde"]['tmp_name'];
                $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
                $type = strtolower(@$_FILES["r_bilde"]['type']);

                if (($ext == 'jpg') || ($ext == 'png')) {
                    $fullPath = C('filelist.root') . '/' . $path . '/' . $rowID . '.jpg';
                    @mkdir(C('filelist.root') . '/' . $path . '/', 0777, true);
                    @chmod(C('filelist.root') . '/' . $path . '/', 0777);
                    convertToJPEG($tmpname, $fullPath);
                    @chmod($fullPath, 0777);
                    CThumbCache::getThumb($fullPath, $width, $height, true);
                }

                header('Location: /lv/Data');
            }

            header('Location: /lv/Data');
            break;
        }
}
header('Location: /lv/Data');
echo "done!";
