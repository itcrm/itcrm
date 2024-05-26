<?php

function processCommand($cmd, $params) {
    switch ($cmd) {
        case 'V': {
                $val = get_variable($params);
                return $val === false ? '' : $val;
            }
        case 'B':
            return C('base-url');
        case 'E': {
                require_once $GLOBALS['_IDC_ENGINE_ROOT'] . 'elements/' . $params . '.php';
                return eval("return $params();");
            }
        case 'SCRIPTS': {
                return get_script_list();
            }
        case 'STYLESHEETS': {
                return get_stylesheet_list();
            }
        default:
            return '';
    }
}

/**
 * Parses a template into a (X)HTML output (or whatever else :)
 *
 * @param string $template
 * @param string $contents
 */
function parseTemplate($template, $contents) {
    $tmp = file_get_contents($template);
    if ($tmp === false) throw new Exception('Neveiksme ielasot sagatavi!');

    $tmp = str_replace('{{CONTENTS}}', $contents, $tmp);

    do {
        $changes = false;

        /**
         * @desc Vēl jaunāks variants - dubultās figūriekavas, arī daudzburtu kodi: {{CMD:PARAMS}}
         */
        while (($pos = strpos($tmp, '{{')) !== false) {
            $len = strpos($tmp, "}}", $pos) - $pos;

            $cmdStr = substr($tmp, $pos + 2, $len - 2);
            $cmdData = explode(':', $cmdStr, 2);
            $cmd = $cmdData[0];
            if (isset($cmdData[1])) {
                $params = $cmdData[1];
            } else {
                $params = '';
            }

            $value = processCommand($cmd, $params);
            $tmp = substr_replace($tmp, $value, $pos, $len + 2);
            $changes = true;
        }
    } while ($changes);

    return $tmp;
}
