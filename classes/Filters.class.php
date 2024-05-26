<?php

class Filters extends DBObject {
    private $ID;
    private $Name;
    private $Date;
    private $DateType;
    private $IDPerson;
    private $IDOperator;
    private $IDOrder;
    private $TextOrder;
    private $IDType;
    private $TextType;
    private $Sum;
    private $Hours;
    private $PlaceTaken;
    private $PlaceDone;
    private $Note;
    private $BookNote;
    private $AddDate;
    private $Status;
    private $Search;
    private $Supertext;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Get':
                $query = 'SELECT F.*,P.Login as PersonFilterSelect, Op.Login as OperatorFilterSelect,
                                 O.Code as OrderFilterSelect, T.Code as TypeFilterSelect
                             FROM Filters F
                        LEFT JOIN Users P ON(F.IDPerson=P.ID)
                        LEFT JOIN Users Op ON(F.IDPerson=Op.ID)
                        LEFT JOIN Orders O ON(F.IDOrder=O.ID)
                        LEFT JOIN Types T ON(F.IDType=T.ID)
                      WHERE F.ID=' . (int)$_POST['ID'];

                if (!$result = self::$DB->query($query))
                    throw new Error('Error on ' . get_class() . ' (' . __LINE__ . ')');

                $Filter = $result->fetch_assoc();
                $Filter['PersonFilterSelect'] =  Filters::MultiPersons($Filter['IDPerson'], 'Users', 'Login');
                $Filter['OperatorFilterSelect'] =  Filters::MultiPersons($Filter['IDOperator'], 'Users', 'Login');
                $Filter['OrderFilterSelect'] =  Filters::MultiPersons($Filter['IDOrder'], 'Orders', 'Code');
                $Filter['TypeFilterSelect'] =  Filters::MultiPersons($Filter['IDType'], 'Types', 'Code');

                unset($Filter['ID']);
                unset($Filter['Name']);
                unset($Filter['Status']);
                unset($Filter['AddDate']);

                $Filter['Date'] = $this->filterDate($Filter['Date']);
                if ($Filter['Date']['To'] == '') $Filter['Date']['To'] = date('Y-m-d 00:00:00');
                if ($Filter['Date']['From'] == -1) $Filter['Date']['From'] = '2000-01-01 00:00:00';

                foreach ($Filter as $k => $v)
                    if ($v == '0' || $v == '') unset($Filter[$k]);

