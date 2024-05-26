<?php

define('VIEW_LIST', 0);
define('VIEW_LARGE', 1);
define('VIEW_MEGA', 2);

if (P('filemanager.charset.convert', 0)) {
    define('CONVERT', true);
} else {
    define('CONVERT', false);
}

function get_mime_type($filename, $mimePath = '') {
    $fileext = substr(strrchr($filename, '.'), 1);

    if (empty($fileext)) return false;
    $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
    $lines = file("{$mimePath}mime.types");

    foreach ($lines as $line) {
        if (substr($line, 0, 1) == '#') continue; // skip comments
        $line = rtrim($line) . " ";
        if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
        return $matches[1];
    }
    return false; // no match at all
}

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

function getFileExt($name) {
    $info = pathinfo($name);
    $fileName = @$info['extension'];
    return strtolower($fileName);
}

function isImage($name) {
    $ext = getFileExt($name);
    return $ext == 'jpg' || $ext == 'png' || $ext == 'gif' || $ext == 'jpeg';
}

class CVStart {
    function processPath($path) {
        $fileListRoot = C('filelist.root');
        return trim(str_replace($fileListRoot, '', $path), '/');
    }

    function urlEncode($s) {
        $s = str_replace('&', '|', $s);
        $s = urlencode($s);

        $s = str_replace('%2F', '/', $s);

        //<a id="link2" class="folder" href="/faili/zr/A+%7Camp%3B+G" rel="zr/A &amp; G">A &amp; G</a>
        $s = str_replace('%7Camp%3B', '%7C', $s);
        return $s;
    }

    function giveFile($path, $fileName) {
        $fullPath = C('filelist.root') . '/' . $path . '/' . $fileName;

        $mime = get_mime_type($fullPath);
        if ($mime == '') $mime = 'application/octet-stream';

        header("Content-type: " . $mime);
        header("Content-Disposition: attachment; filename=\"" . ($fileName) . '"');

        $filesize = filesize($fullPath);
        header("Content-length: " . $filesize);
        readfile_chunked($fullPath);
        die;
    }

    function fixSymbols($dir) {
        return CONVERT ? iconv('windows-1257', 'utf-8', $dir) : $dir;
    }

    function formatPathItem($pathItem) {
        $pathItem = str_replace('.', '.&#8203;', H($pathItem));
        $pathItem = str_replace('_', '_&#8203;', $pathItem);
        $pathItem = str_replace('-', '-&#8203;', $pathItem);

        return $this->fixSymbols($pathItem);
    }

