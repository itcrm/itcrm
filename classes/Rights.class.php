<?php

class Rights extends DBObject {
    private $IDUser;
    private $Type;
    private $Value;

    private $IDPerson;
    private $IDOrder;
    private $IDType;
    private $IDFolder;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    function Load() {
        switch (isset(self::$url[2]) ? self::$url[2] : '') {
            case 'UpdateOpt':
                if (!$_SESSION['isAdmin']) return '';
                return $this->UpdateOpt();
                break;
            case 'Get':
                return self::ArrayToJson($this->getRights($_POST['IDUser']));
                break;
            case 'Hide':
                if (!$_SESSION['isAdmin']) return '';
                if (self::$url[3] == 'Save')
                    return $this->SaveHide();
                elseif (self::$url[3] == 'Delete')
                    return $this->DeleteHide();
                break;
            case 'Save':
                if (!$_SESSION['isAdmin']) return '';

                if ($_POST["Value"] == '0') {
                    return self::ArrayToJson(array($this->AllRights($_POST["IDUser"], $_POST["Type"])));
                } elseif ($_POST["Value"] == 'minus') {
                    return $this->AllRightsDel($_POST["IDUser"], $_POST["Type"]);
                } else {
                    $answ = $this->Save();
                }
                if ($answ == 1) {
                    return self::ArrayToJson(
                        array(
                            Template::Process(
                                'Row',
                                array(
                                    'Value' => '#VAL#',
                                    'ID' => $this->getValue(),
                                    'Type' => $this->getType()
                                )
                            )
                        )
                    );
                } else return $answ;
                break;
            case 'Delete':
                if (!$_SESSION['isAdmin']) return '';
                return $this->Delete();
                break;
            case 'Filter':
                if (!$_SESSION['isAdmin']) return '';
                if (self::$url[3] == 'Save') return $this->saveFilterRights();
                return $this->getFilterRights($_POST['ID']);
                break;
            default:
                if ($_SESSION['isAdmin'])
                    $Vars['Content'] = $this->editRights();
                elseif ($_SESSION['User'])
                    $Vars['Content'] = $this->showUserRights();
                else return;
                break;
        }

        return Template::Process('index', $Vars);
    }

    function getFilterRights($ID) {
        $query = 'SELECT IDUser FROM RightsFilter WHERE IDFilter=' . (int)$ID;

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Rights (' . __LINE__ . ')');

        $Users = array();
        while ($row = $result->fetch_assoc()) {
            $Users[] = $row['IDUser'];
        }

        if (!empty($Users)) return self::ArrayToJson(array(1, implode(',', $Users)));
        else  return self::ArrayToJson(array(0, Language::$Filters['NoUsers']));
    }

    function saveFilterRights() {
        $query = 'DELETE FROM RightsFilter WHERE IDFilter=' . (int)$_POST['IDFilter'];

        if (!$result = self::$DB->query($query))
            throw new Error('Error on Rights (' . __LINE__ . ')');

        $query = 'INSERT INTO RightsFilter
                  VALUES (' . (implode(',' . (int)$_POST['IDFilter'] . '),(', $_POST['User']) . ',' . (int)$_POST['IDFilter']) . ')';

        if (!$result = self::$DB->query($query))
            throw new Error('Error on Rights (' . __LINE__ . ')');

        return 1;
    }

    static function DeleteFilterRights($IDUser = 0, $IDFilter = 0) {
        $query = 'DELETE FROM RightsFilter WHERE IDFilter=' . $IDFilter;

        if (!$result = self::$DB->query($query))
            throw new Error('Error on Rights (' . __LINE__ . ')');

        $query = 'DELETE FROM RightsFilter WHERE IDUser=' . $IDUser;

        if (!$result = self::$DB->query($query))
            throw new Error('Error on Rights (' . __LINE__ . ')');
    }

    static function getRightsArr($ID) {
        $query = 'SELECT *
                    FROM Rights
                   WHERE IDUser=' . (int)$ID;

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Rights (' . __LINE__ . ')');

        $Rights = array(
            'Persons' => array(),
            'Orders' => array(),
            'Types' => array(),
            'Hide' => array()
        );
        while ($row = $result->fetch_assoc()) {
            $Rights[$row['Type'] . 's'][] = $row['Value'];
        }

        $query = 'SELECT *
                    FROM RightsHide
                    WHERE IDUser=' . (int)$ID;

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Rights (' . __LINE__ . ')');

        while ($row = $result->fetch_assoc())
            $Rights['Hide'][$row['IDPerson'] . '.' . $row['IDOrder'] . '.' . $row['IDType'] . '.' . $row['IDFolder']] = true;

        return $Rights;
    }

    static function getRigthsByType($Type) {
        $query = 'SELECT count(*),Value
                    FROM Rights
                   WHERE `Type`="' . $Type . '"
                GROUP BY `Value`';

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Rights (' . __LINE__ . ')');

        $Rights = array();
        while ($row = $result->fetch_assoc()) {
            $Rights[$row['Value']] = $row['count(*)'];
        }

        return $Rights;
    }

