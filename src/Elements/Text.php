<?php


namespace ppg\Elements;


use InvalidArgumentException;
use ppg\Element;

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

        $this->setFont($this->data['font-family'], $this->data['font-style'], $this->data['font-size']);

        $this->pdf->SetY($this->pdf->GetY()+(float)$this->data['margin']);

        //TODO: margin , padding
        $this->data['text'] = preg_replace_callback('/{([\w_.-]+)}/', function(array $matches){
            return $this->document->getData($matches[1]);
        }, $this->data['text']);
        $this->pdf->MultiCell($bounds['width'], $bounds['height'], $this->data['text'], $border, $align, (bool)$fill_color, 1, $bounds['x'], $bounds['y']);

        $this->restoreFont();

        if($fill_color){
            $this->pdf->restoreFillColor();
        }

        if($text_color){
            $this->pdf->restoreTextColor();
        }
    }
}
