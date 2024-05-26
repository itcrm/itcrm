<?php

class Data extends DBObject {
    private $ID;
    private $IDDoc;
    private $IDUser;
    private $IDOrder;
    private $TextOrder;
    private $IDType;
    private $TextType;
    private $Sum;
    private $Hours;
    private $PlaceTaken;
    private $PlaceDone;
    private $IDPerson;
    private $Note;
    private $Date;
    private $BookNote;
    private $TotalPrice;
    private $PriceNote;
    private $AddDate;
    private $RemindDate;
    private $RemindDateEnd;
    private $RemindTo;
    private $Status;
    private $Hidden;
    private $Changes;
    private $AdminEdit;
    private $AllDay;
    private $allDay;
    private $AllDays;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'AutocompliteJosn':
                echo $this->AutocompliteJosn($_GET['term']);
                die;
            case 'Page':
                $_SESSION['page'] = $_POST['lapa'];
                return "1";
            case 'FormSave':
                $ID =  $this->FormSave();
                if (is_numeric($ID)) {
                    $Data = $this->getRow($ID);
                    return self::ArrayToJson(array(1, Template::Process('Row', $Data), $ID));
                } else return $ID;
            case 'Pagesk':
                $_SESSION['pagecount'] = $_POST['sk'];
                return "1";
            case 'Open':
                return $this->Open(self::$url[3]);
            case 'AddAllSelected':
                return $this->AddAllSelected();
            case 'noliktava':
                return $this->noliktava_exist($_POST['DetalasID']);
            case 'NoliktavaAtlikums':
                return json_encode($this->noliktava_atlikums($_POST['ID']));
            case 'SaveDetala':
                return  $this->SaveDetala($_POST);
            case 'NoliktavaSave':
                $this->NoliktavaSave($_POST);
                break;
            case 'GetTpl':
                $Data = $this->getRow($_POST['ID']);
                $Data[0] = 1;
                $Data['OrderSelect'] = $Data['Order'];
                $Data['TypeSelect'] = $Data['Type'];
                $Data['PersonSelect'] = $Data['Person'];
                return self::ArrayToJson($Data);
            case 'CeckRow':
                $this->CeckRow($_POST['row']);
                $Data = $this->getRow($_POST['row']);
                $Data['select'] = "selected";
                $Data['checked'] = "checked";
                $Data['Function'] = "UnCheckRow";
                return self::ArrayToJson(array(Template::Process('Row', $Data)));
            case 'UnCeckRow':
                $this->UnCeckRow($_POST['row']);
                $Data = $this->getRow($_POST['row']);
                $Data['Function'] = "CeckRow";
                return self::ArrayToJson(array(Template::Process('Row', $Data)));
            case 'ChangeSelected':
                return $this->ChangeSelected($_POST);
            case 'photoTagger':
                return json_encode($this->photoTagger($_GET['photoID']));
            case 'SavephotoTagger':
                return $this->SavephotoTagger();
            case 'DeletephotoTagger':
                return $this->DeletephotoTagger($_GET['id']);
            case 'Search':
                if (!$_SESSION['isAdmin']) return;
                $Vars['Content'] = $this->Search();
                $Vars['Pages'] = parent::makePages($_SESSION['entry'], $_SESSION['page'], '#', $_SESSION['pagecount']);
                break;
            case 'Filter':
                $_SESSION['page'] = '1';
                $_SESSION['pagecount'] = '25';
                return $this->setFilter();
            case 'Save':
                if (!$_SESSION['User']) return '';
                if ($_SESSION['User']->getStatus() < 4) return '';
                if ($this->MyEntry($_POST['ID']) != 1) return '';
                $ID = $this->Save();
                if (is_numeric($ID)) {
                    $Data = $this->getRow($ID);
                    return self::ArrayToJson(array(1, Template::Process('Row', $Data), $ID));
                } else return $ID;
            case 'Delete':
            case 'Restore':
                if (!$_SESSION['isAdmin']) return '';
                $Data = $this->getByID($_POST['ID']);
                if (!$Data || !$Data->getID()) return Language::$Data['DataNotFound'];
                return $Data->Delete();
            case 'Export':
                return $this->Export();
            case 'HTMLGrupas':
                return $this->HTMLGrupas($_POST['ID'], $_POST['form']);
            case 'Changes':
                return $this->getChangeList($_POST['ID']);
            case 'Sort':
                return $this->changeSort();
            case 'Reminder':
            default:
                if (!$_SESSION['User']) return;

