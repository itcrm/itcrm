<?php

require_once './tcpdf/config/lang/eng.php';
require_once './tcpdf/tcpdf.php';
require_once "classes/number2text.php";

class PavadzimePrints extends TCPDF {
    function Load() {
    }

    public function Dataget($Object) {
        $ID = $_GET['ID'];

        $query = 'SELECT ID,IDDoc,Date,Note,PlaceTaken,PlaceDone FROM `Data` WHERE ID = ' . $ID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $FullObject = $row[$Object];
        }

        return $FullObject;
    }

    public function date2text($datums) {
        $menesis = array("", "janvāris", "februāris", "marts", "aprīlis", "maijs", "jūnijs", "jūlijs", "augusts", "septembris", "oktobris", "novembris", "decembris");

        list($dat) = explode(" ", $datums);
        list($yehr, $month, $day) = explode("-", $dat);

        $textdate = $yehr . ".gada " . $day . "." . $menesis[ltrim($month, '0')];

        return $textdate;
    }

    public function pavadzimeget($Object, $DocID) {
        $query = 'SELECT ID,DocID,Samaksa,Sanemejs,Atlaide,Izsniedza,Kopa,atlaidessumma,PirmsNodokliem,PVN,Samaksai FROM `pavadzime` WHERE DocID = ' . $DocID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $FullObject =  $row[$Object];
        }
        return $FullObject;
    }

    public function sanemejs($sanemejs) {
        $query = 'SELECT Nosaukums, Kods, Adrese, Banka, Konts FROM `sanemeji` WHERE Nosaukums = "' . $sanemejs . '"';

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $this->Cell(70, 0, 'Saņēmējs:', 0, 0, 'L', 0, '', 0);
            $this->Cell(40, 0, rawurldecode($row['Nosaukums']), 0, 1, 'L', 0, '', 0);

            $this->Cell(70, 0, 'Nodokļu maksātāja kods:', 0, 0, 'L', 0, '', 0);
            $this->Cell(40, 0, $row['Kods'], 0, 1, 'L', 0, '', 0);

            $this->Cell(70, 0, 'Juridiskā adrese:', 0, 0, 'L', 0, '', 0);
            $this->Cell(40, 0, rawurldecode($row['Adrese']), 0, 1, 'L', 0, '', 0);

            $this->Cell(70, 0, 'Kredītiestādes nosaukums:', 0, 0, 'L', 0, '', 0);
            $this->Cell(40, 0, rawurldecode($row['Banka']), 0, 1, 'L', 0, '', 0);

            $this->Cell(70, 0, 'Norēķinu konta Nr:', 0, 0, 'L', 0, '', 0);
            $this->Cell(40, 0, $row['Konts'], 0, 1, 'L', 0, '', 0);
        }
    }

    public function tabula($DocID) {
        $query = 'SELECT ID,Nosaukums,Artikuls,Daudzums,Mervieniba,Cena,Summa FROM `pavadzime_preces` WHERE DocID = ' . $DocID;

        if (!$result = self::$DB->query($query)) {
            throw new Error('Read error on Pavadzimes (' . __LINE__ . ')');
        }

        while ($row = $result->fetch_assoc()) {
            $this->Cell(70, 0, $row['Nosaukums'], 1, 0, 'L', 0, '', 0);
            $this->Cell(40, 0, $row['Artikuls'], 1, 0, 'L', 0, '', 0);
            $this->Cell(20, 0, $row['Daudzums'], 1, 0, 'L', 0, '', 0);
            $this->Cell(20, 0, $row['Mervieniba'], 1, 0, 'L', 0, '', 0);
            $this->Cell(20, 0, $row['Cena'], 1, 0, 'L', 0, '', 0);
            $this->Cell(20, 0, $row['Summa'], 1, 1, 'L', 0, '', 0);
        }
    }
}

