<?php


namespace ppg\Elements;


use InvalidArgumentException;
use ppg\Element;
use TCPDF_STATIC;

class Text extends Element
{
    public function render()
    {
        $bounds = $this->getBounds(false, false, false);

        $border = $this->getBorder();

        $align = strtoupper($this->data['align']);
        if(!in_array($align, ['', 'L','C','R','J'])){
            throw new InvalidArgumentException(__METHOD__ . '(): Invalid align value [ ' . $align . ' ]');
        }

        $fill_color = $this->pdf->convertHTMLColorToDec($this->data['background-color']);
        if($fill_color){
            $this->pdf->saveFillColor();
            $this->pdf->SetFillColorArray($fill_color);
        }

        $text_color = $this->pdf->convertHTMLColorToDec($this->data['color']);
        if($text_color){
            $this->pdf->saveTextColor();
            $this->pdf->SetTextColorArray($text_color);
        }

        $padding = $this->pdf->getCellPaddings();
        if (is_array($this->data['padding'])) {
            $this->pdf->setCellPaddings(
                $this->data['padding'][0], //left
                $this->data['padding'][1], //top
                $this->data['padding'][2], //right
                $this->data['padding'][3] //bottom
            );
        } else if ($this->data['padding']) {
            $this->pdf->SetCellPadding($this->data['padding']);
        }

        $this->setFont($this->data['font-family'], $this->data['font-style'], $this->data['font-size']);

        $marging = (float)$this->data['margin'];
        if($marging){
            $this->pdf->SetY($this->pdf->GetY() + $marging, false);
        }

        //TODO: margin
        $this->data['text'] = preg_replace_callback('/{([\w_.-]+)}/', function(array $matches){
            return $this->document->getData($matches[1]);
        }, $this->data['text']);

        $next = $this->data['next'];
        if(!in_array($next, ['', '0','1','2','3'])){
            throw new InvalidArgumentException(__METHOD__ . '(): Invalid "next" value [ ' . $next . ' ]');
        }
        if($next==''){
            $next = 1;
        }

        if (TCPDF_STATIC::empty_string($bounds['x'])) {
            $bounds['x'] = $this->pdf->GetX();
        }
        //$this->pdf->MultiCell($bounds['width'], $bounds['height'], $this->data['text'], $border, $align, (bool)$fill_color, $next, $bounds['x'], $bounds['y']);
        $this->pdf->writeHTMLCell($bounds['width'], $bounds['height'], $bounds['x'], $bounds['y'], $this->data['text'], $border, $next, (bool)$fill_color, true, $align);

        if($next==3){
            $cell_margin = $this->pdf->getCellMargins();
            $this->pdf->SetX($bounds['x'] + $cell_margin['L'] + $cell_margin['R']);
        }

        if($fill_color){
            $this->pdf->restoreFillColor();
        }

        if($text_color){
            $this->pdf->restoreTextColor();
        }

        $this->restoreFont();


        if ($this->data['padding']) {
            $this->pdf->SetCellPaddings($padding['L'], $padding['T'], $padding['R'], $padding['B']);
        }
    }
}
