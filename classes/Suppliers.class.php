<?php

class Suppliers extends DBObject {
    private $ID;
    private $IDUser;
    private $Name;
    private $Description;
    private $Color;
    private $AddDate;
    private $Status;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function checkDuplicates() {
        $query = 'SELECT * FROM `Suppliers`
                       WHERE UPPER(`Name`)="' . addslashes(strtoupper($this->getName())) . '"
                       ' . ($this->getID() > 0 ? ' AND ID!=' . $this->getID() : '');

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Suppliers (' . __LINE__ . ')');
        }

        if ($result->num_rows == 0) return 1;
        else return Language::$Suppliers['DuplicateName'];
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Save':
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Supp = $this->assignObject();
                    $Supp['IDSupplier'] = $Supp['ID'];
                    $Supp['IDData'] = $_POST['IDData'];
                    return self::ArrayToJson(array(1, Template::Process('Supplier', $Supp)));
                } else return $ID;
                break;

            case 'Delete':
                if (!$_SESSION['isAdmin']) return '';
                $Supp = $this->getByID($_POST['ID']);
                if (!$Supp || !$Supp->getID()) return Language::$Suppliers['NotFound'];
                return $Supp->Delete();
                break;
            default:
                return;
                break;
        }
    }

    static function getList() {
        $query = 'SELECT * FROM `Suppliers`
                 ORDER BY `ID` DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
        }
        $Supp = array();

        while ($row = $result->fetch_assoc()) {
            $Supp[] = $row;
        }

        if (!empty($Supp)) {
            $Supp['__template'] = '/Suppliers/Supplier';
            return $Supp;
        } else return '';
    }
    /**
     * DB functions
     */

    function Save() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $Check = $this->checkDuplicates();
            if ($Check != 1) return $Check;

            if ($this->getID() == 0) $this->Add();
            else $this->Update();

            return $this->getID();
        } else {
            $Err[0] = 0;
            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Suppliers`
                     SET `IDUser`="' . $_SESSION['User']->getID() . '",
                         `Name`="' . addslashes($this->getName()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '",
                         `Color`="' . addslashes($this->getColor()) . '",
                         `AddDate`=NOW(),
                         `Status`=1';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Suppliers (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);

        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Suppliers`
                     SET `Name`="' . addslashes($this->getName()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '",
                         `Color`="' . addslashes($this->getColor()) . '"
                   WHERE `ID`=' . (int)$this->getID();

        if (!self::$DB->query($query)) {
            throw new Error('Update error on Suppliers (' . __LINE__ . ')');
        }
    }

    function Delete() {
        $query = 'DELETE FROM `Suppliers` WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Suppliers (' . __LINE__ . ')');
        }
        Info::Delete($this->getID());

        return 1;
    }

    static function getById($ID) {
        $query = "SELECT * FROM `Suppliers` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Suppliers (' . __LINE__ . ')');
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

    function setName($v) {
        $v = trim(mb_substr($v, 0, 30));
        if ($v == '' || $v == Language::$Data['Name'])
            throw new Error(Language::$Suppliers['SetName']);
        else $this->Name = $v;
    }

    function setColor($v) {
        if (strpos($v, 'rgb') > -1) {
            $v = str_replace(array('rgb(', ')'), array('', ''), $v);
            $v = explode(', ', $v);
            for ($i = 0; $i < 3; $i++)
                $v[$i] = $v[$i] < 17 ? '0' . dechex($v[$i]) : dechex($v[$i]);

            $v = '#' . implode('', $v);
        }

        $this->Color = $v;
    }
}
