<?php

class Users extends DBObject {
    use SoftDelete;
    protected static $tableName = 'Users';
    protected $ID;
    protected $Login;
    protected $Password;
    protected $Color;
    protected $Name;
    protected $Phone;
    protected $AddDate;
    protected $Status;

    function checkDuplicates() {
        $params = [strtoupper($this->getLogin())];
        $query = 'SELECT * FROM `Users` WHERE UPPER(`Login`)=?'
            . ($this->getID() > 0 ? ' AND ID!=?' : '');
        if ($this->getID() > 0) $params[] = $this->getID();

        if (!$result = self::$DB->prepare($query, $params)) {
            throw new AppError('Read error on Users (' . __LINE__ . ')');
        }

        if ($result->num_rows == 0) return 1;
        else return Language::$Users['DuplicateLogin'];
    }

    function checkUser() {
        if (isset($_POST['Login']) && isset($_POST['Password'])) {
            try {
                $this->setLogin($_POST['Login']);
                $this->setPassword($_POST['Password']);
            } catch (AppError $ex) {
                return Language::$Users['WrongLoginPassword'];
            }
            $query = 'SELECT * FROM Users WHERE Login=? AND Password=?';

            if (!$result = self::$DB->prepare($query, [
                $this->getLogin(),
                md5($this->getPassword())
            ])) {
                throw new AppError('Read error on Users (' . __LINE__ . ')');
            }
            if ($result->num_rows == 0) {
                return Language::$Users['WrongLoginPassword'];
            }

            $this->fetchObject($result);

            if ($this->getStatus() == -1)
                return Language::$Users['UserDisabled'];
            else {
                $_SESSION['User'] = $this;
            }

            return 1;
        }
        return;
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Save':
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $User = $this->fetchRow($this->assignObject($this->getByID($ID)));
                    return json_encode(array(1, Template::Process('Row', $User)));
                } else return $ID;
            case 'Logon':
                return $this->checkUser();
            case 'Delete':
            case 'Restore':
                $User = $this->getByID($_POST['ID']);
                if (!$User || !$User->getID()) return Language::$Users['UserNotFound'];
                return $User->Delete();
            default:
                if (!$_SESSION['User']) return;
                $Vars['Content'] = $this->getUsersList();
                break;
        }
        return Template::Process('index', $Vars);
    }

    function getUsersList() {
        $query = 'SELECT * FROM `Users`
                 ORDER BY `Status` DESC';

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Users (' . __LINE__ . ')');
        }
        $Users = array();

        while ($row = $result->fetch_assoc()) {
            $Users[] = $this->fetchRow($row);
        }

        if (!empty($Users)) {
            $Users['__template'] = 'Row';
            return $Users;
        } else return '';
    }

    function fetchRow($row) {
        $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
        $row['RowClass'] = $row['Status'] == -1 ? 'deleted' : '';

        return $row;
    }

    static function getAsArray() {
        $query = 'SELECT * FROM `Users` WHERE `Status`>-2
                   ORDER BY `Login`';

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Users (' . __LINE__ . ')');
        }
        $Users = array();
        while ($row = $result->fetch_assoc()) {
            $Users[$row['ID']] = $row['Login'];
        }

        return $Users;
    }

    /**
     * DB functions
     */
    function Save() {
        $this->fetchObject($_POST);
        $Err = AppError::getErrors(get_class($this));

        if ($this->getID() > 0 && $this->getPassword() == '')
            unset($Err['Password']);

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
        $query = 'INSERT INTO `Users` (`Login`, `Password`, `Color`, `Name`, `Phone`, `AddDate`, `Status`)
                  VALUES (?, ?, ?, ?, ?, datetime(\'now\'), 99)';

        if (!self::$DB->prepare($query, [
            $this->getLogin(),
            md5($this->getPassword()),
            $this->getColor(),
            $this->getName(),
            $this->getPhone()
        ])) {
            throw new AppError('Write error on Users (' . __LINE__ . ') ');
        }

        $this->setID(self::$DB->insert_id);

        return $this->getID();
    }

    function Update() {
        $params = [$this->getLogin()];
        $passwordClause = '';
        if ($this->getPassword() != '') {
            $passwordClause = '`Password`=?,';
            $params[] = md5($this->getPassword());
        }
        $params[] = $this->getColor();
        $params[] = $this->getName();
        $params[] = $this->getPhone();
        $params[] = (int)$this->getID();

        $query = 'UPDATE `Users`
                     SET `Login`=?, ' . $passwordClause . ' `Color`=?, `Name`=?, `Phone`=?
                   WHERE `ID`=?';

        if (!self::$DB->prepare($query, $params)) {
            throw new AppError('Update error on Users (' . __LINE__ . ')');
        }
    }

    function setLogin($value) {
        $value = trim($value);
        if ($value == '') throw new AppError(Language::$Users['SetLogin']);
        else $this->Login = $value;
    }

    function setPassword($value) {
        $value = trim($value);
        if ($value == '') throw new AppError(Language::$Users['SetPassword']);
        else $this->Password = $value;
    }

}
