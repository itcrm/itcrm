<?php

class Types extends DBObject {
    private $ID;
    private $Code;
    private $Description;
    private $AddDate;
    private $Status;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Save':
                if (!$_SESSION['isAdmin']) return '';
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Type = $this->assignObject($this->getByID($ID));
                    if ($Type['Status'] > -1) $Type['Deleted'] = 'hide';
                    else $Type['Status'] = 'deleted';
                    $Rights = Rights::getRigthsByType('Type');
                    if (!$Rights[$Type['ID']]) $Type['Restricted'] = '<b>!</b>';
                    return self::ArrayToJson(array(1, Template::Process('Row', $Type)));
                } else return $ID;
                break;
            case 'Delete':
            case 'Restore':
                if (!$_SESSION['isAdmin']) return '';
                $Type = $this->getByID($_POST['ID']);
                if (!$Type || !$Type->getID()) return Language::$Types['TypeNotFound'];
                return $Type->Delete();
                break;
            default:
                if (!$_SESSION['User']) return;
                $Vars['Content'] = $this->getTypesList();
                break;
        }
        $Vars['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
        return Template::Process('index', $Vars);
    }

    function getTypesList() {
        $query = 'SELECT * FROM `Types`
                 ' . (!$_SESSION['isAdmin'] ? ' WHERE `Status`=1' : '') . '
                 ORDER BY `ID` DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Types (' . __LINE__ . ')');
        }

        $Users = count(Users::getAsArray());
        $Rights = Rights::getRigthsByType('Type');
        $Types = array();
        while ($row = $result->fetch_assoc()) {
            if (isset($Rights[$row['ID']]) && $Rights[$row['ID']] < $Users) $row['Restricted'] = '<b>!</b>';
            $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
            $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
            $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
            $Types[] = $row;
        }

        if (!empty($Types)) {
            $Types['__template'] = 'Row';
            return $Types;
        } else return '';
    }

    static function getOptionsList() {
        $query = 'SELECT * FROM `Types`
                   WHERE `Status`=1
                   ORDER BY `Code`';
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
        }
        $types = '';
        while ($row = $result->fetch_assoc()) {
            $types .= '<option value="' . $row['ID'] . '">' . $row['Code'] . '</option>' . "\n";
        }
        return $types;
    }

    static function getAsArray($Types = -1) {
        if (is_array($Types) && empty($Types)) return '';

        $query = 'SELECT * FROM `Types` WHERE `Status`=1
                 ' . (is_array($Types)  ?  ' AND ID IN (' . implode(',', $Types) . ')' : '') . '
                   ORDER BY `Code`';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Types (' . __LINE__ . ')');
        }
        $Types = array();
        while ($row = $result->fetch_assoc()) {
            $Types[$row['ID']] = $row['Code'];
        }

        return $Types;
    }

    /**
     * DB functions
     */
    function Save() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            if ($this->getID() == 0) $this->Add();
            else $this->Update();

            return $this->getID();
        } else {
            $Err[0] = 0;
            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Types`
                     SET `Code`="' . addslashes($this->getCode()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '",
                         `AddDate`=NOW(),
                         `Status`=1';

        if (!self::$DB->query($query)) {
            if (self::$DB->errno == 1062) throw new Error(Language::$Orders['DuplicateEntry']);
            throw new Error('Write error on Types (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);
        if ($_POST['RightsAdd'] == 1)
            Rights::addRights($this->getID(), 'Type');

        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Types`
                     SET `Code`="' . addslashes($this->getCode()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '"
                   WHERE `ID`=' . (int)$this->getID();

        if ($_POST['RightsDel'] == 1)
            Rights::DeleteById($this->getID(), 'Type');
        elseif ($_POST['RightsAdd'] == 1) Rights::addRights($this->getID(), 'Type');

        if (!self::$DB->query($query)) {
            throw new Error('Update error on Types (' . __LINE__ . ')');
        }
    }

    function Delete() {
        $Status = self::$url[2] == 'Restore' ? 1 : -1;

        if ($this->getStatus() == -1 && $Status == -1) {
            if ($_POST['pass'] != Config::DEL_PASS) return Language::$Main['WrongDelPass'];
            $query = 'DELETE FROM `Types` WHERE `ID`=' . $this->getID();
            Rights::DeleteById($this->getID(), 'Type');
        } else $query = 'Update `Types`
                            SET `Status`=' . $Status . ' WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Types (' . __LINE__ . ')');
        }

        return 1;
    }

    static function getById($ID) {
        $query = "SELECT * FROM `Types` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Types (' . __LINE__ . ')');
        }
        return self::fetchObject($result, new self);
    }

    /**
     * Setters and getters here
     */
    function __call($method, $params) {
        $type = substr($method, 0, 3);
        $key = substr($method, 3);

        if ($type == 'get') return $this->$key;
        elseif ($type == 'set') $this->$key = $params[0];
        else throw new Error(get_class($this) . '::' . $method . ' does not exists');
    }

    function setCode($value) {
        $value = trim($value);
        if ($value == '') throw new Error(Language::$Types['SetCode']);
        else $this->Code = $value;
    }
}
