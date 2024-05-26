<?php

function element_fm_commands() {
    $pathItems = explode('/', get_variable('currentPath'));
    $fullPath = '';
    $formattedPath = '<a href="' . url() . '">Faili</a>';
    foreach ($pathItems as $idx => $pathItem) {
        if ($pathItem) {
            $fullPath .= '/' . $pathItem;
            $formattedPath .= ' &raquo; <a href="' . url(trim($fullPath, '/')) . '">' . H(trim($pathItem, '/')) . '</a>';
        }
    }

    $backPath = C('base-url');
    for ($i = 0; $i < count($pathItems) - 1; $i++) {
        $backPath .= $pathItems[$i] . '/';
    }

    $out = '';

    if ((count($pathItems) >= 1) && ($pathItems[0] != '')) {
        $out .= '<button class="back" onclick="window.location=\'' . $backPath . '\'">Atpakaļ</button>';
    } else {
        $out .= '<button disabled="disabled" class="back disabled" onclick="window.location=\'' . $backPath . '\'">Atpakaļ</button>';
    }
    $out .= '<button class="open" onclick="openCurrentFile(false)">Atvērt</button>';

    if (CFileManagerEx::checkRename()) {
        $out .= '<button id="btn-rename" class="rename" onclick="renameFile()" style="display:none">Pārsaukt</button>';
        $out .= '<div id="commands-rename" style="display:none"><input type="text" value="" id="f_filename" name="f_filename"/>
            <button title="Labot" style="cursor: pointer; padding: 0;float:left;" onclick="submitRename();"><img src="{{B}}template/main/img/icon-ok.png" width="16" height="16" alt="Labot" title="Labot"/></button>
            <button title="Atcelt" style="cursor: pointer; padding: 0;float:left;" onclick="cancelRename();"><img src="{{B}}template/main/img/icon-cancel.png" width="16" height="16" alt="Atcelt" title="Atcelt"/></button>
        </div>';
    }

    if (CFileManagerEx::checkCreate()) {
        $out .= '<button id="btn-mkdir" class="mkdir" onclick="mkDir()">Izveidot</button>';
        $out .= '<div id="commands-mkdir" style="display:none"><input type="text" value="" id="f_dirname" name="f_dirname"/>
            <button title="Izveidot" style="cursor: pointer; padding: 0;float:left;" onclick="submitMkDir();"><img src="{{B}}template/main/img/icon-ok.png" width="16" height="16" alt="Izveidot" title="Izveidot"/></button>
            <button title="Atcelt" style="cursor: pointer; padding: 0;float:left;" onclick="cancelMkDir();"><img src="{{B}}template/main/img/icon-cancel.png" width="16" height="16" alt="Atcelt" title="Atcelt"/></button>
        </div>';
    }

    if (CFileManagerEx::checkUpload()) {
        $out .= '<button id="btn-upload" class="upload" onclick="uploadFile()">Pievienot</button>';
        $out .= '<div id="commands-upload" style="display:none"><form action="" method="post" enctype="multipart/form-data">
            <input type="file" value="" id="f_uploadfile" name="f_uploadfile" size="20"/>
                <button type="submit" title="Pievienot" style="cursor: pointer; padding: 0;float:left;"><img src="{{B}}template/main/img/icon-ok.png" width="16" height="16" alt="Izveidot" title="Pievienot"/></button>
                <button title="Atcelt" style="cursor: pointer; padding: 0;float:left;" onclick="cancelUpload();return false;"><img src="{{B}}template/main/img/icon-cancel.png" width="16" height="16" alt="Atcelt" title="Atcelt"/></button>
            </form>
        </div>';
    }

    if (CFileManagerEx::checkDelete()) {
        $out .= '<button id="btn-delete" class="delete" onclick="deleteFile()" style="display:none">Dzēst</button>';
    }

    $canCopy = CFileManagerEx::checkCopy();
    $canCut = CFileManagerEx::checkCut();

    if ($canCopy) {
        $out .= '<button id="btn-copy" class="copy" onclick="copyFile()" style="display:none">Kopēt</button>';
    }

    if ($canCut) {
        $out .= '<button id="btn-cut" class="cut" onclick="cutFile()" style="display:none">Pārvietot</button>';
    }

    $clipboardEx = trim(CSessionEx::getVar('_clipboard_ex', ''));

    $hasClipboard = (CSessionEx::getVar('_clipboard', '') != '') || ($clipboardEx != '');
    $clipboardRows = explode("\n", $clipboardEx);
    $clipboardRowString = implode('\n', $clipboardRows);

    $out .= '<script type="text/javascript">var _clipboard=' . ($hasClipboard ? 'true' : 'false') . '</script>';
    if ($clipboardEx != '') {
        $out .= '<script type="text/javascript">_clipboardEx="' . $clipboardRowString . '"</script>';
    }

    if ($canCut || $canCopy) {
        $out .= '<button id="btn-paste" class="paste" onclick="pasteFile()" style="' . (($hasClipboard) ? '' : 'display:none') . '">Ievietot</button>';
    }

    $out .= '<button id="btn-select" class="select" onclick="selectAll()">&nbsp;</button>';
    $out .= '<button id="btn-unselect" class="unselect" onclick="unselectAll()">&nbsp;</button>';

    $out .= '<button class="mini-button" onclick="window.location=\'' . url_self_clean_ex(['cmd' => 'list']) . '\'"><img src="{{B}}template/main/img/icon-details.png" /></button>';
    $out .= '<button class="mini-button" onclick="window.location=\'' . url_self_clean_ex(['cmd' => 'large']) . '\'"><img src="{{B}}template/main/img/icon-large.png" /></button>';
    $out .= '<button class="mini-button" onclick="window.location=\'' . url_self_clean_ex(['cmd' => 'mega']) . '\'"><img src="{{B}}template/main/img/icon-mega.png" /></button>';

    $out .= '<br class="c"/>';

    return $out;
}
