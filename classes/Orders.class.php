<?php

class Orders extends DBObject {
    private $ID;
    private $IDUser;
    private $Code;
    private $Color;
    private $Description;
    private $AddDate;
    private $Status;
    private $Changes;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Changes':
                return $this->getChangeList($_POST['ID']);
            case 'Sort':
                return $this->changeSort();
            case 'Filter':
                return $this->setFilter();
            case 'Save':
                if (!$_SESSION['User'] || $_SESSION['User']->getadd_order() == 0) return '';
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Order = $this->fetchRow($this->assignObject($this->getByID($ID)));
                    $Order['User'] = $_SESSION['User']->getLogin();
                    $Rights = Rights::getRigthsByType('Order');
                    if (!$Rights[$Order['ID']]) $Order['Restricted'] = '<b>!</b>';
                    return self::ArrayToJson(array(1, Template::Process('Row', $Order)));
                }
                return $ID;
            case 'Delete':
            case 'Restore':
                if (!$_SESSION['User'] || $_SESSION['User']->getStatus() < 5) return '';
                $Order = $this->getByID($_POST['ID']);
                if (!$Order || !$Order->getID())
                    return Language::$Orders['OrderNotFound'];
                elseif (!$_SESSION['isAdmin'] && $Order->getIDUser() != $_SESSION['User']->getID())
                    return Language::$Orders['NoRights'];
                return $Order->Delete();
            default:
                break;
        }

        if (!$_SESSION['User']) return;

        $Total = $this->countOrders();

        $Page = isset(self::$url[2]) ? (int)self::$url[2] : 0;
        $Start = $Page > 0 ? ((intval($Page) - 1) * Config::PAGE_LENGTH) : 0;
        if ($Start >= $Total) $Start = $Page = 0;
        $Vars['Pages'] = $this->makePages($Total, $Page, Config::ROOT_URL . '/' . self::$url[0] . '/Orders');

        unset(self::$url[2]);

        $Vars['Content'] = $this->getOrdersList($Start);
        $Vars['NoAdmin'] = $_SESSION['User']->getadd_order() == 0 ? 'hide' : '';
        $Vars['Sort'] = $_SESSION['Sort'] == '`ID` DESC' ? '' : '+';
        return Template::Process('index', $Vars);
    }

    function setFilter() {
        $_SESSION['FilterOrder']['Code'] = $_POST['Code'];
        $_SESSION['FilterOrder']['Description'] = $_POST['Description'];

        if (trim($_SESSION['FilterOrder']['Code']) == '' && trim($_SESSION['FilterOrder']['Description']) == '') {
            unset($_SESSION['FilterOrder']);
        }

        return 1;
    }

    function getFilter() {
        $filter = (isset($_SESSION['FilterOrder']['Code']) && $_SESSION['FilterOrder']['Code'] != '')
            ? ' Code LIKE("%' . addslashes($_SESSION['FilterOrder']['Code']) . '%") '
            : '';
        $filter .=  !empty($_SESSION['FilterOrder']['Description'])
            ? ($filter ? ' AND ' : '') . ' Description LIKE("%' . addslashes($_SESSION['FilterOrder']['Description']) . '%") '
            : '';

        return $filter;
    }

    function changeSort() {
        $_SESSION['OrderSort'] = $_SESSION['OrderSort'] == '`Code`' ? '`ID` DESC' : '`Code`';
        return 1;
    }

    function countOrders() {
        $filter = $this->getFilter();

        $query = 'SELECT COUNT(*) FROM Orders '
            . (!$_SESSION['isAdmin'] ? ' WHERE `Status`=1' . ($filter != '' ? ' AND ' . $filter : '')
                : ($filter != '' ? ' WHERE ' . $filter : ''));

        if (!$result = self::$DB->query($query)) {
            throw new Error('Error on ' . get_class() . ' (' . __LINE__ . ')');
        }

        $row = $result->fetch_array();
        return intval($row['COUNT(*)']);
    }

    function getOrdersList($Start) {
        if (empty($_SESSION['OrderSort']))
            $_SESSION['OrderSort'] = '`ID` DESC';

        $filter = $this->getFilter();

        $query = 'SELECT O.*, U.`Login` as User
                    FROM `Orders` O
                    LEFT JOIN Users U ON (O.IDUser=U.ID)
                 ' . (!$_SESSION['isAdmin'] ? ' WHERE O.`Status`=1' . ($filter != '' ? ' AND ' . $filter : '')
            : ($filter != '' ? ' WHERE ' . $filter : '')) . '
                ORDER BY O.' . $_SESSION['OrderSort'] . '
                LIMIT ' . $Start . ',' . Config::PAGE_LENGTH;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Orders (' . __LINE__ . ')');
        }
        $Users = count(Users::getAsArray());
        $Rights = Rights::getRigthsByType('Order');
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            if (isset($Rights[$row['ID']]) && $Rights[$row['ID']] < $Users) $row['Restricted'] = '<b>!</b>';
            $Orders[] = $this->fetchRow($row);
            $row['ID'] . '=' . (isset($Rights[$row['ID']]) ? intval($Rights[$row['ID']]) : 0) . '<br/>';
        }

        if (!empty($Orders)) {
            $Orders['__template'] = 'Row';
            return $Orders;
        }
        return '';
    }

    function fetchRow($row) {
        $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
        $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
        $row['NoAdmin'] = $_SESSION['User']->getadd_order() == 0 ? 'hide' : '';
        $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';

        return $row;
    }

    static function getOptionsList() {
        $query = 'SELECT * FROM `Orders` WHERE `Status`=1 ORDER BY `Code`';
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Orders (' . __LINE__ . ')');
        }
        $orders = '';
        while ($row = $result->fetch_assoc()) {
            $orders .= '<option value="' . $row['ID'] . '">' . $row['Code'] . '</option>' . "\n";
        }

        return $orders;
    }

    static function getAsArray($Orders = -1) {
        if (is_array($Orders) && empty($Orders)) return '';

        $query = 'SELECT * FROM `Orders` WHERE `Status`=1
                  ' . (is_array($Orders) ? ' AND ID IN (' . implode(',', $Orders) . ')' : '') . '
                   ORDER BY `Code`';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Orders (' . __LINE__ . ')');
        }
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            $Orders[$row['ID']] = $row['Code'];
        }

        return $Orders;
    }

    function getUpdateDiff($Obj) {
        $Diffs = array();
        foreach ($this->Fields as $Var) {
            if ($Var == 'AddDate' || $Var == 'Status' || $Var == 'IDUser' || $Var == 'Changes') continue;

            $New = $this->{'get' . $Var}();
            $Old = $Obj->{'get' . $Var}();
            if ($Old != $New) {
                if ($Var == 'IDUser') {
                    if ($Old != 0) {
                        $Old = Users::getById($Old);
                        $Old = $Old->getLogin();
                    }

                    if ($New != 0) {
                        $New = Users::getById($New);
                        $New = $New->getLogin();
                    }
                }

                $Diffs[$Var] = $Old . ' &rarr; ' . $New . '<br/>';
            }
        }
        if (!empty($Diffs)) {
            $Diffs['User'] = $_SESSION['User']->getID() . ' ' . $_SESSION['User']->getLogin();
        }
        return $Diffs;
    }

    function getChangeList($ID) {
        $Data = $this->getById($ID);

        $tmp = '';
        $Changes = unserialize($Data->getChanges());

        if (is_array($Changes))
            foreach ($Changes as $k => $v) {
                $tmp .= '<h4>' . $k . '</h4>';
                $tmp .= ' <small>' . $v['User'] . '</small>';
                foreach ($v as $k => $change) {
                    if ($k == 'User') continue;
                    $tmp .= '<div><span class="light">' . $k . ':</span> ' . $change . '</div>';
                }
            }

        if ($tmp != '') {
            return self::ArrayToJson(
                array(
                    1,
                    Template::Process('Changes', array('Changes' => $tmp, 'ID' => $ID))
                )
            );
        } else return Language::$Data['NoChanges'];
    }

    /**
     * DB functions
     */
    function Save() {
        if ((int)$_POST['ID'] > 0)
            $this->fetchObject($this->assignObject($this->getById($_POST['ID'])));

        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            if ($this->getID() == 0) $this->Add();
            else {
                if ($this->getIDUser() != $_SESSION['User']->getID() && !$_SESSION['isAdmin'])
                    return self::ArrayToJson(array(Language::$Orders['NoRights']));

                $Data = $this->getById($_POST['ID']);
                $Diffs = unserialize($Data->getChanges());
                if (!is_array($Diffs))
                    $Diffs = array();

                $tmp = $this->getUpdateDiff($Data);
                if (!empty($tmp)) {
                    $Diffs[date('Y-m-d H:i:s')] = $tmp;
                    ksort($Diffs);
                    $Diffs = array_reverse($Diffs);
                }
                $this->setChanges(serialize($Diffs));

                $this->Update();
            }

            return $this->getID();
        } else {
            $Err[0] = 0;
            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'INSERT INTO `Orders`
                     SET `IDUser`=' . $_SESSION['User']->getID() . ',
                         `Code`="' . addslashes($this->getCode()) . '",
                         `Color`="' . addslashes($this->getColor()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '",
                         `AddDate`=NOW(),
                         `Status`=1';

        if (!self::$DB->query($query)) {
            if (self::$DB->errno == 1062) throw new Error(Language::$Orders['DuplicateEntry']);
            throw new Error('Write error on Orders (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);
        if ($_POST['RightsAdd'] == 1)
            Rights::addRights($this->getID(), 'Order');
        Rights::addRights($this->getID(), 'Folder');
        file_get_contents('/faili/xml/sysrpc.php?cmd=createorder&orderId=' . $this->getID());
        include("faili/sysapi.php");
        $ID = $this->getCode();
        $map = _faili_create_order_directory_ex($ID);
        return $this->getID();
    }

    function Update() {
        $query = 'UPDATE `Orders`
                     SET `IDUser`=' . $_SESSION['User']->getID() . ',
                         `Code`="' . addslashes($this->getCode()) . '",
                         `Color`="' . addslashes($this->getColor()) . '",
                         `Description`="' . addslashes($this->getDescription()) . '",
                         `Changes`="' . addslashes($this->getChanges()) . '"
                   WHERE `ID`=' . (int)$this->getID();

        if ($_POST['RightsDel'] == 1)
            Rights::DeleteById($this->getID(), 'Order');
        elseif ($_POST['RightsAdd'] == 1) Rights::addRights($this->getID(), 'Order');

        if (!self::$DB->query($query)) {
            throw new Error('Update error on Orders (' . __LINE__ . ')');
        }
    }

    function Delete() {
        $Status = self::$url[2] == 'Restore' ? 1 : -1;

        if ($this->getStatus() == -1 && $Status == -1) {
            if ($_POST['pass'] != Config::DEL_PASS) return Language::$Main['WrongDelPass'];
            $query = 'DELETE FROM `Orders` WHERE `ID`=' . $this->getID();
            Rights::DeleteById($this->getID(), 'Order');
        } else $query = 'Update `Orders`
                            SET `Status`=' . $Status . ' WHERE `ID`=' . $this->getID();

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Orders (' . __LINE__ . ')');
        }

        return 1;
    }

    static function getById($ID) {
        $query = "SELECT * FROM `Orders` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Orders (' . __LINE__ . ')');
        }
        return self::fetchObject($result, new self);
    }

    function getCodeById($ID) {
        $query = "SELECT Code FROM `Orders` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row['Code'];
        }
        return $results;
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
        if ($value == '') throw new Error(Language::$Orders['SetCode']);
        else $this->Code = $value;
    }
}
