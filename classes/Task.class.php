<?php

date_default_timezone_set('Europe/Riga');

class Task extends DBObject {
    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Josn':
                echo $this->Josn();
                break;
            case 'TaskUser':
                $_SESSION['TaskUser'] = $_POST['id'];
                break;
            case 'SaveTaskPlace':
                $_SESSION['TaskSkats'] = $_POST['skats'];
                $_SESSION['TaskGads'] = $_POST['gads'];
                $_SESSION['TaskMenesis'] = $_POST['menesis'];
                $_SESSION['TaskDina'] = $_POST['dina'];
                break;
            case 'Save':
                if (!$_SESSION['User']) return '';
                if ($_SESSION['User']->getStatus() < 5) return '';

                $ID = $this->Save();
                return $ID;
            case 'Changes':
                return $this->getChangeList($_POST['ID']);
                break;
            case 'Move':
                $i = $this->Move($_POST['id']);
                return  $i;
                break;
        }

        if (isset(self::$url[2])) {
            if (self::$url[2] == "Task") {   // vai tiek izsaukta funkcija datums
                $taskid = ($_POST['id']);
                $obj = new Data();
                $query = 'SELECT *  , D.ID AS TaskID, D.Changes AS izmainas, U.Login AS Lietotajs, N.Login AS kas, T.Code as tips,O.Code as Pasutijums, O.ID as PasID, P.Login AS Persona
          FROM `Data` D
          LEFT JOIN Orders O ON ( O.ID = D.IDOrder )
          LEFT JOIN Users U ON ( U.ID = D.RemindTo )
          LEFT JOIN Users N ON ( N.ID = D.IDUser )
          LEFT JOIN Users P ON ( P.ID = D.IDPerson )
          LEFT JOIN Types T ON ( T.ID = D.IDType )
          WHERE D.ID =' . $taskid . '';

                if (!$result = self::$DB->query($query)) {
                    throw new Error('Read error on Tasks (' . __LINE__ . ')');
                }
                $skaits = 0;

                while ($row = $result->fetch_assoc()) {
                    $class = "";
                    if (strlen($row['izmainas']) == 0) {
                        $class = "hide";
                    }

                    $datums = explode(" ", $row['RemindDate']);
                    $Datums = str_replace("-", ".", $datums[0]); // var date ='2010, 03 - 1, 8'
                    $laikss = explode(":", @$datums[1]);
                    $laiks = $Datums . " " . $laikss[0] . ":" . $laikss[1];   /// vai vispar vajadzigs

                    $izmainas = "";
                    $izmainas = $this->getChangeList($row['TaskID']);

                    if (isset($_POST['user'])) {
                        $UID = $_POST['user'];
                    } else {
                        $UIDs = $_SESSION['User']->getID();
                    }

                    if ($_SESSION['User']->getStatus() > 1) {
                        $atbilde = "<div id=\"task\">
<form onkeydown=\"return rejectEnter(event)\" class=\"\" method=\"POST\" action=\"javascript:Save('Task')\" id=\"AddTaskForm\">
<p class=\"add\" style=\"font-size:12px;\">
<span>Laiks:<input  id=\"datepicker\" type=\"text\"><input type=\"text\" name=\"RemindDate\" class=\"hide\"/>
            <input style=\"width:20px;\" ID=\"h\" class=\"hours\" value=\"\">
            <input style=\"width:20px;\" ID=\"m\" class=\"time\" value=\"\">
            <input style=\"width:20px;\" ID=\"end\" class=\"hide\" name=\"RemindDateEnd\" value=\"\">
      Kam: <input id=\"idtype\" type=\"hidden\" name=\"RemindTo\">
            <input  id = \"kam\" type=\"text\" value=\"" . $row['Lietotajs'] . "\" name=\"RemindPerson\" class=\"type light ac_input\"autocomplete=\"off\">
            <input type=\"text\" value=\"" . $row['Persona'] . "\" name=\"PersonSelect\" class=\"hide\"/>
            <input type=\"text\" name=\"Tpl\" value=\"0\" class=\"hide\"/>
            <input type=\"text\" name=\"ID\" value=\"" . $row['TaskID'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"Sum\" value=\"" . $row['Sum'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"Hours\" value=\"" . $row['Hours'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"Date\" value=\"" . $row['Date'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"TotalPrice\" value=\"" . $row['TotalPrice'] . "\" class=\"hide\"/>
             <input type=\"text\" name=\"IDPerson\" value=\"" . $row['IDPerson'] . "\" class=\"hide\"/>

            <input type=\"text\" name=\"PlaceTaken\" value=\"" . $row['PlaceTaken'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"IDDoc\" value=\"" . $row['IDDoc'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"TextOrder\" value=\"" . $row['TextOrder'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"PlaceDone\" value=\"" . $row['PlaceDone'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"TextType\" value=\"" . $row['TextType'] . "\" class=\"hide\"/>

            <input type=\"text\" name=\"PriceNote\" value=\"" . $row['PriceNote'] . "\" class=\"hide\"/>

            <input type=\"text\" name=\"Statuss\" value=\"" . $row['Status'] . "\" class=\"hide\"/>
            <input type=\"text\" name=\"Hidden\" value=\"" . $row['Hidden'] . "\" class=\"hide\"/>
</span>

<span> No:  <span>" . $row['kas'] . "</span>
      Tips:<input type=\"hidden\" name=\"IDType\" value=\"" . $row['IDType'] . "\">
           <input type=\"text\" value=\"" . $row['tips'] . "\" name=\"TypeSelect\" class=\"type light ac_input\"autocomplete=\"off\">
      <a href=\"#\" onClick=\"data(" . $row['PasID'] . "," . $UIDs . ")\">Pasūtijums</a>:
           <input type=\"hidden\" name=\"IDOrder\">
           <input ID=\"Pasutijums\" type=\"text\" value=\"" . $row['Pasutijums'] . "\" name=\"OrderSelect\" class=\"type light ac_input\"autocomplete=\"off\">
</span>

<span><textarea ID=\"Note\" class=\"textarea\" rows=\"6\" name=\"Note\" cols=\"38\">" . $row['Note'] . "</textarea>
      <br>
      <br>
      <textarea class=\"textarea\" rows=\"6\"  name=\"BookNote\" cols=\"38\">" . $row['BookNote'] . "</textarea>
</span>
<p><a ref=\"#\" style=\"cursor: pointer; cursor: hand;\" onClick=\"izmainas()\" ID=\"changeBtn\" class=\"extra changes " . $class . "\"></a></p>

</p>
</form>
</div>";
                    } else {
                        $atbilde = "<div id=\"task\">
<form  style=\"font-size:12px;\">
<p>
<p>Laiks: " . $laiks . " Kam: " . $row['Lietotajs'] . " No: " . $row['kas'] . " </p>
<p>Tips: " . $row['tips'] . " Pasūtijums: <a href=\"#\" onClick=\"data(" . $row['PasID'] . ")\">" . $row['Pasutijums'] . "</a> </p>
<p>" . $row['Note'] . "</p>
</br>
<p>" . $row['BookNote'] . "</p>
<p><a ref=\"#\" style=\"cursor: pointer; cursor: hand;\" onClick=\"izmainas()\" ID=\"changeBtn\" class=\"extra changes " . $class . "\"></a></p>

</p>
</form>
</div>";
                    }

                    $Task['Data'] = $atbilde;
                    $Task['Changes'] = $izmainas;
                    $skaits++;
                }
            }
            return @implode("^", $Task);
        }

        $TaskUsers = Data::getReminder();
        $Vars['TaskUsers'] = json_encode($TaskUsers);

        $Users = Users::getAsArray();
        foreach ($Users as $k => $v) $Users[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Vars['UsersList'] = '{' . implode('},{', $Users) . '}';

        $Orders = Orders::getAsArray();
        foreach ($Orders as $k => $v) $Orders[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Vars['OrdersList'] = '{' . implode('},{', $Orders) . '}';

        $Types = Types::getAsArray();
        foreach ($Types as $k => $v) $Types[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Vars['TypesList'] = '{' . implode('},{', $Types) . '}';

        if (@!$_SESSION['isAdmin']) {
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

        if ($_SESSION['User']->getStatus() <= 1) {
            $Vars['edit'] = "false";
            $Vars['save'] = " ";
        } else {
            $Vars['edit'] = "true";
            $Vars['save'] = ",
        Saglabāt: function() {Saglabat(event);
          $(this).dialog('close');
          } ";
        }

        $Vars['sesionid'] = $_SESSION['User']->getID();
        $Vars['SelectUser'] = isset($_SESSION['TaskUser']) ? $_SESSION['TaskUser'] : null;
        $Vars['TaskSkats'] = isset($_SESSION['TaskSkats']) ? $_SESSION['TaskSkats'] : null;
        $Vars['TaskGads'] = isset($_SESSION['TaskGads']) ? $_SESSION['TaskGads'] : null;
        $Vars['TaskMenesis'] = isset($_SESSION['TaskMenesis']) ? $_SESSION['TaskMenesis'] : null;
        $Vars['TaskDina'] = isset($_SESSION['TaskDina']) ? $_SESSION['TaskDina'] : null;

        if (empty($Vars['SelectUser'])) {
            $Vars['SelectUser'] = (isset($_POST['user']) && $_POST['user'] != '') ? ($_POST['user']) : ($_SESSION['User']->getID());
        }

        return Template::Process('index', $Vars);
    }

    function getChangeList($ID) {
        $obj = new Data();

        $Data = $obj->getById($ID);

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
            return $tmp;
        } else return "";
    }

    function getdata() {
        if (isset($_POST['user'])) {
            $UID = $_POST['user'];
        } else {
            $UID = $_SESSION['User']->getID();
        }

        $query = 'SELECT *, Data.ID as TaskID, Data.Changes as izmainas FROM Data LEFT JOIN Orders ON Orders.ID=Data.IDOrder WHERE `RemindDate` IS NOT NULL and `RemindTo`= ' . $UID . ' ';                                 //and `Date` between '. $a .' and ' . $b .'
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Tasks (' . __LINE__ . ')');
        }
        while ($row = $result->fetch_assoc()) {
            $task['end'] = $row['RemindDateEnd'];
            $task['id'] = $row['TaskID'];
            $task['title'] = $row['Note'];
            $task['start'] = $row['RemindDate'];
            $task['pasutijums'] = $row['Code'];
            $task['paspiez'] = $row['TextOrder'];

            $Task[] = $task;
        }

        return json_encode($Task);
    }

    function Move($id) {
        $query = 'SELECT *  , D.ID AS TaskID, D.Changes AS izmainas, U.Login AS Lietotajs, N.Login AS kas, T.Code as tips,O.Code as Pasutijums, O.ID as PasID
          FROM `Data` D
          LEFT JOIN Orders O ON ( O.ID = D.IDOrder )
          LEFT JOIN Users U ON ( U.ID = D.RemindTo )
          LEFT JOIN Users N ON ( N.ID = D.IDUser )
          LEFT JOIN Types T ON ( T.ID = D.IDType )
          WHERE D.ID =' . $id . '';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Tasks (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $Data = "&Tpl=0&ID=" . $row['TaskID'] . "&Sum=" . $row['Sum'] . "&Hours=" . $row['Hours'] . "&Date=" . $row['Date'] . "&TotalPrice=" . $row['TotalPrice'] . "&IDPerson=" . $row['IDPerson'] . "&PlaceTaken=" . $row['PlaceTaken'] . "&IDDoc=" . $row['IDDoc'] . "&TextOrder=" . $row['TextOrder'] . "&PlaceDone=" . $row['PlaceDone'] . "&TextType=" . $row['TextType'] . "&PriceNote=" . $row['PriceNote'] . "&Statuss=" . $row['Status'] . "&Hidden=" . $row['Hidden'] . "&IDType=" . $row['IDType'] . "&TypeSelect=" . $row['tips'] . "&OrderSelect=" . $row['Pasutijums'] . "&Note=" . $row['Note'] . "&BookNote=" . $row['BookNote'] . "&RemindTo=" . $row['RemindTo'] . "&PersonSelect=" . $row['RemindTo'] . "&IDOrder=" . $row['IDOrder'] . "&AllDay=" . $row['allDay'];
        }

        return $Data;
    }

    function Save() {
        $obj = new Data();
        $_POST = $obj->clearDefaultValues($_POST);
        $obj->fetchObject($_POST);

        if (($obj->getRemindDate() != '0000-00-00 00:00:00' && $obj->getRemindDate() != '2000-00-00 00:00:00')
            && !is_numeric($obj->getRemindTo())
        )
            Error::setError(get_class(), 'RemindDate', 'Set remind to ' . $obj->getRemindTo());
        elseif ($obj->getRemindDate() == '0000-00-00 00:00:00' || $obj->getRemindDate() == '2000-00-00 00:00:00')
            $obj->setRemindTo(0);

        if ($obj->TrimDate($obj->getRemindDate()) == '00:00:00') {
            $obj->setAllDays(1);
        }

        if (empty($Err)) {
            if ($obj->getID() == 0) $obj->Add();
            else {
                $Data = $obj->getById($_POST['ID']);
                $Diffs = unserialize($Data->getChanges());
                if (!is_array($Diffs))
                    $Diffs = array();

                $tmp = $obj->getUpdateDiff($Data);
                if (!empty($tmp)) {
                    $Diffs[date('Y-m-d H:i:s')] = $tmp;
                    ksort($Diffs);
                    $Diffs = array_reverse($Diffs);
                }
                $obj->setChanges(serialize($Diffs));

                $obj->Update();
            }

            return $obj->getID();
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

    function Josn() {
        switch (isset($_GET['Type']) ? $_GET['Type'] : '') {
            case 'Data':
                $ID = $_GET["ID"];
                $Data = Data::getRow($ID);
                return Template::Process('task', $Data);
            default:
                $Start =  date("Y-m-d 00:00:00", $_GET["start"]);
                $Starttt = $_GET["start"] - 24 * 3600;
                $End = date("Y-m-d 00:00:00", $_GET['end']);
                $Enddd = $_GET['end'] + 24 * 3600;
                $Startt =  date("Y-m-1 00:00:00", $Starttt);
                $Endd = date("Y-m-31 00:00:00", $Enddd);
                $User = isset($_SESSION['TaskUser'])
                    ? $_SESSION['TaskUser']
                    : $_SESSION['User']->getID();

                $query = 'SELECT *, Data.ID as TaskID, Data.Changes as izmainas FROM Data LEFT JOIN Orders ON Orders.ID=Data.IDOrder WHERE `RemindDate` BETWEEN "' . $Start . '" AND "' . $End . '" and `RemindTo`=  ' . $User . ' UNION   SELECT *, Data.ID as TaskID, Data.Changes as izmainas FROM Data LEFT JOIN Orders ON Orders.ID=Data.IDOrder WHERE `RemindDate` BETWEEN "' . $Startt . '" AND "' . $Endd . '" and `RemindTo`=  ' . $User . ' AND allDay = 1 ';                                 //and `Date` between '. $a .' and ' . $b .'

                if (!$result = self::$DB->query($query)) {
                    throw new Error('Read error on Tasks (' . __LINE__ . ')');
                }
                while ($row = $result->fetch_assoc()) {
                    $task['color'] = $row['Color'];
                    $task['end'] = $row['RemindDateEnd'];
                    $task['id'] = $row['TaskID'];
                    $task['title'] = str_replace(' `', '', $row['BookNote']);
                    $task['start'] = $row['RemindDate'];
                    $task['pasutijums'] = $row['Code'];
                    $task['paspiez'] = str_replace(' `', '', $row['Note']);
                    $task['allDay'] = ($row['allDay'] == 1) ? "true" : "";

                    $Task[] = $task;
                }

                if (!isset($Task) || $Task == NULL) {
                    return '{"end":"0000-00-00 00:00:00","id":"","title":"","start":"0000-00-00 00:00:00","pasutijums":"","paspiez":""}';
                } else {
                    return json_encode($Task);
                }
        }
    }
}
