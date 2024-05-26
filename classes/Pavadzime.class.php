<?php

class Pavadzime extends DBObject {
    private $ID;
    private $Nosaukums;
    private $Kods;
    private $Adrese;
    private $Banka;
    private $Konts;
    private $Changes;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields')
                $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Sanemejsedit':
                return $this->Sanemejsedit();
            case 'AutoUiReplace':
                return $this->sanemejs($_POST['ID']);
            case 'DeleteEntry':
                return $this->DeleteEntry();
            case 'LineSave':
                return $this->LineSave();
            case 'BildSave':
                return $this->BildSave();
            case 'SanemejsSave':
                return $this->SanemejsSave();
            case 'Sanemejs':
                return $this->Sanemejs($_POST['ID']);
            case 'Save':
                return $this->Save();
            case 'Get':
                return $this->getList($_POST['IDData']);
            case 'EditSanList':
                return $this->EditSanList();
            case 'DelSan':
                return $this->DelSan();
            case 'UpdateCont':
                $this->UpdateCont();
                break;
            default:
                return;
        }
    }

    function getList($Data) {
        $Data = (int)$Data;

        $query = 'SELECT ID,IDDoc,Date,Note,PlaceTaken,PlaceDone FROM `Data` WHERE ID = ' . $Data;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }
        $Info = array();

        while ($row = $result->fetch_assoc()) {
            $Info['ID'] = $row['ID'];
            $Info['IDDoc'] = $row['IDDoc'];
            $Info['Date'] = Pavadzime::date2text($row['Date']);
            $Info['IzDate'] = Pavadzime::izsnigsanastext($row['Date']);
            $Info['Note'] = $row['Note'];
            $Info['PlaceTaken'] = $row['PlaceTaken'];
            $Info['PlaceDone'] = $row['PlaceDone'];
        }

        $Sanemejs = Pavadzime::SanemejsgetAsArray();
        foreach ($Sanemejs as $k => $v)
            $Sanemejs[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Info['Sanemejs'] = '{' . implode('},{', $Sanemejs) . '}';

        $query1 = 'SELECT ID,DocID,Samaksa,Sanemejs,Atlaide,Izsniedza,SanemejaID FROM `pavadzime` WHERE DocID = ' . $Data;

        if (!$result = self::$DB->query($query1)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        $Info['SaveID'] = 0;
        while ($row = $result->fetch_assoc()) {
            $Info['SaveID'] = $row['ID'];
            $Info['Samaksa'] = $row['Samaksa'];
            $Info['SanemejsID'] = $row['SanemejaID'];
            $Info['Atlaide'] = $row['Atlaide'];
            $Info['Izsniedza'] = $row['Izsniedza'];
        }

        $query2 = 'SELECT ID,Nosaukums,Artikuls,Daudzums,Mervieniba,Cena FROM `pavadzime_preces` WHERE DocID = ' . $Data;

        if (!$result = self::$DB->query($query2)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        $a = 0;
        while ($row = $result->fetch_assoc()) {
            $a++;

            $Info['tabula'] .= "<tr class=\"bordersolidadd\" id=\"$a\" name=\"" . $row['ID'] . "\">
                 <td width=\"40%\"> <input value=\"$row[Nosaukums]\" type=\"text\" class=\"Precu_nosaukums\" size=\"106\"></td>
                 <td width=\"20%\"> <input value=\"$row[Artikuls]\" type=\"text\" class=\"Artikuls\" size=\"50\"></td>
                 <td width=\"5%\"> <input value=\"$row[Daudzums]\" type=\"text\" id=\"$a\" class=\"Daudz\" size=\"16\" onblur=\"summ(this.id)\"></td>
                 <td width=\"5%\"> <input value=\"$row[Mervieniba]\" type=\"text\" class=\"Merv\" size=\"15\"></td>
                 <td width=\"10%\"> <input value=\"" . $row['Cena'] . "\" type=\"text\" id=\"$a\" class=\"Cena\" size=\"15\" onblur=\"summ(this.id)\"><a style='float:right' href='javascript:Delete(\"$a\"," . $row['ID'] . ");' class='extra delete'></a></td>
                 <td width=\"10%\" id=\"$a\" class=\"Summa\"> </td>
                    </tr>";
        }

        if ($a == 0) {
            $Info['tabula'] = "<tr ID=\"1\" class=\"bordersolidadd\" name=\"0\">
      <td width = \"40%\"> <input size = \"106\" type=\"text\" class=\"Precu_nosaukums\" /></td>
      <td width = \"20%\"> <input size = \"50\" type=\"text\" class=\"Artikuls\" /></td>
      <td width = \"5%\"> <input onblur=\"summ(this.id)\" size = \"16\" type=\"text\" class=\"Daudz\" id=\"1\"/></td>
      <td width = \"5%\"> <input size = \"15\" type=\"text\" class=\"Merv\" /></td>
      <td width = \"10%\"> <input onblur=\"summ(this.id)\" size = \"15\" type=\"text\" class=\"Cena\" id=\"1\" /><a style='float:right' href='javascript:Delete(1,0);' class='extra delete'></a></td>
      <td width = \"10%\" id=\"1\" class=\"Summa\"> </td>
    </tr>";
        }

        $Info['ierakstusk'] = $a;

        $Info['__template'] = '/Pavadzime/Supplier';
        return self::ArrayToJson(array(1, Template::Process($Info)));
    }

    function Save() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $this->Add();
            return 1;
        } else {
            $Err[0] = 0;
            return self::ArrayToJson($Err);
        }
    }

    function Add() {
        $query = 'REPLACE INTO `Info`
                      SET `IDData`=' . (int)$this->getIDData() . ',
                          `IDSupplier`=' . (int)$this->getIDSupplier() . ',
                          `IDUser`="' . $_SESSION['User']->getID() . '",
                          `Info`="' . addslashes($this->getInfo()) . '",
                          `Color`="' . addslashes($this->getColor()) . '",
                          `AddDate`=NOW()';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    static function Delete($IDS) {
        $query = 'DELETE FROM `Info` WHERE `IDSupplier`=' . $IDS;

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    function sanemejs($id) {
        $query = "SELECT * FROM `sanemeji` WHERE `ID` = '$id'";
        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }
        $Sanemeji = array();
        while ($row = $result->fetch_assoc()) {
            $Sanemeji['ID'] = $row['ID'];
            $Sanemeji['Nosaukums'] = rawurldecode($row['Nosaukums']);
            $Sanemeji['Kods'] = $row['Kods'];
            $Sanemeji['Adrese'] = rawurldecode($row['Adrese']);
            $Sanemeji['Banka'] = rawurldecode($row['Banka']);
            $Sanemeji['Konts'] = $row['Konts'];
        }
        return implode("|", $Sanemeji);
    }

    static function SanemejsgetAsArray() {
        $query = 'SELECT *
FROM `sanemeji`
WHERE ID >0
ORDER BY `Nosaukums`';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            $Orders[$row['ID']] = $row['Nosaukums'];
        }

        return $Orders;
    }

    function date2text($datums) {
        $menesis = array("", "janvaris", "februaris", "marts", "aprilis", "maijs", "junijs", "julijs", "augusts", "septembris", "oktobris", "novembris", "decembris");

        list($dat) = explode(" ", $datums);
        list($yehr, $month, $day) = explode("-", $dat);

        $textdate = $yehr . ".gada " . $day . "." . $menesis[ltrim($month, '0')];

        return $textdate;
    }

    function SanemejsSave() {
        $Nosaukums = rawurlencode($_POST['Nosaukums']);
        $Kods = $_POST['Kods'];
        $JurAdrese = rawurlencode($_POST['JurAdrese']);
        $Kreditiestade = rawurlencode($_POST['Kreditiestade']);
        $Konts = $_POST['Konts'];
        $Telefons = $_POST['Telefons'];
        $Epasts = $_POST['Epasts'];

        $query = 'INSERT INTO `sanemeji` (
    `ID` ,
    `Nosaukums` ,
    `Kods` ,
    `Adrese` ,
    `Banka` ,
    `Konts`,
    `Epasts`,
    `Telefons`
    )
    VALUES (
    NULL , "' . $Nosaukums . '", "' . $Kods . '", "' . $JurAdrese . '", "' . $Kreditiestade . '", "' . $Konts . '", "' . $Epasts . '","' . $Telefons . '"
    );';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }
        $this->MakeCont($_POST['Nosaukums'], $_POST['Kods'], $_POST['JurAdrese'], $_POST['Kreditiestade'], $_POST['Konts'], $_POST['Telefons'], $_POST['Epasts']);
        return 1;
    }

    function LoadSanemeji($ID) {
        $query = 'SELECT * FROM `sanemeji` WHERE ID = "' . $ID . '"';
        //ID,Nosaukums, Kods, Adrese, Banka, Konts, Telefons, Epasts
        $Table = $this->good_query_table($query);
        $Table['__template'] = '/Pavadzime/ChangeSanemejs';
        return $Table;
    }

    function EditSanList() {
        $query = "SELECT * FROM `sanemeji` where Status = 0 ";

        $Table = $this->good_query_table($query);

        $Table['__template'] = '/Pavadzime/Table';
        return Template::Process($Table);
    }

    function CechUsage($ID) {
        $query = "SELECT DocID FROM `pavadzime` WHERE SanemejaID = " . $ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        if ($result->num_rows > 0) {
            $pavadzimes = array();
            while ($row = $result->fetch_assoc()) {
                $pavadzimes[] = $row['DocID'];
            }

            return $pavadzimes;
        }
    }

    function DelSan() {
        $ID = $_POST['ID'];

        $parbaude = $this->CechUsage($ID);
        $Names = implode(", ", $parbaude);

        if ($parbaude > 0) {
            return "Šis uzņēmums tiek izmantots pavadzīmē: " . $Names . " !";
        }

        $query = 'Update `sanemeji` SET `Status`= 1 WHERE `ID`=' . $ID;

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Data (' . __LINE__ . ')');
        }

        return 1;
    }

    function Sanemejsedit() {
        $ID = $_POST['ID'];
        $query = 'UPDATE sanemeji
            SET `Nosaukums` = "' . str_replace("%27%27", "%22", rawurlencode($_POST['Nosaukums'])) . '" ,
                `Kods` = "' . $_POST['Kods'] . '" ,
                `Adrese` = "' . str_replace("%27%27", "%22", rawurlencode($_POST['JurAdrese'])) . '" ,
                `Banka` = "' . str_replace("%27%27", "%22", rawurlencode($_POST['Kreditiestade'])) . '" ,
                `Konts` = "' . $_POST['Konts'] . '",
                `Epasts` = "' . rawurlencode($_POST['Epasts']) . '",
                `Telefons` = "' . rawurlencode($_POST['Telefons']) . '"
            WHERE ID = "' . $ID . '"';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }
        $this->MakeCont($_POST['Nosaukums'], $_POST['Kods'], $_POST['JurAdrese'], $_POST['Kreditiestade'], $_POST['Konts'], $_POST['Telefons'], $_POST['Epasts']);
        return 1;
    }

    static function getById($ID) {
        $query = 'SELECT * FROM `sanemeji` WHERE `ID`=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Data ' . __LINE__);
        }
        return self::fetchObject($result, new self);
    }

    function izsnigsanastext($datums) {
        $menesis = array("", "janvāris", "februāris", "marts", "aprīlis", "maijs", "junījs", "julījs", "augusts", "septembris", "oktobris", "novembris", "decembris");

        list($dat) = explode(" ", $datums);
        list($yehr, $month, $day) = explode("-", $dat);

        $textdate = $yehr . ".gada " . $menesis[date('n', $month)];

        return $textdate;
    }

    function BildSave() {
        $ID = $_POST['ID'];
        $DocID = $_POST['pavadid'];
        $Samaksa = $_POST['samaksaskartiba'];
        $Sanemejs = rawurlencode($_POST['Sanemejs']);
        $Atlaide = $_POST['Atlaide'];
        $Izsniedza = $_POST['izsniedza'];
        $Kopa = $_POST['Kopa'];
        $atlaidessumma = $_POST['atlaidessumma'];
        $PirmsNodokliem = $_POST['PirmsNodokliem'];
        $PVN = $_POST['PVN'];
        $Samaksai = $_POST['Samaksai'];
        $SanemejaID = $_POST['SanemejaID'];

        if ($ID == 0) {
            $query = "INSERT INTO `pavadzime` (
`DocID` ,
`Samaksa` ,
`Sanemejs` ,
`Atlaide` ,
`izsniedza`,
`Kopa` ,
`atlaidessumma` ,
`PirmsNodokliem` ,
`PVN` ,
`Samaksai`,
`SanemejaID`
)
VALUES (
 '$DocID', '$Samaksa', '$Sanemejs', '$Atlaide', '$Izsniedza', '$Kopa', '$atlaidessumma', '$PirmsNodokliem', '$PVN', '$Samaksai','$SanemejaID'
);";
        } else {
            $query = "UPDATE pavadzime
SET `DocID` = '$DocID',
`Samaksa` = '$Samaksa' ,
`Sanemejs` = '$Sanemejs' ,
`Atlaide` =  '$Atlaide',
`izsniedza` = '$Izsniedza',
`Kopa` = '$Kopa',
`atlaidessumma` = '$atlaidessumma',
`PirmsNodokliem` = '$PirmsNodokliem',
`PVN` = '$PVN',
`Samaksai` = '$Samaksai',
`SanemejaID` = '$SanemejaID'
WHERE ID = '$ID'";
        }

        $query2 = "Update Data SET `TextOrder` = '" . addslashes(rawurldecode($Sanemejs)) . "' WHERE ID = '$DocID'";

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }

        if (!self::$DB->query($query2)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }

        return 1;
    }

    function LineSave() {
        $ID = $_POST['entryid'];
        $DocID = $_POST['id'];
        $Nosaukums = $_POST['nosaukums'];
        $Artikuls = $_POST['artikuls'];
        $Daudzums = $_POST['daudzums'];
        $Mervieniba = $_POST['mervieniba'];
        $Cena = $_POST['cena'];
        $Summa = $_POST['summa'];

        if ($ID == 0) {
            $query = 'INSERT INTO `pavadzime_preces` (
`DocID` ,
`Nosaukums` ,
`Artikuls` ,
`Daudzums` ,
`Mervieniba`,
`Cena`,
`Summa`
)
VALUES (
 "' . $DocID . '", "' . $Nosaukums . '", "' . $Artikuls . '", "' . $Daudzums . '", "' . $Mervieniba . '", "' . $Cena . '", "' . $Summa . '"
);';
        } else {
            $query = 'UPDATE pavadzime_preces
SET `DocID` = "' . $DocID . '" ,
`Nosaukums` = "' . $Nosaukums . '" ,
`Artikuls` = "' . $Artikuls . '" ,
`Daudzums` = "' . $Daudzums . '" ,
`Mervieniba` = "' . $Mervieniba . '",
`Cena` = "' . $Cena . '",
`Summa` = "' . $Summa . '"
WHERE ID = "' . $ID . '"';
        }

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Pavadzime (' . __LINE__ . ')');
        }

        return 1;
    }

    static function DeleteEntry() {
        $ID = $_POST['id'];
        $query = 'DELETE FROM `pavadzime_preces` WHERE `ID`=' . $ID;

        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    /**
     * Setters and getters here
     */
    function __call($method, $params) {
        $type = substr($method, 0, 3);
        $key = substr($method, 3);

        if ($type == 'get')
            return $this->$key;
        elseif ($type == 'set')
            $this->$key = $params[0];
        else
            throw new Error(get_class($this) . '::' . $method . ' does not exists');
    }

    /**
     * Funkcija paredzēta automātiskai jaunu kontaktu izveidei tiek apvienota ar save funkciju lai saglabātu loģiku.
     * Rindā 234659 tika pieprasits pec izmaiņām rindu veidot no jauna.
     * Ja kadreiz pardomas un bus nepieciešams apdeitot rindas japievieno Data.ID rinda pie saņēmēju tabulas un jānosūta līdzi uz šeieni.
     * @return void
     * @author
     */
    function MakeCont($Title, $Code, $Adress, $Bank, $Account, $Phone, $Mail) {
        $Phone == 0 ? $Phone = '' : $Phone = $Phone;
        $Cont['IDDoc'] = 'Sistēmas ';
        $Cont['IDOrder'] = '1627';
        $Cont['IDType'] = '60';
        $Cont['PlaceTaken'] = 'srv';
        $Cont['IDPerson'] = '36';
        $Cont['RemindDate'] = '';
        $Cont['Note'] = $Title . '; ' . $Code . '; ' . $Adress . '; ' . $Bank . '; ' . $Account . '; ' . $Phone . '; ' . $Mail;
        $Cont['Date'] = date('Y-m-d H:i:s');
        $Cont['AdminEdit'] = '1';
        $_POST = $Cont;
        $data = new Data;
        $data->Save();
    }

    /**
     * Funkcija paredzēta automātiskai jaunu kontaktu izveide kas ir tabulā sanemeji.
     * @return void
     * @author
     */
    function UpdateCont() {
        $query = "SELECT * FROM `sanemeji`";

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }
        while ($row = $result->fetch_assoc()) {
            $this->MakeCont(rawurldecode($row['Nosaukums']), rawurldecode($row['Kods']), rawurldecode($row['Adrese']), rawurldecode($row['Banka']), rawurldecode($row['Konts']), rawurldecode($row['Telefons']), rawurldecode($row['Epasts']));
        }
    }
}
