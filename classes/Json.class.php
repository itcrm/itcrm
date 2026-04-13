<?php
class Json extends DBObject {
    function Load() {
        switch (@self::$url[2]) {
            case 'FilterOrders':
                $this->GetFilterOrders($_GET['term']);
                die;
            case 'FilterPersons':
                $this->GetFilterPersons($_GET['term']);
                die;
            case 'FilterTypes':
                $this->GetFilterTypes($_GET['term']);
                die;
            case 'Groups':
                $this->GetGroups($_GET['term']);
                die;
            case 'Persons':
                $this->GetPersons($_GET['term']);
                die;
            case 'Types':
                $this->GetTypes($_GET['term']);
                die;
            case 'Orders':
                $this->GetOrders($_GET['term']);
                die;
            case 'Warehouse':
                $this->getWarehouse($_GET['term']);
                die;
            case 'GetVeikals':
                $Data = Data::warehouseDialog($_POST['ID']);
                print Template::Process('/Dialog/GetVeikals', $Data);
                die;
            case 'ProductGroups':
                $Data['ProductGroups'] = Data::ProductGroups($_POST['ID']);
                print Template::Process('/Dialog/ProductGroups', $Data);
                die;
            case 'AddFiles':
                $Data = Data::getRow($_POST['ID']);
                print Template::Process('/Dialog/AddFiles', $Data);
                die;
            case 'AddPicture':
                $Send['ID'] = $_POST['ID'];
                $Data = Data::getRow($_POST['ID']);
                print Template::Process('/Dialog/AddPicture', $Data);
                die;
            case 'ErrorLogger':
                print $this->ErrorLogger($_POST);
                die;
        }
    }

    function GetFilterOrders($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT ID,Code FROM `Orders` WHERE Code LIKE ? ORDER BY `Code` LIMIT 20';
        if (!$result = self::$DB->prepare($query, [$code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            $Orders[] = $row;
        }

        $Orders = json_encode($Orders);
        echo str_replace("Code", "label", $Orders);
    }

    function GetFilterPersons($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT ID, Login FROM `Users` WHERE Login LIKE ? AND `Status` >-3
    ORDER BY `Login` LIMIT 30';
        if (!$result = self::$DB->prepare($query, ['%' . $code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Users = array();
        while ($row = $result->fetch_assoc()) {
            $Users[] = $row;
        }

        $Users = json_encode($Users);
        echo str_replace("Login", "label", $Users);
    }

    function GetFilterTypes($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT ID, Code FROM `Types` WHERE Code LIKE ? ORDER BY `Code` LIMIT 30';
        if (!$result = self::$DB->prepare($query, [$code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Type = array();
        while ($row = $result->fetch_assoc()) {
            $Type[] = $row;
        }

        $Type = json_encode($Type);
        echo str_replace("Code", "label", $Type);
    }

    function GetGroups($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT id as ID, title as label FROM `groups_linear` WHERE title LIKE ? ORDER BY `iorder` LIMIT 30';
        if (!$result = self::$DB->prepare($query, ['%' . $code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Type = array();
        while ($row = $result->fetch_assoc()) {
            $Type[] = $row;
        }

        $Type = json_encode($Type);
        echo $Type;
    }

    function GetPersons($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT ID, Login FROM `Users` WHERE Login LIKE ? AND `Status` > 0 ORDER BY `Login` LIMIT 30';
        if (!$result = self::$DB->prepare($query, [$code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Users = array();
        while ($row = $result->fetch_assoc()) {
            $Users[] = $row;
        }

        $Users = json_encode($Users);
        echo str_replace("Login", "label", $Users);
    }

    function GetTypes($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT ID, Code FROM `Types` WHERE Code LIKE ? AND `Status`=1 ORDER BY `Code` LIMIT 30';
        if (!$result = self::$DB->prepare($query, [$code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Type = array();
        while ($row = $result->fetch_assoc()) {
            $Type[] = $row;
        }

        $Type = json_encode($Type);
        echo str_replace("Code", "label", $Type);
    }

    function GetOrders($code) {
        $code = substr(strrchr(", " . $code, ', '), 2);
        $query = 'SELECT ID,Code FROM `Orders` WHERE Code LIKE ? AND `Status`=1 ORDER BY `Code` LIMIT 20';
        if (!$result = self::$DB->prepare($query, [$code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            $Orders[] = $row;
        }

        $Orders = json_encode($Orders);
        echo str_replace("Code", "label", $Orders);
    }

    function getWarehouse($code) {
        $query = 'SELECT ID, PlaceTaken AS label FROM `Data` WHERE IDType=? AND PlaceTaken LIKE ? AND `Status`=1
                   ORDER BY `PlaceTaken` LIMIT 20';
        if (!$result = self::$DB->prepare($query, [Config::WarehouseTypeID, $code . '%'])) {
            throw new AppError('Read error on Json (' . __LINE__ . ')');
        }
        $warehouse = array();
        while ($row = $result->fetch_assoc()) {
            $warehouse[] = $row;
        }

        $Orders = json_encode($warehouse);
        echo $Orders;
    }

    /**
     * Funkcija saglabā javascript kļūdas ko saņem no error hendlera
     *
     * @return none
     */
    private function ErrorLogger($data) {
        $query = 'INSERT INTO `Error` (`Time`, `User`, `Type`, `Url`, `Line`, `Message`)
                  VALUES (datetime(\'now\'), ?, ?, ?, ?, ?)';

        if (!self::$DB->prepare($query, [
            $_SESSION['User']->getID(),
            $_SESSION['Filter']['Type'],
            $data['url'],
            $data['line'],
            $data['message']
        ])) {
            throw new AppError('Write error on Json (' . __LINE__ . ') : ' . self::$DB->error);
        }
        return 'Kļūda failā: ' . $data['url'] . ' līnijā:' . $data['line'] . ' ar paziņojumu:' . $data['message'];
    }
}
