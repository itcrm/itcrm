<?php

class Noma extends DBObject {
    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'AddAuto':
                return $this->AddAuto($_POST);
            case 'EditAutoList':
                return $this->EditAutoList();
            case 'ChangeAuto':
                return $this->ChangeNomaAuto($_POST);
            case 'SaveNomaForm':
                return $this->NomaAdd($_POST);
            case 'save':
                return $this->Save($_POST);
            case 'savePielikums':
                return $this->savePielikums($_POST);
            case 'saveAkts':
                return $this->saveAkts($_POST);
        }
    }

    function AddAuto($Data) {
        $query = 'INSERT INTO `nomasauto` (
`OrderID` ,
`Nosaukums` ,
`Reg_nr` ,
`Sasija` ,
`Reg_ap`,
`Vertiba`
)
VALUES (
 "' . $Data['OrderID'] . '","' . $Data['Nosaukums'] . '", "' . $Data['Reg_nr'] . '", "' . $Data['Sasija'] . '", "' . $Data['Reg_ap'] . '", "' . $Data['Vertiba'] . '");';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }

        return 1;
    }

    function EditAutoList() {
        $query = "SELECT * FROM `nomasauto` where Status = 0 ";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        $results = array();
        while ($row = $result->fetch_assoc()) {
            $row['Order'] = Orders::getCodeById($row['OrderID']); //Orders::getById(
            $results[] = $row;
        }

        $results['__template'] = '/noma/Table';
        return Template::Process($results);
    }

    function ChangeAuto($ID) {
        $query = 'SELECT * FROM `nomasauto` WHERE ID = "' . $ID . '"';
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data (' . __LINE__ . ')');
        }

        $results = array();
        while ($row = $result->fetch_assoc()) {
            $row['Order'] = Orders::getCodeById($row['OrderID']); //Orders::getById(
            $results[] = $row;
        }
        $results['__template'] = '/noma/ChangeAuto';
        return $results;
    }

    function ChangeNomaAuto($Data) {
        $query = 'UPDATE `nomasauto`
SET `Nosaukums` = "' . str_replace("%27%27", "%22", rawurlencode($Data['Nosaukums'])) . '" ,
`Reg_nr` = "' . str_replace("%27%27", "%22", rawurlencode($Data['Reg_nr'])) . '" ,
`Sasija` = "' . str_replace("%27%27", "%22", rawurlencode($Data['Sasija'])) . '" ,
`Reg_ap` = "' . str_replace("%27%27", "%22", rawurlencode($Data['Reg_ap'])) . '",
`OrderID` = "' . $Data['OrderID'] . '",
`Vertiba` = "' . $Data['Vertiba'] . '"
WHERE ID = "' . $Data['ID'] . '"';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }

        return 1;
    }

    function AutoAutocomplite($text) {
        $query = "select nomasauto.ID, Orders.Code AS Nosaukums from `nomasauto`, `Orders` WHERE nomasauto.OrderID = Orders.ID AND Code LIKE '%" . $text . "%' AND nomasauto.Status = 0";

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Data (' . __LINE__ . ')');

        $results = array();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        $rez = json_encode($results);
        $rez = str_replace('%22', "%27%27", $rez);
        $rez = rawurldecode($rez);
        echo str_replace("Nosaukums", "label", $rez);
    }

    function NomaAdd($Data) {
        $Data = Data::clearDefaultValues($Data);
        //izveidojam ppr rindu ar sākumdatiem    || Nepieciešams pievienot ari uzdevuma parametus.

        $query = 'INSERT INTO `Data`
                     SET `IDDoc`="' . addslashes($Data['IDDoc']) . '",
                         `IDUser`=' . $_SESSION['User']->getID() . ',
                         `IDOrder`=' . (int)$Data['IDOrder'] . ',
                         `TextOrder`="' . addslashes($Data['TextOrder']) . '",
                         `IDType`=' . (int)$Data['IDType'] . ',
                         `TextType`="' . addslashes($Data['TextType']) . '",
                         `Sum`=' . (float)$Data['Sum'] . ',
                         `Hours`=' . (float)$Data['Hours'] . ',
                         `PlaceTaken`="' . addslashes($Data['PlaceTaken']) . '",
                         `PlaceDone`="' . addslashes($Data['PlaceDone']) . '",
                         `IDPerson`=' . (int)$Data['IDPerson'] . ',
                         `Note`="' . addslashes($Data['Note']) . '",
                         `Date`="' . $Data['Date'] . '",
                         `BookNote`="' . addslashes($Data['BookNote']) . '",
                         `TotalPrice`=' . (float)$Data['TotalPrice'] . ',
                         `PriceNote`="' . addslashes($Data['PriceNote']) . '",
                         `AddDate`=NOW(),
                         `RemindDate`="' . $Data['From'] . '",
                         `RemindDateEnd`="' . $Data['To'] . '",
                         `RemindTo`="223",
                         `Hidden`="' . (int)$Data['Hidden'] . '",
                         `allDay`="1",
                         `AdminEdit`="' . (int)$Data['AdminEdit'] . '",
                         `Status`= "' . ($Data['Tpl'] == 1 ? '10' : '1') . '",
                         `Noma` = "1"';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Noma (' . __LINE__ . ') : ' . self::$DB->error);
        }
        $From = strtotime($this->dataconvert($Data['From']));
        $To = strtotime($this->dataconvert($Data['To']));
        $atstarpe = " - ";
        //Ievadam datus pries ppr pavadzimju tabulā
        $ID = self::$DB->insert_id;
        $query = 'INSERT INTO pavadzime(
