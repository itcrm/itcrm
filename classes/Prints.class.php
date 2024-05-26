<?php

require_once './tcpdf/config/lang/eng.php';
require_once './tcpdf/tcpdf.php';

class Prints extends TCPDF {
    public function Load() {
    }

    public function Header() {
        $this->SetFont('freesans', '', 6);
        // Title
        $this->Cell(0, 15, date("Y.m.d H:i"), 0, false, 'R', 0, '', 0, false, 'M', 'M');
    }

    public function addtabless() {
        $X = 8;
        while ($X <= 21) {
            $this->MultiCell(12, 16, $X . '<sup>00</sup>', 'LTRB', 'R', 1, 0, '', '', 'true', '', 'true');
            $this->MultiCell(168, 8, '', 'LTRB', 'L', 0, 0);
            $this->Ln();
            $this->MultiCell(168, 8, '', 'LTRB', 'L', 0, 0, 22);
            $this->Ln();
            $X++;
        }
    }

    public function calendar($m, $G, $X, $Y) {
        $menesis = array("", "Janvāris", "Februāris", "Marts", "Aprīlis", "Maijs", "Junījs", "Julījs", "Augusts", "Septembris", "Oktobris", "Novembris", "Decembris");

        $this->SetFont('freesans', '', 8);
        $this->SetLineWidth(0.2);
        $x = $X;
        $y = $Y;
        $date = mktime(12, 0, 0, $m, 1, $G);
        $daysInMonth = date("t", $date);
        // calculate the position of the first day in the calendar (sunday = 1st column, etc)
        $offset = date("N", $date);
        $rows = 1;
        $this->MultiCell(35, 1, $menesis[date("n", $date)] . "" . date(" Y", $date), '', 'C', 0, 0, $X + 5.5, $Y - 8, 'true', '', 'true');
        $this->MultiCell(35, 1, 'Pr  Ot  Tr  Ce  Pk  Se  Sv', 'B', 'B', 0, 1, $X + 5.5, $Y - 4, 'true', '', 'true');
        for ($i = 3; $i <= $offset; $i++) {
            $x = $x + 5;
            $this->MultiCell(10, 10, '', '', 'R', 0, 1, $x, $y, 'true', '', 'true');
        }
        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (($day + $offset - 2) % 7 == 0 && $day != 0) {
                $y = $y + 3;
                $x = $X;
                $this->MultiCell(10, 10, $day, '', 'R', 0, 1, $x, $y, 'true', '', 'true');
                $day++;
                $rows++;
            }
            $x = $x + 5;
            $this->MultiCell(10, 10, $day, '', 'R', 0, 0, $x, $y, 'true', '', 'true');
        }
        while (($day + $offset) <= $rows * 7) {
            $this->MultiCell(10, 10, ' ', '', 'R', 0, 0, '', '', 'true', '', 'true');
            $day++;
        }
    }

    public function tasks() {
        /*                    Uzdevumu izvades funkcija
//     $sdata,$bdata,$pas,$text
// Funkcija nolasa post nodoto datumu piesledzas pie datubazes un nolasa dienu
// Sakot ar 8:00 in beidzot ar 21:00 visus datus tā izvada uz tabulas labajā Pusē
// Nepieciešamie dati: Sākumdatums sdat beigu datums bdata, pasutijuma nosaukums
// pas un konkretais uzdevuma teksts text.
*/
        if ($_GET['task'] == "true") {
            '';
        } else {
            if (isset($_GET['user'])) {
                $UID = $_GET['user'];
                if (isset($_GET['day'])) {
                    $Day = $_GET['day'];
                } else {
                    $Day = date("Y-m-j");
                }

                $a = "'$Day 08:00:00'";
                $b = "'$Day 21:59:59'";
                $query = 'SELECT *, Data.ID as TaskID, Data.Changes as izmainas FROM Data LEFT JOIN Orders ON Orders.ID=Data.IDOrder WHERE `RemindDate` between ' . $a . ' and ' . $b . ' and `RemindTo`= ' . $UID . ' ';                                 //and `Date` between '. $a .' and ' . $b .'
                if (!$result = self::$DB->query($query)) {
                    throw new Error('Read error on Print Dienaslapa (' . __LINE__ . ')');
                }
                while ($row = $result->fetch_assoc()) {
                    list($sdat, $stime) = explode(" ", $row['RemindDate']);
                    list($syehr, $smonth, $sday) = explode("-", $sdat);
                    list($sh, $sm, $ss) = explode(":", $stime);

                    list($bdat, $btime) = explode(" ", $row['RemindDateEnd']);
                    list($byehr, $bmonth, $bday) = explode("-", $bdat);
                    list($bh, $bm, $bs) = explode(":", $btime);

                    if ($row['RemindDateEnd'] == "0000-00-00 00:00:00") {
                        $bh = $sh + 1;
                        $bm = $sm;
                        $bs = $ss;
                    }

                    $sdatums = mktime($sh, $sm, $ss, $sday, $smonth, $syehr); //"2010-07-11 11:00";
                    $slaiks = (date("G", $sdatums) * 60 + date("i", $sdatums)) / 30 * 8;

                    $bdatums = mktime($bh, $bm, $bs, $bday, $bmonth, $byehr); //"2010-07-11 12:00";
                    $blaiks = (date("G", $bdatums) * 60 + date("i", $bdatums)) / 30 * 8;

                    $x = 110;
                    $y = $slaiks - 128 + 41;
                    $garums = 80;
                    $platums = ($blaiks - 128 + 41) - $y;
                    $this->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
                    $this->RoundedRect($x, $y, $garums, $platums, 3.50, '1111', 'DF');
                    $this->Line($x, $y + 3, $x + $garums, $y + 3);
                    $this->SetFont('freesans', '', 8);
                    $this->Text($x + 2, $y - 0.5, $row['Code']);
                    $this->MultiCell(80, $platums - 5, $row['TextOrder'] . "\n" . $row['Note'], 0, 'L', 0, 0, $x, $y + 3, false, 0, false, false, $platums - 5, 'T', true);
                }
            } else {
                throw new Error('Nav saņemti parametri drukāšanai');
            }
        }
    }
}

