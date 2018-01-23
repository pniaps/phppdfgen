<?php


namespace ppg\Elements;


use ppg\Element;

class Image extends Element
{

    public function render()
    {
        $bounds = $this->getBounds(true, true, false);

        $border = $this->getBorder();

        $this->pdf->Image($this->data['src'], $bounds['x'], $bounds['y'], $bounds['width'], $bounds['height'], '', '', 'N', false, 300, '', false, false, $border, 'CM');

//        $this->pdf->SetY($this->pdf->GetY()+5); //TODO: margin-bottom
    }
}