    function listDirectoryLarge($currentPath) {
        $dir = scandir($currentPath);
        natcasesort($dir);
        $dir = array_reverse($dir);

        $out = '';

        foreach ($dir as $idx => $dirEntry) {
            $fullpath = rtrim($currentPath, '/') . '/' . ltrim($dirEntry, '/');
            $relPath = H($this->processPath($currentPath) . '/' . $dirEntry, '/');
            $relPath = $this->fixSymbols(ltrim($relPath, '/'));

            if (($dirEntry != '.') && ($dirEntry != '..') && (CFileManagerEx::checkPath($relPath))) {
                $entryClass = (is_dir($fullpath)) ? 'folder' : 'file';

                $thumbStyle = '';
                if (isImage($dirEntry)) {
                    $thumbStyle = ' style="background:url({{B}}xml/filemgr.php?path=' . urlencode($relPath) . '&width=32&height=32&pad=1) center top no-repeat !important;" ';
                    $asImage = 'true';
                    $entryClass .= ' image';
                } else {
                    $asImage = 'false';
                }

                $out .= '<div id="file' . $idx . '" class="direntry-large" onclick="setFile(' . $idx . ');return false" onDblclick="openFile(' . $idx . ',' . $asImage . ');return false" ><a id="link' . $idx . '" class="' . $entryClass . '"' . $thumbStyle . 'href="' . C('base-url') . $this->urlEncode($relPath) . '" rel="' . $relPath . '">' . $this->formatPathItem($dirEntry) . '</a></div>';
            }
        }

        $out .= '<br class="c"/>';

        register_startup('
            window.addEvent("domready", function() {
                initLoadingOverlay();
            });
        ');

        return $out;
    }

    function listDirectoryMega($currentPath) {
        $dir = scandir($currentPath);
        natcasesort($dir);
        $dir = array_reverse($dir);

        $out = '';

        foreach ($dir as $idx => $dirEntry) {
            $fullpath = rtrim($currentPath, '/') . '/' . ltrim($dirEntry, '/');
            $relPath = H($this->processPath($currentPath) . '/' . $dirEntry, '/');
            $relPath = $this->fixSymbols(ltrim($relPath, '/'));

            if (($dirEntry != '.') && ($dirEntry != '..') && (CFileManagerEx::checkPath($relPath))) {
                $entryClass = (is_dir($fullpath)) ? 'folder' : 'file';

                $thumbStyle = '';
                if (isImage($dirEntry)) {
                    $thumbStyle = ' style="background:url({{B}}xml/filemgr.php?path=' . urlencode($relPath) . '&width=64&height=64&pad=1) center top no-repeat !important;" ';
                    $asImage = 'true';
                    $entryClass .= ' image';
                } else {
                    $asImage = 'false';
                }

                $out .= '<div id="file' . $idx . '" class="direntry-mega" onclick="setFile(' . $idx . ');return false" onDblclick="openFile(' . $idx . ',' . $asImage . ');return false" ><a id="link' . $idx . '" class="' . $entryClass . '"' . $thumbStyle . 'href="' . C('base-url') . $this->urlEncode($relPath) . '" rel="' . $relPath . '">' . $this->formatPathItem($dirEntry) . '</a></div>';
            }
        }

        $out .= '<br class="c"/>';

        register_startup('
            window.addEvent("domready", function() {
                initLoadingOverlay();
            });
        ');

        return $out;
    }

    function listDirectory($currentPath) {
        global $_core;
        $maxRowCount = $_core->readSetting('rows_' . $_core->user->login, 15);
        $dir = scandir($currentPath);
        natcasesort($dir);
        $dir = array_reverse($dir);

        $out = '<table class="filelisttable"><tr valign="top" style="vertical-align: top">';

        $rowCount = 0;

        foreach ($dir as $idx => $dirEntry) {
            $fullpath = rtrim($currentPath, '/') . '/' . ltrim($dirEntry, '/');
            $relPath = H($this->processPath($currentPath) . '/' . $dirEntry, '/');
            $relPath = $this->fixSymbols(ltrim($relPath, '/'));

            $hasPermission = CFileManagerEx::checkPath($relPath);

            if (($dirEntry != '.') && ($dirEntry != '..') && ($hasPermission)) {
                $entryClass = (is_dir($fullpath)) ? 'folder' : 'file';

                $thumbStyle = '';
                if (isImage($dirEntry)) {
                    $thumbStyle = ' style="left top no-repeat !important;" ';
                    $asImage = 'true';
                    $entryClass .= ' image';
                } else {
                    $asImage = 'false';
                }

                if ($rowCount == 0) {
                    $out .= '<td>';
                }

                $out .= '<div id="file' . $idx . '" class="direntry" alt="' . $idx . '|' . (($asImage == 'true') ? 1 : 0) . '"><a id="link' . $idx . '" class="' . $entryClass . '"' . $thumbStyle . 'href="' . C('base-url') . $this->urlEncode($relPath) . '" rel="' . $relPath . '">' . $this->formatPathItem($dirEntry) . '</a></div>';
                $rowCount++;
                if ($rowCount == $maxRowCount) {
                    $rowCount = 0;
                    $out .= '</td>';
                }
            }
        }

        if ($rowCount != 0) $out .= '</td>';
        $out .= '<tr></table>';

        $out .= '<br class="c"/>';

        $allowSlimboxEdits = (CFileManagerEx::checkSlimboxEdit()) ? 1 : 0;

        register_script('scripts/wheel.js');
        register_startup('
            window._allowSlimboxEdits = ' . ($allowSlimboxEdits) . ';

            window.addEvent("domready", function() {
                initLoadingOverlay();

                $(\'filelist\').addEvent(\'click:relay(.direntry)\', function(event, target){
                    event.preventDefault();
                    var alt = target.get(\'alt\');
                    var res = alt.split("|");
                    var idx = res[0];
                    var asImage = res[1];
                    //alert(asImage);

                    var isShift = event.control == true;

                    setFile(idx,isShift);
                });
                $(\'filelist\').addEvent(\'dblclick:relay(.direntry)\', function(event, target){
                    event.preventDefault();
                    var alt = target.get(\'alt\');
                    var res = alt.split("|");
                    var idx = res[0];
                    var asImage = res[1];
                    openFile(idx,asImage==\'1\');
                });

            });
        ');

        return $out;
    }

    function renderFileList($currentView) {
        $fileListRoot = C('filelist.root');

        $subPath = CCore::getRequestPath(false);
        $subPath = str_replace('|', '&', $subPath);
        if (CONVERT) {
            $subPath = iconv('utf-8', 'windows-1257', $subPath);
        }

        if (!CFileManagerEx::checkCurrentUser() || !CFileManagerEx::checkPath($subPath, true)) {
            return '<div class="error" id="error">Pieeja liegta!</div>';
        }

        if (($subPath != '')) {
            $currentPath = $fileListRoot . '/' . $subPath . '/';
            if (!is_dir($currentPath)) {
                $info = pathinfo($currentPath);
                $fileName = $info['basename'];
                $currentPath = $info['dirname'];
            } else {
                $fileName = '';
            }
        } else {
            $currentPath = $fileListRoot;
            $fileName = '';
        }

        register_variable('currentPath', $this->fixSymbols($this->processPath($currentPath)));

        if ($fileName != '') {
            return $this->giveFile($this->processPath($currentPath), $fileName);
        }

        switch ($currentView) {
            case VIEW_LIST:
                return $this->listDirectory($currentPath);
            case VIEW_MEGA:
                return $this->listDirectoryMega($currentPath);
            default:
                return $this->listDirectoryLarge($currentPath);
        }
    }

    /**
     * @global CCore $_core
     */
    function render() {
        global $_core;

        if ($_core->user->id <= 0) {
            header('Location: /');
            die;
        }

        CFileManagerEx::processUpload();

        $cmd = get_param('cmd', 'GP');
        switch ($cmd) {
            case 'list': {
                    $_core->saveSetting('view' . $_core->user->id, VIEW_LIST);
                    return $this->renderFileList(VIEW_LIST);
                }
            case 'large': {
                    $_core->saveSetting('view' . $_core->user->id, VIEW_LARGE);
                    return $this->renderFileList(VIEW_LARGE);
                }
            case 'mega': {
                    $_core->saveSetting('view' . $_core->user->id, VIEW_MEGA);
                    return $this->renderFileList(VIEW_MEGA);
                }
            default: {
                    return $this->renderFileList(
                        $_core->readSetting('view' . $_core->user->id, VIEW_LARGE)
                    );
                }
        }
    }
}