// create new PDF document
$pdf = new Prints(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 011');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setPrintFooter(false);

$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

$pdf->SetFont('freesans', '', 12);

$pdf->AddPage();

$men = array("", "Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec");
$dienas = array("", "Pirmdiena", "Otrdiena", "Trešdiena", "Ceturtdiena", "Piektdiena", "Sestdiena", "Svētdiena");

if (isset($_GET['day'])) {
    $Days = $_GET['day'];
} else {
    $Days = date('Y-m-j');
}

list($dat) = explode(" ", $Days);
list($yehr, $month, $day) = explode("-", $dat);
$datums = mktime(0, 0, 0, $month, $day,  $yehr);

$pdf->SetFont('freesans', '', 12);
$html = '<h2>' . date('Y.', $datums) . "" . date('m', $datums) . "." . date('d', $datums) . '</h2><br><span>' . $dienas[date('N', $datums)] . '</span><br><span>' . $_SESSION['User']->getName() . '</span>';
$pdf->SetFillColor(219, 219, 219);
$pdf->SetLineWidth(0.2);

$pdf->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$pdf->RoundedRect(10, 10, 180.4, 30, 3.50, '0000', 'DF'); //180.4

$pdf->SetFont('freesans', '', 12);

$pdf->SetFillColor(219, 219, 219);
$pdf->SetLineWidth(0.2);
$pdf->MultiCell(60, 30, $html, 'LTRB', 'L', 1, 1, '', '', 'true', '', 'true');

$pdf->calendar($month, $yehr, 100, 18);
$pdf->calendar($month + 1, $yehr, 145, 18);
$pdf->MultiCell(1, 30, '', 'LTRB', 'L', 1, 1, '150', '10', 'true', '', 'true');
$pdf->Ln(1);

$pdf->SetTextColor(0);
$pdf->SetLineWidth(0.1);

$pdf->addtabless();

$pdf->tasks();

//Close and output PDF document
$pdf->Output('example_01100.pdf', 'I');
