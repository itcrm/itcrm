<?php

class Changes extends DBObject {
    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        $Vars['Content'] = $this->GetChanges(self::$url[2]);
        return Template::Process('index', $Vars);
    }

    function GetChanges($ID) {
        $query = 'SELECT  D.ID as ID_Row, D.*,
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
               WHERE D.`ID`=' . $ID . ' UNION
               SELECT D.*, D.ID as IDS,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `DateShow`,
                         DATE_FORMAT(D.`Date`,"%y.%m.%d %H:%i") as `Date`,
                         DATE_FORMAT(D.`AddDate`,"%y.%m.%d %H:%i") as `AddDate`,
                         DATE_FORMAT(D.`RemindDate`,"%y.%m.%d %H:%i") as `RemindDate`,
                         `RemindDate` as RemindDateStamp,
                         P.Login as Person, U.Login as User, R.Login as RemindTo,
                         O.Code as `Order`, T.Code as Type
                    FROM `data_auditing` D
               LEFT JOIN Users P ON (P.ID=D.IDPerson)
               LEFT JOIN Users U ON (U.ID=D.IDUser)
               LEFT JOIN Users R ON (R.ID=D.RemindTo)
               LEFT JOIN Orders O ON (O.ID=D.IDOrder)
               LEFT JOIN Types T ON (T.ID=D.IDType)
               WHERE D.`ID_Row`=' . $ID . ' ORDER BY ID_ROW DESC';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Changes  (' . __LINE__ . ')');
        }
        $Changes = array();

        while ($row = $result->fetch_assoc()) {
            if ($i % 2 == 0) $row['Odd'] = 'Odd';
            $i++;
            $row['Changes'] = $row['Changes'] == '' ? 'hide' : '';
            $row['AdminEditClass'] = $row['AdminEdit'] == 1 ? 'AdminEdit' : '';
            $row['HiddenClass'] = $row['Hidden'] == 1 ? 'hidden' : '';
            $Changes[] = $row;
        }
        if (!empty($Changes)) {
            $Changes['__template'] = 'Row';
            return $Changes;
        }
        return '';
    }
}
