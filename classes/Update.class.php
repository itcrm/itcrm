<?php

/**
 * klase paredzēta veikala preču apdeitošanai
 */
class Update extends DBObject {
    function __construct() {
        // 2362 Detaļu tipa ID

        $this->GetID(2362);
    }

    /**
     * Nosaka visus tipa Identifikatorus un atlasa nepieciešamas rindas datupievienošanai.
     *
     * @return String
     * @author
     */
    private function GetID($Type) {
        // add faili image function
        $fileFnExists = false;
        if (file_exists("faili/sysapi.php")) {
            require_once "faili/sysapi.php";
            $fileFnExists = true;
        }

        $query = "SELECT ID FROM Data WHERE IDType =" . $Type;

        if (!$result = self::$DB->query($query)) {
            throw new Error('SQL Read error on Class (' . get_class($this) . ') in function (' . __FUNCTION__ . ') on Line (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $shop = Warehous::getRow($row['ID']);
            $shop['Shop'] == 1 ? $Shop = 'V' : $Shop = '';
            if ($fileFnExists) {
                $link = _faili_row_file_exists($row['ID']);
            }
            $link != NULL ? $Picture = 'B' : $Picture = '';
            $Shop != NULL && $Picture != NULL ? $Space = ' ' : $Space = '';
            $TextType = $Shop . $Space . $Picture;
            if ($Shop == NULL && $Picture == NULL) {
                // Apdeitot ka tukšu
                $this->Update($row['ID'], $TextType);
            } else {
                // Apdeitot V + B vai B vai V
                $this->Update($row['ID'], $TextType);
            }
        }
        return $data;
    }

    /**
     * Funkcija Apdeito data tabulā TextType kolonu
     *
     * @return none
     * @author
     */
    function Update($ID, $TextType) {
        $query = 'UPDATE `Data` SET TextType = "' . $TextType . '" WHERE ID=' . $ID;
        if (!self::$DB->query($query)) {
            throw new Error('SQL Read error on Class (' . get_class($this) . ') in function (' . __FUNCTION__ . ') on Line (' . __LINE__ . ')');
        }
    }
}
