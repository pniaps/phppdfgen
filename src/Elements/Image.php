<?php


namespace ppg\Elements;


use ppg\Element;

class Image extends Element
{

    public function render()
    {
        $bounds = $this->getBounds(false, false, false);

        $border = $this->getBorder();

        if($this->data['center']){
            list($imgwidth, $imgheight) = $this->pdf->imageDimensions($this->data['src']);
            if($imgwidth < $bounds['width']){
                $bounds['x'] += (($bounds['width'] - $imgwidth) / 2);
                $bounds['width'] = $imgwidth;
            }
        }

        if(!$bounds['width'] && $bounds['height']){
            $this->pdf->Image($this->data['src'], $bounds['x'], $bounds['y'], $bounds['width'], $bounds['height'], '', '', 'N', false, 300, '', false, false, 0, false, 'CM');
            $bounds['x'] += (($bounds['width'] + $bounds['x'] - $this->pdf->getImageRBX()) / 2);
        }

        $this->pdf->Image($this->data['src'], $bounds['x'], $bounds['y'], $bounds['width'], $bounds['height'], '', '', 'N', false, 300, '', false, false, $border, 'CM');

//        $this->pdf->SetY($this->pdf->GetY()+5); //TODO: margin-bottom
    }
}
