<?php

class Orders extends DBObject {
    use SoftDelete;
    protected static $tableName = 'Orders';
    protected $ID;
    protected $IDUser;
    protected $Code;
    protected $Color;
    protected $Description;
    protected $AddDate;
    protected $Status;
    protected $Changes;

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Changes':
                return $this->getChangeList($_POST['ID']);
            case 'Sort':
                return $this->changeSort();
            case 'Filter':
                return $this->setFilter();
            case 'Save':
                if (!$_SESSION['User']) return '';
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Order = $this->fetchRow($this->assignObject($this->getByID($ID)));
                    $Order['User'] = $_SESSION['User']->getLogin();
                    return self::ArrayToJson(array(1, Template::Process('Row', $Order)));
                }
                return $ID;
            case 'Delete':
            case 'Restore':
                if (!$_SESSION['User']) return '';
                $Order = $this->getByID($_POST['ID']);
                if (!$Order || !$Order->getID())
                    return Language::$Orders['OrderNotFound'];
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
            . ($filter != '' ? ' WHERE ' . $filter : '');

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Error on ' . get_class() . ' (' . __LINE__ . ')');
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
                 ' . ($filter != '' ? ' WHERE ' . $filter : '') . '
                ORDER BY O.' . $_SESSION['OrderSort'] . '
                LIMIT ' . $Start . ',' . Config::PAGE_LENGTH;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Orders (' . __LINE__ . ')');
        }
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            $Orders[] = $this->fetchRow($row);
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
        $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';

        return $row;
    }

    static function getOptionsList() {
        $query = 'SELECT * FROM `Orders` WHERE `Status`=1 ORDER BY `Code`';
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Orders (' . __LINE__ . ')');
        }
        $orders = '';
        while ($row = $result->fetch_assoc()) {
            $orders .= '<option value="' . $row['ID'] . '">' . $row['Code'] . '</option>' . "\n";
        }

        return $orders;
    }

    static function getAsArray() {
        $query = 'SELECT * FROM `Orders` WHERE `Status`=1
                   ORDER BY `Code`';

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Orders (' . __LINE__ . ')');
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
        $Err = AppError::getErrors(get_class($this));

        if (empty($Err)) {
            if ($this->getID() < 1) $this->Add();
            else {
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
            if (self::$DB->errno == 1062) throw new AppError(Language::$Orders['DuplicateEntry']);
            throw new AppError('Write error on Orders (' . __LINE__ . ')');
        }

        $this->setID(self::$DB->insert_id);
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

        if (!self::$DB->query($query)) {
            throw new AppError('Update error on Orders (' . __LINE__ . ')');
        }
    }

    static function getById($ID) {
        $query = "SELECT * FROM `Orders` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Orders (' . __LINE__ . ')');
        }
        return (new self)->fetchObject($result, new self);
    }

    function getCodeById($ID) {
        $query = "SELECT Code FROM `Orders` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row['Code'];
        }
        return $results;
    }

    function setCode($value) {
        $value = trim($value);
        if ($value == '') throw new AppError(Language::$Orders['SetCode']);
        else $this->Code = $value;
    }
}
