<?php

class Users extends DBObject {
    private $ID;
    private $Login;
    private $Password;
    private $Color;
    private $Name;
    private $Phone;
    private $AddDate;
    private $Status;
    private $add_order;
    private $add_r_bilde;
    private $add_files;
    private $OneDay;
    private $noliktava;
    private $MultiChange;
    private $DelFile;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function checkDuplicates() {
        $query = 'SELECT * FROM `Users`
                       WHERE UPPER(`Login`)="' . addslashes(strtoupper($this->getLogin())) . '"
                       ' . ($this->getID() > 0 ? ' AND ID!=' . $this->getID() : '');

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
        }

        if ($result->num_rows == 0) return 1;
        else return Language::$Users['DuplicateLogin'];
    }

    function checkUser() {
        if (isset($_POST['Login']) && isset($_POST['Password'])) {
            try {
                $this->setLogin($_POST['Login']);
                $this->setPassword($_POST['Password']);
            } catch (Error $ex) {
                return Language::$Users['WrongLoginPassword'];
            }
            $query = "SELECT *
                       FROM Users
                      WHERE Login='" . addslashes($this->getLogin()) . "'
                        AND Password='" . md5($this->getPassword()) . "'";

            if (!$result = self::$DB->query($query)) {
                throw new Error('Read error on Users (' . __LINE__ . ')');
            }
            if ($result->num_rows == 0) {
                return Language::$Users['WrongLoginPassword'];
            }

            $this->fetchObject($result);

            if ($this->getStatus() == -1)
                return Language::$Users['UserDisabled'];
            else {
                $_SESSION['User'] = $this;
                $_SESSION['isAdmin'] = $this->getStatus() == 99;
                $_SESSION['Rights'] = Rights::getRightsArr($_SESSION['User']->getID());
                if ($_SESSION['Rights']['Persons'] == '') $_SESSION['Rights']['Persons'] = array(-1);
                if ($_SESSION['Rights']['Orders'] == '') $_SESSION['Rights']['Orders'] = array(-1);
                if ($_SESSION['Rights']['Types'] == '') $_SESSION['Rights']['Types'] = array(-1);
            }

            return 1;
        }
        return;
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Save':
                if (!$_SESSION['isAdmin']) return '';
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $User = $this->fetchRow($this->assignObject($this->getByID($ID)));
                    return self::ArrayToJson(array(1, Template::Process('Row', $User)));
                } else return $ID;
                break;
            case 'Logon':
                return $this->checkUser();
                break;
            case 'Delete':
            case 'Restore':
                if (!$_SESSION['isAdmin']) return '';
                $User = $this->getByID($_POST['ID']);
                if (!$User || !$User->getID() || $User->getStatus() == '99') return Language::$Users['UserNotFound'];
                return $User->Delete();
                break;
            default:
                if (!$_SESSION['User']) return;
                $Vars['Content'] = $this->getUsersList();
                break;
        }
        $Vars['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
        return Template::Process('index', $Vars);
    }

    function getUsersList() {
        $query = 'SELECT * FROM `Users`
                 ' . (!$_SESSION['isAdmin'] ? ' WHERE `Status`<99' : '') . '
                 ORDER BY `Status` DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
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
        $StatusTexts = array(
            -1 => Language::$Users['Deleted'],
            1 => Language::$Users['Read'],
            4 => Language::$Users['SuperUser'],
            5 => Language::$Users['ReadWrite'],
            99 => Language::$Users['Admin']
        );
        $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
        $row['RowClass'] = $row['Status'] == -1 ? 'deleted' : '';
        $row['StatusText'] = isset($StatusTexts[$row['Status']]) ? $StatusTexts[$row['Status']] : '';
        $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
        $row['add_order'] = $row['add_order'] == 0 ? 'Nav' : 'Ir';
        $row['add_r_bilde'] = $row['add_r_bilde'] == 0 ? 'Nav' : 'Ir';
        $row['add_files'] = $row['add_files'] == 0 ? 'Nav' : 'Ir';
        $row['OneDay'] = $row['OneDay'] == 0 ? 'Nav' : 'Ir';
        $row['noliktava'] = $row['noliktava'] == 0 ? 'Nav' : 'Ir';
        $row['MultiChange'] = $row['MultiChange'] == 0 ? 'Nav' : 'Ir';
        $row['DelFile'] = $row['DelFile'] == 0 ? 'Nav' : 'Ir';

        return $row;
    }

    static function getOptionsList() {
        $query = 'SELECT * FROM `Users` WHERE `Status`>-2
                   ORDER BY `Login`';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
        }
        $users = '';
        while ($row = $result->fetch_assoc()) {
            $users .= '<option value="' . $row['ID'] . '">' . $row['Login'] . '</option>' . "\n";
        }

        return $users;
    }

    static function getAsArray($Users = -1) {
        if (is_array($Users) && empty($Users)) return '';

        $query = 'SELECT * FROM `Users` WHERE `Status`>-2 '
            . ($Users == 1
                ? 'AND Status<99'
                : (is_array($Users)
                    ? ' AND ID IN (' . implode(',', $Users) . ')'
                    : '')
            ) . '
                   ORDER BY `Login`';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
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
        $Err = Error::getErrors(get_class($this));

        if ($this->getID() > 0 && $this->getPassword() == '')
            unset($Err['Password']);

        if (empty($Err)) {
            $Check = $this->checkDuplicates();
            if ($Check != 1) return $Check;

            if ($this->getID() == 0) $this->Add();
            else $this->Update();

            if ($_POST['CopyRights'] == 1) {
                if ($_POST['ID'] == 0) {
                    $_POST['ID'] = $this->getID();
                }

                $this->CopyRights($this->getID(), $_POST['FromID']);
            }

            return $this->getID();
        } else {
            $Err[0] = 0;
            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Users`
                     SET `Login`="' . addslashes($this->getLogin()) . '",
                         `Password`="' . md5($this->getPassword()) . '",
                         `Color`="' . addslashes($this->getColor()) . '",
                         `Name`="' . addslashes($this->getName()) . '",
                         `Phone`="' . addslashes($this->getPhone()) . '",
                         `AddDate`=NOW(),
                         `Status`=' . (int)$this->setStauts() . ',
                         `add_order`=' . (int)$this->getadd_order() . ',
                         `add_r_bilde`=' . (int)$this->getadd_r_bilde() . ',
                         `add_files`=' . (int)$this->getadd_files() . ',
                         `OneDay`=' . (int)$this->getOneDay() . ',
                         `noliktava`=' . (int)$this->getnoliktava() . ',
                         `DelFile`=' . (int)$this->getDelFile() . ',
                         `MultiChange`=' . (int)$this->getMultiChange();

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Users (' . __LINE__ . ') ');
        }

        $this->setID(self::$DB->insert_id);
        if ($_POST['RightsAdd'] == 1)
            Rights::addRightsToUser($this->getID());

        if ($_POST['RightsUserAdd'] == 1)
            Rights::addUsersToUser($this->getID());

        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Users`
                     SET `Login`="' . addslashes($this->getLogin()) . '",
                         ' . ($this->getPassword() != ''
            ? '`Password`="' . md5($this->getPassword()) . '",'
            : '') . '
                         `Color`="' . addslashes($this->getColor()) . '",
                         `Name`="' . addslashes($this->getName()) . '",
                         `Phone`="' . addslashes($this->getPhone()) . '",
                         `Status`=' . (int)$this->getStatus() . ',
                         `add_order`=' . (int)$this->getadd_order() . ',
                         `add_r_bilde`=' . (int)$this->getadd_r_bilde() . ',
                         `add_files`=' . (int)$this->getadd_files() . ',
                         `OneDay`=' . (int)$this->getOneDay() . ',
                         `noliktava`=' . (int)$this->getnoliktava() . ',
                         `DelFile`=' . (int)$this->getDelFile() . ',
                         `MultiChange`=' . (int)$this->getMultiChange() . '
                   WHERE `ID`=' . (int)$this->getID();

        if ($_POST['RightsDel'] == 1)
            Rights::DeleteByUser($this->getID());
        elseif ($_POST['RightsAdd'] == 1) Rights::addRightsToUser($this->getID());

        if ($_POST['RightsUserAdd'] == 1)
            Rights::addUsersToUser($this->getID());

        if ($_POST['RightsAddAllUser'] == 1)
            Rights::addUserToUsers($this->getID());

        if (!self::$DB->query($query)) {
            throw new Error('Update error on Users (' . __LINE__ . ')');
        }
    }

    function Delete() {
        $Status = self::$url[2] == 'Restore' ? 1 : -1;

        if ($this->getStatus() == -1 && $Status == -1) {
            if ($_POST['pass'] != Config::DEL_PASS) return Language::$Main['WrongDelPass'];
            $query = 'DELETE FROM `Users` WHERE `ID`=' . $this->getID();
            Rights::DeleteById($this->getID(), 'Person');
            Rights::DeleteFilterRights($this->getID());
        } else $query = 'Update `Users`
                            SET `Status`=' . $Status . ' WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Users (' . __LINE__ . ')');
        }

        return 1;
    }

    static function getById($ID) {
        $query = "SELECT * FROM `Users` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Users (' . __LINE__ . ')');
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

    function setLogin($value) {
        $value = trim($value);
        if ($value == '') throw new Error(Language::$Users['SetLogin']);
        else $this->Login = $value;
    }

    function setPassword($value) {
        $value = trim($value);
        if ($value == '') throw new Error(Language::$Users['SetPassword']);
        else $this->Password = $value;
    }

    function GetUserByID($ID) {
        $query = "SELECT Name FROM `Users` WHERE `ID`='" . $ID . "'";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row['Name'];
        }

        return $results;
    }

    function CopyRights($ID, $FromID) {
        if ($ID != $FromID) {
            $query = "
    INSERT INTO `Rights` (`IDUser`, `Type`, `Value`)
SELECT '" . $ID . "', `Type`, `Value`
FROM   `Rights`
WHERE IDUser = '" . $FromID . "'";
            if (!self::$DB->query($query)) {
                throw new Error('Clone Rights error on Users (' . __LINE__ . ')');
            }

            $user = $this->GetUserByID($FromID);

            $query = "UPDATE `Users` SET Phone=CONCAT(Phone, ' +" . $user . "') where `ID` = " . $ID;

            if (!self::$DB->query($query)) {
                throw new Error('Clone LOG error on Users (' . __LINE__ . ')');
            }
        }
    }
}
