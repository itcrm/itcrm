<?php
function PS($function) {
    global $micro;
    global $lastPStart;
    $lastPStart = $function;

    // echo 'open: '.$function.'<br/>';

    if (isset($micro[$function . "_start"])) {
        // It's recursive, 40-32=8, 32-(40-32) = 2(32) - 40 = 24
        $micro[$function . "_depth"]++;
        list($org_micro, $org_int) = explode(" ", $micro[$function . "_start"]);
        list($now_micro, $now_int) = explode(" ", microtime());
        $new_micro = 2 * $org_micro - $now_micro;
        $new_int = 2 * $org_int - $now_int;
        while ($new_micro < 0) {
            $new_micro += 1;
            $new_int--;
        }
        $micro[$function . "_start"] = join(" ", array($new_micro, $new_int));
    } else {
        $micro[$function . "_start"] = microtime();
    }
}

function PE($function = false) {
    global $micro;
    global $lastPStart;
    if ($function == false) $function = $lastPStart;

    //echo 'close: '.$function.'<br/>';

    if (@$micro[$function . "_depth"] > 0) {
        $micro[$function . "_depth"]--;
    } else {
        list($start_micro, $start_int) = explode(" ", $micro[$function . "_start"]);
        list($end_micro, $end_int) = explode(" ", microtime());
        $new_micro = $end_micro - $start_micro;
        $new_int = $end_int - $start_int;
        while ($new_micro < 0) {
            $new_micro += 1;
            $new_int--;
        }
        if (isset($micro[$function])) {
            // We're adding this time to a previous one.
            list($time_micro, $time_int) = explode(" ", $micro[$function]);
            $new_micro += $time_micro;
            $new_int += $time_int;
            while ($new_micro > 1) {
                $new_micro -= 1;
                $new_int++;
            }
        }
        $micro[$function] = join(" ", array($new_micro, $new_int));
        unset($micro[$function . "_start"]);
    }
}
function micro_display() {
    global $micro;
    echo "<br />";
    foreach ($micro as $key => $time) {
        list($func_micro, $func_int) = explode(" ", $time);
        $func_time = 0.0 + $func_int + $func_micro;
        print "$key took $func_time seconds<br />\n";
    }
}
