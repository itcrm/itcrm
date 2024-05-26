<?php

function splitURL() {
    $q = get_param('q', 'G');
    $request = explode('/', $q);

    if (empty($request[(count($request) - 1)])) {
        unset($request[count($request) - 1]);
    }

    return $request;
}

function url_self_clean_ex($params) {
    // generates a self-referencing url with only parameters passed as argument
    // also, any predefined (in config.php) parameters will be transferred (if set)
    // accepts associative array of parameter=>value as input
    $url = CCore::getRequestPath() . '?';

    foreach ($params as $key => $value) {
        if (trim($value) != '') {
            $url .= $key . '=' . $value . '&';
        }
    }

    $url = trim($url, '&? ');
    return str_replace('&', '&amp;', $url);
}

function url($req = '') {
    return C('base-url') . $req;
}
