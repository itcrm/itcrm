<?php

class Suppliers extends DBObject {
    protected static $tableName = 'Suppliers';
    protected $ID;
    protected $IDUser;
    protected $Name;
    protected $Description;
    protected $Color;
    protected $AddDate;
    protected $Status;

    function checkDuplicates() {
        $params = [strtoupper($this->getName())];
        $query = 'SELECT * FROM `Suppliers` WHERE UPPER(`Name`)=?'
            . ($this->getID() > 0 ? ' AND ID!=?' : '');
        if ($this->getID() > 0) $params[] = $this->getID();

        if (!$result = self::$DB->prepare($query, $params)) {
            throw new AppError('Read error on Suppliers (' . __LINE__ . ')');
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
                    return json_encode(array(1, Template::Process('Supplier', $Supp)));
                } else return $ID;
            case 'Delete':
                $Supp = $this->getByID($_POST['ID']);
                if (!$Supp || !$Supp->getID()) return Language::$Suppliers['NotFound'];
                return $Supp->Delete();
            default:
                return;
        }
    }

    /**
     * DB functions
     */

    function Save() {
        $this->fetchObject($_POST);
        $Err = AppError::getErrors(get_class($this));

        if (empty($Err)) {
            $Check = $this->checkDuplicates();
            if ($Check != 1) return $Check;

            if ($this->getID() < 1) $this->Add();
            else $this->Update();

            return $this->getID();
        } else {
            $Err[0] = 0;
            return json_encode($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Suppliers` (`IDUser`, `Name`, `Description`, `Color`, `AddDate`, `Status`)
                  VALUES (?, ?, ?, ?, datetime(\'now\'), 1)';

        if (!self::$DB->prepare($query, [
            $_SESSION['User']->getID(),
            $this->getName(),
            $this->getDescription(),
            $this->getColor()
        ])) {
            throw new AppError('Write error on Suppliers (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);

        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Suppliers` SET `Name`=?, `Description`=?, `Color`=? WHERE `ID`=?';

        if (!self::$DB->prepare($query, [
            $this->getName(),
            $this->getDescription(),
            $this->getColor(),
            (int)$this->getID()
        ])) {
            throw new AppError('Update error on Suppliers (' . __LINE__ . ')');
        }
    }

    function Delete() {
        $query = 'DELETE FROM `Suppliers` WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new AppError('Delete error on Suppliers (' . __LINE__ . ')');
        }
        Info::Delete($this->getID());

        return 1;
    }

    function setName($v) {
        $v = trim(mb_substr($v, 0, 30));
        if ($v == '' || $v == Language::$Data['Name'])
            throw new AppError(Language::$Suppliers['SetName']);
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
