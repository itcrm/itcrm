<?php

class Types extends DBObject {
    use SoftDelete;
    protected static $tableName = 'Types';
    protected $ID;
    protected $Code;
    protected $Description;
    protected $AddDate;
    protected $Status;

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Save':
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Type = $this->assignObject($this->getByID($ID));
                    if ($Type['Status'] > -1) $Type['Deleted'] = 'hide';
                    else $Type['Status'] = 'deleted';
                    return json_encode(array(1, Template::Process('Row', $Type)));
                } else return $ID;
                break;
            case 'Delete':
            case 'Restore':
                $Type = $this->getByID($_POST['ID']);
                if (!$Type || !$Type->getID()) return Language::$Types['TypeNotFound'];
                return $Type->Delete();
                break;
            default:
                if (!$_SESSION['User']) return;
                $Vars['Content'] = $this->getTypesList();
                break;
        }
        return Template::Process('index', $Vars);
    }

    function getTypesList() {
        $query = 'SELECT * FROM `Types`
                 ORDER BY `ID` DESC';

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Types (' . __LINE__ . ')');
        }

        $Types = array();
        while ($row = $result->fetch_assoc()) {
            $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
            $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
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
            throw new AppError('Read error on Users (' . __LINE__ . ')');
        }
        $types = '';
        while ($row = $result->fetch_assoc()) {
            $types .= '<option value="' . $row['ID'] . '">' . $row['Code'] . '</option>' . "\n";
        }
        return $types;
    }

    static function getAsArray() {
        $query = 'SELECT * FROM `Types` WHERE `Status`=1
                   ORDER BY `Code`';

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Types (' . __LINE__ . ')');
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
        $Err = AppError::getErrors(get_class($this));

        if (empty($Err)) {
            if ($this->getID() < 1) $this->Add();
            else $this->Update();

            return $this->getID();
        } else {
            $Err[0] = 0;
            return json_encode($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Types`
                     SET `Code`="' . addslashes($this->getCode()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '",
                         `AddDate`=NOW(),
                         `Status`=1';

        if (!self::$DB->query($query)) {
            if (self::$DB->errno == 1062) throw new AppError(Language::$Orders['DuplicateEntry']);
            throw new AppError('Write error on Types (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);

        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Types`
                     SET `Code`="' . addslashes($this->getCode()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '"
                   WHERE `ID`=' . (int)$this->getID();

        if (!self::$DB->query($query)) {
            throw new AppError('Update error on Types (' . __LINE__ . ')');
        }
    }

    static function getById($ID) {
        $query = "SELECT * FROM `Types` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Types (' . __LINE__ . ')');
        }
        return (new self)->fetchObject($result, new self);
    }

    function setCode($value) {
        $value = trim($value);
        if ($value == '') throw new AppError(Language::$Types['SetCode']);
        else $this->Code = $value;
    }
}
