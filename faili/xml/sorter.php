<?php

header("Content-type: text/plain; charset=UTF-8");
$_IDC_ENGINE_ROOT = '../';
require $_IDC_ENGINE_ROOT . 'core/classes/ccore.php';
$_core = new CCore();
$_core->initialize();

function getFileDate($name) {
    $parts = explode('.', $name);
    /**
     * 5-ciparu variants
     */
    if (
        is_numeric(@$parts[0]) &&
        is_numeric(@$parts[1]) &&
        is_numeric(@$parts[2]) &&
        is_numeric(@$parts[3]) &&
        is_numeric(@$parts[4]) &&
        (($parts[0] >= 0)) &&            // gads
        (($parts[1] >= 0) && ($parts[1] <= 12)) &&            // mēnesis
        (($parts[2] >= 0) && ($parts[2] <= 31)) &&            // datums, pārbaude gan ir aptuvena, ignorējot mēneša garumu
        (($parts[3] >= 0) && ($parts[3] <= 24)) &&            // stunda
        (($parts[4] >= 0) && ($parts[4] <= 60))                // minūte
    ) {
        $timestamp = '20' . $parts[0] . '-' . $parts[1] . '-' . $parts[2] . ' ' . $parts[3] . ':' . $parts[4] . ':' . '00';

        return $timestamp;
    }

    /**
     * 3-ciparu variants
     */
    if (
        is_numeric(@$parts[0]) &&
        is_numeric(@$parts[1]) &&
        is_numeric(@$parts[2]) &&
        (($parts[0] >= 0)) &&            // gads
        (($parts[1] >= 0) && ($parts[1] <= 12)) &&            // mēnesis
        (($parts[2] >= 0) && ($parts[2] <= 31))                // datums, pārbaude gan ir aptuvena, ignorējot mēneša garumu
    ) {
        $timestamp = '20' . $parts[0] . '-' . $parts[1] . '-' . $parts[2] . ' ' . '00' . ':' . '00' . ':' . '00';

        return $timestamp;
    }

    return false;
}

$log = "";

//echo "<PRE>"; // ja nu outputs tiek skatīts browserī, lai izskatās cik-necik normāli

$sourceRoot = $_IDC_CONFIG['sorter.root'];

$_orderCache = new CList('COrder');
$_orderCache->indexAttributes = array('Code');
$_orderCache->loadByField('', '');

$userCache = new CList('CSysUser');
$userCache->loadByField('', '');

$ddList = array();
foreach ($userCache->objects as $user) {
    $userData = '..' . strtolower($user->Login) . '..dd.';
    $ddList[$user->Login] = $userData;
}

$finList = array(
    '..cek..' => 'ppr/',
    '..ppr..' => 'ppr/',
    '..rek..' => 'ppr/',
    '..pas..' => 'ppr/'
);

$dirList = scandir($sourceRoot);

$query = new CQuery("SELECT ID FROM Types WHERE code='sorter'");
$row = $query->fetch();
if ($row === false) die("Neatradu sortera tipu!\n");
$sorterType = $row[0];

$query = new CQuery("SELECT ID FROM Users WHERE Login='sorter'");
$row = $query->fetch();
if ($row === false) die("Neatradu sortera lietotāju!\n");
$sorterUser = $row[0];

$query = new CQuery("SELECT ID FROM Orders WHERE Code='fin'");
$row = $query->fetch();
if ($row === false) die("Neatradu finanšu pasūtījumu!\n");
$finOrder = $row[0];