    function getRights($ID) {
        $query = 'SELECT *
                    FROM Rights
                   WHERE IDUser=' . (int)$ID;

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Rights (' . __LINE__ . ')');

        $Values = array(
            'Person' => Users::getAsArray(),
            'Order' => Orders::getAsArray(),
            'Type' => Types::getAsArray(),
            'Folder' => Orders::getAsArray()
        );
        $Rights = array('Persons' => '', 'Orders' => '', 'Types' => '', 'Folders' => '');
        while ($row = $result->fetch_assoc()) {
            if ($Values[$row['Type']][$row['Value']])
                $Rights[$row['Type'] . 's'][] =
                    array(
                        'Value' => $Values[$row['Type']][$row['Value']],
                        'ID' => $row['Value'],
                        'Type' => $row['Type']
                    );
        }

        if (!empty($Rights['Persons'])) {
            asort($Rights['Persons']);
            $Rights['Persons'] = Template::Process('Row', $Rights['Persons']);
        }
        if (!empty($Rights['Orders'])) {
            asort($Rights['Orders']);
            $Rights['Orders'] = Template::Process('Row', $Rights['Orders']);
        }
        if (!empty($Rights['Types'])) {
            asort($Rights['Types']);
            $Rights['Types'] = Template::Process('Row', $Rights['Types']);
        }
        if (!empty($Rights['Folders'])) {
            asort($Rights['Folders']);
            $Rights['Folders'] = Template::Process('Row', $Rights['Folders']);
        }

        $query = 'SELECT *
                    FROM RightsHide
                   WHERE IDUser=' . (int)$ID;

        if (!$result = self::$DB->query($query))
            throw new Error('Read error on Rights (' . __LINE__ . ')');

        $Hide = array();
        while ($row = $result->fetch_assoc()) {
            //$row['IDPerson'].'.'.$row['IDOrder'].'.'.$row['IDType']
            $Hide[] =
                array(
                    'Person' => $Values['Person'][$row['IDPerson']],
                    'Order' => $Values['Order'][$row['IDOrder']],
                    'Type' => $Values['Type'][$row['IDType']],
                    'Folder' => $Values['Folder'][$row['IDFolder']],
                    'IDPerson' => $row['IDPerson'],
                    'IDOrder' => $row['IDOrder'],
                    'IDType' => $row['IDType'],
                    'IDFolder' => $row['IDFolder']
                );
        }
        if (!empty($Hide))
            $Rights['Hide'] = Template::Process('HideRow', $Hide);
        return $Rights;
    }

    function editRights() {
        $Vars = array();
        $Vars['Users'] = Users::getOptionsList();
        $Vars['Orders'] = Orders::getOptionsList();
        $Vars['Types'] = Types::getOptionsList();

        return Template::Process('Edit', $Vars);
    }

    function showUserRights() {
        $Rights = $this->getRights($_SESSION['User']->getID());

        if (empty($Rights['Persons'])) $Rights['Persons'] = '&mdash;';
        if (empty($Rights['Orders'])) $Rights['Orders'] = '&mdash;';
        if (empty($Rights['Types'])) $Rights['Types'] = '&mdash;';

        $Rights['NoAdmin'] = 'hide';
        return Template::Process('Show', $Rights);
    }

    static function addRightsToUser($ID) {
        $Types = Types::getAsArray();
        $Orders = Orders::getAsArray();
        $Users = Users::getAsArray();
        $Folders = Orders::getAsArray();

        $query = 'REPLACE INTO `Rights` (`Value`,Type,IDUser) VALUES
                  (' . implode(',"Order",' . $ID . '),(', array_keys($Orders)) . ',"Order",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }

        $query = 'REPLACE INTO `Rights` (`Value`,Type,IDUser) VALUES
                  (' . implode(',"Type",' . $ID . '),(', array_keys($Types)) . ',"Type",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }

        $query = 'REPLACE INTO `Rights` (`Value`,Type,IDUser) VALUES
                  (' . implode(',"Person",' . $ID . '),(', array_keys($Users)) . ',"Person",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }

        $query = 'REPLACE INTO `Rights` (`Value`,Type,IDUser) VALUES
                  (' . implode(',"Folder",' . $ID . '),(', array_keys($Folders)) . ',"Folder",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }
    }

    static function addUsersToUser($ID) {
        $Users = Users::getAsArray();

        $query = 'REPLACE INTO `Rights` (`Value`,Type,IDUser) VALUES
                  (' . implode(',"Person",' . $ID . '),(', array_keys($Users)) . ',"Person",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }
    }

    static function addUserToUsers($ID) {
        $Users = Users::getAsArray();

        $query = 'REPLACE INTO `Rights` (IDUser,Type,`Value`) VALUES
                  (' . implode(',"Person",' . $ID . '),(', array_keys($Users)) . ',"Person",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }
    }

