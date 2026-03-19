<?php
class Josn extends DBObject {
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
            case 'Noliktava':
                $this->GetNoliktava($_GET['term']);
                die;
            case 'EditSanemejs':
                $Data['Dati'] = urldecode(Pavadzime::EditSanList());
                print Template::Process('/Dialog/EditSanemejs', $Data);
                die;
            case 'GetVeikals':
                $Data = Data::noliktavaDialog($_POST['ID']);
                print Template::Process('/Dialog/GetVeikals', $Data);
                die;
            case 'PrecuGrupas':
                $Data['PrecuGrupas'] = Data::PrecuGrupas($_POST['ID']);
                print Template::Process('/Dialog/PrecuGrupas', $Data);
                die;
            case 'AddSanemejs':
                $Data = Pavadzime::LoadSanemeji($_POST['ID']);
                print urldecode(Template::Process('/Pavadzime/ChangeSanemejs', $Data));
                die;
            case 'NewSanemejs':
                print urldecode(Template::Process('/Pavadzime/NewSanemejs', $Data));
                die;
            case 'NrExist':
                echo $this->NrExist($_GET['value']);
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
        $query = "SELECT ID,Code FROM `Orders` WHERE Code LIKE '" . $code . "%'
                   ORDER BY `Code` LIMIT 0,20";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
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
        $query = "SELECT ID, Login FROM `Users` WHERE Login LIKE '%" . $code . "%' AND `Status` >-3
    ORDER BY `Login` LIMIT 0,30";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
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
        $query = "SELECT ID, Code FROM `Types` WHERE Code LIKE '" . $code . "%'
                   ORDER BY `Code` LIMIT 0,30";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
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
        $query = "SELECT id as ID, title as label FROM `groups_linear` WHERE title LIKE '%" . $code . "%'
                   ORDER BY `iorder` LIMIT 0,30";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
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
        $query = "SELECT ID, Login FROM `Users` WHERE Login LIKE '" . $code . "%' AND `Status` > 0
                   ORDER BY `Login` LIMIT 0,30";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
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
        $query = "SELECT ID, Code FROM `Types` WHERE Code LIKE '" . $code . "%' AND `Status`=1
                   ORDER BY `Code` LIMIT 0,30";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
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
        $query = "SELECT ID,Code FROM `Orders` WHERE Code LIKE '" . $code . "%' AND `Status`=1
                   ORDER BY `Code` LIMIT 0,20";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
        }
        $Orders = array();
        while ($row = $result->fetch_assoc()) {
            $Orders[] = $row;
        }

        $Orders = json_encode($Orders);
        echo str_replace("Code", "label", $Orders);
    }

    function GetNoliktava($code) {
        $query = "SELECT ID, PlaceTaken AS label FROM `Data` WHERE IDType='" . Config::Noliktava . "' AND PlaceTaken LIKE '" . $code . "%' AND `Status`=1
                   ORDER BY `PlaceTaken` LIMIT 0,20";
        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
        }
        $Noliktava = array();
        while ($row = $result->fetch_assoc()) {
            $Noliktava[] = $row;
        }

        $Orders = json_encode($Noliktava);
        echo $Orders;
    }

    function NrExist($value) {
        $query = "SELECT * FROM `Data` WHERE IDType='72' AND IDDoc = '" . $value . "'";

        if (!$result = self::$DB->query($query)) {
            throw new AppError('Read error on Josn (' . __LINE__ . ')');
        }

        if ($result->num_rows > 0) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Funkcija saglabā javascript kļūdas ko saņem no error hendlera
     *
     * @return none
     */
    private function ErrorLogger($data) {
        $query = 'INSERT INTO `Error`
                     SET `Time`=NOW(),
                         `User`="' . $_SESSION['User']->getID() . '",
                         `Type`="' . $_SESSION['Filter']['Type'] . '",
                         `Url`="' . $data['url'] . '",
                         `Line`="' . $data['line'] . '",
                         `Message`="' . $data['message'] . '"';

        if (!self::$DB->query($query)) {
            throw new AppError('Write error on Josn (' . __LINE__ . ') : ' . self::$DB->error);
        }
        return 'Kļūda failā: ' . $data['url'] . ' līnijā:' . $data['line'] . ' ar paziņojumu:' . $data['message'];
    }
}
