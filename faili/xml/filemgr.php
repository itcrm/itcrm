<?php

require_once "../classes/FileSessionHandler.php";

header("Content-type: image/jpeg; charset=UTF-8");

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
$path = get_param('path', 'GP');
$width = get_param_int('width', 'G');
if ($width === false) $width = 0;
$height = get_param_int('height', 'G');
if ($height === false) $height = 0;

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
        unset($buffer);
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}

function moveToTrash($path) {
    $fullPath = C('filelist.root') . '/' . trim($path, '/');

    $pathItems = explode('/', trim($path, '/'));

    $lastPathItem = $pathItems[count($pathItems) - 1];
    unset($pathItems[count($pathItems) - 1]);

    $rootPath = implode('/', $pathItems);

    global $_core;

    $dateCode = $_core->user->login . '-' . $_core->user->id . '-' . date('Ymd-His');

    $targetRootPath =  C('filelist.root') . '/' . C('trash.directory') . '/' . $dateCode;
    $targetPath =  $targetRootPath . '/' . $rootPath;

    // izveidojam miskasti kā tādu
    @mkdir(C('filelist.root') . '/' . C('trash.directory'), 0777, true);
    @chmod(C('filelist.root') . '/' . C('trash.directory'), 0777);
    // izveidojam "dzēsuma" saknes direktoriju
    @mkdir($targetRootPath, 0777, true);
    @chmod($targetRootPath, 0777);

    // izvedojam struktūru, kurā pārvietosim failus
    $tmpTargetPath = $targetRootPath;
    foreach ($pathItems as $item) {
        $tmpTargetPath = $tmpTargetPath . '/' . $item;
        @mkdir($tmpTargetPath, 0777, true);
        @chmod($tmpTargetPath, 0777);
    }

    if (is_dir($fullPath)) {
        moveDirectory($fullPath, rtrim($targetPath, '/') . '/' . $lastPathItem, false);
        @rmdir($fullPath);
    } else {
        return @rename($fullPath, rtrim($targetPath, '/') . '/' . $lastPathItem);
    }
}