                $Vars['Content'] = $this->getDataList();
                $Vars['Pages'] = parent::makePages($_SESSION['entry'], $_SESSION['page'], '#', $_SESSION['pagecount']);
                break;
        }

        $Vars['Function'] = "CeckRow";

        $Vars['TplList'] = $this->getTplAsOpt();

        $Vars['Total'] =  $_SESSION['Summa'];
        $Vars['PriceTotal'] = $_SESSION['PriceTotal'];
        $Vars['TotalHours'] = $_SESSION['TotalHours'];

        $Vars['add_r_bilde'] = $_SESSION['User']->getadd_r_bilde() == 0 ? 'hide' : '';
        $Vars['add_files'] = $_SESSION['User']->getadd_files() == 0 ? 'hide' : '';

        $Vars['Reminder'] = $this->getReminder();

        $Vars['MultiChange'] = $_SESSION['User']->getMultiChange() == 1 ? 'block' : 'none';
        $Vars['NoAdmin'] = $_SESSION['User']->getStatus() < 4 ? 'hide' : '';

        $Vars['HidePeriods'] = !$_SESSION['isAdmin'] ? 'hide' : '';

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
                if ($k == 'FindDeleted' && $v == 1) $Vars[$k] = 'checked';
                else $Vars[$k] = $v;
            }

        $Vars['Filters'] = Filters::getOptionsList();
        $Vars['DateSort'] = $_SESSION['Sort'] == '`ID`' ? Language::$Data['AddDate'] : Language::$Data['Date'];
        $Vars['Login'] = $_SESSION['User']->getLogin();
        $Vars['UserID'] = $_SESSION['User']->getID();
        $Vars['slieder'] = !isset($_SESSION['menu']) || $_SESSION['menu'] == 0 ? "none" : "block";
        $Vars['SLO'] = !isset($_SESSION['menu']) || $_SESSION['menu'] == 0 ? "blok" : "none";

        return Template::Process('index', $Vars);
    }

    public static function getReminder() {
        $query = 'SELECT MIN(D.RemindDate) as Min,D.RemindDate,D.RemindTo,U.Login,U.Color,U.Status
                   FROM `Data` D
               LEFT JOIN Users U ON (U.ID=D.RemindTo)
               WHERE D.RemindDate>"2000-00-00 00:00:00" AND U.Status<=' . $_SESSION['User']->getStatus() .
            ' GROUP BY D.RemindTo';

        if (!$result = self::$DB->query($query))
            throw new Error('Error on ' . get_class() . ' (' . __LINE__ . ')');

        $Data = array();
        $now = strtotime(date('Y-m-d H:i:s'));
        while ($row = $result->fetch_assoc()) {
            if (strtotime($row['Min']) < $now) $row['Alert'] = '#F00; font-weight:700;';
            $Data[] = $row;
        }

        if (empty($Data)) return '';
        else {
            if ($_SESSION['isAdmin']) $Data[] = array('RemindTo' => '0', 'Login' => Language::$Data['All']);
            return $Data;
        }
    }

    function changeSort() {
        $_SESSION['Sort'] = $_SESSION['Sort'] == '`Date`' ? '`ID`' : '`Date`';
        return 1;
    }

    function Search() {
        $str = addslashes($_POST['Search']);
        $startDate = '';
        switch ($_POST['Period']) {
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
                $startDate = -1;
                break;
            default:
                $startDate = date('Y-m-d', mktime(0, 0, 0, date('n'), 1, date('Y')));
                break;
        }

        $search = explode(' ', trim($str));
        $str = array();
        foreach ($search as $k => $v) $str[] = '(
                     D.IDDoc LIKE ("%' . $v . '%") OR
                     D.TextOrder LIKE ("%' . $v . '%") OR
                     D.TextType LIKE ("%' . $v . '%") OR
                     D.PlaceTaken LIKE ("%' . $v . '%") OR
                     D.PlaceDone LIKE ("%' . $v . '%") OR
                     D.Note LIKE ("%' . $v . '%") OR
                     D.BookNote LIKE ("%' . $v . '%") OR
                     D.PriceNote LIKE ("%' . $v . '%") OR
                     P.Login LIKE ("%' . $v . '%") OR
                     U.Login LIKE ("%' . $v . '%") OR
                     O.Code LIKE ("%' . $v . '%") OR
                     T.Code LIKE ("%' . $v . '%")
                     )';
        if (!isset($_POST['FindDeleted']) || $_POST['FindDeleted'] != 1) {
            $str[] = 'D.Status=1';
        }

        $str = implode(' AND ', $str);

        $query = 'SELECT D.*,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `DateShow`,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `Date`,
                         DATE_FORMAT(D.`AddDate`,"%y.%m.%d %H:%i") as `AddDate`,
                         DATE_FORMAT(D.`RemindDate`,"%y.%m.%d %H:%i") as `RemindDate`,
                         `RemindDate` as RemindDateStamp,
                         P.Login as Person, U.Login as User, R.Login as RemindTo,
                         O.Code as `Order`, T.Code as Type
                    FROM `Data` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)
               WHERE ' . $str . '
                     ' . ($startDate > -1 ? 'AND D.Date BETWEEN "' . $startDate . '" AND "' . ($endDate != '' ? $endDate : date('Y-m-d 23:59:59')) . '"' : '');

        $where = ' WHERE ' . $str . '
                  ' . ($startDate > -1 ? 'AND D.Date BETWEEN "' . $startDate . '" AND "' . ($endDate != '' ? $endDate : date('Y-m-d 23:59:59')) . '"' : '');

        $query .= ' ORDER BY ' . ($_POST['Sort'] == 1 ? 'Date' : 'ID') . ' DESC';

        if (!$_SESSION['pagecount']) {
            $_SESSION['pagecount'] = '25';
        }

        if (!$_SESSION['page']) {
            $_SESSION['page'] = 0;
        }

        if ($_SESSION['page'] < 1) {
            $pagestart = 0;
        } else {
            $pagestart = $_SESSION['pagecount'] * ($_SESSION['page'] - 1);
        }

        $page = $_SESSION['pagecount'];

        $query2 = 'SELECT
        count(D.ID) as kopskaits,
        sum(sum) as summa,
        sum(TotalPrice) as PriceTotal,
        sum(Hours) as TotalHours

        FROM
        `Data` D
        LEFT JOIN Users P ON (P.ID=D.IDPerson)
        LEFT JOIN Users U ON (U.ID=D.IDUser)
        LEFT JOIN Users R ON (R.ID=D.RemindTo)
        LEFT JOIN Orders O ON (O.ID=D.IDOrder)
        LEFT JOIN Types T ON (T.ID=D.IDType)';

        $query2 .= $where;

        if (!$result = self::$DB->query($query2)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }
        while ($row = $result->fetch_assoc()) {
            $_SESSION['entry'] = $row['kopskaits'];
            $_SESSION['Summa'] = round($row['summa'], 2);
            $_SESSION['PriceTotal'] = round($row['PriceTotal'], 2);
            $_SESSION['TotalHours'] = round($row['TotalHours'], 2);
        }

        if ($pagestart > $_SESSION['entry']) {
            $_SESSION['page'] = 1;
            $pagestart = 0;
        }

        $query .= ' LIMIT ' . $pagestart . ', ' . $page;

        // add faili image function
        $fileFnExists = false;
        if (file_exists("faili/sysapi.php")) {
            require_once "faili/sysapi.php";
            $fileFnExists = true;
        }

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        $Data = array();
        $now = strtotime(date('Y-m-d H:i'));
        while ($row = $result->fetch_assoc()) {
            if ($row['IDType'] == 61) $row['dblClick'] = 'getSupplier(this);';

            if ($row['Noma'] == 1) $row['dblClick'] = 'MakeNoma(this);';
            if ($row['IDType'] == 72) $row['dblClick'] = 'getPavadzime(this);';

            if ($row['IDType'] == Config::Noliktava) $row['dblClick'] = "OpenForm('GetVeikals','DialogForm','scrollDiv','Prece','1500'," . $row['ID'] . ");";

            if ($row['IDType'] == Config::AddNoliktava) $row['dblClick'] = 'getNoliktava(this,1); addNoliktavaAutoComp();';
            if ($row['IDType'] == Config::DelNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
            if ($row['IDType'] == Config::ReservNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
            if ($row['IDType'] == Config::ReturnNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';

            $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
            $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
            $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';
            $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
            if ($fileFnExists) $row['link'] = _faili_row_file_exists($row['ID']);
            $row['order_title'] = urlencode($row['Order']);
            if ($row['RemindDate'] == '00.00.00 00:00')
                $row['RemindDate'] = '';
            else {
                if (strtotime($row['RemindDateStamp']) < $now)
                    $row['reminderColor'] = 'red';
                else $row['reminderColor'] = 'green';
            }

            $Data[] = $row;
        }

        if (!empty($Data)) {
            $Data['__template'] = 'Row';
            return $Data;
        } else return '';
    }

    function getDataList() {
        $fileFnExists = false;
        if (file_exists("faili/sysapi.php")) {
            require_once "faili/sysapi.php";
            $fileFnExists = true;
        }

        if ($_SESSION['User']->getOneDay() == 1) {
            $_SESSION['Filter']['DateFrom'] = date("Y-m-j");
            $_SESSION['Filter']['DateTo'] = date("Y-m-j", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
            $_SESSION['Filter']['Operator'] = $_SESSION['User']->getID();
        }

        $Filter = $this->getFilter();

        $query = 'SELECT D.*,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `DateShow`,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `Date`,
                         DATE_FORMAT(D.`AddDate`,"%y.%m.%d %H:%i") as `AddDate`,
                         DATE_FORMAT(D.`RemindDate`,"%y.%m.%d %H:%i") as `RemindDate`,
                         `RemindDate` as RemindDateStamp,
                         P.Login as Person, U.Login as User, R.Login as RemindTo,
                         O.Code as `Order`, T.Code as Type
                    FROM `Data` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)
               ';

        if (!isset($_SESSION['Filter']['FindDeleted']) || $_SESSION['Filter']['FindDeleted'] != 1) {
            $Filter[] = 'D.`Status`=1';
        }

        if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
            $Filter[] = 'D.`Hidden`=0';
        }

        $Sort = $_SESSION['Sort'] . ' DESC';

        if (isset(self::$url[2]) && self::$url[2] == 'Reminder') {
            unset($Filter);

            if (!$_SESSION['isAdmin']) {
                if (!empty($_SESSION['Rights']['Types']))
                    $Filter['Type'] = 'D.IDType IN (' . implode(',', $_SESSION['Rights']['Types']) . ')';
                if (!empty($_SESSION['Rights']['Orders']))
                    $Filter['Order'] = 'D.IDOrder IN (' . implode(',', $_SESSION['Rights']['Orders']) . ')';
                if (!empty($_SESSION['Rights']['Persons']))
                    $Filter['Operator'] = 'D.IDUser IN (' . implode(',', $_SESSION['Rights']['Persons']) . ')';
            }

            if (self::$url[3] === '0' && $_SESSION['isAdmin']) $Filter['RemindTo'] = 'RemindTo>0';
            else $Filter['RemindTo'] = 'RemindTo=' . (is_numeric(self::$url[3]) ? self::$url[3] : $_SESSION['User']->getID());
            unset(self::$url[2]);

            $Sort = 'D.RemindDate DESC ';
        }

        if (!$_SESSION['isAdmin']) {
            $where = ' WHERE D.`Status`=1 ' . (!empty($Filter) ? ' AND ( (' . implode(' AND ', $Filter) . ') )'                 // $query .= ' WHERE D.`Status`=1 '.(!empty($Filter) ? ' AND ( ('.implode(' AND ',$Filter).') OR D.RemindTo='.$_SESSION['User']->getID().')'
                : ' OR D.RemindTo=' . $_SESSION['User']->getID()) . ' AND D.Hidden = 0 ';
            $query .= $where;
        } else {
            $where = !empty($Filter) ? 'WHERE ' . implode(' AND ', $Filter) : '';
            $query .= $where;
        }

        if (!empty($_SESSION['Filter']['Search'])) {
            $search = explode(' ', trim($_SESSION['Filter']['Search']));
            $str = array();
            foreach ($search as $k => $v) $str[] = '(
                     D.IDDoc LIKE ("%' . $v . '%") OR
                     D.TextOrder LIKE ("%' . $v . '%") OR
                     D.TextType LIKE ("%' . $v . '%") OR
                     D.PlaceTaken LIKE ("%' . $v . '%") OR
                     D.PlaceDone LIKE ("%' . $v . '%") OR
                     D.Note LIKE ("%' . $v . '%") OR
                     D.BookNote LIKE ("%' . $v . '%") OR
                     D.PriceNote LIKE ("%' . $v . '%") OR
                     P.Login LIKE ("%' . $v . '%") OR
                     U.Login LIKE ("%' . $v . '%") OR
                     O.Code LIKE ("%' . $v . '%") OR
                     T.Code LIKE ("%' . $v . '%")
                     )';
            if ($_POST['FindDeleted'] != 1) {
                $str[] = 'D.Status=1';
            }

            $str = implode(' AND ', $str);

            if (!empty($str))
                $where .= ' AND ' . $str;
            $query .=  ' AND ' . $str;
        }

        $query4 = 'SELECT
        count(D.ID) as kopskaits,
        sum(sum) as summa,
        sum(TotalPrice) as PriceTotal,
        sum(Hours) as TotalHours

        FROM
        `Data` D
        LEFT JOIN Users P ON (P.ID=D.IDPerson)
        LEFT JOIN Users U ON (U.ID=D.IDUser)
        LEFT JOIN Users R ON (R.ID=D.RemindTo)
        LEFT JOIN Orders O ON (O.ID=D.IDOrder)
        LEFT JOIN Types T ON (T.ID=D.IDType)';

        $query3 = 'SELECT D.*,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `DateShow`,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `Date`,
                         DATE_FORMAT(D.`AddDate`,"%y.%m.%d %H:%i") as `AddDate`,
                         DATE_FORMAT(D.`RemindDate`,"%y.%m.%d %H:%i") as `RemindDate`,
                         `RemindDate` as RemindDateStamp,
                         P.Login as Person, U.Login as User, R.Login as RemindTo,
                         O.Code as `Order`, T.Code as Type
                    FROM `Data` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)';
        $_SESSION['CechedRow']['0'] = '0';

        if (!empty($_SESSION['CechedRow'])) {
            $CechedRow = ' (' . implode(',', $_SESSION['CechedRow']) . ')';

            $query3 .= "WHERE D.ID IN" . $CechedRow . ' ORDER BY ' . $Sort;
            $query4 .= " WHERE D.ID IN" . $CechedRow;

            if (!$result = self::$DB->query($query4)) {
                throw new Error('Read error on Data (' . __LINE__ . ')');
            }
            while ($row = $result->fetch_assoc()) {
                $start_kopskaits = $row['kopskaits'];
                $starp_summa = isset($row['summa']) ? round($row['summa'], 2) : 0;
                $starp_PriceTotal = isset($row['PriceTotal']) ? round($row['PriceTotal'], 2) : 0;
                $starp_TotalHours = isset($row['TotalHours']) ? round($row['TotalHours'], 2) : 0;
            }

            if (!$result = self::$DB->query($query3)) {
                throw new Error('Read error on Data (' . __LINE__ . ')');
            }

            $Data = array();
            $i = 0;
            $now = strtotime(date('Y-m-d H:i:00'));
            while ($row = $result->fetch_assoc()) {
                if ($i % 2 == 0) $row['Odd'] = 'Odd';
                $i++;

                if ($row['IDType'] == 61) $row['dblClick'] = 'getSupplier(this);';
                if ($row['IDType'] == 72) $row['dblClick'] = 'getPavadzime(this);';
                if ($row['Noma'] == 1) $row['dblClick'] = 'MakeNoma(this);';

                if ($row['IDType'] == Config::Noliktava) $row['dblClick'] = "OpenForm('GetVeikals','DialogForm','scrollDiv','Prece','1500'," . $row['ID'] . ");";

                if ($row['IDType'] == Config::AddNoliktava) $row['dblClick'] = 'getNoliktava(this,1); addNoliktavaAutoComp();';
                if ($row['IDType'] == Config::DelNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
                if ($row['IDType'] == Config::ReservNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
                if ($row['IDType'] == Config::ReturnNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';

                $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
                $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
                $row['HiddenClass'] = $row['Hidden'] == 1 ? 'hidden' : '';
                $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';
                if ($fileFnExists) $row['link'] = _faili_row_file_exists($row['ID']);
                $row['order_title'] = urlencode($row['Order']);
                $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
                $row['select'] = 'selected';
                $row['checked'] = 'checked';
                $row['Function'] = "UnCheckRow";
                $row['CanEdit'] =   $this->MyEntry($row['ID']) == 1 ? '' : 'hide';
                $row['CanCopy'] =   $_SESSION['User']->getStatus() < 4 ? 'hide' : '';
                $row['AdminEditClass'] = $row['AdminEdit'] == 1 ? 'AdminEdit' : '';

                $hideRights = isset($_SESSION['Rights']['Hide']) ? $_SESSION['Rights']['Hide'] : [];
                $keysToCheck = [
                    $row['IDPerson'] . '.' . $row['IDOrder'] . '.' . $row['IDType'],
                    '0.' . $row['IDOrder'] . '.' . $row['IDType'],
                    $row['IDPerson'] . '.0.' . $row['IDType'],
                    $row['IDPerson'] . '.' . $row['IDOrder'] . '.0',
                    $row['IDPerson'] . '.0.0',
                    '0.' . $row['IDOrder'] . '.0',
                    '0.0.' . $row['IDType'],
                ];
                foreach ($keysToCheck as $key) {
                    if (isset($hideRights[$key]) && $hideRights[$key]) {
                        $row['Sum'] = '&mdash;';
                        $row['Hours'] = '&mdash;';
                        break;
                    }
                }

                if ($row['RemindDate'] == '00.00.00 00:00')
                    $row['RemindDate'] = '';
                else {
                    if (strtotime($row['RemindDateStamp']) < $now)
                        $row['reminderColor'] = 'red';
                    else $row['reminderColor'] = 'green';
                }

                $Data2[] = $row;
            }
        }

        if (!isset($_SESSION['pagecount']) || !$_SESSION['pagecount']) $_SESSION['pagecount'] = '25';

        if (!isset($_SESSION['page']) || !$_SESSION['page']) $_SESSION['page'] = 0;

        if ($_SESSION['page'] < 1) {
            $pagestart = 0;
        } else {
            $pagestart = $_SESSION['pagecount'] * ($_SESSION['page'] - 1);
        }

        $page = $_SESSION['pagecount'];

        $query2 = 'SELECT
        count(D.ID) as kopskaits,
        sum(sum) as summa,
        sum(TotalPrice) as PriceTotal,
        sum(Hours) as TotalHours

        FROM
        `Data` D
        LEFT JOIN Users P ON (P.ID=D.IDPerson)
        LEFT JOIN Users U ON (U.ID=D.IDUser)
        LEFT JOIN Users R ON (R.ID=D.RemindTo)
        LEFT JOIN Orders O ON (O.ID=D.IDOrder)
        LEFT JOIN Types T ON (T.ID=D.IDType)';

        $query2 .= $where;
        $query2 .= ' and D.ID NOT IN' . $CechedRow;

        if (!$result = self::$DB->query($query2)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }
        while ($row = $result->fetch_assoc()) {
            $_SESSION['entry'] = $row['kopskaits'] + $start_kopskaits;
            $_SESSION['Summa'] = (isset($row['summa']) ? round($row['summa'], 2) : 0) + $starp_summa;
            $_SESSION['PriceTotal'] = (isset($row['PriceTotal']) ? round($row['PriceTotal'], 2) : 0) + $starp_PriceTotal;
            $_SESSION['TotalHours'] = (isset($row['TotalHours']) ? round($row['TotalHours'], 2) : 0) + $starp_TotalHours;
        }

        if ($pagestart > $_SESSION['entry']) {
            $pagestart = 0;
        }

        $query .= ' and D.ID NOT IN' . $CechedRow;
        $query .= ' ORDER BY ' . $Sort;
        $query .= ' LIMIT ' . $pagestart . ', ' . $page;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        $Data = array();
        $i = 0;
        $now = strtotime(date('Y-m-d H:i:00'));
        while ($row = $result->fetch_assoc()) {
            if ($i % 2 == 0) $row['Odd'] = 'Odd';
            $i++;

            if ($row['IDType'] == 61) $row['dblClick'] = 'getSupplier(this);';
            if ($row['IDType'] == 72) $row['dblClick'] = 'getPavadzime(this);';
            if ($row['Noma'] == 1) $row['dblClick'] = 'MakeNoma(this);';

            if ($row['IDType'] == Config::Noliktava) $row['dblClick'] = "OpenForm('GetVeikals','DialogForm','scrollDiv','Prece','1500'," . $row['ID'] . ");";

            if ($row['IDType'] == Config::AddNoliktava) $row['dblClick'] = 'getNoliktava(this,1); addNoliktavaAutoComp();';
            if ($row['IDType'] == Config::DelNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
            if ($row['IDType'] == Config::ReservNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
            if ($row['IDType'] == Config::ReturnNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';

            $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
            $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
            $row['HiddenClass'] = $row['Hidden'] == 1 ? 'hidden' : '';
            $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';
            $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
            if ($fileFnExists) $row['link'] = _faili_row_file_exists($row['ID']);
            $row['order_title'] = urlencode($row['Order']);

            $row['CanEdit'] =   $this->MyEntry($row['ID']) == 1 ? '' : 'hide';
            $row['CanCopy'] =   $_SESSION['User']->getStatus() < 4 ? 'hide' : '';
            $row['AdminEditClass'] = $row['AdminEdit'] == 1 ? 'AdminEdit' : '';

            $hideRights = isset($_SESSION['Rights']['Hide']) ? $_SESSION['Rights']['Hide'] : [];
            $keysToCheck = [
                $row['IDPerson'] . '.' . $row['IDOrder'] . '.' . $row['IDType'],
                '0.' . $row['IDOrder'] . '.' . $row['IDType'],
                $row['IDPerson'] . '.0.' . $row['IDType'],
                $row['IDPerson'] . '.' . $row['IDOrder'] . '.0',
                $row['IDPerson'] . '.0.0',
                '0.' . $row['IDOrder'] . '.0',
                '0.0.' . $row['IDType'],
            ];
            foreach ($keysToCheck as $key) {
                if (isset($hideRights[$key]) && $hideRights[$key]) {
                    $row['Sum'] = '&mdash;';
                    $row['Hours'] = '&mdash;';
                    break;
                }
            }

            if ($row['RemindDate'] == '00.00.00 00:00')
                $row['RemindDate'] = '';
            else {
                if (strtotime($row['RemindDateStamp']) < $now)
                    $row['reminderColor'] = 'red';
                else $row['reminderColor'] = 'green';
            }
            $Data1[] = $row;
        }
        $Data = array_merge((array)$Data2, (array)$Data1);
        if (!empty($Data)) {
            $Data['__template'] = 'Row';
            return $Data;
        } else return '';
    }

    function setFilter() {
        $currFilter = $_SESSION['Filter']['IDFilter'];
        $_SESSION['Filter'] = $_POST;

        if ($_SESSION['Filter']['DateTo'] == $_SESSION['Filter']['DateFrom'])
            $_SESSION['Filter']['DateTo'] = $_SESSION['Filter']['DateTo'] . " 23:59:59";
        $_SESSION['Filter']['DateFrom'] = $_SESSION['Filter']['DateFrom'] . " 00:00:00";

        $_SESSION['Filters'] = Filters::getFilter($_POST['IDFilter']);

        $_POST['IDFilter'] = '0';

        if ($_POST['IDFilter'] > 0) {
            if ($_POST['IDFilter'] != $_SESSION['FilterSaved']['ID']) {
                $_SESSION['FilterSaved'] = Filters::getFilter($_POST['IDFilter']);
                $_SESSION['Filter']['IDFilter'] = $_POST['IDFilter']; //,'Search'=>$_POST['Search']);
                if ($_SESSION['FilterSaved']['Date']) {
                    $_SESSION['Filter']['DateFrom'] = $_SESSION['FilterSaved']['Date']['From'];
                    $_SESSION['Filter']['DateTo'] = $_SESSION['FilterSaved']['Date']['To'];
                }
                if ($_SESSION['FilterSaved']['DateType'] == 1) {
                    $_SESSION['Sort'] = '`ID`';
                } elseif ($_SESSION['FilterSaved']['DateType'] == 2) {
                    $_SESSION['Sort'] = '`Date`';
                }
            }
        } else {
            if ($currFilter > 0) unset($_SESSION['Filter']);
            unset($_SESSION['FilterSaved']);
        }
        if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}'
            . '|[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}'
            . '|[0-9]{4} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}'
            . '|[0-9]{2,4} [0-9]{1,2} [0-9]{1,2}/', $_SESSION['Filter']['DateFrom']))
            unset($_SESSION['Filter']['DateFrom']);
        else {
            $tmp = explode(' ', str_replace('-', ' ', $_SESSION['Filter']['DateFrom']));
            $_SESSION['Filter']['DateFrom'] = $tmp[0] . '-' . $tmp[1] . '-' . $tmp[2] . ($tmp[3] != '' ? ' ' . $tmp[3] : '') . ($tmp[4] != '' ? ':' . $tmp[4] : '');
        }

        if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}'
            . '|[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}'
            . '|[0-9]{4} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}'
            . '|[0-9]{2,4} [0-9]{1,2} [0-9]{1,2}/', $_SESSION['Filter']['DateTo']))
            unset($_SESSION['Filter']['DateTo']);
        else {
            $tmp = explode(' ', str_replace('-', ' ', $_SESSION['Filter']['DateTo']));
            $_SESSION['Filter']['DateTo'] = $tmp[0] . '-' . $tmp[1] . '-' . $tmp[2] . ($tmp[3] != '' ? ' ' . $tmp[3] : '') . ($tmp[4] != '' ? ':' . $tmp[4] : '');
        }

        if (!$_SESSION['isAdmin']) {
            $personss  = explode(", ", $_SESSION['Filter']['Person']);
            $person_rez = array();
            foreach ($personss as $k => $v) {
                if (!in_array($k, $_SESSION['Rights']['Persons']))
                    array_push($person_rez, $v);
            }
            if (empty($person_rez)) {
                unset($_SESSION['Filter']['Person']);
            }

            $orderss  = explode(", ", $_SESSION['Filter']['Order']);
            $order_rez = array();
            foreach ($orderss as $k => $v) {
                if (!in_array($k, $_SESSION['Rights']['Orders']))
                    array_push($order_rez, $v);
            }
            if (empty($order_rez)) {
                unset($_SESSION['Filter']['Order']);
            }

            $typess = explode(", ", $_SESSION['Filter']['Type']);
            $type_rez = array();
            foreach ($typess as $k => $v) {
                if (!in_array($k, $_SESSION['Rights']['Types']))
                    array_push($type_rez, $v);
            }
            if (empty($type_rez)) {
                unset($_SESSION['Filter']['Type']);
            }
        }
        unset($_SESSION['Filter']['IDFilter']);
        return 1;
    }

    function getFilter() {
        if (isset($_SESSION['Filter']['ID']) && $_SESSION['Filter']['ID'] != '')
            $Vars['ID'] = 'D.ID="' . $_SESSION['Filter']['ID'] . '"';

        if (isset($_SESSION['Filter']['IDDoc']) && $_SESSION['Filter']['IDDoc'] != '')
            $Vars['IDDoc'] = 'D.IDDoc LIKE "%' . $_SESSION['Filter']['IDDoc'] . '%"';

        if (empty($_SESSION['Filter']['DateFrom']))
            $_SESSION['Filter']['DateFrom'] = date('Y-m-d', time() - 60 * 60 * 24 * Config::SHOW_PERIOD);
        if (empty($_SESSION['Filter']['DateTo']))
            $_SESSION['Filter']['DateTo'] = date('Y-m-d', time() + 60 * 60 * 24);

        if (empty($_SESSION['Sort']))
            $_SESSION['Sort'] = '`ID`';

        if (isset($_SESSION['FilterSaved']['Date']['From']) && isset($_SESSION['Filter']['DateFrom']) && $_SESSION['FilterSaved']['Date']['From'] > $_SESSION['Filter']['DateFrom']) {
            $_SESSION['Filter']['DateFrom'] = $_SESSION['FilterSaved']['Date']['From'];
        }

        if (isset($_SESSION['FilterSaved']['Date']['To']) && isset($_SESSION['Filter']['DateTo']) && $_SESSION['FilterSaved']['Date']['To'] < $_SESSION['Filter']['DateTo'])
            $_SESSION['Filter']['DateTo'] = $_SESSION['FilterSaved']['Date']['To'];

        $Vars['Date'] = ($_SESSION['Sort'] == '`ID`' ? 'D.AddDate ' : 'D.Date') . ' BETWEEN "' . $_SESSION['Filter']['DateFrom'] . '" AND "' . $_SESSION['Filter']['DateTo'] . '"';
        if (isset($_SESSION['FilterSaved']['Date']['From']) && $_SESSION['FilterSaved']['Date']['From'] == -1) unset($Vars['Date']);

        if (isset($_SESSION['FilterSaved']['IDPerson']) && $_SESSION['FilterSaved']['IDPerson']) {
            $Vars['Person'] = 'D.IDPerson=' . $_SESSION['FilterSaved']['IDPerson'];
            $_SESSION['Filter']['Person'] = $_SESSION['FilterSaved']['IDPerson'];
        } else {
            $Vars['Person'] = isset($_SESSION['Filter']['Person']) && $_SESSION['Filter']['Person'] > 0
                ? 'D.IDPerson in (' . $_SESSION['Filter']['Person'] . ')'
                : '';
        }

        if (isset($_SESSION['FilterSaved']['IDOperator']) && $_SESSION['FilterSaved']['IDOperator']) {
            $Vars['Person'] = 'D.IDUser=' . $_SESSION['FilterSaved']['IDOperator'];
            $_SESSION['Filter']['Operator'] = $_SESSION['FilterSaved']['IDOperator'];
        } else {
            $Vars['Operator'] = isset($_SESSION['Filter']['Operator']) && $_SESSION['Filter']['Operator'] > 0
                ? 'D.IDUser IN (' . $_SESSION['Filter']['Operator'] . ')'
                : (!$_SESSION['isAdmin']
                    ? (!empty($_SESSION['Rights']['Persons'])
                        ? 'D.IDUser IN (' . implode(',', $_SESSION['Rights']['Persons']) . ')'
                        : 'D.IDUser=-1')
                    : '');
        }

        if (isset($_SESSION['FilterSaved']['IDOrder']) && $_SESSION['FilterSaved']['IDOrder']) {
            $Vars['Order'] = 'D.IDOrder=' . $_SESSION['FilterSaved']['IDOrder'];
            $_SESSION['Filter']['Order'] = $_SESSION['FilterSaved']['IDOrder'];
        } else {
            $Vars['Order'] = isset($_SESSION['Filter']['Order']) && $_SESSION['Filter']['Order'] > 0
                ? 'D.IDOrder IN (' . $_SESSION['Filter']['Order'] . ')'
                : (!$_SESSION['isAdmin']
                    ? (!empty($_SESSION['Rights']['Orders'])
                        ? 'D.IDOrder IN (' . implode(',', $_SESSION['Rights']['Orders']) . ')'
                        : 'D.IDOrder=-1'
                    )
                    : '');
        }
        if (isset($_SESSION['FilterSaved']['TextOrder']) && $_SESSION['FilterSaved']['TextOrder'])
            $_SESSION['Filter']['TextOrder'] = $_SESSION['FilterSaved']['TextOrder'];

        if (isset($_SESSION['Filter']['TextOrder']) && $_SESSION['Filter']['TextOrder'] != '')
            $Vars['TextOrder'] = 'D.TextOrder LIKE "%' . addslashes($_SESSION['Filter']['TextOrder']) . '%"';

        if (isset($_SESSION['FilterSaved']['IDType']) && $_SESSION['FilterSaved']['IDType']) {
            $Vars['Type'] = 'D.IDType=' . $_SESSION['FilterSaved']['IDType'];
            $_SESSION['Filter']['Type'] = $_SESSION['FilterSaved']['IDType'];
        } else {
            $Vars['Type'] = isset($_SESSION['Filter']['Type']) && $_SESSION['Filter']['Type'] > 0
                ? 'D.IDType IN(' . $_SESSION['Filter']['Type'] . ')'
                : (!$_SESSION['isAdmin']
                    ? (!empty($_SESSION['Rights']['Types'])
                        ? 'D.IDType IN (' . implode(',', $_SESSION['Rights']['Types']) . ')'
                        : 'D.IDType=-1'
                    )
                    : '');
        }

        if (isset($_SESSION['FilterSaved']['TextType']) && $_SESSION['FilterSaved']['TextType'])
            $_SESSION['Filter']['TextType'] = $_SESSION['FilterSaved']['TextType'];

        if (isset($_SESSION['Filter']['TextType']) && $_SESSION['Filter']['TextType'] != '')
            $Vars['TextType'] = 'D.TextType LIKE "%' . addslashes($_SESSION['Filter']['TextType']) . '%"';

        if (isset($_SESSION['FilterSaved']['Sum']) && $_SESSION['FilterSaved']['Sum'])
            $_SESSION['Filter']['Sum'] = $_SESSION['FilterSaved']['Sum'];

        if (isset($_SESSION['Filter']['Sum']) && $_SESSION['Filter']['Sum'] != '')
            $Vars['Sum'] = 'D.Sum LIKE "%' . addslashes($_SESSION['Filter']['Sum']) . '%"';

        if (isset($_SESSION['FilterSaved']['Hours']) && $_SESSION['FilterSaved']['Hours'])
            $_SESSION['Filter']['Hours'] = $_SESSION['FilterSaved']['Hours'];

        if (isset($_SESSION['Filter']['Hours']) && $_SESSION['Filter']['Hours'] != '')
            $Vars['Hours'] = 'D.Hours LIKE "%' . addslashes($_SESSION['Filter']['Hours']) . '%"';

        if (isset($_SESSION['FilterSaved']['PlaceTaken']) && $_SESSION['FilterSaved']['PlaceTaken'])
            $_SESSION['Filter']['PlaceTaken'] = $_SESSION['FilterSaved']['PlaceTaken'];

        if (isset($_SESSION['Filter']['PlaceTaken']) && $_SESSION['Filter']['PlaceTaken'] != '')
            $Vars['PlaceTaken'] = 'D.PlaceTaken LIKE "%' . addslashes($_SESSION['Filter']['PlaceTaken']) . '%"';

        if (isset($_SESSION['FilterSaved']['PlaceDone']) && $_SESSION['FilterSaved']['PlaceDone'])
            $_SESSION['Filter']['PlaceDone'] = $_SESSION['FilterSaved']['PlaceDone'];

        if (isset($_SESSION['Filter']['PlaceDone']) && $_SESSION['Filter']['PlaceDone'] != '')
            $Vars['PlaceDone'] = 'D.PlaceDone LIKE "%' . addslashes($_SESSION['Filter']['PlaceDone']) . '%"';

        if (isset($_SESSION['FilterSaved']['Note']) && $_SESSION['FilterSaved']['Note'])
            $_SESSION['Filter']['Note'] = $_SESSION['FilterSaved']['Note'];

        if (isset($_SESSION['Filter']['Note']) && $_SESSION['Filter']['Note'] != '') {
            $str = explode(' ', $_SESSION['Filter']['Note']);
            $Vars['Note'] = array();
            foreach ($str as $k => $v) {
                $Vars['Note'][] = 'D.Note LIKE "%' . addslashes($v) . '%"';
            }
            $Vars['Note'] = '(' . implode(' OR ', $Vars['Note']) . ')';
        }

        if (isset($_SESSION['FilterSaved']['BookNote']) && $_SESSION['FilterSaved']['BookNote'])
            $_SESSION['Filter']['BookNote'] = $_SESSION['FilterSaved']['BookNote'];

        if (isset($_SESSION['Filter']['BookNote']) && $_SESSION['Filter']['BookNote'] != '')
            $Vars['BookNote'] = 'D.BookNote LIKE "%' . addslashes($_SESSION['Filter']['BookNote']) . '%"';

        if (isset($_SESSION['Filter']['TotalPrice']) && $_SESSION['Filter']['TotalPrice'] != '')
            $Vars['BookNote'] = 'D.TotalPrice=' . (float)$_SESSION['Filter']['TotalPrice'];

        if (isset($_SESSION['Filter']['PriceNote']) && $_SESSION['Filter']['PriceNote'] != '')
            $Vars['BookNote'] = 'D.PriceNote LIKE "%' . addslashes($_SESSION['Filter']['PriceNote']) . '%"';

        foreach ($Vars as $k => $v)
            if ($v == '') unset($Vars[$k]);

        return $Vars;
    }

    function getUpdateDiff($Obj) {
        $Diffs = array();
        foreach ($this->Fields as $Var) {
            if ($Var == 'AddDate' || $Var == 'Status' || $Var == 'IDUser' || $Var == 'Changes') continue;

            $New = $this->{'get' . $Var}();
            $Old = $Obj->{'get' . $Var}();

            if ($Old != $New) {
                if ($Var == 'RemindTo' || $Var == 'IDPerson') {
                    if ($Old != 0) {
                        $Old = Users::getById($Old);
                        $Old = $Old->getLogin();
                    }

                    if ($New != 0) {
                        $New = Users::getById($New);
                        $New = $New->getLogin();
                    }
                } elseif ($Var == 'Hidden') {
                    if ((int)$New == $Old) continue;
                } elseif ($Var == 'IDType') {
                    if ($Old != 0) {
                        $Old = Types::getById($Old);
                        $Old = $Old->getCode();

                        $New = Types::getById($New);
                        $New = $New->getCode();
                    }
                } elseif ($Var == 'IDOrder') {
                    if ($Old != 0) {
                        $Old = Orders::getById($Old);
                        $Old = $Old->getCode();

                        $New = Orders::getById($New);
                        $New = $New->getCode();
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

    function Export() {
        $Data = $this->getDataList();

        if (!empty($Data)) {
            foreach ($Data as $k => $v) {
                if ($v['Sum'] < 0) $Data[$k]['Sum'] *= -1;
            }
            $Data['__template'] = 'ExcelRow';
            $xml = Template::Process('Excel', array('Data' => $Data));
        } else return Language::$Data['NoDataToExport'];

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=Data.xml;");
        header("Content-Type: application/ms-excel");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $xml;
        exit();
    }

    function clearDefaultValues($arr) {
        foreach ($arr as $k => $v) {
            switch ($k) {
                case 'RemindDate':
                    if ($v == Language::$Data['Reminder']) $arr[$k] = '';
                    break;
                case 'Date':
                    if ($v == Language::$Data['Date']) $arr[$k] = '';
                    break;
                case 'IDDoc':
                    if ($v == Language::$Data['IDDoc']) $arr[$k] = '';
                    break;
                case 'Note':
                    if ($v == Language::$Data['Notes']) $arr[$k] = '';
                    break;
                case 'BookNote':
                    if ($v == Language::$Data['BookNotes']) $arr[$k] = '';
                    break;
                case 'PlaceDone':
                    if ($v == Language::$Data['PlaceDone']) $arr[$k] = '';
                    break;
                case 'PlaceTaken':
                    if ($v == Language::$Data['PlaceTaken']) $arr[$k] = '';
                    break;
                case 'Sum':
                    if ($v == Language::$Data['Sum']) $arr[$k] = '';
                    break;
                case 'Hours':
                    if ($v == Language::$Data['Hours']) $arr[$k] = '';
                    break;
                case 'TextOrder':
                    if ($v == Language::$Data['OrderText']) $arr[$k] = '';
                    break;
                case 'TextType':
                    if ($v == Language::$Data['TypeText']) $arr[$k] = '';
                    break;
                case 'PriceNote':
                    if ($v == Language::$Data['PriceNote']) $arr[$k] = '';
                    break;
            }
        }
        return $arr;
    }

    function getTplAsOpt() {
        $query = 'SELECT BookNote, ID FROM Data WHERE Status=10 ORDER BY BookNote';
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        $Opts = '';
        while ($row = $result->fetch_assoc()) {
            $Opts .= '<option value="' . $row['ID'] . '">' . $row['BookNote'] . '</option>';
        }

        return $Opts;
    }

    /**
     * DB functions
     */
    function Save() {
        $_POST = $this->clearDefaultValues($_POST);

        $this->SaveNoliktava($_POST);

        $this->fetchObject($_POST);
        if (($this->getRemindDate() != '0000-00-00 00:00:00' && $this->getRemindDate() != '2000-00-00 00:00:00')
            && !is_numeric($this->getRemindTo())
        )
            Error::setError(get_class(), 'RemindDate', 'Set remind to ' . $this->getRemindTo());
        elseif ($this->getRemindDate() == '0000-00-00 00:00:00' || $this->getRemindDate() == '2000-00-00 00:00:00')
            $this->setRemindTo(0);

        if ($this->TrimDate($this->getRemindDate()) == '00:00:00') {
            $this->setAllDays(1);
        }

        $Err = Error::getErrors(get_class($this));
        if ($_POST['Tpl'] == 1) unset($Err);

        if (empty($Err)) {
            if ($this->getID() == 0) $this->Add();
            else {
                if ($_SESSION['User']->getStatus() < 99) {
                    if ($this->getAdminEdit() == "1") {
                        if ($_POST['pass'] != Config::EDIT_PASS) {
                            return "Nepareiza parole";
                        }
                    }
                }

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
        if ($_SESSION['User']->getStatus() < 99) {
            $this->AdminEdit  = 0;
        }

        if ($_POST[IDType] == Config::Noliktava) {
            $res = $this->ceckArtikuls($_POST[PlaceTaken]);
            if ($res == 1) {
                return print "Vienadi artukuli.";
            }
            $this->setAdminEdit('1');
        }

        $query = 'INSERT INTO `Data`
                     SET `IDDoc`="' . addslashes($this->getIDDoc()) . '",
                         `IDUser`=' . $_SESSION['User']->getID() . ',
                         `IDOrder`=' . (int)$this->getIDOrder() . ',
                         `TextOrder`="' . addslashes($this->getTextOrder()) . '",
                         `IDType`=' . (int)$this->getIDType() . ',
                         `TextType`="' . addslashes($this->getTextType()) . '",
                         `Sum`=' . (float)$this->getSum() . ',
                         `Hours`=' . (float)$this->getHours() . ',
                         `PlaceTaken`="' . addslashes($this->getPlaceTaken()) . '",
                         `PlaceDone`="' . addslashes($this->getPlaceDone()) . '",
                         `IDPerson`=' . (int)$this->getIDPerson() . ',
                         `Note`="' . addslashes($this->getNote()) . '",
                         `Date`="' . $this->getDate() . '",
                         `BookNote`="' . addslashes($this->getBookNote()) . '",
                         `TotalPrice`=' . (float)$this->getTotalPrice() . ',
                         `PriceNote`="' . addslashes($this->getPriceNote()) . '",
                         `AddDate`=NOW(),
                         `RemindDate`="' . $this->getRemindDate() . '",
                         `RemindDateEnd`="' . $this->getRemindDateEnd() . '",
                         `RemindTo`="' . (int)$this->getRemindTo() . '",
                         `Hidden`="' . (int)$this->getHidden() . '",
                         `allDay`="' . (int)$this->getallDay() . '",
                         `AdminEdit`="' . (int)$this->getAdminEdit() . '",
                         `Status`=' . ($_POST['Tpl'] == 1 ? '10' : '1');

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
        }

        $this->setID(self::$DB->insert_id);

        $type = (int)$this->getIDType();
        if ($type == Config::Noliktava) {
            Warehous::AddNew($this->getID(), $this->getSum());
        }

        if ($type == Config::AddNoliktava || $type == Config::DelNoliktava || $type == Config::ReturnNoliktava || $type == Config::ReservNoliktava) {
            $NolDat = array(
                "rindasID" => $this->getID(), "detalasID" => $_POST['detalasID'], "daudzums" => $_POST['daudzums'], "order" => (int)$this->getIDOrder()
            );

            $this->NoliktavaSave($NolDat);
        }
        return $this->getID();
    }

    function Update() {
        $allDay = $this->getAllDays();

        if ($allDay == 1) {
            $allDay = ($allDay == 1) ? true : false;
        } else {
            $allDay = ($allDay == "true") ? 1 : 0;
        }
        $query = 'UPDATE `Data` SET
                         `IDDoc`="' . addslashes($this->getIDDoc()) . '",
                         `IDUser`=' . $_SESSION['User']->getID() . ',
                         `IDOrder`=' . (int)$this->getIDOrder() . ',
                         `TextOrder`="' . addslashes($this->getTextOrder()) . '",
                         `IDType`=' . (int)$this->getIDType() . ',
                         `TextType`="' . addslashes($this->getTextType()) . '",
                         `Sum`=' . (float)$this->getSum() . ',
                         `Hours`=' . (float)$this->getHours() . ',
                         `PlaceTaken`="' . addslashes($this->getPlaceTaken()) . '",
                         `PlaceDone`="' . addslashes($this->getPlaceDone()) . '",
                         `IDPerson`=' . (int)$this->getIDPerson() . ',
                         `Note`="' . addslashes($this->getNote()) . '",
                         `Date`="' . $this->getDate() . '",
                         `BookNote`="' . addslashes($this->getBookNote()) . '",
                         `TotalPrice`=' . (float)$this->getTotalPrice() . ',
                         `PriceNote`="' . addslashes($this->getPriceNote()) . '",
                         `RemindDate`="' . $this->getRemindDate() . '",
                         `RemindDateEnd`="' . $this->getRemindDateEnd() . '",
                         `RemindTo`="' . (int)$this->getRemindTo() . '",
                         `Changes`="' . addslashes($this->getChanges()) . '",
                         `AdminEdit`="' . (int)$this->getAdminEdit() . '",
                         `allDay`="' . $allDay . '",
                         `Hidden`="' . (int)$this->getHidden() . '"

                   WHERE `ID`=' . (int)$this->getID();
        if (!self::$DB->query($query)) {
            throw new Error('Update error on Data (' . __LINE__ . ')');
        }

        $type = (int)$this->getIDType();

        if ($type == Config::AddNoliktava || $type == Config::DelNoliktava || $type == Config::ReturnNoliktava || $type == Config::ReservNoliktava) {
            $NolDat = array("rindasID" => $this->getID(), "detalasID" => $_POST['detalasID'], "daudzums" => $_POST['daudzums'], "order" => (int)$this->getIDOrder());

            $this->NoliktavaSave($NolDat);
        }
    }

    function Delete() {
        $Status = self::$url[2] == 'Restore' ? 1 : -1;

        if ($this->getStatus() == -1 && $Status == -1) {
            if ($_POST['pass'] != Config::DEL_PASS) return Language::$Main['WrongDelPass'];
            $query = 'DELETE FROM `Data` WHERE `ID`=' . $this->getID();
        } else {
            $Changes = unserialize($this->getChanges());
            $Changes[date('Y-m-d H:i:s')] = array('User' => $_SESSION['User']->getID() . ' ' . $_SESSION['User']->getLogin(), 'Status' => $Status);
            ksort($Changes);
            $Changes = array_reverse($Changes);

            $query = 'Update `Data`
                            SET `Status`=' . $Status . ',
                            Changes="' . addslashes(serialize($Changes)) . '" WHERE `ID`=' . $this->getID();
        }

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Data (' . __LINE__ . ')');
        }
        return 1;
    }

    static function getById($ID) {
        $query = 'SELECT *, DATE_FORMAT(`Date`,"%Y-%m-%d-%H-%i") as `Date`
                    FROM `Data` WHERE `ID`=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data ' . __LINE__);
        }
        return self::fetchObject($result, new self);
    }

    static function getRow($ID) {
        $query = 'SELECT D.*,
                         DATE_FORMAT(`Date`,"%y.%m.%d %H:%i") as `Date`,
                         DATE_FORMAT( D.`RemindDateEnd` , "%y.%m.%d %H:%i" ) AS `RemindDateEnd`,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `DateShow`,
                         DATE_FORMAT(D.`AddDate`,"%y.%m.%d %H:%i") as `AddDate`,
                         DATE_FORMAT(D.`RemindDate`,"%y.%m.%d %H:%i") as `RemindDate`,
                         `RemindDate` as RemindDateStamp,
                         P.Login as Person, U.Login as User, R.Login as RemindTo,
                         O.Code as `Order`, T.Code as Type
                    FROM `Data` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)
                   WHERE D.ID=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        $row = $result->fetch_assoc();
        $now = strtotime(date('Y-m-d H:i:00'));

        if ($row['IDType'] == 61) $row['dblClick'] = 'getSupplier(this);';
        if ($row['IDType'] == 72) $row['dblClick'] = 'getPavadzime(this);';
        if ($row['Noma'] == 1) $row['dblClick'] = 'MakeNoma(this);';

        if ($row['IDType'] == Config::Noliktava) $row['dblClick'] = "OpenForm('GetVeikals','DialogForm','scrollDiv','Prece','1500'," . $row['ID'] . ");";

        if ($row['IDType'] == Config::AddNoliktava) $row['dblClick'] = 'getNoliktava(this,1); addNoliktavaAutoComp();';
        if ($row['IDType'] == Config::DelNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
        if ($row['IDType'] == Config::ReservNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';
        if ($row['IDType'] == Config::ReturnNoliktava) $row['dblClick'] = 'getNoliktava(this,2); addNoliktavaAutoComp();';

        if (in_array($row['ID'], $_SESSION['CechedRow'])) {
            $row['checked'] = 'checked';
            $row['Function'] = "UnCheckRow";
            $row['select'] = "selected";
        }
        $row['Function'] = "CeckRow";
        $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
        $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
        $row['HiddenClass'] = $row['Hidden'] == 1 ? 'hidden' : '';
        $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';
        $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';
        $row['AdminEditClass'] = $row['AdminEdit'] == 1 ? 'AdminEdit' : '';
        if ($row['RemindDate'] == '00.00.00 00:00')
            $row['RemindDate'] = '';
        else {
            if (strtotime($row['RemindDateStamp']) < $now)
                $row['reminderColor'] = 'red';
            else $row['reminderColor'] = 'green';
        }

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

    function setIDDoc($value) {
        if ($value == '') throw new Error(Language::$Data['SetIDDoc']);
        $this->IDDoc = $value;
    }

    function setDate($value) {
        $value = str_replace(array('-', ',', ' ', ':'), array('.', '.', '.', '.'), $value);
        $Date = explode('.', $value);

        if ($Date[0] < 1000) $Date[0] += 2000;
        if (!$Date[3]) $Date[3] = '00';
        if (!$Date[4]) $Date[4] = '00';
        if (
            !is_numeric($Date[0]) || !is_numeric($Date[1]) || !is_numeric($Date[2])
            || !is_numeric($Date[3]) || !is_numeric($Date[4])
            || $Date[3] > 23 || $Date[4] > 59 || $Date[1] > 12 || $Date[3] > 31 || $Date[0] > 2099
        )
            throw new Error(Language::$Data['WrongDateFormat']);
        else $this->Date = $Date[0] . '-' . $Date[1] . '-' . $Date[2] . ' ' . $Date[3] . ':' . $Date[4];
    }

    function setRemindDate($value) {
        if ($value == '') {
            $this->RemindDate = '2000-00-00 00:00:00';
            return;
        }

        $value = str_replace(array('-', ',', ' ', ':'), array('.', '.', '.', '.'), $value);
        $Date = explode('.', $value);

        if ($Date[0] < 1000) $Date[0] += 2000;
        if (!$Date[3]) $Date[3] = '00';
        if (!$Date[4]) $Date[4] = '00';
        if (
            !is_numeric($Date[0]) || !is_numeric($Date[1]) || !is_numeric($Date[2])
            || !is_numeric($Date[3]) || !is_numeric($Date[4])
            || $Date[3] > 23 || $Date[4] > 59 || $Date[1] > 12 || $Date[3] > 31 || $Date[0] > 2099
        )
            throw new Error(Language::$Data['WrongDateFormat']);
        else $this->RemindDate = $Date[0] . '-' . $Date[1] . '-' . $Date[2] . ' ' . $Date[3] . ':' . $Date[4] . ':00';
    }

    function setIDPerson($value) {
        $value = (int)$value;
        if ($value == 0) throw new Error(Language::$Data['SetIDPerson']);
        else $this->IDPerson = $value;
    }

    function setIDOrder($value) {
        $value = (int)$value;
        if ($value == 0) throw new Error(Language::$Data['SetIDOrder']);
        else $this->IDOrder = $value;
    }

    function setIDType($value) {
        $value = (int)$value;
        if ($value == 0) throw new Error(Language::$Data['SetIDType']);
        else $this->IDType = $value;
    }

    function setSum($v) {
        $this->Sum = str_replace(',', '.', $v);
    }

    function setHours($v) {
        $this->Hours = str_replace(',', '.', $v);
    }

    function setTotalPrice($v) {
        $this->TotalPrice = str_replace(',', '.', $v);
    }

    function Open($ID) {
        $query = 'SELECT D.*,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `DateShow`,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `Date`,
                         DATE_FORMAT(D.`AddDate`,"%y.%m.%d %H:%i") as `AddDate`,
                         DATE_FORMAT(D.`RemindDate`,"%y.%m.%d %H:%i") as `RemindDate`,
                         `RemindDate` as RemindDateStamp,
                         P.Login as Person, U.Login as User, R.Login as RemindTo,
                         O.Code as `Order`, T.Code as Type
                    FROM `Data` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)
               where D.ID = ' . $ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $Dati['ID'] = $row['ID'];
            $Dati['IDDoc'] = $row['IDDoc'];
            $Dati['IDUser'] = $row['IDUser'];
            $Dati['IDOrder'] = $row['IDOrder'];
            $Dati['TextOrder'] = $row['TextOrder'];
            $Dati['IDType'] = $row['IDType'];
            $Dati['TextType'] = $row['TextType'];
            $Dati['Sum'] = $row['Sum'];
            $Dati['Hours'] = $row['Hours'];
            $Dati['PlaceTaken'] = $row['PlaceTaken'];
            $Dati['PlaceDone'] = $row['PlaceDone'];
            $Dati['IDPerson'] = $row['IDPerson'];
            $Dati['Note'] = $row['Note'];
            $Dati['Date'] = $row['Date'];
            $Dati['BookNote'] = $row['BookNote'];
            $Dati['TotalPrice'] = $row['TotalPrice'];
            $Dati['PriceNote'] = $row['PriceNote'];
            $Dati['AddDate'] = $row['AddDate'];
            $Dati['RemindDate'] = $row['RemindDate'];
            $Dati['RemindDateEnd'] = $row['RemindDateEnd'];
            $Dati['RemindTo'] = $row['RemindTo'];
            $Dati['Status'] = $row['Status'];
            $Dati['Hidden'] = $row['Hidden'];
            $Dati['Changes'] = $row['Changes'];
            $Dati['DateShow'] = $row['DateShow'];
            $Dati['Date'] = $row['Date'];
            $Dati['AddDate'] = $row['AddDate'];
            $Dati['RemindDate'] = $row['RemindDate'];
            $Dati['RemindDateStamp'] = $row['RemindDateStamp'];
            $Dati['Person'] = $row['Person'];
            $Dati['User'] = $row['User'];
            $Dati['RemindTo'] = $row['RemindTo'];
            $Dati['Order'] = $row['Order'];
            $Dati['Type'] = $row['Type'];
        }

        return Template::Process('Form', $Dati);
    }

    function GetUserByName($name) {
        $query = "SELECT ID FROM `Users` WHERE `Name`='" . $name . "'";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        return $result;
    }

    function MyEntry($ID) {
        $query = "SELECT IDUser FROM `Data` WHERE `ID`='" . $ID . "'";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row['IDUser'];
        }
        if ($_SESSION['User']->getStatus() < 3) {
            return "0";
        }

        if ($_SESSION['User']->getStatus() >= 5) {
            return "1";
        } else {
            if ($results == $_SESSION['User']->getID()) {
                return "1";
            }

            if ($ID == 0) {
                return "1";
            }
        }
    }

    function MyAdminEntry($ID) {
        $query = "SELECT IDUser FROM `Data` WHERE `ID`='" . $ID . "'";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row['IDUser'];
        }
        if ($_SESSION['User']->getStatus() < 3) {
            return "0";
        }

        if ($_SESSION['User']->getStatus() >= 5) {
            return "1";
        } else {
            if ($results == $_SESSION['User']->getID()) {
                return "1";
            }

            if ($ID == 0) {
                return "1";
            }
        }
    }

    function AutocompliteJosn($text) {
        $vowels = array("ē", "ū", "ī", "ā", "š", "ģ", "ķ", "ļ", "ž", "č", "ņ", "Ē", "Ū", "Ī", "Ā", "Š", "Ģ", "Ķ", "Ļ", "Ž", "Č", "Ņ");
        $text = str_replace($vowels, "%", $text);
        $query = "select ID, Nosaukums as label from sanemeji WHERE Nosaukums LIKE '%" . $text . "%' AND Status = 0 ";
        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Data (' . __LINE__ . ')');

        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        $rez = json_encode($results);
        $rez = str_replace('%22', "%27%27", $rez);
        $rez = rawurldecode($rez);
        echo $rez;
    }

    function CeckRow($ID) {
        $_SESSION['CechedRow'][$ID] = $ID;
    }

    function UnCeckRow($ID) {
        unset($_SESSION['CechedRow'][$ID]);
    }

    function photoTagger($ID) {
        $query = "SELECT * FROM photo_tagger WHERE photoid =" . $ID;

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Data (' . __LINE__ . ')');

        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        return $results;
    }

    function SavephotoTagger() {
        $query = 'INSERT INTO `photo_tagger`
                     SET `photoid`="' . $_GET['photoID'] . '",
                         `y`=' . $_GET['y'] . ',
                         `width`=' . $_GET['width'] . ',
                         `height`="' . $_GET['height'] . '",
                         `message`="' . $_GET['message'] . '",
                         `x`="' . $_GET['x'] . '"';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
        }
        $this->setID(self::$DB->insert_id);
        return $this->getID();
    }

    function DeletephotoTagger($ID) {
        $query = 'DELETE FROM photo_tagger WHERE id=' . $ID;

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
        }
        $this->setID(self::$DB->insert_id);
        return $this->getID();
    }

    function DataByID($ID, $colum) {
        $query = "SELECT " . $colum . " FROM Data WHERE ID=" . $ID;
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error with Data class Function DataByID On Line:(' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row[$colum];
        }
        return $results;
    }

    function NolByID($ID, $colum) {
        $query = "SELECT " . $colum . " FROM `noliktava` WHERE rindasID=" . $ID;
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row[$colum];
        }
        return $results;
    }

    function noliktava_exist($ID) {
        $query = "SELECT ID AS SuperID, rindasID, detalasID, daudzums, Shop, ShopTitle, ShopDescription, ShopModelID, ShopCategoryID FROM noliktava WHERE rindasID='" . $ID . "'";

        $result = self::$DB->query($query);
        if ($result->num_rows == 0) {
            $results = array();
        } else {
            $results = $result->fetch_assoc();
            $daudzums = $results['daudzums'];
            $rindasID = $results['rindasID'];
            $detalasID = $results['detalasID'];
            $results[daudzums] = $daudzums;
            $results[rindasID] = $rindasID;
            $results[detalasID] = $detalasID;
        }

        $query2 = "SELECT PriceNote as mervieniba, PlaceTaken as nosaukums, Hours, TotalPrice as atlikums, PlaceDone, Note, BookNote FROM Data WHERE ID='" . $detalasID . "'";
        $result2 = self::$DB->query($query2);
        $results2 = $result2->fetch_assoc();

        $results = $results + $results2;
        echo json_encode($results);
    }

    function noliktavaDialog($ID) {
        $query = "SELECT ID AS SuperID, rindasID, detalasID, daudzums, Shop, ShopTitle, ShopDescription, ShopModelID, ShopCategoryID, OrginalCode, addition, offer, state, used  FROM noliktava WHERE rindasID='" . $ID . "'";

        $result = self::$DB->query($query);

        if ($result->num_rows == 0) {
            $results = array();
        } else {
            $results = $result->fetch_assoc();
            $daudzums = $results['daudzums'];
            $rindasID = $results['rindasID'];
            $detalasID = $results['detalasID'];
            $results['daudzums'] = $daudzums;
            $results['Kategorijas'] = Data::categoryMaker($results['ShopCategoryID']);

            $a = array("Nav pieejams", "Pieejams", "Pasūtāms");
            $i = 0;
            foreach ($a as $e) {
                $i == $results['state'] ? $selected = 'selected="selected"' : $selected = "";
                $piejamiba[] = "<option " . $selected . " value='" . $i . "'>" . $e . "</option>";
                $i++;
            }
            $piejams = implode("\n", $piejamiba);
            $results['piejams']  = $piejams;

            $results['rindasID'] = $rindasID;
            $results['ShopModel'] = Data::HTMLGrupas($results[ShopModelID], $results['SuperID']);
            $results['offer'] == 0 ? $results['offer'] = "" : $results['offer'] = 'checked="yes"';
            $results['used'] == 0 ?  $results['used'] = "" : $results['used'] = 'checked="yes"';
            $results['Shop'] == 0 ?  $results['Shop'] = "" : $results['Shop'] = 'checked="yes"';
            $results['detalasID'] = $detalasID;
        }

        $query2 = "SELECT PriceNote, PlaceTaken, Hours, TotalPrice, PlaceDone, Note, BookNote, AdminEdit FROM Data WHERE ID='" . $ID . "'";
        $result2 = self::$DB->query($query2);
        $results2 = $result2->fetch_assoc();

        $results = $results + $results2;

        return $results;
    }

    function categoryMaker($ID) {
        $query = "SELECT * FROM categories_linear Order by iorder ASC";
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error with Data class Function categoryMaker On Line:(' . __LINE__ . ')');
        }
        $results = array();
        while ($row = $result->fetch_assoc()) {
            $atstarpe = "";
            for ($i = 1; $i < $row[level]; $i++) {
                $atstarpe = "&nbsp; ";
                $atstarpe = $atstarpe . $atstarpe;
            }
            $row['id'] == $ID ? $selected = 'selected="selected"' : $selected = "";
            $results[] = '<option ' . $selected . ' value="' . $row['ID'] . '">' . $atstarpe . $row[title] . '</option>';
        }
        return implode("\n", $results);
    }

    function noliktava_atlikums($ID) {
        $query = "SELECT ID AS detalasID,PlaceTaken AS artikuls, Note AS nosaukums, TotalPrice AS atlikums, PriceNote AS mervieniba, Data.Hours AS rezervets FROM Data WHERE ID=" . $ID;
        $result = self::$DB->query($query);
        return $result->fetch_assoc();
    }

    function SaveDetala($data) {
        if ($_SESSION['User']->getStatus() < 99) {
            if ($_POST['AdminEdit'] == "1") {
                if ($_POST['pass'] != Config::EDIT_PASS) {
                    return "Nepareiza parole";
                }
            }
        }

        $query = 'UPDATE `noliktava` SET
                         `detalasID`="' . $data[detalasID] . '",
                         `daudzums`="' . $data[daudzums] . '",
                         `type`="1",
                         `Shop`="' . (int)$data[Shop] . '",
                         `ShopTitle`="' . $data[ShopTitle] . '",
                         `ShopDescription`="' . $data[ShopDescription] . '",
                         `ShopModelID`="' . $data[ShopModelID] . '",
                         `ShopCategoryID`="' . $data[ShopCategoryID] . '",
                         `OrginalCode`="' . $data[OrginalCode] . '",
                         `addition`="' . $data[addition] . '",
                         `offer`="' . (int)$data[offer] . '",
                         `state`="' . $data[state] . '",
                         `used`="' . (int)$data[used] . '"
                   WHERE `rindasID`=' . $data[rindasID];

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
        }

        if ($data[SuperID] == 0) {
            $query = 'INSERT INTO `noliktava` (`rindasID`,`detalasID`,`daudzums`,`type`,`Shop`,`ShopTitle`,`ShopDescription`,`ShopModelID`,`ShopCategoryID`,`OrginalCode`,`addition`,`offer`,`state`,`used`) VALUES (' . $data[rindasID] . ',"' . $data[detalasID] . '","' . $data[daudzums] . '",1,"' . $data[Shop] . '","' . $data[ShopTitle] . '","' . $data[ShopDescription] . '","' . $data[ShopModelID] . '","' . $data[ShopCategoryID] . '","' . $data[OrginalCode] . '","' . $data[addition] . '","' . $data[offer] . '","' . $data[state] . '","' . $data[used] . '")';

            if (!self::$DB->query($query)) {
                throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
            }
        }

        $this->setID(self::$DB->insert_id);
        $Data = $this->getRow($data[rindasID]);
        return self::ArrayToJson(array(1, Template::Process('Row', $Data)));
    }

    function NoliktavaSave($data) {
        $type = $this->DataByID($data[rindasID], 'IDType');
        $title = substr($this->DataByID($data[detalasID], 'Note'), 0, 25) . " " . $this->DataByID($data[detalasID], 'PlaceTaken');
        $vienibas = $this->DataByID($data[detalasID], 'PriceNote');
        $sum = ($this->NolByID($data[detalasID], 'daudzums')) * $data[daudzums];

        $query = "select ID from noliktava where rindasID =" . $data[rindasID];
        $result = self::$DB->query($query);
        if ($result->num_rows == 0) {
            $query = "INSERT INTO `noliktava` (`rindasID`, `detalasID`, `daudzums`) VALUES (" . $data[rindasID] . ", " . $data[detalasID] . ", " . $data[daudzums] . ")";
            if (!self::$DB->query($query)) {
                throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
            }
        } else {
            $query = "UPDATE `noliktava` SET `rindasID`= " . $data[rindasID] . ", `detalasID`=" . $data[detalasID] . ", `daudzums`=" . $data[daudzums] . " WHERE rindasID =" . $data[rindasID];
            if (!self::$DB->query($query)) {
                throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
            }
        }

        //Tiek izdota prece
        Warehous::izdot($data[detalasID], $data[rindasID], $data[daudzums], $vienibas, $title, $type, $data[order], $sum);
    }

    /**
     * Pārliecinas vai ir ievadītas preces vērtības
     *
     * @return Error
     * @param array $Data
     * @author Jānis
     */
    function SaveNoliktava($Data) {
        if ($data['IDType'] == Config::AddNoliktava || $data['IDType'] == Config::DelNoliktava) {
            $value = $Data['detalasID'];
            $value = (int)$value;
            if ($value == 0) throw new Error(Language::$Data['SetIDDetaļas']);

            $value2 = $Data['daudzums'];
            $value2 = (int)$value2;
            if ($value2 == 0) throw new Error(Language::$Data['SetSkaits']);
        }
    }

    function ChangeSelected($data) {
        foreach ($_SESSION['CechedRow'] as $k => $v) {
            $OldID = $k;
            $ID = $OldID;
            if ($data['copy'] == 1) {
                if ($k > 0) {
                    $dati = $this->getRow($OldID);
                    $dati['ID'] = 0;
                    $dati['AddDate'] =  date("y-m-d H:i:s");
                    $dati['Date'] =  date("y-m-d H:i:s");
                    $dati['IDOrder'] = $data['value'];
                    $_POST = $dati;
                    $ID = $this->Save();

                    unset($_SESSION['CechedRow'][$OldID]);
                    $_SESSION['CechedRow'][$ID] = $ID;
                }
            } else {
                if ($k > 0) {
                    if ($data['position'] == "left")  $position = "concat('" . $data['value'] . "'," . $data['fields'] . ")";
                    if ($data['position'] == "right") $position = "concat(" . $data['fields'] . ",'" . $data['value'] . "')";
                    if ($data['position'] == "replace") $position = "'" . $data['value'] . "'";
                    $query = "UPDATE `Data` SET `IDUser`=" . $_SESSION['User']->getID() . ", `" . $data['fields'] . "` = " . $position . " WHERE ID = " . $ID;
                    if (!self::$DB->query($query)) {
                        throw new Error('Write error on Data (' . __LINE__ . ') : ' . self::$DB->error);
                    }
                }
            }
        }

        return  1;
    }

    function ceckArtikuls($text) {
        $query = "SELECT PlaceTaken FROM Data WHERE `IDType`  = " . Config::Noliktava . " AND Status = 1 AND PlaceTaken = '" . $text . "'";
        $result = self::$DB->query($query);
        return $result->num_rows == 0 ? 0 : 1;
    }

    function FormSave() {
        $Data = $this->getRow($_POST[ID]);
        $Data2 = $_POST;
        $Data['pass'] = $Data2['pass'];

        foreach ($Data as $key => $value) {
            foreach ($Data2 as $key2 => $value2) {
                if ($key == "IDDoc") {
                    if ($value == "") {
                        $Data[IDDoc] = "MadeBySystem";
                    }
                }
                if ($key == $key2) {
                    $Data[$key] = $value2;
                }
            }
        }

        // Ja rinda ir detaļa un viņa ir veikalā tipa piezīmēs ievada V un ja viņai ari ir pievienota rindas bilde B

        if ($Data['IDType'] == 2362) {
            if ($Data2['Shop'] == 1) {
                $TextType = 'V';
                // add faili image function
                $fileFnExists = false;
                if (file_exists("faili/sysapi.php")) {
                    require_once "faili/sysapi.php";
                    $fileFnExists = true;
                }

                if ($fileFnExists) $link = _faili_row_file_exists($Data[ID]);

                if ($link != NULL) {
                    $TextType = 'V B';
                }
            } else {
                $TextType = '';
            }

            $Data['TextType'] = $TextType;
        }

        $_POST = $Data;

        return $this->Save();
    }

    function AddAllSelected() {
        $Filter = $this->getFilter();
        $Sort = 'D.RemindDate DESC ';
        $query = 'SELECT D.ID
                    FROM `Data` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)
               ';

        if (!$_SESSION['isAdmin']) {
            $where = ' WHERE D.`Status`=1 ' . (!empty($Filter) ? ' AND ( (' . implode(' AND ', $Filter) . ') )'                // $query .= ' WHERE D.`Status`=1 '.(!empty($Filter) ? ' AND ( ('.implode(' AND ',$Filter).') OR D.RemindTo='.$_SESSION['User']->getID().')'
                : ' OR D.RemindTo=' . $_SESSION['User']->getID()) . ' AND D.Hidden = 0 ';
        } else {
            $where = !empty($Filter) ? 'WHERE ' . implode(' AND ', $Filter) : '';
        }

        if (!empty($_SESSION['Filter']['Search'])) {
            $search = explode(' ', trim($_SESSION['Filter']['Search']));
            $str = array();
            foreach ($search as $k => $v) $str[] = '(
                     D.IDDoc LIKE ("%' . $v . '%") OR
                     D.TextOrder LIKE ("%' . $v . '%") OR
                     D.TextType LIKE ("%' . $v . '%") OR
                     D.PlaceTaken LIKE ("%' . $v . '%") OR
                     D.PlaceDone LIKE ("%' . $v . '%") OR
                     D.Note LIKE ("%' . $v . '%") OR
                     D.BookNote LIKE ("%' . $v . '%") OR
                     D.PriceNote LIKE ("%' . $v . '%") OR
                     P.Login LIKE ("%' . $v . '%") OR
                     U.Login LIKE ("%' . $v . '%") OR
                     O.Code LIKE ("%' . $v . '%") OR
                     T.Code LIKE ("%' . $v . '%")
                     )';
            if ($_POST['FindDeleted'] != 1) {
                $str[] = 'D.Status=1';
            }

            $str = implode(' AND ', $str);

            if (!empty($str))
                $where .= ' AND ' . $str;
        }

        if (!empty($_SESSION['CechedRow'])) {
            $CechedRow = ' (' . implode(',', $_SESSION['CechedRow']) . ')';
            $where .= " AND D.ID NOT IN" . $CechedRow;
        }

        $query .= $where;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }
        while ($row = $result->fetch_assoc()) {
            $_SESSION['CechedRow'][$row['ID']] = $row['ID'];
        }

        return print $query;
    }

    function PrecuGrupas($ID) {
        $IDD = $ID . '0';
        $ID = explode(",", $IDD);
        $query = "SELECT * FROM groups_linear ORDER BY FIELD(id, " . $IDD . ") DESC";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            if ($i % 2 == 0) {
                $row['Odd'] = 'Odd';
                $i++;
            } else {
                $row['Odd'] = '';
                $i++;
            }

            if (in_array($row['id'], $ID)) {
                $row['selected'] = 'checked="yes"';
            } else {
                $row['selected'] = "";
            }

            $rows[] = $row;
        }
        return Template::Process('/Dialog/PrecuGrupasRow', $rows);
    }

    function HTMLGrupas($ID, $form) {
        $query = "SELECT * FROM groups_linear WHERE id IN (" . $ID . "0)";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }
        while ($row = $result->fetch_assoc()) {
            $rows .= "<h6 style='margin: 2px;'>" . $row['title'] . "</h6>";
        }
        if ($ID == "") {
            $ID = 0;
        }
        return "<span onclick=\"Javasript:OpenForm('PrecuGrupas','GrupasMenu" . $form . "','DialogForm','Grupas','530','" . $ID . "')\"><span style=\"text-decoration: underline; cursor: pointer; color: blue;\">Modeļi:</span> " . $rows . "</span>";
    }

    function TrimDate($date) {
        $parts = explode(' ', $date);
        return isset($parts[1]) ? $parts[1] : '';
    }
}
