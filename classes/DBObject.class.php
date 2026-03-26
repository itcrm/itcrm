<?php

class SQLiteResultWrapper {
    private $rows = array();
    private $pos = 0;
    public $num_rows;

    function __construct($result) {
        if ($result instanceof SQLite3Result) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $this->rows[] = $row;
            }
            $result->finalize();
        }
        $this->num_rows = count($this->rows);
    }

    function fetch_assoc() {
        if ($this->pos < $this->num_rows) {
            return $this->rows[$this->pos++];
        }
        return null;
    }

    function fetch_array() {
        return $this->fetch_assoc();
    }
}

class SQLiteDBWrapper {
    private $db;
    public $insert_id = 0;
    public $errno = 0;
    public $error = '';

    function __construct($path) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->db = new SQLite3($path);
        $this->db->busyTimeout(5000);
        $this->db->exec('PRAGMA journal_mode=WAL');
        $this->db->exec('PRAGMA foreign_keys=ON');
    }

    function query($sql) {
        $this->errno = 0;
        $this->error = '';

        $result = @$this->db->query($sql);
        if ($result === false) {
            $this->errno = $this->db->lastErrorCode();
            $this->error = $this->db->lastErrorMsg();
            return false;
        }

        $this->insert_id = $this->db->lastInsertRowID();
        return new SQLiteResultWrapper($result);
    }

    function exec($sql) {
        $this->errno = 0;
        $this->error = '';

        $result = @$this->db->exec($sql);
        if ($result === false) {
            $this->errno = $this->db->lastErrorCode();
            $this->error = $this->db->lastErrorMsg();
            return false;
        }

        $this->insert_id = $this->db->lastInsertRowID();
        return true;
    }

    function prepare($sql, $params = []) {
        $this->errno = 0;
        $this->error = '';

        $stmt = @$this->db->prepare($sql);
        if ($stmt === false) {
            $this->errno = $this->db->lastErrorCode();
            $this->error = $this->db->lastErrorMsg();
            return false;
        }

        foreach ($params as $i => $value) {
            $stmt->bindValue($i + 1, $value);
        }

        $result = @$stmt->execute();
        if ($result === false) {
            $this->errno = $this->db->lastErrorCode();
            $this->error = $this->db->lastErrorMsg();
            $stmt->close();
            return false;
        }

        $this->insert_id = $this->db->lastInsertRowID();
        $wrapped = new SQLiteResultWrapper($result);
        $stmt->close();
        return $wrapped;
    }

    function escapeString($str) {
        return $this->db->escapeString($str);
    }

    function close() {
        $this->db->close();
    }
}

abstract class DBObject {
    protected $Fields = array();
    static $url = array();

    static public $DB;

    function __construct() {
        foreach ($this as $k => $v) {
            if ($k != 'Fields') $this->Fields[] = $k;
        }
    }

    static function Connect() {
        DBObject::$DB = new SQLiteDBWrapper(Config::DB_PATH);
    }

    static function escape($str) {
        return self::$DB->escapeString($str ?? '');
    }

    function __call($method, $params) {
        $type = substr($method, 0, 3);
        $key = substr($method, 3);

        if ($type == 'get') return $this->$key;
        elseif ($type == 'set') $this->$key = $params[0];
        else throw new AppError(get_class($this) . '::' . $method . ' does not exists');
    }

    static function getById($ID) {
        $table = static::$tableName;
        $query = "SELECT * FROM `" . $table . "` WHERE `ID`=" . (int)$ID;

        if (!$result = self::$DB->query($query))
            throw new AppError('Read error on ' . $table . ' (' . __LINE__ . ')');

        return (new static)->fetchObject($result, new static);
    }

    function fetchObject($vals, $Object = '') {
        if ($vals instanceof SQLiteResultWrapper) $vals = $vals->fetch_assoc();

        $Obj = $Object == '' ? $this : $Object;

        foreach ($Obj->Fields as $k => $v) {
            if (isset($vals[$v]))
                try {
                    $Obj->{'set' . $v}($vals[$v]);
                } catch (AppError $ex) {
                    AppError::setError(get_class($Obj), $v, $ex->getMessage());
                }
        }

        return $Obj;
    }

    function assignObject($Obj = '') {
        $aVars = array();

        if (!$Obj) $Obj[0] = $this;
        elseif ($Obj instanceof SQLiteResultWrapper) {
            while ($row = $Obj->fetch_assoc()) $aVars[] = $row;
            return empty($aVars) ? '' : $aVars;
        }
        if (!is_array($Obj)) $Obj = array($Obj);

        foreach ($Obj as $Obj) {
            $aVars[] = array();
            $c = count($aVars) - 1;
            foreach ($Obj->Fields as $k => $v)
                $aVars[$c][$v] = $Obj->{'get' . $v}();
        }
        if (count($aVars) == 1) $aVars = $aVars[0];
        return $aVars;
    }

    static function getRoot() {
        return Config::ROOT_URL . '/' . self::$url[0];
    }

    function makePages($items, $page, $url = '', $perPage = Config::PAGE_LENGTH) {
        $page = intval($page) > 0 ? intval($page) : 1;
        $value = '';

        if ($items <= $perPage) return '';

        $currentPage = ceil((($page - 1) * $perPage + $perPage) / $perPage);
        $maxPage = ceil($items / $perPage);

        for ($i = 1; $i <= $maxPage; $i++) {
            if ($i == 8 && $i < $currentPage - 6) {
                $i = $currentPage - 6;
                $value .= '<span>&nbsp;...&nbsp;</span>';
            } elseif ($i > $currentPage + 6 && $i < $maxPage - 6) {
                $i = $maxPage - 6;
                $value .= '<span>&nbsp;...&nbsp;</span>';
            }

            if ($currentPage == $i)
                $value .= '<a class="pageActive">' . $i . '</a>';
            else
                $value .= '<a class="page" onclick="NextPage(' . $i . ')" href="' . $url . '">' . $i . '</a>';
        }
        $sk = $_SESSION['entry'];
        $psk = $_SESSION['pagecount'];
        $pgsv = "";
        for ($i = 50; $i <= 1050; $i++) {
            if ($i == $_SESSION['pagecount']) {
                $pgsv .= "<option selected value='$i'>$i</option>";
            } else {
                $pgsv .= "<option value='$i'>$i</option>";
            }
            $i = $i + 99;
        }
        return '<div style="position:fixed; bottom:0; width:100%; height:34px; background:#BBBBBB;"> <div style="padding:10px;" align="center"><a class="page" onclick="InPage(' . $items . ')" href="' . $url . '">Kopā</a>' . $value . '
<select onchange="InPage(this.value)"  value="' . $psk . '">
' . $pgsv . '
</select>  <span  style="float: right;"> Kopā: ' . $sk . ' ieraksti</span> </div> </div>';
    }

    function good_query($string) {
        $result = self::$DB->query($string);

        if ($result == false) {
            error_log("SQL error: " . self::$DB->error . "\n\nOriginal query: $string\n");
        }
        return $result;
    }

    function good_query_table($sql) {
        $result = $this->good_query($sql);

        $table = array();

        if ($result->num_rows > 0) {
            $i = 0;
            while ($table[$i] = $result->fetch_assoc())
                $i++;
            unset($table[$i]);
        }

        return $table;
    }
}
