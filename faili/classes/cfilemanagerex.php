<?php

/**
 * Description of cfilemanager
 *
 * @author wth
 */
class CFileManagerEx {
    static $_orderCache = false;


    /**
     * Pārbauda vai tekošajam lietotājam ir tiesības piekļūt noteiktajam ceļam
     * @param String $s
     */
    static function checkPath($path, $setOrder = false) {
        if (self::$_orderCache === false) {
            self::$_orderCache = new CList('COrder');
            self::$_orderCache->indexAttributes = array('Code');
            self::$_orderCache->loadByField('', '');
        }

        $pathItems = explode('/', trim($path, '/'));

        if (@$pathItems[count($pathItems) - 1] == 'Thumbs.db') return false;

        if (@$pathItems[0] == C('rowimage.directory')) return false;
        if (@$pathItems[0] == C('trash.directory')) return false;

        foreach ($pathItems as $idx => $pathItem) {
            if (substr($pathItem, 0, 1) == '.') return false;
            if (substr($pathItem, 0, 1) == '-') return false;
            if ($idx == 0 && $setOrder) {
                $project = trim($pathItem, ' /');
                if (isset(self::$_orderCache->attributeIndex['Code'][$project])) {
                    $orderId = self::$_orderCache->attributeIndex['Code'][$project];
                    register_variable('currentProject', self::$_orderCache->objects[$orderId]);
                }
            }
        }
        return true;
    }

    static function getCurrentPath() {
        $fileListRoot = C('filelist.root');

        $subPath = CCore::getRequestPath(false);
        $subPath = str_replace('|', '&', $subPath);

        if (CONVERT)
            $subPath = iconv('utf-8', 'windows-1257', $subPath);

        if (self::checkPath($subPath, false)) {
            if (($subPath != '')) {
                $currentPath = $fileListRoot . '/' . trim($subPath, '/');
            } else {
                $currentPath = rtrim($fileListRoot, '/');
            }

            return $currentPath;
        } else {
            return '';
        }
    }

    static function processUpload() {
        if (isset($_FILES['f_uploadfile'])) {
            if (($_FILES['f_uploadfile']['name'] != '') && ($_FILES['f_uploadfile']['error'] == 0) && ($_FILES['f_uploadfile']['size'] > 0)) {
                $path = self::getCurrentPath();
                $destination = $path . '/' . $_FILES['f_uploadfile']['name'];
                if (!file_exists($destination) && is_dir($path)) {
                    move_uploaded_file($_FILES['f_uploadfile']['tmp_name'], $destination);
                } else {
                    echo 'File exists!';
                    die;
                }
            }
        }
    }
}
