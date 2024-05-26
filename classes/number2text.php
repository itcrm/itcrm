<?php

if (!function_exists('number2string')) {
    function number2string($n) {
        $b = '';
        if ($n < 0) {
            $b = 'mīnus ';
        }
        if ($n == '0') {
            return 'nulle';
        } else {
            return $b . _number2stringBig($n);
        }
    }
}

if (!function_exists('_number2stringBig')) {
    function _number2stringBig($n) {
        if ($n == '0') return;
        $e = array(
            array(
                '1' => 'tūkstotis',
                '2' => 'miljons',
                '3' => 'miljards',
                '4' => 'triljons',
                '5' => 'kvadriljons',
                '6' => 'kvintiljons',
                '7' => 'sekstiljons',
                '8' => 'septiljons',
                '9' => 'oktiljons',
                '10' => 'nontiljons',
                '11' => 'deciljons',
                '12' => 'undeciljons',
                '13' => 'duodeciljons',
                '14' => 'trideciljons',
                '15' => 'kvartdeciljons',
                '16' => 'kvintdeciljons',
                '17' => 'seksdeciljons',
                '18' => 'septdeciljons',
                '19' => 'oktdeciljons',
                '20' => 'nondeciljons',
            ),
            array(
                '1' => 'tūkstoši',
                '2' => 'miljoni',
                '3' => 'miljardi',
                '4' => 'triljoni',
                '5' => 'kvadriljoni',
                '6' => 'kvintiljoni',
                '7' => 'sekstiljoni',
                '8' => 'septiljoni',
                '9' => 'oktiljoni',
                '10' => 'nontiljoni',
                '11' => 'deciljoni',
                '12' => 'undeciljoni',
                '13' => 'duodeciljoni',
                '14' => 'trideciljoni',
                '15' => 'kvartdeciljoni',
                '16' => 'kvintdeciljoni',
                '17' => 'seksdeciljoni',
                '18' => 'septdeciljoni',
                '19' => 'oktdeciljoni',
                '20' => 'nondeciljoni',
            ),
        );
        $length = strlen((string)$n);
        $pow = ceil($length / 3) - 1;
        $digits = ($length - 1) % 3 + 1;

        $begin = substr($n, 0, $digits);
        $s = _number2stringSmall($begin);
        if ($pow > 0) {
            $end = substr($n, $digits);
            if (substr($begin, -1) == 1 && substr($begin, 0, 1) == 1) {
                $middle = $e[0][$pow];
            } else {
                $middle = $e[1][$pow];
            }
            $s .= ' ' . $middle;
            $s .= ' ' . _number2stringBig($end);
        }

        return $s;
    }
}

if (!function_exists('_number2stringSmall')) {
    function _number2stringSmall($n) {
        $digits = array('', 'viens', 'divi', 'trīs', 'četri', 'pieci', 'seši', 'septiņi', 'astoņi', 'deviņi');
        $preDigits = array('', 'vien', 'div', 'trīs', 'četr', 'piec', 'seš', 'septiņ', 'astoņ', 'deviņ');
        $n = (string)$n;
        $l = strlen($n);
        if ($l > 3) return false;
        if ($l == 3) {
            if ($n{
                0} == 0) return $digits[$n{
                0}] . ' ' . _number2stringSmall(substr($n, 1));
            return $digits[$n{
                0}] . ($n{
                0} == 1 ? ' simts' : ' simti') . ' ' . _number2stringSmall(substr($n, 1));
        } else {
            if ($l == 1) return $digits[$n];

            if ($n{
                0} == 1) {
                if ($n == '10') return 'desmit';
                return $preDigits[$n{
                    1}] . 'padsmit';
            }

            if ($n{
                0} == 0 && $n{
                1} == 0)  return '';

            $s = $preDigits[$n{
                0}] . 'desmit';

            if ($n{
                1} != '0') {
                $s .= ' ' . $digits[$n{
                    1}];
            }
            return $s;
        }
    }
}

function addBigCurrency($n) {
    $last = substr($n, -1);
    if ($last == 0) {
        return " eiro"; //" latu";
    } elseif ($last == 1) {
        return " eiro";  //" lats";
    } else {
        return " eiro";  //" lati";
    }
}

function addSmallCurrency($n) {
    $last = substr($n, -1);
    if ($last == 0) {
        return  " centi"; //" santīmu";
    } elseif ($last == 1) {
        return " cents"; //" santīms";
    } else {
        return " centi"; //" santīmi";
    }
}

function amount2words($num) {
    if ($num > 999999999999999999999999999999999999999999999999999999999999999.99) {
        return "ERROR: Skaitlis ir par lielu!";
    } elseif ($num < -999999999999999999999999999999999999999999999999999999999999999.99) {
        return "ERROR: Skaitlis ir par mazu!";
    } else {
        //funkcija pieņem tikai skaitļus ar "." kā decimālattalītāju
        $parts = explode(".", $num);
        if (count($parts) <= 2) {
            $int = $parts[0];
            $dec = $parts[1];
            $str_resp = number2string($int) . addBigCurrency($int);
            if (count($parts) == 2) {
                if ($dec > 99 || $dec < 0) {
                    return "ERROR: Unknown situation!";
                } elseif ($dec > 0 || $dec < 10) {
                    if (strlen($dec) > 1) {
                        $dec = intval($dec);
                    } else {
                        $dec = $dec * 10;
                    }
                    $str_resp .= " un " . $dec . addSmallCurrency($dec);
                } else {
                    $str_resp .= " un " . $dec . addSmallCurrency($dec);
                }
            } //RG: ja nepieciešams izvadīt arī 00 santīmus, kad tie nav pierakstīti! /*
            else {
                $str_resp .= " un 00" . addSmallCurrency($dec);
            } //*/
            return $str_resp;
        } else {
            return "ERROR: Unknown situation!";
        }
    }
}

function date2text($datums) {
    $menesis = array("", "janvārī", "februārī", "martā", "aprīlī", "maijā", "jūnijā", "jūlijā", "augustā", "septembrī", "oktobrī", "novembrī", "decembrī");

    list($dat) = explode(" ", $datums);
    list($yehr, $month, $day) = explode("-", $dat);

    $textdate = $yehr . ".gada " . $day . "." . $menesis[ltrim($month, '0')];

    return $textdate;
}
