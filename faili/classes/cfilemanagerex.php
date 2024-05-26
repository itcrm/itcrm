<?php

/**
 * Description of cfilemanager
 *
 * @author wth
 */
class CFileManagerEx {
    static $_orderCache = false;
    static $_rightsCache = false;

    /**
     * Pārbauda, vai tekošajam lietotājam ir tiesības piekļūt failu managerim
     */
    static function checkCurrentUser() {
        global $_core;
        $userList = P('filemanager.users.allowed', '');
        if ($userList == '') return true;

        $list = explode(' ', $userList);
        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkDelete() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.delete.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkCreate() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.create.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkRename() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.rename.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkUpload() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.upload.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkCopy() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.copy.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkCut() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.cut.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    static function checkSlimboxEdit() {
        global $_core;

        $isAdmin = ($_core->user->status == 99);
        if ($isAdmin) return true;

        $userList = P('filemanager.users.imagerename.allowed', '');
        if ($userList == '') return false;

        $list = explode(' ', $userList);

        foreach ($list as $userName) {
            if (strtolower($userName) == strtolower($_core->user->login)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Pārbauda vai tekošajam lietotājam ir tiesības piekļūt noteiktajam ceļam
     * @param String $s
     */
    static function checkPath($path, $setOrder = false) {
        global $_core;

        if (self::$_orderCache === false) {
            self::$_orderCache = new CList('COrder');
            self::$_orderCache->indexAttributes = array('Code');
            self::$_orderCache->loadByField('', '');
        }

        $isAdmin = ($_core->user->status == 99);

        $pathItems = explode('/', trim($path, '/'));

        if (@$pathItems[count($pathItems) - 1] == 'Thumbs.db') return false;

        if (@$pathItems[0] == C('rowimage.directory')) return false;
        if (@$pathItems[0] == C('trash.directory')) return false;

        $currentOrder = '';

        if (self::$_rightsCache === false) {
            $rights = $_SESSION['Rights']['Folders'];
            self::$_rightsCache = array_flip($rights);
        }

        foreach ($pathItems as $idx => $pathItem) {
            if (substr($pathItem, 0, 1) == '.') return false;    // nerādam "dot-failus" kas linkusī būtu hiddenotie anyway. ideāla vieta kā kaut ko apslēpt ;)
            if (substr($pathItem, 0, 1) == '-') return false;    // nerādam failus, kas saakas ar "-"
            if ($idx == 0) { // pirmais itamzs ceļā ir projekta kods. paskatamies uz to, a ja nu lietotājam vispār nav tiesību redzēt projektu
                $project = trim($pathItem, ' /');

                if (isset(self::$_orderCache->attributeIndex['Code'][$project])) {
                    // projekts kā tāds eksistē, jāpārbauda vai nav noliegts

                    $orderId = self::$_orderCache->attributeIndex['Code'][$project];
                    $currentOrder = $project;

                    if (!isset(self::$_rightsCache[$orderId]) && (!$isAdmin)) {
                        return false;
                    }
                    if ($setOrder) {
                        register_variable('currentProject', self::$_orderCache->objects[$orderId]);
                    }
                } else {
                    // projekts neeksistē - lietotājiem nepieejams
                    if ($project != '') { // pārbaude, vai nemēģinam apslēpt rootu, lietotāji neko neredzēs vispār, jo / projekta protams ka nav
                        if (!$isAdmin) return false;
                    }
                }
            } elseif ($idx == 1) {
                // pārbaudam, vai otrais itamzs nav [projekts kods]x folderis, to arī nerādam lietotājiem
                if ($currentOrder != '') { // iepriekš ceļš ir norādījis uz konkrētu pasūtījumu
                    if (trim($pathItem, ' /') == ($currentOrder . 'x')) {
                        if (!$isAdmin) return false;
                    }
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

        if (self::checkCurrentUser() && self::checkPath($subPath, false)) {
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
                if (self::checkUpload()) {
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
}
