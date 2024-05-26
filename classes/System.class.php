<?php

class System extends DBObject {
    /**
     * Main system's function for run application
     *
     */

    function __construct() {
        System::Connect();

        if (isset($_GET['url'])) {
            self::$url = explode("/", $_GET['url']);

            $tmp = array();
            foreach (self::$url as $k => $v) {
                if (isset($v) && trim($v) != '' && $v != '..' && $v != '.') $tmp[] = $v;
            }
            self::$url = $tmp;
        } else self::$url = array('lv');
    }

    function Run() {
        try {
            echo $this->Load();
        } catch (Error $ex) {
            die($ex->getMessage());
        }
    }

    /**
     * Showing main site screen
     *
     */
    function Load() {
        switch (isset(self::$url[1]) ? self::$url[1] : '') {
            case 'Logout':
                unset($_SESSION);
                session_destroy();
                break;
            case '':
                if (isset($_SESSION['User']) && $_SESSION['User']) {
                    self::$url[1] = 'Data';
                }
            default:
                $TMP = Engine::Run(isset(self::$url[1]) ? self::$url[1] : '');
                break;
        }

        if (is_array($TMP)) $Vars = $TMP;
        elseif ($TMP != '') $Vars['Content'] = $TMP;

        $Global[self::$url[0] . 'Active'] = 'Active';
        $Global['URL'] = self::getRoot();

        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ||
            strpos($TMP, 'script') == 1
        ) return Template::Clear(Template::Parse($TMP, $Global, 'Main'));
        elseif (!empty($Vars['Ajax'])) return Template::Clear(Template::Parse($Vars['Ajax'], $Global, 'Main'));

        if (!isset($Vars['Header'])) {
            $Header = array();
            if ($_SESSION['User']) {
                $sortKey = isset($_POST['Sort']) ? 'Sort' . $_POST['Sort'] : 'Sort';
                $searchPeriodKey = isset($_POST['Period']) ? 'SearchP' . $_POST['Period'] : 'SearchP';
                $Header['Menu'] = Template::Process(
                    '/Menu',
                    array(
                        'Login' => $_SESSION['User']->getLogin(),
                        'NoAdmin' => isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] ? '' : 'none',
                        'SearchStr' => isset($_POST['Search']) ? $_POST['Search'] : '',
                        $sortKey => 'selected',
                        'FindDeleted' => isset($_POST['FindDeleted']) && $_POST['FindDeleted'] == 1 ? 'checked' : '',
                        $searchPeriodKey => 'selected'
                    )
                );
            }

            $Vars['Header'] = Template::Process('/Header', $Header);
        }

        if (!isset($Vars['Footer'])) $Vars['Footer'] = Template::Process('/Footer');

        if (!isset($Vars['Content'])) {
            if (!$_SESSION['User']) {
                $tpl = '/LoginForm';
                $Vars['Content'] = Template::Process($tpl);
            }
        }
        $Vars['NOW'] = date('y.m.d H:i');

        if (!isset($Vars['JSVariables']))
            $Vars['JSVariables'] =
                'var MSG_CONFIRM_DEL="' . Language::$Main['msgConfirmDel'] . '";
                        var MSG_DEL_PASS="' . Language::$Main['msgDelPass'] . '";
                        var URL="' . self::getRoot() . '";
                        var ROOT="' . Config::ROOT_URL . '";
                        var LANG="' . self::$url[0] . '";
                        var Admin="' . $_SESSION['isAdmin'] . '";
                        var Months = [\'' . implode('\',\'', explode(',', Language::$Main['Months'])) . '\'];
                        var Week = [\'' . implode('\',\'', explode(',', Language::$Main['CalendarWeek'])) . '\'];
                        var noliktava="' . Config::Noliktava . '";
                        var AddNolTyp="' . Config::AddNoliktava . '";
                        var DelNolTyp="' . Config::DelNoliktava . '";
                        var RezNolTyp="' . Config::ReservNoliktava . '";
                        var AtgNolTyp="' . Config::ReturnNoliktava . '";
                        ';

        $Vars = array_merge($Vars, array_diff_key($Global, $Vars));

        return Template::Clear(Template::Process($Vars, '/index'));
    }
}
