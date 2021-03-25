<?php


namespace ppg;


use TCPDF;
use TCPDF_COLORS;
use TCPDF_FONTS;
use TCPDF_STATIC;

/**
 * Class PDF
 *
 * @package ppg
 *
 * @method getCSSBorderStyle($cssborder) Returns the border style array from CSS border properties
 * @method getCSSBorderWidth($width) Returns the border width from CSS property
 * @method getCSSBorderDashStyle($style) Returns the border dash style from CSS property
 */
class PDF extends TCPDF
{
    public $layout = null;

    private $old_FillColor = '0 g';
    private $old_bgcolor = array('R' => 255, 'G' => 255, 'B' => 255);

    private $old_TextColor = '0 g';
    private $old_fgcolor = array('R' => 0, 'G' => 0, 'B' => 0);

    private $old_ColorFlag = false;

    /**
     * @var Document
     */
    protected $document;

    public function configure($layout, Document $document)
    {
        $this->document = $document;

        $this->SetMargins(
            $layout['document']['margin'][0], //left
            $layout['document']['margin'][1], //top
            $layout['document']['margin'][2] //right
        );
        $this->SetAutoPageBreak(true, $layout['document']['margin'][4]);

        $this->layout = $layout;

        $this->configurePadding();

        $this->FontFamily = $layout['document']['font-family'] ?: $this->FontFamily;
        $this->FontStyle = $layout['document']['font-style'] ?: $this->FontStyle;
        $this->FontSizePt = $layout['document']['font-size'] ?: $this->FontSizePt;

        $this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
        $this->setHeaderFont([
            $layout['header']['font-family'] ?: $this->FontFamily,
            $layout['header']['font-style'] ?: $this->FontStyle,
            $layout['header']['font-size'] ?: $this->FontSizePt
        ]);
        $this->setFooterFont([
            $layout['footer']['font-family'] ?: $this->FontFamily,
            $layout['footer']['font-style'] ?: $this->FontStyle,
            $layout['footer']['font-size'] ?: $this->FontSizePt
        ]);
    }

    private function configurePadding()
    {
        if (is_array($this->layout['document']['padding'])) {
            $this->setCellPaddings(
                $this->layout['document']['padding'][0], //left
                $this->layout['document']['padding'][1], //top
                $this->layout['document']['padding'][2], //right
                $this->layout['document']['padding'][3] //bottom
            );
        } else if ($this->layout['document']['padding']) {
            $this->SetCellPadding($this->layout['document']['padding']);
        }
    }

    public function Header()
    {
        //restore global padding
        $this->configurePadding();

        if ($this->layout['header'] && is_array($this->layout['header']['objects'])) {
            $top = $this->y;
            foreach ($this->layout['header']['objects'] as $data) {
                $object = Element::create($data, $this->document, $this);
                $object->render();
                $top = max($this->y, $top);
            }
            $this->y = $top;
        }

        $this->SetTopMargin($this->y + (float)$this->layout['header']['margin']);
    }

    public function Footer()
    {
        //restore global padding
        $this->configurePadding();

        if ($this->layout['footer'] && is_array($this->layout['footer']['objects'])) {
            foreach ($this->layout['footer']['objects'] as $data) {
                $object = Element::create($data, $this->document, $this);
                $object->render();
            }
        }
    }

    public function saveFillColor()
    {
        $this->old_bgcolor = $this->bgcolor;
    }

    public function restoreFillColor()
    {
        $this->SetFillColorArray($this->old_bgcolor);
    }

    public function saveTextColor()
    {
        $this->old_fgcolor = $this->fgcolor;
    }

    public function restoreTextColor()
    {
        $this->SetTextColorArray($this->old_fgcolor);
    }

    /**
     * Call protected methods from outside
     *
     * @param       $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->{$name}(...$arguments);
    }

    public function checkFontExists($font)
    {
        return array_key_exists($font, $this->CoreFonts);
    }

    /**
     * Return the imagen dimensions in page unit
     * @param $img
     * @return array
     */
    public function imageDimensions($img)
    {
        $this->Image($img, 0, 0, 0, 0, '', '', '', false, 300, '', false, false, 0, false,true);
        return [$this->getImageRBX(), $this->getImageRBY()];
    }

    public function convertHTMLColorToDec($hcolor, $default = false)
    {
        return TCPDF_COLORS::convertHTMLColorToDec($hcolor,$this->spot_colors, $default);
    }
}
