<?php

class Template extends DBObject {
    private static $_cache = array();

    private static function ParseTemplate($Tpl, $Path) {
        preg_match_all('# \{:([A-Za-z0-9\/]*):\} (.*) \{:/\\1:\} #xis', $Tpl, $result);

        if (!empty($result[1])) {
            foreach ($result[1] as $k => $v) {
                self::$_cache[$Path . $v] = self::ParseTemplate($result[2][$k], $Path);
                $Tpl = preg_replace('# \{:' . $v . ':\} .*? \{:/' . $v . ':\} #xis', '[:' . $v . ':]', $Tpl);
            }
        }

        return $Tpl;
    }

    private static function LoadTemplate($Path, $Tpl) {
        $Tpl = $Path . $Tpl;
        if (is_dir('templates/' . $Tpl . '/'))
            $Tpl .= '/index';

        $file = 'templates/' . $Tpl . '.tpl';

        if (empty(self::$_cache[$Tpl])) {
            if (file_exists($file)) {
                self::$_cache[$Tpl] = self::ParseTemplate(file_get_contents($file), $Path);
            } else die('Template ' . $file . ' not found!');
        }
        return self::$_cache[$Tpl];
    }

    private static function getTemplate($Tpl = '') {
        if ($Tpl == '') $Tpl = self::$url[count(self::$url) - 1];

        $Path = explode('/', $Tpl);
        $Tpl = array_pop($Path);

        if (empty($Path)) {
            $Path = array();
            for ($i = 1, $l = count(self::$url); $i < $l - 1; $i++) { //$i=1: skip 0 - it's language
                if (!is_numeric(self::$url[$i])) //skip numeric values - it's definetly not a folder
                    $Path[] = self::$url[$i];
            }
            $Path = implode('/', $Path);
            $Path .= $Path != '' ? '/' : self::$url[1] . '/';
        } else {
            if ($Path[0] == '.') $Path[0] = self::$url[1];
            $Path = implode('/', $Path) . '/';
        }

        return self::LoadTemplate($Path, $Tpl);
    }

    /**
     * Compile array of data and string constant to a tpl file
     *
     * @param Tpl or Array of data $P1
     * @param Array of data or Tpl  $P2
     * @param String $Const
     * @return String
     */
    public static function Process($P1 = '', $P2 = '', $Const = '') {
        if (is_array($P1)) {
            $Values = $P1;
            $Tpl = $P2;
        } else {
            $Tpl = $P1;
            $Values = $P2;
        }

        if (is_array($Values) && isset($Values['__template'])) {
            $Tpl = $Values['__template'];
            unset($Values['__template']);
        }

        if ($Const == '') {
            if (strpos($Tpl, '/') !== false) {
                $Dir = explode('/', $Tpl);
                $Dir = $Dir[0] != '' ? $Dir[0]
                    : ($Dir[1] != '' ? $Dir[1] : self::$url[1]);
            } else $Dir = self::$url[1];
        }

        $Const = $Const != ''
            ? $Const
            : (property_exists('Language', $Dir) ? $Dir : ''); // : 'Main'

        $Template = self::getTemplate($Tpl);

        if (is_array($Values)) {
            $dataArray = false;
            foreach ($Values as $key => $var) {
                if (is_array($var) && !empty($var)) {
                    if (is_numeric($key)) $dataArray = true;
                    $Values[$key] = self::Process(is_numeric($key) ? $Tpl : $key, $var);
                }
            }
            if ($dataArray) return implode("\n", $Values);
        }
        return self::Parse($Template, (array)$Values, $Const);
    }

    /**
     * Replace metadata in tpl file with array of data and array of constant
     *
     * @param string $Tpl
     * @param Array $Values
     * @param String $Const
     * @return String
     */
    public static function Parse($Tpl, $Values, $Const = '') {
        //Processing language
        if ($Const != '') {
            $aKeys = array_keys((array)Language::${$Const});
            for ($i = 0; $i < count($aKeys); $i++) {
                $Tpl = str_replace('[[:' . $aKeys[$i] . ':]]', Language::${$Const}[$aKeys[$i]], $Tpl);
            }
        }

        //Processing variables
        if (is_array($Values)) {
            $aKeys = array_keys($Values);
            for ($i = 0; $i < count($aKeys); $i++) {
                if (isset($Values[$aKeys[$i]])) {
                    $Tpl = str_replace('[:' . $aKeys[$i] . ':]', $Values[$aKeys[$i]], $Tpl);
                }
            }
        }
        return $Tpl;
    }

    /**
     * Clears all meta data such [:xxx:] and [[:xxx:]]
     *
     * @param string $Tpl
     * @return string
     */
    public static function Clear($Tpl) {
        $aKeys = array_keys((array)Language::$Main);
        for ($i = 0; $i < count($aKeys); $i++) {
            $Tpl = str_replace('[[:' . $aKeys[$i] . ':]]', Language::$Main[$aKeys[$i]], $Tpl);
        }

        $Tpl = preg_replace('/(\[\[:)[a-zA-Z0-9\.\/]+(:\]\])/', '', $Tpl);
        return preg_replace('/(\[:)[a-zA-Z0-9\.\/]+(:\])/', '', $Tpl);
    }
}