    static function addRights($ID, $Type) {
        $Users = Users::getAsArray();

        $query = 'REPLACE INTO `Rights` VALUES
                  (' . implode(',"' . $Type . '",' . $ID . '),(', array_keys($Users)) . ',"' . $Type . '",' . $ID . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }
    }

    /**
     * DB functions
     */
    function Save() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $query = 'REPLACE INTO `Rights`
                          SET `IDUser`=' . (int)$this->getIDUser() . ',
                              `Type`="' . addslashes($this->getType()) . '",
                              `Value`=' . (int)$this->getValue();

            if (!self::$DB->query($query)) {
                throw new Error('Write error on Rights (' . __LINE__ . ')');
            }
            return 1;
        } else {
            return array_pop($Err);
        }
    }

    function SaveHide() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $query = 'REPLACE INTO `RightsHide`
                          SET `IDUser`=' . (int)$this->getIDUser() . ',
                              `IDPerson`=' . (int)$this->getIDPerson() . ',
                              `IDOrder`=' . (int)$this->getIDOrder() . ',
                              `IDType`=' . (int)$this->getIDType() . ',
                              `IDFolder`=' . (int)$this->getIDFolder();

            if (!self::$DB->query($query)) {
                throw new Error('Write error on Rights (' . __LINE__ . ')');
            }

            $Vars =  array(
                'Person' => '#Person#',
                'Order' => '#Order#',
                'Type' => '#Type#',
                'Folder' => '#Folder#',
                'IDPerson' => $this->getIDPerson(),
                'IDOrder' => $this->getIDOrder(),
                'IDType' => $this->getIDType(),
                'IDFolder' => $this->getIDFolder()
            );
            return self::ArrayToJson(array(Template::Process('/Rights/HideRow', $Vars)));
        } else {
            return array_pop($Err);
        }
    }

    function Delete() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $query = 'DELETE FROM `Rights`
                       WHERE `IDUser`=' . $this->getIDUser() . '
                         AND `Type`="' . addslashes($this->getType()) . '"
                         AND `Value`=' . $this->getValue();
            if (!self::$DB->query($query)) {
                throw new Error('Delete error on Types (' . __LINE__ . ')');
            }

            return 1;
        }
        return array_pop($Err);
    }

    function DeleteById($ID, $Type) {
        $query = 'DELETE FROM `Rights`
                       WHERE `Type`="' . $Type . '"
                         AND `Value`=' . $ID;
        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Types (' . __LINE__ . ')');
        }
    }

    function DeleteByUser($ID) {
        $query = 'DELETE FROM `Rights`
                       WHERE `IDUser`=' . $ID;
        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Types (' . __LINE__ . ')');
        }
    }

    function DeleteHide() {
        $this->fetchObject($_POST);
        $Err = Error::getErrors(get_class($this));

        if (empty($Err)) {
            $query = 'DELETE FROM `RightsHide`
                       WHERE `IDUser`=' . (int)$this->getIDUser() . '
                         AND `IDPerson`=' . (int)$this->getIDPerson() . '
                         AND `IDOrder`=' . (int)$this->getIDOrder() . '
                         AND `IDType`=' . (int)$this->getIDType();
            if (!self::$DB->query($query)) {
                throw new Error('Delete error on Types (' . __LINE__ . ')');
            }

            return 1;
        } else return array_pop($Err);
    }

    function AllRightsDel($IDUser, $type) {
        $query = 'DELETE FROM `Rights`
                       WHERE `Type`="' . $type . '"
                         AND `IDUser`=' . $IDUser;
        if (!self::$DB->query($query)) {
            throw new Error('Delete error on Types (' . __LINE__ . ')');
        }

        return "1";
    }

    function AllRights($IDUser, $type) {
        $masivs = array();

        switch ($type) {
            case 'Type':
                $masivs = Types::getAsArray();
                break;
            case 'Order':
                $masivs = Orders::getAsArray();
                break;
            case 'Person':
                $masivs = Users::getAsArray();
                break;
            case 'Folder':
                $masivs = Orders::getAsArray();
                break;
        }

        $query = 'REPLACE INTO `Rights` (`Value`,Type,IDUser) VALUES
                  (' . implode(',"' . $type . '",' . $IDUser . '),(', array_keys($masivs)) . ',"' . $type . '",' . $IDUser . ')';

        if (!self::$DB->query($query)) {
            throw new Error('Write error on Rights (' . __LINE__ . ')');
        }

        $Rights = $this->getRights($_SESSION['User']->getID());

        if (empty($Rights['Persons'])) $Rights['Persons'] = '&mdash;';
        if (empty($Rights['Orders'])) $Rights['Orders'] = '&mdash;';
        if (empty($Rights['Types'])) $Rights['Types'] = '&mdash;';

        switch ($type) {
            case 'Type':
                return $Rights['Types'];
            case 'Order':
                return $Rights['Orders'];
            case 'Person':
                return $Rights['Persons'];
            case 'Folder':
                return $Rights['Folders'];
        }
    }

    /**
     * Setters and getters here
     */
    function __call($method, $params) {
        $type = substr($method, 0, 3);
        $key = substr($method, 3);

        if ($type == 'get') return $this->$key;
        elseif ($type == 'set') $this->$key = $params[0];
        else throw new Error(get_class($this) . '::' . $method . ' does not exists');
    }

    function setIDUser($value) {
        $value = (int)$value;
        if ($value == 0) throw new Error(Language::$Rights['SetUser']);
        else $this->IDUser = $value;
    }
}