                $Filter[0] = 1;
                return self::ArrayToJson($Filter);
            case 'editRow':
                return $this->editFilter($_POST['ID']);
            case 'Save':
                if (!$_SESSION['User'] || ($_POST['ID'] && !$_SESSION['isAdmin'])) return '';
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Data = $this->getRow($ID);
                    return self::ArrayToJson(array(1, Template::Process('Row', $Data)));
                }
                return $ID;
            case 'Delete':
            case 'Restore':
                if (!$_SESSION['isAdmin']) return '';
                $Filter = $this->getByID($_POST['ID']);
                if (!$Filter || !$Filter->getID()) return Language::$Filters['DataNotFound'];
                return $Filter->Delete();
            default:
                break;
        }
        if (!$_SESSION['isAdmin']) return;
        $Vars['Content'] = $this->getFiltersList();
        $Users = Users::getAsArray(1);
        $Vars['Users1'] = array();
        $Vars['Users2'] = array();
        $half = ceil(count($Users) / 2);
        $i = 1;
        foreach ($Users as $id => $login) {
            if ($half <= $i) $Vars['Users1'][] = array('ID' => $id, 'Login' => $login);
            else  $Vars['Users2'][] = array('ID' => $id, 'Login' => $login);
            $i++;
        }
        if (!empty($Vars['Users1'])) $Vars['Users1']['__template'] = 'User';
        else unset($Vars['Users1']);
        if (!empty($Vars['Users2'])) $Vars['Users2']['__template'] = 'User';
        else unset($Vars['Users2']);
        $Vars['NoAdmin'] = $_SESSION['User']->getStatus() < 5 ? 'hide' : '';

        $Users = Users::getAsArray();
        foreach ($Users as $k => $v) $Users[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Vars['UsersList'] = '{' . implode('},{', $Users) . '}';

        $Orders = Orders::getAsArray();
        foreach ($Orders as $k => $v) $Orders[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Vars['OrdersList'] = '{' . implode('},{', $Orders) . '}';

        $Types = Types::getAsArray();
        foreach ($Types as $k => $v) $Types[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Vars['TypesList'] = '{' . implode('},{', $Types) . '}';

        if (!$_SESSION['isAdmin']) {
            $Users = Users::getAsArray($_SESSION['Rights']['Persons']);
            if (!empty($Users)) {
                foreach ($Users as $k => $v) $Users[$k] = 'name: "' . $v . '", val:"' . $k . '"';
                $Vars['AllowedUsersList'] = '{' . implode('},{', $Users) . '}';
            } else $Vars['AllowedUsersList'] = '';

            $Orders = Orders::getAsArray($_SESSION['Rights']['Orders']);
            if (!empty($Orders)) {
                foreach ($Orders as $k => $v) $Orders[$k] = 'name: "' . $v . '", val:"' . $k . '"';
                $Vars['AllowedOrdersList'] = '{' . implode('},{', $Orders) . '}';
            } else $Vars['AllowedOrdersList'] = '';

            $Types = Types::getAsArray($_SESSION['Rights']['Types']);
            if (!empty($Types)) {
                foreach ($Types as $k => $v) $Types[$k] = 'name: "' . $v . '", val:"' . $k . '"';
                $Vars['AllowedTypesList'] = '{' . implode('},{', $Types) . '}';
            } else $Vars['AllowedTypesList'] = '';
        } else {
            $Vars['AllowedUsersList'] = $Vars['UsersList'];
            $Vars['AllowedOrdersList'] = $Vars['OrdersList'];
            $Vars['AllowedTypesList'] = $Vars['TypesList'];
        }

        if (is_array($_SESSION['Filter']))
            foreach ($_SESSION['Filter'] as $k => $v) {
                $Vars[$k] = $v;
            }

        return Template::Process('index', $Vars);
    }

    function getFiltersList() {
        $query = 'SELECT * FROM `Filters` ORDER BY ID DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Filters (' . __LINE__ . ')');
        }

        $Dates = array(
            99 => Language::$Filters['AllPeriod'],
            5 => Language::$Filters['Today'],
            6 => Language::$Filters['Yesterday'],
            7 => Language::$Filters['Week'],
            1 => Language::$Filters['LastMonth'],
            8 => Language::$Filters['Last'],
            9 => Language::$Filters['Tomorrow'],
            10 => Language::$Filters['FutureWeek'],
            11 => Language::$Filters['FutureMonth'],
            12 => Language::$Filters['Future']
        );
        $DateTypes = array(
            0 => '',
            1 => Language::$Filters['AddDate'],
            2 => Language::$Filters['Date']
        );
        $Data = array();
        while ($row = $result->fetch_assoc()) {
            $row['Person'] =  $this->MultiPersons($row['IDPerson'], 'Users', 'Login');
            $row['Operator'] =  $this->MultiPersons($row['IDOperator'], 'Users', 'Login');
            $row['Order'] =  $this->MultiPersons($row['IDOrder'], 'Orders', 'Code');
            $row['Type'] =  $this->MultiPersons($row['IDType'], 'Types', 'Code');

            $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
            $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
            $row['DateShow'] = $Dates[$row['Date']];
            $row['DateTypeShow'] = $DateTypes[$row['DateType']];
            $row['Sum'] = $row['Sum'] == 0 ? '' : $row['Sum'];
            $row['Hours'] = $row['Hours'] == 0 ? '' : $row['Hours'];

            $Data[] = $row;
        }

        if (!empty($Data)) {
            $Data['__template'] = 'Row';
            return $Data;
        }
        return '';
    }

    static function getFilter($ID) {
        if (!$_SESSION['isAdmin'])
            $query = 'SELECT F.* FROM `Filters` F, `RightsFilter` RF
                   WHERE F.ID=RF.IDFilter AND RF.`IDUser`=' . $_SESSION['User']->getID() . '
                         AND Status=1 AND F.ID=' . (int)$ID;
        else $query = 'SELECT * FROM `Filters`
                        WHERE ID=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Filters (' . __LINE__ . ')');
        }

        $Filter = $result->fetch_assoc();

        $Filter['Date'] = self::filterDate($Filter['Date']);

        foreach ($Filter as $k => $v)
            if ($v == '') unset($Filter[$k]);

        return $Filter;
    }

    function filterDate($d) {
        switch ($d) {
            case 1:
                $startDate = date('Y-m-d', time() - 60 * 60 * 24 * 30);
                break;
            case 2:
                $startDate = date('Y-m-d', strtotime('3 month ago'));
                break;
            case 3:
                $startDate = date('Y-m-d', strtotime('6 month ago'));
                break;
            case 4:
                $startDate = date('Y-m-d', strtotime('1 year ago'));
                break;
            case 5:
                $startDate = date('Y-m-d 00:00:00');
                break;
            case 6:
                $startDate = date('Y-m-d 00:00:00', strtotime('yesterday'));
                $endDate = date('Y-m-d H:i:s');
                break;
            case 7:
                $startDate = date('Y-m-d 00:00:00', time() - 24 * 60 * 60 * 7);
                break;
            case 8:
                $endDate = date('Y-m-d H:i:s', time());
                break;
            case 9:
                $startDate = date('Y-m-d 00:00:00', time() + 60 * 60 * 24);
                $endDate = date('Y-m-d 23:59:59', time() + 60 * 60 * 24);
                break;
            case 10:
                $startDate = date('Y-m-d 00:00:00', time() + 60 * 60 * 24);
                $endDate = date('Y-m-d 23:59:59', time() + 60 * 60 * 24 * 7);
                break;
            case 11:
                $startDate = date('Y-m-d 00:00:00', time() + 60 * 60 * 24);
                $endDate = date('Y-m-d 23:59:59', time() + 60 * 60 * 24 * 30);
                break;
            case 12:
                $startDate = date('Y-m-d 23:59:59');
                $endDate = '2029-12-31 23:59:59';
                break;
            case 99:
                $startDate =  '2000-01-01 00:00:00';
                $endDate = date('Y-m-d 23:59:59') + 1;
                break;
            default:
                $startDate = date('Y-m-d', mktime(0, 0, 0, date('n'), 1, date('Y')));
                break;
        }

        return array('From' => $startDate, 'To' => $endDate);
    }

    static function getOptionsList() {
        if (!$_SESSION['isAdmin'])
            $query = 'SELECT F.ID,F.Name FROM `Filters` F, `RightsFilter` RF
                   WHERE F.ID=RF.IDFilter AND RF.`IDUser`=' . $_SESSION['User']->getID() . ' AND Status=1
                ORDER BY `Name`';
        else $query = 'SELECT ID,Name FROM `Filters`
                   WHERE Status=1
                ORDER BY `Name`';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Filters (' . __LINE__ . ')');
        }
        $filters = '';
        while ($row = $result->fetch_assoc()) {
            $filters .= '<option value="' . $row['ID'] . '" ' . (isset($_SESSION['Filter']['IDFilter']) && $row['ID'] == $_SESSION['Filter']['IDFilter'] ? 'selected' : '') . '>' . $row['Name'] . '</option>' . "\n";
        }

        return $filters;
    }

    function clearDefaultValues($arr) {
        foreach ($arr as $k => $v) {
            switch ($k) {
                case 'Date':
                    if ($v == Language::$Filters['Date']) $arr[$k] = '';
                    break;
                case 'Name':
                    if ($v == Language::$Filters['Name']) $arr[$k] = '';
                    break;
                case 'PlaceDone':
                    if ($v == Language::$Filters['PlaceDone']) $arr[$k] = '';
                    break;
                case 'PlaceTaken':
                    if ($v == Language::$Filters['PlaceTaken']) $arr[$k] = '';
                    break;
                case 'Sum':
                    if ($v == Language::$Filters['Sum']) $arr[$k] = '';
                    break;
                case 'Hours':
                    if ($v == Language::$Filters['Hours']) $arr[$k] = '';
                    break;

                case 'TextOrder':
                    if ($v == Language::$Filters['OrderText']) $arr[$k] = '';
                    break;
                case 'TextType':
                    if ($v == Language::$Filters['TypeText']) $arr[$k] = '';
                    break;
                case 'Note':
                    if ($v == Language::$Filters['Note']) $arr[$k] = '';
                    break;
                case 'BookNote':
                    if ($v == Language::$Filters['BookNote']) $arr[$k] = '';
                    break;
            }
        }
        return $arr;
    }

    /**
     * DB functions
     */
    function Save() {
        $_POST = $this->clearDefaultValues($_POST);
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            if ($this->getID() == 0) $this->Add();
            else $this->Update();

            return $this->getID();
        } else {
            $Err[0] = 0;
            if ($Err['IDOrder']) {
                $Err['OrderSelect'] = $Err['IDOrder'];
                unset($Err['IDOrder']);
            }
            if ($Err['IDType']) {
                $Err['TypeSelect'] = $Err['IDType'];
                unset($Err['IDType']);
            }
            if ($Err['IDPerson']) {
                $Err['PersonSelect'] = $Err['IDPerson'];
                unset($Err['IDPerson']);
            }

            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Filters`
                     SET `Name`="' . addslashes($this->getName()) . '",
                         `Date`=' . (int)$this->getDate() . ',
                         `DateType`=' . (int)$this->getDateType() . ',
                         `IDPerson`="' . substr_replace($this->getIDPerson(), "", -2) . '",
                         `IDOperator`="' . substr_replace($this->getIDOperator(), "", -2) . '",
                         `IDOrder`="' . substr_replace($this->getIDOrder(), "", -2) . '",
                         `TextOrder`="' . addslashes($this->getTextOrder()) . '",
                         `IDType`="' . substr_replace($this->getIDType(), "", -2) . '",
                         `TextType`="' . addslashes($this->getTextType()) . '",
                         `Sum`=' . (float)$this->getSum() . ',
                         `Hours`=' . (float)$this->getHours() . ',
                         `PlaceTaken`="' . addslashes($this->getPlaceTaken()) . '",
                         `PlaceDone`="' . addslashes($this->getPlaceDone()) . '",
                         `Note`="' . addslashes($this->getNote()) . '",
                         `BookNote`="' . addslashes($this->getBookNote()) . '",
                         `Search`="' . addslashes($this->getSearch()) . '",
                         `AddDate`=NOW(),
                         `Status`=1';
        if (!self::$DB->query($query)) {
            throw new Error('Write error on Filters (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);
        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Filters` SET
                         `Name`="' . addslashes($this->getName()) . '",
                         `Date`=' . (int)$this->getDate() . ',
                         `DateType`=' . (int)$this->getDateType() . ',
                         `IDPerson`="' . substr_replace($this->getIDPerson(), "", -2) . '",
                         `IDOperator`="' . substr_replace($this->getIDOperator(), "", -2) . '",
                         `IDOrder`="' . substr_replace($this->getIDOrder(), "", -2) . '",
                         `TextOrder`="' . addslashes($this->getTextOrder()) . '",
                         `IDType`="' . substr_replace($this->getIDType(), "", -2) . '",
                         `TextType`="' . addslashes($this->getTextType()) . '",
                         `Sum`=' . (float)$this->getSum() . ',
                         `Hours`=' . (float)$this->getHours() . ',
                         `PlaceTaken`="' . addslashes($this->getPlaceTaken()) . '",
                         `PlaceDone`="' . addslashes($this->getPlaceDone()) . '",
                         `Note`="' . addslashes($this->getNote()) . '",
                         `Search`="' . addslashes($this->getSearch()) . '",
                         `BookNote`="' . addslashes($this->getBokkNote()) . '"
                   WHERE `ID`=' . (int)$this->getID();
        if (!self::$DB->query($query)) {
            throw new Error('Update error on Filters (' . __LINE__ . ')');
        }
    }

    function Delete() {
        $Status = self::$url[2] == 'Restore' ? 1 : -1;

        if ($this->getStatus() == -1 && $Status == -1) {
            $query = 'DELETE FROM `Filters` WHERE `ID`=' . $this->getID();
            Rights::DeleteFilterRights(0, $this->getID());
        } else $query = 'Update `Filters`
                            SET `Status`=' . $Status . ' WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Filters (' . __LINE__ . ')');
        }

        return 1;
    }

    static function getById($ID) {
        $query = 'SELECT *
                    FROM `Filters` WHERE `ID`=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Filters ' . __LINE__);
        }
        return self::fetchObject($result, new self);
    }

    static function getRow($ID) {
        $query = 'SELECT * FROM `Filters` WHERE ID=' . (int)$ID . ' ORDER BY ID DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error($query . 'Read error on Filter (' . __LINE__ . ')');
        }

        $Dates = array(
            99 => Language::$Filters['AllPeriod'],
            5 => Language::$Filters['Today'],
            6 => Language::$Filters['Yesterday'],
            7 => Language::$Filters['Week'],
            1 => Language::$Filters['LastMonth'],
            8 => Language::$Filters['Last'],
            9 => Language::$Filters['Tomorrow'],
            10 => Language::$Filters['FutureWeek'],
            11 => Language::$Filters['FutureMonth'],
            12 => Language::$Filters['Future']
        );
        $DateTypes = array(
            0 => '',
            1 => Language::$Filters['AddDate'],
            2 => Language::$Filters['Date']
        );
        $row = $result->fetch_assoc();

        $row['Person'] =  Filters::MultiPersons($row['IDPerson'], 'Users', 'Login');
        $row['Operator'] =  Filters::MultiPersons($row['IDOperator'], 'Users', 'Login');
        $row['Order'] =  Filters::MultiPersons($row['IDOrder'], 'Orders', 'Code');
        $row['Type'] =  Filters::MultiPersons($row['IDType'], 'Types', 'Code');
        $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
        $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
        $row['DateShow'] = $Dates[$row['Date']];
        $row['DateTypeShow'] = $DateTypes[$row['DateType']];
        $row['Sum'] = $row['Sum'] == 0 ? '' : $row['Sum'];
        $row['Hours'] = $row['Hours'] == 0 ? '' : $row['Hours'];
        return $row;
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
        $v = trim($v);
        if ($v == '')
            throw new Error(Language::$Filters['SetName']);

        $this->Name = $v;
    }

    function MultiPersons($ID, $table, $colum) {
        if ($ID == '') {
            return '';
        }
        $query = "SELECT " . $colum . " FROM " . $table . " WHERE ID IN (" . $ID . ")";
        if (!$result = self::$DB->query($query)) {
            throw new Error($query . ' Read error on Data (' . __LINE__ . ')');
        }
        $res = '';
        while ($row = $result->fetch_assoc()) {
            $res .= $row["$colum"] . ", ";
        }
        return $res;
    }

    function editFilter($ID) {
        $query = 'SELECT * FROM `Filters` WHERE ID=' . (int)$ID . ' ORDER BY ID DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error($query . 'Read error on Filter (' . __LINE__ . ')');
        }

        $Dates = array(
            99 => Language::$Filters['AllPeriod'],
            5 => Language::$Filters['Today'],
            6 => Language::$Filters['Yesterday'],
            7 => Language::$Filters['Week'],
            1 => Language::$Filters['LastMonth'],
            8 => Language::$Filters['Last'],
            9 => Language::$Filters['Tomorrow'],
            10 => Language::$Filters['FutureWeek'],
            11 => Language::$Filters['FutureMonth'],
            12 => Language::$Filters['Future']
        );
        $DateTypes = array(
            0 => '',
            1 => Language::$Filters['AddDate'],
            2 => Language::$Filters['Date']
        );
        $row = $result->fetch_assoc();

        $row['PersonSelect'] =  Filters::MultiPersons($row['IDPerson'], 'Users', 'Login');
        $row['OperatorSelect'] =  Filters::MultiPersons($row['IDOperator'], 'Users', 'Login');
        $row['OrderSelect'] =  Filters::MultiPersons($row['IDOrder'], 'Orders', 'Code');
        $row['TypeSelect'] =  Filters::MultiPersons($row['IDType'], 'Types', 'Code');
        $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
        $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
        $row['DateShow'] = $Dates[$row['Date']];
        $row['DateTypeShow'] = $DateTypes[$row['DateType']];
        $row['Sum'] = $row['Sum'] == 0 ? '' : $row['Sum'];
        $row['Hours'] = $row['Hours'] == 0 ? '' : $row['Hours'];
        $row['IDPerson'] = $row['IDPerson'] . ", ";
        $row['IDOperator'] = $row['IDOperator'] . ", ";
        $row['IDOrder'] = $row['IDOrder'] . ", ";
        $row['IDType'] = $row['IDType'] . ", ";
        echo json_encode($row);
    }
}
