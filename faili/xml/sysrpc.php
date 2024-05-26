<?php

header("Content-type: text/html; charset=UTF-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

$_IDC_ENGINE_ROOT = '../';
require $_IDC_ENGINE_ROOT . 'core/classes/ccore.php';
$_core = new CCore();
$_core->initialize();

class CParam extends CPersistent {
    public $param_name;
    public $param_value;

    function __construct() {
        parent::__construct('parameters', 'param_id', '', 1);
    }
}

$cmd = get_param('cmd');
$id = get_param_int('cid');

switch ($cmd) {
    case 'setparam': {
            $id = get_param('contour_id', 'GP');

            $name = trim(get_param('f_name', 'P'));
            $value = get_param('f_value', 'P');

            $param = new CParam;
            if ($id > 0) {
                $param->load($id);
            }

            $err = '';
            if ($name == '') {
                $err .= "name:Nosaukums nevar būt tukšs!\n";
            } else {
                $oldId = $_core->readSettingId($name);
                if (($oldId !== false) && ($oldId != $id)) $err .= "name:Tāds parametrs jau eksistē!\n";
            }

            if ($err == '') {
                $param->param_name = $name;
                $param->param_value = $value;

                $param->save();

                $changes = "id=" . $param->id . "\t";
                echo $changes;
            } else {
                echo $err;
            }

            break;
        }

    case 'deleteparam': {
            $id = get_param_int('contour_id', 'GP');

            $param = new CParam();
            $param->id = $id;
            $param->delete();

            break;
        }

    case 'createorder': {
            $orderId = get_param_int('orderId');
            $order = new COrder();
            try {
                $order->load($orderId);
                $orderName = $order->Code;
            } catch (Exception $e) {
                $orderName = '';
            }

            $rootDir =  $_IDC_CONFIG['filelist.root'] . '/' . $orderName;

            if (!file_exists($rootDir)) {
                @mkdir($rootDir, 0777, true);
                @chmod($rootDir, 0777);
            }

            break;
        }
}
