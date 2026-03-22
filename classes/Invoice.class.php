<?php

class Invoice extends DBObject {
    protected static $tableName = 'recipients';
    protected $ID;
    protected $Nosaukums;
    protected $Kods;
    protected $Adrese;
    protected $Banka;
    protected $Konts;
    protected $Changes;

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'EditRecipient':
                return $this->editRecipient();
            case 'DeleteEntry':
                return $this->DeleteEntry();
            case 'LineSave':
                return $this->LineSave();
            case 'ImageSave':
                return $this->ImageSave();
            case 'SaveRecipient':
                return $this->saveRecipient();
            case 'Recipient':
                return $this->getRecipient($_POST['ID']);
            case 'Save':
                return $this->Save();
            case 'Get':
                return $this->getList($_POST['IDData']);
            case 'EditRecipientList':
                return $this->editRecipientList();
            case 'DeleteRecipient':
                return $this->deleteRecipient();
            default:
                return;
        }
    }

    function getList($Data) {
        $Data = (int)$Data;

        $query = 'SELECT ID,IDDoc,Date,Note,PlaceTaken,PlaceDone FROM `Data` WHERE ID = ' . $Data;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Invoices (' . __LINE__ . ')');
        }
        $Info = array();

        while ($row = $result->fetch_assoc()) {
            $Info['ID'] = $row['ID'];
            $Info['IDDoc'] = $row['IDDoc'];
            $Info['Date'] = Invoice::date2text($row['Date']);
            $Info['IzDate'] = Invoice::izsnigsanastext($row['Date']);
            $Info['Note'] = $row['Note'];
            $Info['PlaceTaken'] = $row['PlaceTaken'];
            $Info['PlaceDone'] = $row['PlaceDone'];
        }

        $recipients = Invoice::getRecipientsAsArray();
        foreach ($recipients as $k => $v)
            $recipients[$k] = 'name: "' . $v . '", val:"' . $k . '"';
        $Info['recipients'] = '{' . implode('},{', $recipients) . '}';

        $query1 = 'SELECT ID,DocID,Samaksa,recipient,Atlaide,Izsniedza,recipientID FROM `invoices` WHERE DocID = ' . $Data;

        if (!$result = self::$DB->query($query1)) {
            throw new AppError('Read error on Invoices (' . __LINE__ . ')');
        }

        $Info['SaveID'] = 0;
        while ($row = $result->fetch_assoc()) {
            $Info['SaveID'] = $row['ID'];
            $Info['Samaksa'] = $row['Samaksa'];
            $Info['recipientID'] = $row['recipientID'];
            $Info['Atlaide'] = $row['Atlaide'];
            $Info['Izsniedza'] = $row['Izsniedza'];
        }

        $query2 = 'SELECT ID,Nosaukums,Artikuls,Daudzums,Mervieniba,Cena FROM `invoice_items` WHERE DocID = ' . $Data;

        if (!$result = self::$DB->query($query2)) {
            throw new AppError('Read error on Invoices (' . __LINE__ . ')');
        }

        $a = 0;
        while ($row = $result->fetch_assoc()) {
            $a++;

            $Info['tabula'] .= "<tr class=\"bordersolidadd\" id=\"$a\" name=\"" . $row['ID'] . "\">
                 <td width=\"40%\"> <input value=\"{$row['Nosaukums']}\" type=\"text\" class=\"Precu_nosaukums\" size=\"106\"></td>
                 <td width=\"20%\"> <input value=\"{$row['Artikuls']}\" type=\"text\" class=\"Artikuls\" size=\"50\"></td>
                 <td width=\"5%\"> <input value=\"{$row['Daudzums']}\" type=\"text\" id=\"$a\" class=\"Daudz\" size=\"16\" onblur=\"summ(this.id)\"></td>
                 <td width=\"5%\"> <input value=\"{$row['Mervieniba']}\" type=\"text\" class=\"Merv\" size=\"15\"></td>
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

        $Info['__template'] = '/Invoice/Supplier';
        return json_encode(array(1, Template::Process($Info)));
    }

    function Save() {
        $this->fetchObject($_POST);
        $Err = AppError::getErrors(get_class($this));

        if (empty($Err)) {
            $this->Add();
            return 1;
        } else {
            $Err[0] = 0;
            return json_encode($Err);
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
            throw new AppError('Write error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    static function Delete($IDS) {
        $query = 'DELETE FROM `Info` WHERE `IDSupplier`=' . $IDS;

        if (!self::$DB->query($query)) {
            throw new AppError('Delete error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    function getRecipient($id) {
        $query = "SELECT * FROM `recipients` WHERE `ID` = '$id'";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Invoices (' . __LINE__ . ')');
        }
        $recipients = array();
        while ($row = $result->fetch_assoc()) {
            $recipients['ID'] = $row['ID'];
            $recipients['Nosaukums'] = rawurldecode($row['Nosaukums']);
            $recipients['Kods'] = $row['Kods'];
            $recipients['Adrese'] = rawurldecode($row['Adrese']);
            $recipients['Banka'] = rawurldecode($row['Banka']);
            $recipients['Konts'] = $row['Konts'];
        }
        return implode("|", $recipients);
    }

    static function getRecipientsAsArray() {
        $query = 'SELECT *
FROM `recipients`
WHERE ID >0
ORDER BY `Nosaukums`';

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Invoices (' . __LINE__ . ')');
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

    function saveRecipient() {
        $Nosaukums = rawurlencode($_POST['Nosaukums']);
        $Kods = $_POST['Kods'];
        $JurAdrese = rawurlencode($_POST['JurAdrese']);
        $Kreditiestade = rawurlencode($_POST['Kreditiestade']);
        $Konts = $_POST['Konts'];
        $Telefons = $_POST['Telefons'];
        $Epasts = $_POST['Epasts'];

        $query = 'INSERT INTO `recipients` (
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
            throw new AppError('Write error on Invoice (' . __LINE__ . ')');
        }
        $this->MakeCont($_POST['Nosaukums'], $_POST['Kods'], $_POST['JurAdrese'], $_POST['Kreditiestade'], $_POST['Konts'], $_POST['Telefons'], $_POST['Epasts']);
        return 1;
    }

    function loadRecipient($ID) {
        $query = 'SELECT * FROM `recipients` WHERE ID = "' . $ID . '"';
        //ID,Nosaukums, Kods, Adrese, Banka, Konts, Telefons, Epasts
        $Table = $this->good_query_table($query);
        $Table['__template'] = '/Invoice/ChangeRecipient';
        return $Table;
    }

    function editRecipientList() {
        $query = "SELECT * FROM `recipients` where Status = 0 ";

        $Table = $this->good_query_table($query);

        $Table['__template'] = '/Invoice/Table';
        return Template::Process($Table);
    }

    function CheckUsage($ID) {
        $query = "SELECT DocID FROM `invoices` WHERE recipientID = " . $ID;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Invoices (' . __LINE__ . ')');
        }

        if ($result->num_rows > 0) {
            $invoices = array();
            while ($row = $result->fetch_assoc()) {
                $invoices[] = $row['DocID'];
            }

            return $invoices;
        }
    }

    function deleteRecipient() {
        $ID = $_POST['ID'];

        $parbaude = $this->CheckUsage($ID);
        $Names = implode(", ", $parbaude);

        if ($parbaude > 0) {
            return "Šis uzņēmums tiek izmantots rēķinā: " . $Names . " !";
        }

        $query = 'Update `recipients` SET `Status`= 1 WHERE `ID`=' . $ID;

        if (!self::$DB->query($query)) {
            throw new AppError('Delete error on Data (' . __LINE__ . ')');
        }

        return 1;
    }

    function editRecipient() {
        $ID = $_POST['ID'];
        $query = 'UPDATE recipients
            SET `Nosaukums` = "' . str_replace("%27%27", "%22", rawurlencode($_POST['Nosaukums'])) . '" ,
                `Kods` = "' . $_POST['Kods'] . '" ,
                `Adrese` = "' . str_replace("%27%27", "%22", rawurlencode($_POST['JurAdrese'])) . '" ,
                `Banka` = "' . str_replace("%27%27", "%22", rawurlencode($_POST['Kreditiestade'])) . '" ,
                `Konts` = "' . $_POST['Konts'] . '",
                `Epasts` = "' . rawurlencode($_POST['Epasts']) . '",
                `Telefons` = "' . rawurlencode($_POST['Telefons']) . '"
            WHERE ID = "' . $ID . '"';

        if (!self::$DB->query($query)) {
            throw new AppError('Write error on Invoice (' . __LINE__ . ')');
        }
        $this->MakeCont($_POST['Nosaukums'], $_POST['Kods'], $_POST['JurAdrese'], $_POST['Kreditiestade'], $_POST['Konts'], $_POST['Telefons'], $_POST['Epasts']);
        return 1;
    }

    function izsnigsanastext($datums) {
        $menesis = array("", "janvāris", "februāris", "marts", "aprīlis", "maijs", "junījs", "julījs", "augusts", "septembris", "oktobris", "novembris", "decembris");

        list($dat) = explode(" ", $datums);
        list($yehr, $month, $day) = explode("-", $dat);

        $textdate = $yehr . ".gada " . $menesis[date('n', $month)];

        return $textdate;
    }

    function ImageSave() {
        $ID = $_POST['ID'];
        $DocID = $_POST['invoiceID'];
        $Samaksa = $_POST['samaksaskartiba'];
        $recipient = rawurlencode($_POST['Recipient']);
        $Atlaide = $_POST['Atlaide'];
        $Izsniedza = $_POST['izsniedza'];
        $Kopa = $_POST['Kopa'];
        $atlaidessumma = $_POST['atlaidessumma'];
        $PirmsNodokliem = $_POST['PirmsNodokliem'];
        $PVN = $_POST['PVN'];
        $Samaksai = $_POST['Samaksai'];
        $recipientID = $_POST['recipientID'];

        if ($ID == 0) {
            $query = "INSERT INTO `invoices` (
`DocID` ,
`Samaksa` ,
`recipient` ,
`Atlaide` ,
`izsniedza`,
`Kopa` ,
`atlaidessumma` ,
`PirmsNodokliem` ,
`PVN` ,
`Samaksai`,
`recipientID`
)
VALUES (
 '$DocID', '$Samaksa', '$recipient', '$Atlaide', '$Izsniedza', '$Kopa', '$atlaidessumma', '$PirmsNodokliem', '$PVN', '$Samaksai','$recipientID'
);";
        } else {
            $query = "UPDATE invoices
SET `DocID` = '$DocID',
`Samaksa` = '$Samaksa' ,
`recipient` = '$recipient' ,
`Atlaide` =  '$Atlaide',
`izsniedza` = '$Izsniedza',
`Kopa` = '$Kopa',
`atlaidessumma` = '$atlaidessumma',
`PirmsNodokliem` = '$PirmsNodokliem',
`PVN` = '$PVN',
`Samaksai` = '$Samaksai',
`recipientID` = '$recipientID'
WHERE ID = '$ID'";
        }

        $query2 = "Update Data SET `TextOrder` = '" . addslashes(rawurldecode($recipient)) . "' WHERE ID = '$DocID'";

        if (!self::$DB->query($query)) {
            throw new AppError('Write error on Invoice (' . __LINE__ . ')');
        }

        if (!self::$DB->query($query2)) {
            throw new AppError('Write error on Invoice (' . __LINE__ . ')');
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
            $query = 'INSERT INTO `invoice_items` (
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
            $query = 'UPDATE invoice_items
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
            throw new AppError('Write error on Invoice (' . __LINE__ . ')');
        }

        return 1;
    }

    static function DeleteEntry() {
        $ID = $_POST['id'];
        $query = 'DELETE FROM `invoice_items` WHERE `ID`=' . $ID;

        if (!self::$DB->query($query)) {
            throw new AppError('Delete error on Info (' . __LINE__ . ')');
        }

        return 1;
    }

    /**
     * Automatically creates new contacts, combined with the save function to preserve logic.
     * In row 234659 it was requested to recreate the row after changes.
     * If in the future it becomes necessary to update rows, add Data.ID row to the recipients table and pass it here.
     * @return void
     * @author
     */
    function MakeCont($Title, $Code, $Address, $Bank, $Account, $Phone, $Mail) {
        $Phone == 0 ? $Phone = '' : $Phone = $Phone;
        $Cont['IDDoc'] = 'Sistēmas ';
        $Cont['IDOrder'] = '1627';
        $Cont['IDType'] = '60';
        $Cont['PlaceTaken'] = 'srv';
        $Cont['IDPerson'] = '36';
        $Cont['RemindDate'] = '';
        $Cont['Note'] = $Title . '; ' . $Code . '; ' . $Address . '; ' . $Bank . '; ' . $Account . '; ' . $Phone . '; ' . $Mail;
        $Cont['Date'] = date('Y-m-d H:i:s');
        $_POST = $Cont;
        $data = new Data;
        $data->Save();
    }

}
