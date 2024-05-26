<?php

/***************************************************************

WebSTORM CMS Engine Core functions
A part of WebSTORM Content Management System
(C) 2003-2007 StorM/LV Creative Group
(C) 2006-2007 SIA All4U

 ***************************************************************/

if (!isset($_IDC_ENGINE_ROOT)) $_IDC_ENGINE_ROOT = '';

require_once $_IDC_ENGINE_ROOT . 'config/core.php';

require_once $_IDC_ENGINE_ROOT . 'core/classes/database.php';
require_once $_IDC_ENGINE_ROOT . 'core/classes/cpersistent.php';
require_once $_IDC_ENGINE_ROOT . 'core/classes/clinkedpersistent.php';

require_once $_IDC_ENGINE_ROOT . 'core/urlhandlers.php';
require_once $_IDC_ENGINE_ROOT . 'core/shim.php';

require_once $_IDC_ENGINE_ROOT . 'core/templates.php';

require_once $_IDC_ENGINE_ROOT . 'core/classes/user.php';

require_once $_IDC_ENGINE_ROOT . 'core/sessions.php';
require_once $_IDC_ENGINE_ROOT . 'core/strfunc.php';

require_once $_IDC_ENGINE_ROOT . 'core/imagehandlers.php';

require_once $_IDC_ENGINE_ROOT . 'includes/fileops.php';
require_once $_IDC_ENGINE_ROOT . 'includes/integration.php';

spl_autoload_register(function ($class_name) {
    require_once $GLOBALS['_IDC_ENGINE_ROOT'] . 'classes/' . strtolower($class_name) . '.php';
});

$_IDC_SCRIPTS = array();
$_IDC_STYLESHEETS = array();
$_IDC_STARTUP = array();

$_IDC_PARAMETERS = array();

function get_param($name, $order = '') {
    global $_IDC_CONFIG;
    global $_core;

    $p = false;

    if (is_string($name)) {
        if ($order == '') {
            $order = $_IDC_CONFIG['param_order'];
        }
        for ($i = 0; $i < strlen($order); $i++) {
            switch ($order[$i]) {
                case 'G':
                case 'g': {
                        if (isset($_GET[$name])) $p = remove_slashes($_GET[$name]);
                        break;
                    }
                case 'P':
                case 'p': {
                        if (isset($_POST[$name])) $p = remove_slashes($_POST[$name]);
                        break;
                    }
                case 'C':
                case 'c': {
                        if (isset($_COOKIE[$name])) $p = remove_slashes($_COOKIE[$name]);
                        break;
                    }
            }
        }
    } else {
        if (isset($_core->requestParams[$name])) {
            $p = $_core->requestParams[$name];
        }
    }
    return $p;
}

// tas pats, kas get_param() bet atgriež false ja parametrs NAV skaitlisks
function get_param_int($name, $order = '') {
    $value = get_param($name, $order);
    if (is_numeric($value)) {
        return intval($value);
    } else {
        return false;
    }
}

///////////////// REQUEST VARIABLE SUPPORT /////////////////////////

/**
 * Set variable value
 *
 * @param string $type
 * @param string $function_name
 */
function register_variable($name, $value) {
    $GLOBALS['_IDC_PARAMETERS'][$name] = $value;
}

/**
 * Get variable value
 * returns boolean false if variable not set
 *
 * @param unknown_type $name
 * @return unknown
 */
function get_variable($name) {
    if (isset($GLOBALS['_IDC_PARAMETERS'][$name])) {
        return $GLOBALS['_IDC_PARAMETERS'][$name];
    } else {
        return false;
    }
}

// dynamically attachable script and stylesheet handling
function register_script($filename) {
    $GLOBALS['_IDC_SCRIPTS'][$filename] = $filename;
}

function register_stylesheet($filename) {
    $GLOBALS['_IDC_STYLESHEETS'][$filename] = $filename;
}

/**
 * Reģistrē JavaScript startup funkciju (<HEAD> ievietojamu skripta fragmentu)
 *
 * @param string $text
 */
function register_startup($text) {
    $GLOBALS['_IDC_STARTUP'][] = $text;
}

function get_script_list() {
    $out = '';
    foreach ($GLOBALS['_IDC_SCRIPTS'] as $filename) {
        $out .= '<script LANGUAGE="javascript" TYPE="text/javascript" SRC="' . C('base-url') . $filename . '"></SCRIPT>' . "\n";
    }

    foreach ($GLOBALS['_IDC_STARTUP'] as $script) {
        $out .= '<script LANGUAGE="javascript" TYPE="text/javascript">' . $script . "</script>\n";
    }

    return $out;
}

function get_stylesheet_list() {
    $out = '';
    foreach ($GLOBALS['_IDC_STYLESHEETS'] as $filename) {
        $out .= '<link rel="stylesheet" href="' . C('base-url') . $filename . '" type="text/css" />' . "\n";
    }
    return $out;
}

