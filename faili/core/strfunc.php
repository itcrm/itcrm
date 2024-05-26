<?php

/**
 * Mēģinam apiet debīlo magic_quotes_gpc
 *
 * @param string $s
 */
function remove_slashes($s) {
    if (get_magic_quotes_gpc()) {
        return stripslashes($s);
    } else {
        return $s;
    }
}

function html_remove($s) {
    $s = str_replace('&', '&amp;', $s);
    $s = str_replace('"', '&quot;', $s);
    $s = str_replace('>', '&gt;', $s);
    $s = str_replace('<', '&lt;', $s);
    return $s;
}