switch ($cmd) {
    case 'rename': {
            $newName = get_param('newname', 'GP');

            if (strpos($newName, '/') !== false || strpos($newName, '"') !== false) {
                echo 'E_CHARS';
                break;
            }

            if (CONVERT) {
                $path = iconv('utf-8', 'windows-1257', $path);
                $newName = iconv('utf-8', 'windows-1257', $newName);
            }

            $path = C('filelist.root') . '/' . trim($path, '/');
            $info = explode('/', $path);
            unset($info[count($info) - 1]);
            $basePath = implode('/', $info);
            $newPath = $basePath . '/' . $newName;

            if (file_exists($newPath)) {
                echo 'E_EXISTS';
            } else {
                rename($path, $newPath);
            }

            break;
        }
    case 'delete': {
            if (CONVERT) {
                $path = iconv('utf-8', 'windows-1257', $path);
            }
            if (CFileManagerEx::checkDelete()) {
                moveToTrash($path);
            }
            break;
        }
    case 'delete-multi': {
            $pathStr = get_param('paths', 'P');
            $paths = explode("\n", $pathStr);
            foreach ($paths as $fpath) {
                $fpath = trim($fpath, "\r\n ");
                if ($fpath != '') {
                    if (CONVERT) {
                        $fpath = iconv('utf-8', 'windows-1257', $fpath);
                    }
                    if (CFileManagerEx::checkDelete()) {
                        moveToTrash($fpath);
                    }
                }
            }
            break;
        }
    case 'mkdir': {
            $newName = get_param('newname', 'GP');

            if (strpos($newName, '/') !== false || strpos($newName, '"') !== false) {
                echo 'E_CHARS';
                break;
            }

            if (CONVERT) {
                $path = iconv('utf-8', 'windows-1257', $path);
                $newName = iconv('utf-8', 'windows-1257', $newName);
            }
            $path = C('filelist.root') . '/' . trim($path, '/');
            $newPath = $path . '/' . $newName;

            if (file_exists($newPath)) {
                echo 'E_EXISTS';
            } else {
                mkdir($newPath);
                @chmod($newPath, 0777);
            }
            break;
        }
    case 'clipop': {
            $op = get_param('op');

            switch ($op) {
                case 'copy': {
                        $path = get_param('path');

                        if (CONVERT) {
                            $path = iconv('utf-8', 'windows-1257', $path);
                        }
                        CSessionEx::setVar('_clipboard', $path);
                        CSessionEx::setVar('_clipboard_ex', '');
                        CSessionEx::setVar('_clipboard_op', 'copy');
                        break;
                    }
                case 'copy-multi': {
                        $paths = get_param('paths', 'P');

                        CSessionEx::setVar('_clipboard_ex', $paths);
                        CSessionEx::setVar('_clipboard', '');
                        CSessionEx::setVar('_clipboard_op', 'copy');
                        break;
                    }
                case 'cut': {
                        $path = get_param('path');

                        if (CONVERT) {
                            $path = iconv('utf-8', 'windows-1257', $path);
                        }
                        CSessionEx::setVar('_clipboard', $path);
                        CSessionEx::setVar('_clipboard_ex', '');
                        CSessionEx::setVar('_clipboard_op', 'cut');
                        break;
                    }
                case 'cut-multi': {
                        $paths = get_param('paths', 'P');

                        CSessionEx::setVar('_clipboard_ex', $paths);
                        CSessionEx::setVar('_clipboard', '');
                        CSessionEx::setVar('_clipboard_op', 'cut');
                        break;
                    }
                case 'paste': {
                        $extClb = trim(CSessionEx::getVar('_clipboard_ex', ''));
                        if ($extClb != '') {
                            // multi-file mode
                            $paths = explode("\n", $extClb);

                            $nwop = CSessionEx::getVar('_clipboard_op', '');
                            foreach ($paths as $fpath) {
                                $fpath = trim($fpath, "\r\n ");
                                if ($fpath != '') {
                                    if (CONVERT) {
                                        $fpath = iconv('utf-8', 'windows-1257', $fpath);
                                    }
                                    $path = $_IDC_CONFIG['filelist.root'] . '/' . ltrim($fpath, '/');

                                    $target = get_param('target');
                                    if (CONVERT) {
                                        $target = iconv('utf-8', 'windows-1257', $target);
                                    }
                                    $fileinfo = pathinfo($path);
                                    $dest = $_IDC_CONFIG['filelist.root'] . '/' . rtrim($target, '/') . '/' . $fileinfo['basename'];

                                    switch ($nwop) {
                                        case 'copy': {
                                                copyItem($path, $dest, $targetDir = $_IDC_CONFIG['filelist.root'] . '/' . $target);
                                                break;
                                            }
                                        case 'cut': {
                                                moveItem($path, $dest, $targetDir = $_IDC_CONFIG['filelist.root'] . '/' . $target);
                                                CSessionEx::setVar('_clipboard_ex', '');
                                                CSessionEx::setVar('_clipboard', '');
                                                CSessionEx::setVar('_clipboard_op', '');
                                                break;
                                            }
                                    }
                                }
                            }
                        } else {
                            // single file mode
                            $path = $_IDC_CONFIG['filelist.root'] . '/' . ltrim(CSessionEx::getVar('_clipboard', ''), '/');
                            $nwop = CSessionEx::getVar('_clipboard_op', '');

                            $target = get_param('target');
                            if (CONVERT) {
                                $target = iconv('utf-8', 'windows-1257', $target);
                            }
                            $fileinfo = pathinfo($path);
                            $dest = $_IDC_CONFIG['filelist.root'] . '/' . rtrim($target, '/') . '/' . $fileinfo['basename'];

                            switch ($nwop) {
                                case 'copy': {
                                        copyItem($path, $dest, $targetDir = $_IDC_CONFIG['filelist.root'] . '/' . $target);
                                        break;
                                    }
                                case 'cut': {
                                        moveItem($path, $dest, $targetDir = $_IDC_CONFIG['filelist.root'] . '/' . $target);
                                        CSessionEx::setVar('_clipboard_ex', '');
                                        CSessionEx::setVar('_clipboard', '');
                                        CSessionEx::setVar('_clipboard_op', '');
                                        break;
                                    }
                            }
                        }
                    }
            }

            break;
        }
    default: {
            if (CONVERT)
                $path = iconv('utf-8', 'windows-1257', $path);
            $path = C('filelist.root') . '/' . trim($path, '/');

            if (get_param('fullheight')) $height = 0;
            $thumb = CThumbCache::getThumb($path, $width, $height);
            if ($thumb) {
                readfile_chunked($_IDC_ENGINE_ROOT . C('filelist.thumbcache.root') . '/' . $thumb->id . '.jpg');
            } else {
                readfile_chunked($_IDC_ENGINE_ROOT . 'template/main/img/image.jpg');
            }
            break;
        }
}
