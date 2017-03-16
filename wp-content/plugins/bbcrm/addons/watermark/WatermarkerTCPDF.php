<?php

require_once('tcpdf/tcpdf.php');
require_once('FPDI/fpdi.php');

class WatermarkerTCPDF extends FPDI {
    public $pdf, $file, $newFile,
            $wmText = "",
            $fontsize = 24,
            $fontfamily = 'helvetica';

    /** $file and $newFile have to include the full path. */
    public function __construct($file = null, $newFile = null) {
        $this->pdf = new FPDI();
        //custom fonts
        //$this->fontfamily = $this->pdf->addTTFfont(APPPATH . 'third_party/tcpdf/ttf/ptsansnarrow400.ttf', 'TrueTypeUnicode', '');
        if (!empty($file)) {
            $this->file = $file;
        }
        if (!empty($newFile)) {
            $this->newFile = $newFile;
        }
    }

    /** $file and $newFile have to include the full path. */
    public static function applyAndSpit($file, $newFile = null) {
        $wm = new Watermarker($file, $newFile);

        if ($wm->isWaterMarked())
            return $wm->spitWaterMarked();
        else {
            $wm->doWaterMark();
            return $wm->spitWaterMarked();
        }
    }

    /** @todo Make the text nicer and add to all pages */
    public function doWaterMark() {
        $currentFile = $this->file;
        $newFile = $this->newFile;

        $pagecount = $this->pdf->setSourceFile($currentFile);

        for ($i = 1; $i <= $pagecount; $i++) {
            $tplidx = $this->pdf->importPage($i);
            $specs = $this->pdf->getTemplateSize($tplidx);
            $this->pdf->SetPrintHeader(false);
            $this->pdf->SetPrintFooter(false);
            $this->pdf->addPage($specs['h'] > $specs['w'] ? 'P' : 'L');
            $this->pdf->useTemplate($tplidx, null, null, 0, 0, true);

            // now write some text above the imported page
            $this->pdf->SetFont($this->fontfamily, '', $this->fontsize);
            $this->pdf->SetTextColor(146, 158, 158);
            //$this->pdf->SetXY($specs['w']/2, $specs['h']/2);
            $_x = ($specs['w']/2.7) - ($this->pdf->GetStringWidth($this->wmText, $this->fontfamily, '', $this->fontsize)/2.8);
            $_y = $specs['h']/2.7;
            $this->pdf->SetXY($_x, $_y);
            //$this->pdf->SetXY(0, 0);
            $this->pdf->setAlpha(0.3);
            $this->_rotate(45, 100, 100);
            $this->pdf->Write(0, $this->wmText);
            //$this->pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $this->wmText, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
        }

        if (empty($newFile)) {
            header('Content-Type: application/pdf');
            $this->pdf->Output();
        } else {
            $this->pdf->Output($newFile, 'F');
        }
    }

    public function isWaterMarked() {
        //return (file_exists($this->newFile));
        $_file = $this->newFile;
        $file = file_get_contents($_file);
        force_download($file);
    }

    public function spitWaterMarked() {
        $_file = $this->newFile;
        $file = file_get_contents($_file);
        force_download($file);
        //return readfile($this->newFile);
    }

    protected function _rotate($angle, $x = -1, $y = -1) {
        if ($x == -1)
            $x = $this->pdf->x;
        if ($y == -1)
            $y = $this->pdf->y;
        //if ($this->pdf->angle != 0)
            //$this->pdf->_out('Q');
        $this->pdf->angle = $angle;

        if ($angle != 0) {
            $angle*=M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->pdf->k;
            $cy = ($this->pdf->h - $y) * $this->pdf->k;

            $this->pdf->_out(sprintf(
                            'q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    public function wmText($text = null)
    {
        $total = 1;
        if (!empty($text)) {
            $this->wmText = '';
            for ($i = 0; $i < $total; $i++) {
                $this->wmText .= ' ' . $text;
            }

        }

        return $this;
    }
}
?>