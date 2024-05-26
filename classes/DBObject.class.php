<?php

abstract class DBObject {
    protected $Fields = array();
    static $url = array();

    static public $DB;

    static function Connect() {
        DBObject::$DB = mysqli_connect(Config::DB_HOST_NAME, Config::DB_USER_NAME, Config::DB_PASSWORD, Config::DB_DATABASE_NAME);
        if (mysqli_connect_errno()) {
            throw new Error(mysqli_connect_error());
        }

        DBObject::$DB->query('SET NAMES UTF8');
    }

    function getFields() {
        return $this->Fields;
    }

    function fetchObject($vals, $Object = '') {
        if (get_class($vals) == 'mysqli_result') $vals = $vals->fetch_assoc();

        $Obj = $Object == '' ? $this : $Object;

        foreach ($Obj->Fields as $k => $v) {
            if (isset($vals[$v]))
                try {
                    $Obj->{'set' . $v}($vals[$v]);
                } catch (Error $ex) {
                    $ex->setError(get_class($Obj), $v);
                }
        }

        return $Obj;
    }

    function assignObject($Obj = '') {
        $aVars = array();

        if (!$Obj) $Obj[0] = $this;
        elseif (get_class($Obj) == 'mysqli_result') {
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

    static function ArrayToJson(array $array) {
        $out = array();
        foreach ($array as $key => $val) {
            if (!is_array($val)) {
                if (is_string($val) || empty($var)) $val = '"' . addslashes($val) . '"';
                $out[] = $key . ': ' . $val;
            } else $out[] = $key . ': ' . self::ArrayToJson($val);
        }
        if (is_array($out)) $out = implode(',', $out);
        return '{' . str_replace(array("\r", "\n"), array('', ''), $out) . '}';
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

    function makeCaptcha() {
        $str = array_merge(range('A', 'Z'), range(1, 9));

        $_SESSION['Captcha'] = array();
        for ($i = 0; $i < 8; $i++) $_SESSION['Captcha'][] = $str[rand(0, count($str) - 1)];
        $Img = new Image();
        $Captcha = $Img->getCapthca($_SESSION['Captcha']);
        $_SESSION['Captcha'] = implode($_SESSION['Captcha']);
        return $Captcha;
    }

    function good_query($string) {
        $result = self::$DB->query($string);

        if ($result == false) {
            error_log("SQL error: " . mysqli_error(self::$DB) . "\n\nOriginal query: $string\n");
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
