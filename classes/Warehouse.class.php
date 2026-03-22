<?php

class Warehouse extends DBObject {
    protected $ID;
    protected $rindasID;
    protected $Password;
    protected $partID;
    protected $daudzums;
    protected $type;
    protected $Shop;
    protected $ShopCategoryID;
    protected $ShopDescription;
    protected $ShopModelID;
    protected $ShopTitle;
    protected $OriginalCode;
    protected $addition;
    protected $offer;
    protected $state;
    protected $used;

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'Export':
                return $this->Export();
            case 'slider':
                return $this->slider();
            default:
                $Vars['Content'] = $this->ListLimits();
                break;
        }
        $Vars['slider'] = isset($_SESSION['menu']) && $_SESSION['menu'] == 0 ? "none" : "block";
        $Vars['SLO'] = isset($_SESSION['menu']) && $_SESSION['menu'] == 0 ? "blok" : "none";
        return Template::Process('index', $Vars);
    }

    function izdot($ID, $RID, $daudzums, $vienibas, $title, $type, $order, $sum) {
        //Tiek noteikta atiecigā darbība konkrētajam tipam;
        $TotalPrice = "";
        if ($type == Config::AddToWarehouseTypeID) {
            $TotalPrice = "`TotalPrice` = `TotalPrice`+" . $daudzums;
        }
        if ($type == Config::RemoveFromWarehouseTypeID) {
            $TotalPrice = Warehouse::NewOrder($ID, $order) . $daudzums;
            $RTotalPrice = ", `TotalPrice` = '" . $sum . "'";
        }

        if ($type == Config::ReserveFromWarehouseTypeID) {
            $TotalPrice = "`TotalPrice` = `TotalPrice`-" . $daudzums;
            $Hours =  ", `Hours` = `Hours`+" . $daudzums;
            $RTotalPrice = ", `TotalPrice` = '" . $sum . "'";
        }

        if ($type == Config::ReturnToWarehouseTypeID) {
            //$return = Warehouse::ReturnToOrder($ID,$order,$daudzums);  //parbaude uz atgriesanu vai nav mazak ka velas atgries, ka ari vai vispar ir ko.
            $TotalPrice = "`TotalPrice` = `TotalPrice`+" . $daudzums;
            $Hours =  ", `Hours` = `Hours`-" . $daudzums;
            $RTotalPrice = ", `TotalPrice` = '" . $sum . "'";
        }

        //funkcija matematski norada darbibu ar daudzumu;
        //Darbības ar Preci;
        $query = "UPDATE `Data` SET " . $TotalPrice . " " . $Hours . " WHERE `ID`=" . $ID;
        if (!self::$DB->query($query)) {
            throw new AppError('Write error on Warehouse (' . __LINE__ . ') : ' . self::$DB->error);
        }
        // Rindai tiek pievienots teksts ar detaļas numuru un daudzumu kas tika pievienots;
        //Darbibas ar Rindu;
        $query = "UPDATE `Data` SET
                     `Note` = '" . addslashes(Data::getNote()) . " " . $title . "*" . $daudzums . " " . $vienibas . "' " . $RTotalPrice . " WHERE `ID`=" . $RID;
        if (!self::$DB->query($query)) {
            throw new AppError('Write error on Warehouse (' . __LINE__ . ') : ' . self::$DB->error);
        }
    }

    function NewOrder($ID, $order) {
        $query = "SELECT Data.ID, Data.IDorder, warehouse.partID, warehouse.daudzums FROM Data, warehouse  WHERE warehouse.rindasID = Data.ID and Data.IDorder = " . $order . " AND IDtype = " . Config::ReserveFromWarehouseTypeID . " AND warehouse.partID =" . $ID;
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Write error on Warehouse (' . __LINE__ . ') : ' . self::$DB->error);
        }
        if ($result->num_rows == 0) {
            return "`TotalPrice` = `TotalPrice`-";
        } else {
            return "`Hours` = `Hours`-";
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
               LEFT JOIN warehouse N ON (D.TotalPrice <= N.partID)
WHERE N.rindasID = D.ID AND N.type = 1 AND D.IDType = ' . Config::WarehouseTypeID;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Warehouse (' . __LINE__ . ')');
        }
        $warehouse = array();

        while ($row = $result->fetch_assoc()) {
            if ($i % 2 == 0) $row['Odd'] = 'Odd';
            $i++;

            if (in_array($row['ID'], $_SESSION['CheckedRow'])) {
                $row['checked'] = 'checked';
                $row['Function'] = "UnCheckRow";
                $row['select'] = "selected";
            }
            $row['Function'] = "CheckRow";

            $row['Deleted'] = $row['Status'] != -1 ? 'hide' : '';
            $row['Status'] = $row['Status'] == -1 ? 'deleted' : '';
            $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';
            $row['order_title'] = urlencode($row['Order']);

            if ($row['IDType'] == Config::WarehouseTypeID) $row['dblClick'] = 'getWarehouseMaterials(this);';

            $warehouse[] = $row;
        }

        if (!empty($warehouse)) {
            $warehouse['__template'] = 'Row';
            return $warehouse;
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

    function slider() {
        if ($_SESSION['menu'] == 1) {
            $_SESSION['menu'] = 0;
        } else {
            $_SESSION['menu'] = 1;
        }
    }

    function getRow($ID) {
        $query = 'SELECT * FROM `warehouse` WHERE `rindasID`=' . (int)$ID;

        if (!$result = self::$DB->query($query)) {
            throw new AppError('SQL Read error on Class (' . get_class($this) . ') in function (' . __FUNCTION__ . ') on Line (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $results = $row;
        }

        return  $results;
    }

    function AddNew($ID, $Sum) {
        $query = "INSERT INTO `warehouse` (rindasID, daudzums, type) VALUES('" . $ID . "', '" . $Sum . "', 1)";

        if (!self::$DB->query($query)) {
            throw new AppError('Write error on Warehouse (' . __LINE__ . ') : ' . self::$DB->error);
        }
    }
}