$thisDate = date("d.m.Y H:i:s");
$log .= "Sākam sortēt: {$thisDate}\n";
foreach ($dirList as $file) {
    $sorted = false;
    if (($file == '.') || ($file == '..'))
        continue;
    $error = '';

    $date = getFileDate($file);
    if ($date !== false) {
        $log .= "Atpazīts faila datums: " . $date . "\n";
    }

    /**
     * ******************************** Specifiskie gadījumi ****************************
     */
    /**
     * Dienas lapas
     */
    foreach ($ddList as $user => $ddEntry) {
        if (strpos(strtolower($file), strtolower($ddEntry)) !== false) {
            $targetDir = $_IDC_CONFIG['filelist.root'] . '/' . $user . '/';
            $log .= "Atradu lietotāju, kam iesortēt dienas lapu: {$file} -> {$user}. Sortēšu uz {$targetDir}: ";
            $sorted = true;

            $target = rtrim($targetDir, '/') . '/' . $file;

            if (!file_exists(rtrim($targetDir, '/'))) {
                $log .= "Nesanāca!\n";
                $error .= "Lietotāja folderis neeksistē ({$user})\n";
                continue;
            }

            if (file_exists($target)) {
                $log .= "Nesanāca!\n";
                $error .= "Tāds fails jau tur ir, atstājam visu savā vietā. ({$user})\n";
                continue;
            }

            $result = @moveItem(rtrim($sourceRoot, '/') . '/' . $file, rtrim($targetDir, '/') . '/' . $file, $targetDir);

            if ($result === false) {
                $log .= "Nesanāca!\n";
                $error .= "Kaut kas nesanāca. Var būt, ka tāds fails jau eksistē? Mistika, bet nu atstājam kā ir.\n";
            } else {
                $moved = true;

                $query = new CQuery("SELECT ID FROM Orders WHERE Code='{$user}'");
                $row = $query->fetch();
                if ($row === false) {
                    $error .= "Neatradu pasūtījumu lietotājam {$user}!\n";
                } else {
                    $log .= "Izdarīts!\n";
                    $userOrder = $row[0];
                    if ($date !== false) {
                        $addDate = SQLnow(true);
                        $log .= "Veidoju rindu: Date:{$date},AddDate:{$addDate},Type:{$sorterType},Order:{$userOrder}, Note:{$file}\n";
                        new CQuery("INSERT INTO Data (IDDoc,IDUser,IDPerson,Date,AddDate,IDType,IDOrder,Note,Status,AdminEdit,Hidden) VALUES ('sorter',{$sorterUser},{$sorterUser},'{$date}','{$addDate}',{$sorterType},{$userOrder},'" . sql_escape($file) . "',1,1,0)");
                    }
                }

                break;
            }
        }
    }
    /**
     * Čeki/PPR
     */
    foreach ($finList as $finEntry => $target) {
        if (strpos($file, $finEntry) !== false) {
            $targetDir = $_IDC_CONFIG['filelist.root'] . '/' . $target;
            $log .= "Atradu finanšu dokumentu: {$file}. Sortēšu uz {$targetDir}\n";
            $sorted = true;

            $result = @moveItem(rtrim($sourceRoot, '/') . '/' . $file, rtrim($targetDir, '/') . '/' . $file, $targetDir);

            if ($result === false) {
                $log .= "Nesanāca!\n";
                $error .= "Kaut kas nesanāca. Var būt, ka tāds fails jau eksistē? Baidos aiztikt, atstāšu kā ir.\n";
            } else {
                $log .= "Izdarīts!\n";
                $moved = true;
                if ($date !== false) {
                    $addDate = SQLnow(true);
                    $log .= "Veidoju rindu: Date:{$date},AddDate:{$addDate},Type:{$sorterType},Order:{$finOrder}, Note:{$file}\n";
                    new CQuery("INSERT INTO Data (IDDoc,IDUser,IDPerson,Date,AddDate,IDType,IDOrder,Note,Status,AdminEdit,Hidden) VALUES ('sorter',{$sorterUser},{$sorterUser},'{$date}','{$addDate}',{$sorterType},{$finOrder},'" . sql_escape($file) . "',1,1,0)");
                }
                break;
            }
        }
    }

    /**
     * "Parastā" sortēšana
     */
    if (!$sorted) {
        foreach ($_orderCache->objects as $order) {
            $moved = false;
            if ($order->Code != '') {
                $orderpath = '..' . strtolower($order->Code) . '..';
                if (strpos(strtolower($file), $orderpath) !== false) {
                    $target = $_IDC_CONFIG['filelist.root'] . '/' . $order->Code;
                    $log .= 'Gribu sortēt: ' . $file . ' uz ' . $target . ": ";
                    if (!file_exists($target)) {
                        $log .= "Nesanāca!\n";
                        $error .= "Pasūtījuma folderis neeksistē ({$order->Code})\n";
                        continue;
                    }

                    // TODO: direktorijām var šo izvākt, bet failiem moš atstāt?
                    if (file_exists($target . '/' . $file)) {
                        $log .= "Nesanāca!\n";
                        $error .= "Ā nē, tāds fails jau tur ir. ({$order->Code})\n";
                        continue;
                    }
                    $result = @moveItem(rtrim($sourceRoot, '/') . '/' . $file, rtrim($target, '/') . '/' . $file, $target);

                    if ($result === false) {
                        $log .= "Nesanāca!\n";
                        $error .= "Kaut kas nesanāca. Var būt ka tāds fails jau eksistē? Mistika, bet nu atstājam kā ir ($order->Code)\n";
                    } else {
                        $log .= "Izdarīts!\n";
                        if ($date !== false) {
                            $addDate = SQLnow(true);
                            $log .= "Veidoju rindu: Date:{$date},AddDate:{$addDate},Type:{$sorterType},Order:{$order->id}, Note:{$file}\n";
                            new CQuery("INSERT INTO Data (IDDoc,IDUser,IDPerson,Date,AddDate,IDType,IDOrder,Note,Status,AdminEdit,Hidden) VALUES ('sorter',{$sorterUser},{$sorterUser},'{$date}','{$addDate}',{$sorterType},{$order->id},'" . sql_escape($file) . "',1,1,0)");
                        }
                        $moved = true;
                        break;
                    }
                }
            }
        }
    }
    if (!$moved) {
        if ($error) {
            $log .= "Gribēju darīt, bet nesanāca: {$file} - {$error}";
        } else {
            $log .= "Nesapratu, ko darīt ar šo: {$file}\n{$error}";
        }
    }
}

echo $log;
echo "Papildinformācija:\n";
echo $_MOVE_ERROR_LOG;
$thisDate = date("Y-m-d H-i-s");
file_put_contents('autosorteris-' . $thisDate . '.txt', $log . "\r\nPapildinformācija:\r\n" . $_MOVE_ERROR_LOG);
