<?php

class Warehous extends DBObject {
    private $ID;
    private $rindasID;
    private $Password;
    private $detalasID;
    private $daudzums;
    private $type;
    private $Shop;
    private $ShopCategoryID;
    private $ShopDescription;
    private $ShopModelID;
    private $ShopTitle;
    private $OrginalCode;
    private $addition;
    private $offer;
    private $state;
    private $used;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Export':
                return $this->Export();
                break;
            case 'slieder':
                return $this->slieder();
                break;
            default:
                $Vars['Content'] = $this->ListLimits();
                break;
        }
        $Vars['slieder'] = isset($_SESSION['menu']) && $_SESSION['menu'] == 0 ? "none" : "block";
        $Vars['SLO'] = isset($_SESSION['menu']) && $_SESSION['menu'] == 0 ? "blok" : "none";
        return Template::Process('index', $Vars);
    }

    function izdot($ID, $RID, $daudzums, $vienibas, $title, $type, $order, $sum) {
        //Tiek noteikta atiecigā darbība konkrētajam tipam;
        $TotalPrice = "";
        if ($type == Config::AddNoliktava) {
            $TotalPrice = "`TotalPrice` = `TotalPrice`+" . $daudzums;
        }
        if ($type == Config::DelNoliktava) {
            $TotalPrice = Warehous::HewOrder($ID, $order) . $daudzums;
            $RTotalPrice = ", `TotalPrice` = '" . $sum . "'";
        }

        if ($type == Config::ReservNoliktava) {
            $TotalPrice = "`TotalPrice` = `TotalPrice`-" . $daudzums;
            $Hours =  ", `Hours` = `Hours`+" . $daudzums;
            $RTotalPrice = ", `TotalPrice` = '" . $sum . "'";
        }

        if ($type == Config::ReturnNoliktava) {
            //$return = Warehous::ReturnToOrder($ID,$order,$daudzums);  //parbaude uz atgriesanu vai nav mazak ka velas atgries, ka ari vai vispar ir ko.
            $TotalPrice = "`TotalPrice` = `TotalPrice`+" . $daudzums;
            $Hours =  ", `Hours` = `Hours`-" . $daudzums;
            $RTotalPrice = ", `TotalPrice` = '" . $sum . "'";
        }

        //funkcija matematski norada darbibu ar daudzumu;
        //Darbības ar Preci;
        $query = "UPDATE `Data` SET " . $TotalPrice . " " . $Hours . " WHERE `ID`=" . $ID;
        if (!self::$DB->query($query)) {
            throw new Error('Write error on warehous (' . __LINE__ . ') : ' . self::$DB->error);
        }
        // Rindai tiek pievienots teksts ar detaļas numuru un daudzumu kas tika pievienots;
        //Darbibas ar Rindu;
        $query = "UPDATE `Data` SET
                     `Note` = '" . addslashes(Data::getNote()) . " " . $title . "*" . $daudzums . " " . $vienibas . "' " . $RTotalPrice . " WHERE `ID`=" . $RID;
        if (!self::$DB->query($query)) {
            throw new Error('Write error on warehous (' . __LINE__ . ') : ' . self::$DB->error);
        }
    }

    function HewOrder($ID, $order) {
        $query = "SELECT Data.ID, Data.IDorder, noliktava.detalasID, noliktava.daudzums FROM Data, noliktava  WHERE noliktava.rindasID = Data.ID and Data.IDorder = " . $order . " AND IDtype = " . Config::ReservNoliktava . " AND noliktava.detalasID =" . $ID;
        if (!$result = self::$DB->query($query)) {
            throw new Error('Write error on warehous (' . __LINE__ . ') : ' . self::$DB->error);
        }
        if ($result->num_rows == 0) {
            return "`TotalPrice` = `TotalPrice`-";
        } else {
            return "`Hours` = `Hours`-";
        }
    }

    function ReturnToOrder($ID, $order, $darbiba) {
        // atgriešanas parbaude -- Jarisina jautajums ko darit ja parbaude izgazas dzest rindu vai mainit statusu, piedavaju atgriest mainigo lai veiktu darbibu.
        $query = "SELECT Data.ID, Data.IDorder, noliktava.detalasID, noliktava.daudzums FROM Data, noliktava  WHERE noliktava.rindasID = Data.ID and Data.IDorder = " . $order . " AND IDtype = " . Config::ReservNoliktava . " AND noliktava.detalasID =" . $ID;
        if (!$result = self::$DB->query($query)) {
            throw new Error('Write error on warehous (' . __LINE__ . ') : ' . self::$DB->error);
        }
        if ($result->num_rows == 0) {
            return print "Šim pasūtijumam nav rezervētā atlikuma";
        } else {
            while ($row = $result->fetch_assoc()) {
                $results = $row[Hours];
            }
            return $results;
        }
    }
    function ListLimits() {
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
               LEFT JOIN noliktava N ON (D.TotalPrice <= N.detalasID)
WHERE N.rindasID = D.ID AND N.type = 1 AND D.IDType = 2362';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Warehous (' . __LINE__ . ')');
        }
        $Warehous = array();

        while ($row = $result->fetch_assoc()) {
            if ($i % 2 == 0) $row['Odd'] = 'Odd';
            $i++;

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
            $row['order_title'] = urlencode($row['Order']);
            $row['NoAdmin'] = $_SESSION['isAdmin'] ? '' : 'hide';

            if ($row['IDType'] == Config::Noliktava) $row['dblClick'] = 'getNolMatreals(this);';

            $Warehous[] = $row;
        }

        if (!empty($Warehous)) {
            $Warehous['__template'] = 'Row';
            return $Warehous;
        } else return '';
    }

    function Export() {
        $Data = $this->ListLimits();

        if (!empty($Data)) {
            foreach ($Data as $k => $v) {
                if ($v['Sum'] < 0) $Data[$k]['Sum'] *= -1;
            }
            $Data['__template'] = 'Data/ExcelRow';
            $xml = Template::Process('Data/Excel', array('Data' => $Data));
        } else return Language::$Data['NoDataToExport'];

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=Data.xml;");
        header("Content-Type: application/ms-excel");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $xml;
        exit();
    }

    function slieder() {
        if ($_SESSION['menu'] == 1) {
            $_SESSION['menu'] = 0;
        } else {
            $_SESSION['menu'] = 1;
        }
    }

    /**
     * Funkcija atgriež visus datus no noliktavas tabulas
     *
     * @return array
     * @author
     */
    function getRow($ID) {
        $query = 'SELECT * FROM `noliktava` WHERE `rindasID`=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('SQL Read error on Class (' . get_class($this) . ') in function (' . __FUNCTION__ . ') on Line (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row;
        }

        return  $results;
    }

    /**
     * Funkcija izveido tukšu ierakstu noliktavas tabula ja tiek veidota noliktavas tipa prece ar rokam.
     *
     * Radās kļūdas kad atverot detaļu nav iespējamp pievienot aprakstus jo datus nevar ielasit un ir redzams tikai [: **** :]
     *
     * @return void
     * @author
     */
    function AddNew($ID, $Sum) {
        $query = "INSERT INTO `noliktava` (rindasID, daudzums, type) VALUES('" . $ID . "', '" . $Sum . "', 1)";

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Warehous (' . __LINE__ . ') : ' . self::$DB->error);
        }
    }
}