function detectBaseUrl() {
    $path = $_SERVER['REQUEST_URI'];

    $baseUrl = dirname($path);

    if ($baseUrl != '') $baseUrl .= '/' . $GLOBALS['_IDC_ENGINE_ROOT'];
    return $baseUrl;
}

///////////////// CORE FUNCTIONS /////////////////

class CCore {
    public $uid;
    private $ensureAuthenticated;
    /**
     * Tekošais lietotājs
     *
     * @var CUser
     */
    public $user;

    /**
     * Datubāzes instance
     * TODO: šitais ir jāizvāc uz globālajiem mainīgajiem un objekts jāveido tikai vaicājumam
     *
     * @var CDatabase
     */
    public $database;

    /**
     * Sesijas instance
     *
     * @var CSession
     */
    public $session;

    /**
     * @desc
     */
    public $requestParams;

    function __construct() {
        if (isset($GLOBALS['_core'])) {
            throw new Exception('Atļauta tikai viena kodola instance!');
        } else {
            $GLOBALS['_core'] = &$this;
        }
    }

    function readSetting($name, $default = false) {
        $res = new CQuery("SELECT param_value FROM parameters WHERE param_name='$name'");

        if ($row = $res->fetch()) {
            return $row[0];
        } else {
            $this->saveSetting($name, $default);
            return $default;
        }
    }

    function readSettingId($name) {
        $res = new CQuery("SELECT param_id FROM parameters WHERE param_name='$name'");

        if ($row = $res->fetch()) {
            return $row[0];
        } else {
            return false;
        }
    }

    function saveSetting($name, $value) {
        $res = $this->database->query("INSERT INTO parameters (param_name,param_value) VALUES ('" . $this->database->escape($name) . "','" . $this->database->escape($value) . "')", DB_RESULT_TYPE_NONE, false);
        if ($res === false) {
            $query = "UPDATE parameters SET param_value='" . $this->database->escape($value) . "' WHERE param_name='" . $this->database->escape($name) . "'";
            new CQuery($query);
        }
    }

    function removeSetting($name) {
        $this->database->query("DELETE FROM parameters WHERE param_name='" . $this->database->escape($name) . "'", DB_RESULT_TYPE_NONE, false);
    }

    function getRequest($start = 0) {
        global $_core;

        $script = $_SERVER["PHP_SELF"];
        $baseName = basename($script);

        $path = '';
        $curr = 0;
        foreach ($_core->requestParams as $param) {
            if (($param != $baseName) && ($curr >= $start)) {
                $path .= $param . '/';
            }
            $curr++;
        }
        return $path;
    }

    public static function getRequestPath($appendBaseUrl = true) {
        global $_core;

        $script = $_SERVER["PHP_SELF"];
        $baseName = basename($script);

        $path = '';

        $curr = 0;
        foreach ($_core->requestParams as $param) {
            if ($param != $baseName) {
                $path .= $param . '/';
            }
            $curr++;
        }
        if ($appendBaseUrl) {
            return C('base-url') . $path;
        } else {
            return $path;
        }
    }

    function initialize($ensureAuthenticated = false) {
        /**
         * Labojums lighttpd rewrite specifikai
         */
        if (isset($_GET['q']) && preg_match('/\?/', $_GET['q'])) {
            $tmp = explode('?', $_GET['q']);
            parse_str($tmp[1], $arr);
            foreach ($arr as $key => $val) {
                if ($key != 'q') {
                    $_GET[$key] = $val;
                }
            }
            $_GET['q'] = $tmp[0];
        }

        /**
         * Slēdzamies datubāzei
         */
        $this->database = new CDatabase();
        if ($this->database->connect(C('dbServer'), C('dbUser'), C('dbPass'), C('dbName'))) {
        } else {
            throw new Exception("Neveiksmīga pieslēgšanās datubāzei!");
        }

        $GLOBALS['_IDC_DATABASE'] = &$this->database;

        $this->requestParams = splitURL();

        $this->ensureAuthenticated = $ensureAuthenticated;

        $this->session = new CSession();

        $this->uid = $this->session->user_id;
        $this->user = new CUser($this->uid);
    }

    function render() {
        $contents = '';

        try {
            if ($this->ensureAuthenticated) {
                $this->requestLogin();
            }

            $currentViewName = get_param(0);

            if ($currentViewName === 'params') {
                require_once 'views/system/sysparams.php';
                $view = new CVSysParams();
                $contents = $view->render();
            } else {
                require_once 'views/start.php';
                $view = new CVStart();
                $contents = $view->render();
            }

            echo parseTemplate('template/main/main.php', $contents);
        } catch (Exception $e) {
            echo file_get_contents('template/sys/error.php');
        }
    }

    function requestLogin() {
        if ($this->user->id <= 0) {
            header('Location: ' . url('login/' . $this->getRequest()));
            die();
        }
    }
}