`DocID`,
`SanemejaID`,
`Samaksa`,
`Izsniedza`)
VALUES("' . $ID . '","' . $Data['PersonID'] . '","1 (viena) darbadiena","' . "20" . $Data['From'] . $atstarpe . "20" . $Data['To'] . '")';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Noma (' . __LINE__ . ') : ' . self::$DB->error);
        }

        $dienas = ($To - $From) / 86400;
        $Data['CautionMoney'] = round((($Data['Price'] / $dienas) / 1.21) * 4, 3);
        $Data['DayMony'] = round((($Data['Price'] / $dienas) / 1.21), 3);
        $_POST['id'] = $ID;
        $_POST['nosaukums'] = "Autopakalpojums";
        $_POST['daudzums'] = $dienas;
        $_POST['mervieniba'] = "dienas";
        $_POST['cena'] = $Data['DayMony'];
        Pavadzime::LineSave();

        $_POST['id'] = $ID;
        $_POST['nosaukums'] = "Garantijas summa";
        $_POST['daudzums'] = 1;
        $_POST['mervieniba'] = "gab";
        $_POST['cena'] = $Data['CautionMoney'];
        Pavadzime::LineSave();

        $query = 'INSERT INTO `noma` (
`PersonID` ,
`RowID`,
`AutoID` ,
`Nr` ,
`From` ,
`To` ,
`GetLocation` ,
`ReturnLocation`
)
VALUES (
 "' . $Data['PersonID'] . '","' . $ID . '","' . $Data['AutoID'] . '","' . $Data['Nr'] . '","' . $Data['From'] . '","' . $Data['To'] . '","' . rawurlencode($Data['GetLocation']) . '","' . rawurlencode($Data['ReturnLocation']) . '");';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }

        $Data = Data::getRow($ID);
        return self::ArrayToJson(array(1, Template::Process('/Data/Row', $Data)));
    }

    function dataconvert($datums) {
        list($dat) = explode(" ", $datums);
        list($yehr, $month, $day) = explode(".", $dat);

        return "20" . $yehr . "-" . $month . "-" . $day;
    }

    function GetNoma($ID) {
        $query0 = 'SELECT * FROM `pavadzime_preces` WHERE DocID = "' . $ID . '"';

        $Table0 = $this->good_query_table($query0);

        $query = 'SELECT * FROM `noma` WHERE RowID = "' . $ID . '"';

        $Table = $this->good_query_table($query);
        $samaksa =  round(($Table0[0][Summa] + ($Table0[0][Summa] * 0.21)), 3);
        $dienas = (strtotime(substr($Table[0]['To'], 0, -9)) - strtotime(substr($Table[0]['From'], 0, -9))) / 86400;
        $dienasnauda = round(($samaksa / $dienas), 2);

        include("classes/number2text.php");
        $Table[0]['CautionMoney'] = round(($Table0[1][Summa] + ($Table0[1][Summa] * 0.21)), 2);
        $Table[0]['DayMony'] =  $dienasnauda;
        $Table[0]['DayMonyText'] = amount2words($Table[0]['DayMony']); //amount2words(
        $Table[0]['Summ'] = round((($samaksa / $dienas) * $dienas), 2);
        $Table[0]['SummText'] = amount2words($Table[0]['Summ']);
        $Table[0]['CautionMoneyText'] = amount2words($Table[0]['CautionMoney']);
        $Table[0]['Now'] = date2text(date("Y-m-d hh:mm")); //date2text(
        $Table[0]['Days'] = (strtotime(substr($Table[0]['To'], 0, -9)) - strtotime(substr($Table[0]['From'], 0, -9))) / 86400;
        $Table[0]['DaysText'] = _number2stringSmall((strtotime(substr($Table[0]['To'], 0, -9)) - strtotime(substr($Table[0]['From'], 0, -9))) / 86400);
        $Table[0]['From'] = date2text(substr($Table[0]['From'], 0, -3)) . " plkst. " . substr($Table[0]['To'], 10, -3);
        $Table[0]['To'] = date2text(substr($Table[0]['To'], 0, -3)) . " plkst. " . substr($Table[0]['To'], 10, -3);

        $query2 = 'SELECT *,Nosaukums as Name FROM `sanemeji` WHERE ID = ' . $Table[0]["PersonID"];

        $Table2 = $this->good_query_table($query2);

        $query3 = 'SELECT *,Nosaukums as Auto FROM `nomasauto` WHERE ID = ' . $Table[0]["AutoID"];

        $Table3 = $this->good_query_table($query3);
        $Table3[0]['VertibaTeksts'] = amount2words($Table3[0]['Vertiba']);

        $query4 = 'SELECT * FROM `Data` WHERE ID = ' . $ID;

        $Table4 = $this->good_query_table($query4);

        $Data = array_merge((array)$Table[0], (array)$Table2[0], (array)$Table3[0], (array)$Table4[0]);

        $Data['Auto'] = rawurldecode($Data['Auto']);
        $Data['Reg_nr'] = rawurldecode($Data['Reg_nr']);
        $Data['Sasija'] = rawurldecode($Data['Sasija']);
        $Data['Reg_ap'] = rawurldecode($Data['Reg_ap']);

        $Data['Name'] = rawurldecode($Data['Name']);
        $Data['Adrese'] = rawurldecode($Data['Adrese']);
        $Data['GetLocation'] = rawurldecode($Data['GetLocation']);
        $Data['ReturnLocation'] = rawurldecode($Data['ReturnLocation']);
        $Data['Banka'] = rawurldecode($Data['Banka']);
        $Data['Telefons'] = rawurldecode($Data['Telefons']);

        if ($_SESSION['User']->getStatus() < 99) {
            $Data['Rights'] = " mode : 'textareas',
        theme : 'advanced',
        fullscreen_new_window : true,
         fullscreen_settings : {
              theme_advanced_buttons1 : 'print,|,pagebreak,template,|,fullscreen',
        },
        plugins : 'pagebreak,save,print,template',
        theme_advanced_buttons1 : 'save,print,|,pagebreak,template,|,fullscreen',
        theme_advanced_buttons2 : '',
        theme_advanced_buttons3 : '',
        theme_advanced_buttons4 : '',
        theme_advanced_toolbar_location : 'top',
        theme_advanced_toolbar_align : 'left',
        theme_advanced_statusbar_location : 'bottom',
        theme_advanced_resizing : true,
        template_external_list_url : '/js/noma_template_list.js',
           ";
        } else {
            $Data['Rights'] =  '
        mode : "textareas",
        theme : "advanced",
        fullscreen_new_window : true,
         fullscreen_settings : {
              theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        },

        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,visualblocks",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,visualblocks",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "/js/noma_template_list.js",
        ';
        }

        return $Data;
    }
    function Save($Data) {
        $query = "UPDATE `noma` SET ligums='" . urlencode($Data['Data']) . "' WHERE RowID=" . $Data['ID'];

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Noma (' . __LINE__ . ')');
        }

        return 1;
    }

    function savePielikums($Data) {
        $query = "UPDATE `noma` SET Pielikums='" . urlencode($Data['Data']) . "' WHERE RowID=" . $Data['ID'];

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Noma (' . __LINE__ . ')');
        }

        return 1;
    }

    function saveAkts($Data) {
        $query = "UPDATE `noma` SET Akts='" . urlencode($Data['Data']) . "' WHERE RowID=" . $Data['ID'];

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Noma (' . __LINE__ . ')');
        }

        return 1;
    }
}
