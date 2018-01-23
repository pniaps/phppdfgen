<?php


namespace ppg\Elements;


use InvalidArgumentException;
use ppg\Element;
use TCPDF_COLORS;

class Table extends Element
{
    public function render()
    {
        $this->pdf->SetY($this->pdf->GetY() + (float)$this->data['margin']);

        $columns = $this->data['columns'];
        $numColumns = count($columns);

        $border = $this->getBorder();
        if (isset($this->data['border-color'])) {
            $border['LTRB']['color'] = TCPDF_COLORS::convertHTMLColorToDec($this->data['border-color'], $this->pdf->getAllSpotColors());
        }

        $this->setFont($this->data['font-family'], $this->data['font-style'], $this->data['font-size']);

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

        //calculate height of header
        $height = 0;
        $left = (float)$this->data['left'] ?: $this->pdf->getMargins()['left'];
        foreach ($columns as $index => $column) {
            if($column['width']){
                $left += $column['width'];
            }else{
                $columns[$index]['width'] = $this->pdf->getPageWidth() - $left - ($this->pdf->getMargins()['right']);
                if($columns[$index]['width'] < 0){
                    throw new InvalidArgumentException(__METHOD__ . '(): Column [ '. ($column['text'] ?: $index) .' ] is wider than page ');
                }
            }
            if ($column['text']) {
                $height = max($height, $this->pdf->getStringHeight($columns[$index]['width'], $column['text']));
            }
        }
        if($height){
            foreach ($columns as $index => $column) {
                $this->pdf->MultiCell($column['width'], $height, $column['text'], $border, $column['align'] ?: 'L', false, $index == $numColumns - 1 ? 1 : 0);
            }
        }

        if ($this->data['rows']) {
            foreach ($this->data['rows'] as $row) {
                if (isset($this->data['left'])) {
                    $this->pdf->SetX($this->data['left']);
                }
                $height = 0;
                foreach ($row as $index => &$columnText) {
                    $columnText = preg_replace_callback('/\{([\w-_\.]+)\}/', function(array $matches){
                        return $this->document->getData($matches[1]);
                    }, $columnText);
                    $height = max($height, $this->pdf->getStringHeight($columns[$index]['width'], $columnText));
                    $height = $height + 1 - 1;
                }
                unset($columnText); // rompe la referencia con el último elemento
                foreach ($row as $index => $columnText) {
                    $this->pdf->MultiCell($columns[$index]['width'], $height, $columnText, $border, $columns[$index]['align'] ?: 'L', false, $index == $numColumns - 1 ? 1 : 0);
                }
            }
        }

        $this->restoreFont();
        if ($this->data['padding']) {
            $this->pdf->SetCellPaddings($padding['L'], $padding['T'], $padding['R'], $padding['B']);
        }
    }
}