// create new PDF document
$pdf = new PavadzimePrints(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 011');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default header data

$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// set font
$pdf->SetFont('freesans', '', 12);

// add a page
$pdf->AddPage();

$pdf->SetFont('freesans', '', 12);

$pdf->SetFillColor(219, 219, 219);
$pdf->SetLineWidth(0.2);

$pdf->SetLineStyle(array('width' => 1.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

$LineStyle = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

$pdf->SetFont('freesans', '', 12);

$pdf->SetFillColor(219, 219, 219);
$pdf->SetLineWidth(0.2);

$rekinanr = $pdf->Dataget('IDDoc');

$pdf->Cell(0, 0, 'PREČU PAVADZIME- RĒĶINS Nr. A1-' . $rekinanr, 0, 1, 'C', 0, '', 0);
$pdf->Cell(0, 0, '', 0, 1, 'C', 0, '', 0);

$pdf->SetFont('freesans', '', 10);

// Nosūtītājs
$datums = $pdf->Dataget('Date');
$datums = $pdf->date2text($datums);

$pdf->Cell(0, 0, $datums, 0, 1, 'L', 0, '', 0);

$pdf->Cell(70, 0, 'Nosūtītājs:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, 'SIA "Auto1.LV"', 0, 1, 'L', 0, '', 0);

$pdf->Cell(70, 0, 'Nodokļu maksātāja kods:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, '44103128171', 0, 1, 'L', 0, '', 0);

$pdf->Cell(70, 0, 'Juridiskā adrese:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, 'Rīga, Brīvības gatve 197C, LV-1039', 0, 1, 'L', 0, '', 0);

$pdf->Cell(70, 0, 'Kredītiestādes nosaukums:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, 'AS "Citadele banka"', 0, 1, 'L', 0, '', 0);

$pdf->Cell(70, 0, 'Swift kods:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, 'PARXLV22', 0, 1, 'L', 0, '', 0);

$pdf->Cell(70, 0, 'Norēķinu konta Nr:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, 'LV28PARX0022842500001', 0, 1, 'L', 0, '', 0);

$vieta2 = $pdf->Dataget('PlaceTaken');

$pdf->Cell(70, 0, 'Izsniegšanas vieta:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, $vieta2, 0, 1, 'L', 0, '', 0);

$pdf->line(10, 55.7, 200, 55.7, $LineStyle);

// Saņēmējs
$DocID = $pdf->Dataget('ID');
$sanemejs = $pdf->pavadzimeget('Sanemejs', $DocID);
$pdf->sanemejs($sanemejs);

$vieta = $pdf->Dataget('PlaceDone');
$pdf->Cell(70, 0, 'Saņemšanas vieta:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, $vieta, 0, 1, 'L', 0, '', 0);

// Piezimes
$pdf->line(10, 78, 200, 78, $LineStyle);
$piezimes = $pdf->Dataget('Note');
$pdf->Cell(70, 0, 'Speciālas piez.:', 0, 0, 'L', 0, '', 0);
$pdf->MultiCell(120, 0, $piezimes, 0, 'L', 0, 1, '', '', false);

$samaksa = $pdf->pavadzimeget('Samaksa', $DocID);
$pdf->Cell(70, 0, 'Samaksas veids un kārtība:', 0, 0, 'L', 0, '', 0);
$pdf->MultiCell(120, 0, $samaksa, 0, 'L', 0, 1, '', '', false);

$Izsniedza = $samaksa = $pdf->pavadzimeget('Izsniedza', $DocID);
$pdf->Cell(70, 0, 'Pakalpojuma sniegšanas laiks:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(40, 0, $Izsniedza, 0, 1, 'L', 0, '', 0);

// Tabula
$pdf->SetFont('freesans', '', 8);
$pdf->Cell(70, 0, 'Preču nosaukums', 1, 0, 'C', 1, '', 0);
$pdf->Cell(40, 0, 'Artikuls', 1, 0, 'C', 1, '', 0);
$pdf->Cell(20, 0, 'daudz', 1, 0, 'C', 1, '', 0);
$pdf->Cell(20, 0, 'mērv', 1, 0, 'C', 1, '', 0);
$pdf->Cell(20, 0, 'Cena(eiro)', 1, 0, 'C', 1, '', 0);
$pdf->Cell(20, 0, 'Summa(eiro)', 1, 1, 'C', 1, '', 0);
$pdf->SetFont('freesans', '', 10);
// Rinda(1)

$pdf->tabula($DocID);

// footers
$pdf->Cell(170, 0, 'Kopā izsniegts:', 1, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $pdf->pavadzimeget('Kopa', $DocID), 1, 1, 'L', 0, '', 0);

$atlaide = $pdf->pavadzimeget('Atlaide', $DocID);
$pdf->Cell(170, 0, 'Atlaide ' . $atlaide . '%', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $pdf->pavadzimeget('atlaidessumma', $DocID), 1, 1, 'L', 0, '', 0);

$pdf->Cell(170, 0, 'Summa pirms nodokļiem', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $pdf->pavadzimeget('PirmsNodokliem', $DocID), 1, 1, 'L', 0, '', 0);

$pdf->Cell(170, 0, 'Pievienotās vērtības nodoklis 0%:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, $pdf->pavadzimeget('PVN', $DocID), 1, 1, 'L', 0, '', 0);

$pdf->Cell(170, 0, 'Pavisam samaksai:', 0, 0, 'L', 0, '', 0);
$pdf->SetFont('freesans', '', 12);
$pdf->Cell(20, 0, $pdf->pavadzimeget('Samaksai', $DocID), 1, 1, 'L', 0, '', 0);
$pdf->SetFont('freesans', '', 10);

$pdf->Cell(20, 0, 'Summa vārdiem:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(170, 0, amount2words($pdf->pavadzimeget('Samaksai', $DocID)), 0, 1, 'C', 0, '', 0);
$pdf->ln();

$pdf->Cell(20, 0, 'Izsniedza:', 0, 0, 'L', 0, '', 0);
$pdf->Cell(110, 0, 'R.Timrots', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, 'Pieņēma:______________________', 0, 1, 'L', 0, '', 0);
$pdf->ln();

$pdf->Cell(130, 0, $datums, 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0, 'Datums: _______________________', 0, 1, 'L', 0, '', 0);
$pdf->ln();

$pdf->Cell(130, 0, 'Paraksts:____________________', 0, 0, 'L', 0, '', 0);
$pdf->Cell(20, 0,  'Paraksts:_______________________', 0, 0, 'L', 0, '', 0);

$pdf->Ln(1);

$pdf->SetTextColor(0);
$pdf->SetLineWidth(0.1);

//Close and output PDF document
$pdf->Output('example_01100.pdf', 'I');
