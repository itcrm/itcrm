<?php

class Info extends DBObject {
    protected $IDData;
    protected $IDSupplier;
    protected $IDUser;
    protected $Info;
    protected $Color;
    protected $AddDate;

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
            throw new AppError('Read error on Info (' . __LINE__ . ')');
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
            return json_encode(array(1, Template::Process($Info)));
        } else return json_encode(array(1, ''));
    }

    /**
     * DB functions
     */
    function Save() {
        $this->fetchObject($_POST);
        $Err = AppError::getErrors(get_class($this));

        if (empty($Err)) {
            $this->Add();
            return 1;
        } else {
            $Err[0] = 0;
            return json_encode($Err);
        }
    }

    function Add() {
        $query = 'INSERT OR REPLACE INTO `Info` (`IDData`, `IDSupplier`, `IDUser`, `Info`, `Color`, `AddDate`)
                  VALUES (?, ?, ?, ?, ?, datetime(\'now\'))';

        if (!self::$DB->prepare($query, [
            (int)$this->getIDData(),
            (int)$this->getIDSupplier(),
            $_SESSION['User']->getID(),
            $this->getInfo(),
            $this->getColor()
        ])) {
            throw new AppError('Write error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    static function Delete($IDS) {
        $query = 'DELETE FROM `Info` WHERE `IDSupplier`=' . $IDS;

        if (!self::$DB->query($query)) {
            throw new AppError('Delete error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

}
