<?php
require("pdflib.php");

function certificate_print_text($pdf, $x, $y, $align, $font='freeserif', $style, $size = 10, $text, $width = 0) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($width, 0, '', '', $text, 0, 0, 0, true, $align);
}

$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("My Awesome Certificate");
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

    $x = 10;
    $y = 40;

    $sealx = 150;
    $sealy = 220;
    $seal = realpath("./seal.png");

    $sigx = 30;
    $sigy = 230;
    $sig = realpath("./signature.png");

    $custx = 30;
    $custy = 230;

    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $wmark = realpath("./watermark.png");

    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;


$fontsans = 'helvetica';
$fontserif = 'times';

// border
$pdf->SetLineStyle(array('width' => 1.5, 'color' => array(0,0,0)));
$pdf->Rect(10, 10, 190, 277);
// create middle line border
$pdf->SetLineStyle(array('width' => 0.2, 'color' => array(64,64,64)));
$pdf->Rect(13, 13, 184, 271);
// create inner line border
$pdf->SetLineStyle(array('width' => 1.0, 'color' => array(128,128,128)));
$pdf->Rect(16, 16, 178, 265);


// Set alpha to semi-transparency
if (file_exists($wmark)) {
    $pdf->SetAlpha(0.2);
    $pdf->Image($wmark, $wmarkx, $wmarky, $wmarkw, $wmarkh);
}

$pdf->SetAlpha(1);
if (file_exists($seal)) {
    $pdf->Image($seal, $sealx, $sealy, '', '');
}
if (file_exists($sig)) {
    $pdf->Image($sig, $sigx, $sigy, '', '');
}

// Add text
$pdf->SetTextColor(0, 0, 120);
certificate_print_text($pdf, $x, $y, 'C', $fontsans, '', 30, "Certificate of Awesomeness");
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y + 20, 'C', $fontserif, '', 20, "This is to certify that");
certificate_print_text($pdf, $x, $y + 36, 'C', $fontsans, '', 30, "JOHN CENA");
certificate_print_text($pdf, $x, $y + 55, 'C', $fontsans, '', 20, "has successfully been declared awesome in");
certificate_print_text($pdf, $x, $y + 72, 'C', $fontsans, '', 20, "the Butt of Many Jokes");
certificate_print_text($pdf, $x, $y + 92, 'C', $fontsans, '', 14,  "13th June 1992");
certificate_print_text($pdf, $x, $y + 102, 'C', $fontserif, '', 10, "With a grade of 12%");
certificate_print_text($pdf, $x, $y + 112, 'C', $fontserif, '', 10, "Earning him a E- :(");
certificate_print_text($pdf, $x, $y + 122, 'C', $fontserif, '', 10, "In only 206 hours. Yep. 206.");

header ("Content-Type: application/pdf");
echo $pdf->Output('', 'S');