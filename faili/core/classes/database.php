<?php

define('DB_RESULT_TYPE_NONE', 0);            // vaicājuma rezultāta nav vispār
define('DB_RESULT_TYPE_ARRAY', 1);            // viss vaicājuma rezultāts tiek salasīts rindu masīvā (masīvā no masīviem) (izmantojot $rows[])
define('DB_RESULT_TYPE_SINGLE', 2);            // vaicājuma rezultāts ir pieejams pa vienai rindai (izmantojot fetch())
define('DB_RESULT_TYPE_OBJECT', 3);            // vaicājuma rezultāts ir pieejams pa vienai rindai (izmantojot fetch('class')), tiek atgriezts norādītās klases objekts

function read_param($name, $default) {
    global $_IDC_DATABASE;
    $query = "SELECT param_value FROM parameters WHERE param_name='$name'";

    $res = $_IDC_DATABASE->query($query, DB_RESULT_TYPE_SINGLE);
    if ($res !== false) {
        if ($row = $res->fetch()) {
            return $row[0];
        }
    }

    return $default;
}

function save_param($name, $value) {
    global $_IDC_DATABASE;
    $query = "INSERT OR REPLACE INTO parameters (param_name, param_value) VALUES ('" . sql_escape($name) . "', '" . sql_escape($value) . "')";
    $res = $_IDC_DATABASE->db_link->exec($query);
    if ($res === false) {
        die("Unable to save setting '$name': " . $_IDC_DATABASE->db_link->lastErrorMsg());
    }
}

function sql_escape($s) {
    global $_IDC_DATABASE;
    return $_IDC_DATABASE->db_link->escapeString($s);
}

class CDatabase {
    public $db_link = null;
    public $errno = 0;
    public $error = '';

    function escape($s) {
        return sql_escape($s);
    }

    function connect($server, $user, $pass, $dbname) {
        if ($this->db_link != null) {
            // esam jau pieslēgušies
            return $this->db_link;
        } else {
            // slēdzamies pie DB — use the same SQLite database
            $dbPath = defined('_DB_PATH') ? _DB_PATH : $dbname;
            try {
                $this->db_link = new SQLite3($dbPath);
                $this->db_link->busyTimeout(5000);
                $this->db_link->exec('PRAGMA journal_mode=WAL');
                $this->errno = 0;
                $this->error = '';
                return $this->db_link;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /**
     * Kārtējā "galvenā:)" funkcija - veic vaicājumus un atgriež rezultātu smuki salasītu CRowSet klases instancē
     * Ja vaicājums neatgriež resultātu (UPDATE/DELETE utml) - atgriež BOOLEAN TRUE
     * Ja vaicājums ir neveiksmīgs - atgriež BOOLEAN FALSE
     *
     * @param string $query
     * @return CRowSet
     */
    function query($query, $result_type = DB_RESULT_TYPE_SINGLE, &$rowset = false) {
        if ($this->db_link != null) {
            $res = @$this->db_link->query($query);
            if ($res !== false) {
                $this->errno = 0;
                $this->error = '';
                if (($res === true) || ($result_type == DB_RESULT_TYPE_NONE)) {
                    return true;
                } else {
                    if ($rowset === false) $rowset = new CRowSet;
                    $rowset->add_sqlite($res, $result_type);
                    return $rowset;
                }
            } else {
                $this->errno = $this->db_link->lastErrorCode();
                $this->error = $this->db_link->lastErrorMsg();
                return false;
            }
        } else {
            return false;
        }
    }

    function queryVars($query, $vars = '', $result_type = DB_RESULT_TYPE_SINGLE) {
        if ($vars === '') $vars = array();
        $tokens = preg_split('/((?<!\\\)[&])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        $cnt = 0;
        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i] == '&') {
                if (isset($vars[$cnt]) && !empty($vars[$cnt])) {
                    $tokens[$i] = "'" . $this->escape($vars[$cnt]) . "'";
                    $cnt++;
                } else {
                    $tokens[$i] = 'NULL';
                    $cnt++;
                }
            }
        }
        $tquery = implode('', $tokens);
        return $this->query($tquery, $result_type);
    }

    function last_insert_id() {
        if ($this->db_link != null) {
            return $this->db_link->lastInsertRowID();
        } else {
            return false;
        }
    }
}

class CRowSet {
    public $rows;
    private $rs_rows;
    private $type;
    private $rownum;
    private $pos;

    function __construct() {
        $this->rows = array();
        $this->rs_rows = array();
        $this->type = DB_RESULT_TYPE_NONE;
        $this->rownum = 0;
        $this->pos = 0;
    }

    /**
     * Pievieno SQLite rezultātu rowsetam
     *
     * @param SQLite3Result $res
     */
    function add_sqlite($res, $type = DB_RESULT_TYPE_ARRAY) {
        $this->type = $type;
        $this->rownum = 0;
        $this->pos = 0;

        // Buffer all rows from SQLite3Result
        $allRows = array();
        $i = 0;
        while ($row = $res->fetchArray(SQLITE3_BOTH)) {
            $row = array_change_key_case($row, CASE_LOWER);
            $row['_UC_ROWID'] = $i;
            $allRows[] = $row;
            $i++;
        }
        $res->finalize();

        switch ($type) {
            case DB_RESULT_TYPE_ARRAY:
                $this->rows = $allRows;
                break;
            case DB_RESULT_TYPE_SINGLE:
                $this->rows = false;
                $this->rs_rows = $allRows;
                $this->pos = 0;
                break;
            default:
        }
    }

    function fetch() {
        switch ($this->type) {
            case DB_RESULT_TYPE_ARRAY:
                if (($rowinfo = each($this->rows)) !== false) {
                    return $rowinfo[1];
                } else {
                    return false;
                }
                break;
            case DB_RESULT_TYPE_SINGLE:
                if ($this->pos < count($this->rs_rows)) {
                    $row = $this->rs_rows[$this->pos];
                    $this->rownum = $this->pos;
                    $this->pos++;
                    return $row;
                }
                return false;

            default:
                return false;
        }
    }

    function fetchAssoc() {
        switch ($this->type) {
            case DB_RESULT_TYPE_ARRAY:
                if (($rowinfo = each($this->rows)) !== false) {
                    return $rowinfo[1];
                }
                return false;
            case DB_RESULT_TYPE_SINGLE:
                if ($this->pos < count($this->rs_rows)) {
                    $row = $this->rs_rows[$this->pos];
                    $this->rownum = $this->pos;
                    $this->pos++;
                    return $row;
                }
                return false;
            default:
                return false;
        }
    }

    function count() {
        switch ($this->type) {
            case DB_RESULT_TYPE_ARRAY:
                return count($this->rows);
            case DB_RESULT_TYPE_SINGLE:
                return count($this->rs_rows);
            default:
                return false;
        }
    }

    function rownum() {
        return $this->rownum;
    }
}

$_IDC_DATABASE = new CDatabase();

class CQuery extends CRowSet {
    public $error;

    /**
     * "ašais" vaicājuma objekts
     *
     * @param string $query
     * @return CRowSet
     */
    function __construct($query, $type = DB_RESULT_TYPE_SINGLE) {
        global $_IDC_DATABASE;
        $res = $_IDC_DATABASE->query($query, $type, $this);
        if ($this->error = ($res === false));
    }

    function lastInsertId() {
        global $_IDC_DATABASE;
        return $_IDC_DATABASE->last_insert_id();
    }
}
