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
    $query = "INSERT INTO parameters (param_name,param_value) VALUES ('$name','$value')";
    $res = mysqli_query($_IDC_DATABASE->db_link, $query);
    if (!$res) {
        $query = "UPDATE parameters SET param_value='" . sql_escape($value) . "' WHERE param_name='" . sql_escape($name) . "'";
        $res = mysqli_query($_IDC_DATABASE->db_link, $query);
        if ((!$res)) {
            die("Unable to save setting '$name': " . mysqli_error($_IDC_DATABASE->db_link));
        }
    }
}

function sql_escape($s) {
    global $_IDC_DATABASE;
    return mysqli_escape_string($_IDC_DATABASE->db_link, $s);
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
            // slēdzamies pie DB
            if ($this->db_link = mysqli_connect($server, $user, $pass, $dbname)) {
                $this->query('SET NAMES utf8');
                $this->query('SET CHARACTER SET utf8');
                $this->query('SET COLLATION_CONNECTION=\'utf8_general_ci\'');
                $this->errno = 0;
                $this->error = '';
                return $this->db_link;
            } else {
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
            $res = mysqli_query($this->db_link, $query);
            if ($res != false) {
                $this->errno = 0;
                $this->error = '';
                if (($res === true) || ($result_type == DB_RESULT_TYPE_NONE)) {
                    return true;
                } else {
                    if ($rowset === false) $rowset = new CRowSet;
                    $rowset->add_mysql($res, $result_type);
                    return $rowset;
                }
            } else {
                $this->errno = mysqli_errno($this->db_link);
                $this->error = mysqli_error($this->db_link);
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
            return mysqli_insert_id($this->db_link);
        } else {
            return false;
        }
    }
}

class CRowSet {
    public $rows;
    private $rs_res;
    private $type;
    private $rownum;

    function __construct() {
        $this->rows = array();
        $this->rs_res = false;
        $this->type = DB_RESULT_TYPE_NONE;
        $this->rownum = 0;
    }

    /**
     * Pievieno MySQL rezultātu rowsetam
     *
     * @param mysqli_result $res
     */
    function add_mysql($res, $type = DB_RESULT_TYPE_ARRAY) {
        $this->type = $type;
        $this->rownum = 0;

        switch ($type) {
            case DB_RESULT_TYPE_ARRAY:
                $this->rows = array();
                $i = 0;
                while ($row = mysqli_fetch_array($res, MYSQLI_BOTH)) {
                    $row = array_change_key_case($row, CASE_LOWER);
                    $row['_UC_ROWID'] = $i;
                    $this->rows[] = $row;
                    $i++;
                }
                $this->rs_res = false;
                mysqli_free_result($res);
                break;
            case DB_RESULT_TYPE_SINGLE:
                $this->rows = false;
                $this->rs_res = $res;
                $this->rownum = 0;
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
                if ($this->rs_res !== false) {
                    $row = mysqli_fetch_array($this->rs_res, MYSQLI_BOTH);
                    if ($row != false) {
                        $row = array_change_key_case($row, CASE_LOWER);
                        $row['_UC_ROWID'] = $this->rownum;
                        $this->rownum++;
                        return $row;
                    }
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
                if ($this->rs_res !== false) {
                    $row = mysqli_fetch_array($this->rs_res, MYSQL_ASSOC);
                    if ($row != false) {
                        $row = array_change_key_case($row, CASE_LOWER);
                        $row['_UC_ROWID'] = $this->rownum;
                        $this->rownum++;
                        return $row;
                    }
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
                if ($this->rs_res !== false) {
                    return mysqli_num_rows($this->rs_res);
                }
                return false;
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
