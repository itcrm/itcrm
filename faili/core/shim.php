<?php

/**
 * @desc Funkcijas dzīves atvieglošanai
 */

/**
 * @desc Konfigurācijas parametru lasīšana
 * @param string Parametra nosaukums
 * @return string atgriež parametra vērtību vai FALSE ja parametrs nav definēts
 */
function C($param) {
    if (isset($GLOBALS['_IDC_CONFIG'][$param])) {
        return $GLOBALS['_IDC_CONFIG'][$param];
    } else {
        return false;
    }
}

/**
 * @desc Debug outputs
 */
function PRE($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Uzvāc visus html tagus nafig
 *
 * @param string $s
 */
function H($s) {
    return html_remove($s);
}

/**
 * @desc Atgriež tekošo datumu/laiku SQL formātā
 */
function SQLnow($time = false) {
    if ($time === false) $time = time();
    return date('Y-m-d H:i:s');
}

/**
 * Atgriež sistēmas parametra vērtību
 * @global CCore $_core
 * @param string $name
 * @param string $default
 * @return string
 */
function P($name, $default = false) {
    global $_core;
    return $_core->readSetting($name, $default);
}
