<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/includes/fpdf/fpdf.php");

class Table extends FPDF
{
    protected $widths;
    protected $aligns;
    protected $degree;
    protected $signature;
    public $number = '';

    function Header()
    {

        // $this->Image($_SERVER['DOCUMENT_ROOT'] . "/images/oficial/seph.png", 10, 10, 70);
        // $this->subtitle();
    }

    public function subtitle()
    {
        $this->setY($this->getY() + 10);
        $this->SetFont('Times', 'B', 12);
        if ($this->signature) {
            $this->Cell(0, 6, utf8_decode("FORMATO DE REPORTE DE EVALUACIÓN SEMESTRAL CON FIRMA DE ENTERADO"), 0, 0, 'C');
        } else {
            $this->Cell(0, 6, utf8_decode("FORMATO DE REPORTE DE EVALUACIÓN SEMESTRAL"), 0, 0, 'C');
        }
        $this->setY($this->getY() + 5);
        // $this->SetFont('Times', '', 10);
        $this->Cell(0, 6, strtoupper(utf8_decode("{$this->degree->degree}, PLAN {$this->degree->plan}")), 0, 0, 'C');
    }

    public function setDegree($degree)
    {
        $this->degree = $degree;
    }
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    function SetWidths($w)
    {

        $this->widths = $w;
    }

    function SetAligns($a)
    {

        $this->aligns = $a;
    }

    function Row($data, $fill = false, $isFirst = false)
    {
        $this->SetFillColor(229, 231, 233);
        $this->SetFont('Times', 'B', 8);

        $nb = 0;
        for ($i = 0; $i < count($data); $i++) $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;

        $this->CheckPageBreak($h);

        $isCenter = true;
        $temporalFill = $fill;

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            if ($i < 2) {
                $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                // $isCenter = true;
            } else {
                $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
                // $isCenter = false;
            }

            $x = $this->GetX();
            $y = $this->GetY();

            if ($isFirst) {
                $this->SetFillColor(130, 224, 170);
            }
            $haveExtra = strpos($data[$i], '#');
            if($haveExtra){
                
                $price = explode( '#', $data[$i] ) ;
                $data[$i] = $price[0];
                switch($price[1]){
                    case 'at':
                        $this->SetFillColor(249, 242, 236);
                        $fill = true;
                        break;
                    case 'longSt':
                        $this->SetFillColor(255, 240, 230);
                        $fill = true;
                        break;
                    case 'twice':
                        $this->SetFillColor(249, 255, 230);
                        $fill = true;
                        break;
                    default:
                    break;
                }
            }

            if ($fill) {
                $this->Rect($x, $y, $w, $h, 'DF');
            } else {
                $this->Rect($x, $y, $w, $h);
            }

            $this->MultiCell($w, 5, utf8_decode($data[$i]), 0, $a);

            $this->SetXY($x + $w, $y);
            $fill = $temporalFill;
            $this->SetFillColor(229, 231, 233);
        }

        $this->Ln($h);
    }
    function SetHeader($data, $style = '')
    {
        $this->SetFontSize(6.9);
        // $this->SetFont('Calibri', $style, 6.9);
        $this->SetFont('Times', 'B', 12);

        $nb = 0;
        for ($i = 0; $i < count($data); $i++) $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 4 * $nb;

        $this->CheckPageBreak($h);

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';

            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $w, $h);

            // $center = $y + ($h - $this->FontSize) / 2;
            // if($center+ 3.5 <= 7){
            //     $this->SetXY($x, $y + ($h - $this->FontSize) / 2);

            // }

            $this->MultiCell($w, 3.5, utf8_decode($data[$i]), 0, $a);

            $this->SetXY($x + $w, $y);
        }

        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        $x = $this->getX();

        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->setY($this->getY() + 15);
            $this->setX($x);
        }
    }

    function NbLines($w, $txt)
    {

        if (!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
        $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}