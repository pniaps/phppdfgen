<?php


namespace ppg\Elements;


use ppg\Element;
use TCPDF_COLORS;

class Rect extends Element
{

    public function render()
    {
        $bounds = $this->getBounds(true, true, true);

        $border = $this->data['border'] ? $this->pdf->getCSSBorderStyle($this->data['border']) : null;

        $fill_color = TCPDF_COLORS::convertHTMLColorToDec($this->data['background-color'],$this->pdf->getAllSpotColors());

        $style = '';

        if ($border) {
            $style .= 'D';
        }
        if ($fill_color !== null) {
            $style .= 'F';
        }


        $this->pdf->saveFillColor();

        if (empty($this->data['border-radius'])) {
            $this->pdf->Rect($bounds['x'], $bounds['y'], $bounds['width'], $bounds['height'], $style, [ 'all' => $border] , $fill_color);
        } else {
            $this->pdf->RoundedRect($bounds['x'], $bounds['y'], $bounds['width'], $bounds['height'], $this->data['border-radius'], '1111', $style, $border, $fill_color);
        }

        $this->pdf->restoreFillColor();
    }
}