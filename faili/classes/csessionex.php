<?php

class CSessionEx {
    static $_varCache;

    static function setVar($param, $value = '') {
        global $_core;
        $id = $_core->session->id;
        if (!is_array(self::$_varCache)) self::$_varCache = array();
        if ($id) {
            global $_IDC_DATABASE;

            $res = $_IDC_DATABASE->query("SELECT count(*) FROM session_storage
                                            WHERE (session_id = '$id')
                                            AND (session_param = '" . sql_escape($param) . "')");

            if ($res !== false) {
                $row = $res->fetch();
                $count = $row[0];
                self::$_varCache[$param] = $value;
                if ($count == 0) {
                    $query = "INSERT INTO session_storage (session_id,
                                                            session_param,
                                                            session_value)
                                VALUES ('$id','" . sql_escape($param) . "','" . sql_escape($value) . "')";
                    $res = $_IDC_DATABASE->query($query);
                } else {
                    $query = "UPDATE session_storage SET session_value='" . sql_escape($value) . "'
                                WHERE (session_id='$id') AND (session_param='" . sql_escape($param) . "')";
                    $res = $_IDC_DATABASE->query($query);
                }
            }
        }
    }

    static function getVar($param, $default = false) {
        global $_core;
        $id = $_core->session->id;
        if (!is_array(self::$_varCache)) self::$_varCache = array();
        if ($id) {
            if (isset(self::$_varCache[$param])) return self::$_varCache[$param];

            global $_IDC_DATABASE;

            $query = "SELECT session_value FROM session_storage WHERE (session_id = '$id') AND (session_param = '" . sql_escape($param) . "')";
            $res = $_IDC_DATABASE->query($query);
            if ($res === false) throw new Exception('Neveiksme nolasot sesijas mainīgo');

            if ($row = $res->fetch()) {
                self::$_varCache[$param] = $row[0];
                return $row[0];
            } else {
                self::$_varCache[$param] = $default;
                return $default;
            }
        } else {
            return false;
        }
    }
}
