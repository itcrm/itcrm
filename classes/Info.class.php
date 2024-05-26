<?php

class Info extends DBObject {
    private $IDData;
    private $IDSupplier;
    private $IDUser;
    private $Info;
    private $Color;
    private $AddDate;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Save':
                return $this->Save();
            case 'Get':
                return $this->getList($_POST['IDData']);
            default:
                return;
        }
    }

    function getList($Data) {
        $Data = (int)$Data;
        $query = 'SELECT I.*, I.Color as InfoColor, S.Name, S.Color, S.ID as IDSupplier,S.Description FROM `Suppliers` S
                    LEFT JOIN `Info` I ON (I.IDSupplier=S.ID AND I.IDData=' . $Data . ')
                   ORDER BY S.`Name` ASC';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Info (' . __LINE__ . ')');
        }
        $Info = array(array(), array());

        while ($row = $result->fetch_assoc()) {
            $row['IDData'] = $Data;
            if ($row['Info'] != '') $Info[0][] = $row;
            else $Info[1][] = $row;
        }
        $Info = array_merge($Info[0], $Info[1]);

        if (!empty($Info)) {
            $Info['__template'] = '/Suppliers/Supplier';
            return self::ArrayToJson(array(1, Template::Process($Info)));
        } else return self::ArrayToJson(array(1, ''));
    }

    /**
     * DB functions
     */
    function Save() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $this->Add();
            return 1;
        } else {
            $Err[0] = 0;
            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'REPLACE INTO `Info`
                      SET `IDData`=' . (int)$this->getIDData() . ',
                          `IDSupplier`=' . (int)$this->getIDSupplier() . ',
                          `IDUser`="' . $_SESSION['User']->getID() . '",
                          `Info`="' . addslashes($this->getInfo()) . '",
                          `Color`="' . addslashes($this->getColor()) . '",
                          `AddDate`=NOW()';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    static function Delete($IDS) {
        $query = 'DELETE FROM `Info` WHERE `IDSupplier`=' . $IDS;

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Info (' . __LINE__ . ')');
        }

        return 1;
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
}
