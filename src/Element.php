<?php


namespace ppg;


use InvalidArgumentException;
use TCPDF_COLORS;

abstract class Element
{
    protected $data = null;

    /**
     * @var Document
     */
    protected $document;

    /**
     * @var PDF
     */
    protected $pdf;

    public function __construct($data, Document $document, PDF $pdf)
    {
        $this->data = $data;
        $this->document = $document;
        $this->pdf = $pdf;
    }

    /**
     * @param          $data
     * @param Document $document
     * @param PDF      $pdf
     * @return static
     */
    public static function create($data, Document $document, PDF $pdf)
    {
        $object = '\\ppg\\Elements\\' . ucfirst(strtolower($data['type']));
        if (!class_exists($object)) {
            throw new InvalidArgumentException(__METHOD__ . '(): Invalid object type [ ' . $data['type'] . ' ]');
        }

        return new $object($data, $document, $pdf);
    }

    abstract public function render();

    public function getBounds($leftTopRequired, $widthRequired, $heightRequired)
    {
        if ($leftTopRequired && !isset($this->data['left'], $this->data['top'])) {
            throw new InvalidArgumentException(__METHOD__ . '(): missing required value [ left ] or [ top ]');
        }
        if ($widthRequired && !isset($this->data['width']) && !isset($this->data['right'])) {
            throw new InvalidArgumentException(__METHOD__ . '(): missing required value [ width ] or [ right ]');
        }
        if ($heightRequired && !isset($this->data['height']) && !isset($this->data['bottom'])) {
            throw new InvalidArgumentException(__METHOD__ . '(): missing required value [ height ] or [ bottom ]');
        }
        $bounds = [
            'x' => $this->data['left'],
            'y' => $this->data['top'],
            'width' => $this->data['width'],
            'height' => $this->data['height']
        ];

        if (is_null($bounds['width']) && !is_null($this->data['right']) && !is_null($this->data['left'])) {
            $bounds['width'] = $this->data['right'] - $this->data['left'];
        }
        if (is_null($bounds['x']) && !is_null($this->data['right']) && !is_null($this->data['width'])) {
            $bounds['x'] = $this->data['right'] - $this->data['width'];
        }
        if (is_null($bounds['height']) && !is_null($this->data['bottom'])) {
            $bounds['height'] = $this->data['bottom'] - $this->data['top'];
        }

        return $bounds;
    }

    /**
     * @param $attribute
     * @return array
     */
//    public function getColor($attribute)
//    {
//        $colorhex = $this->data[$attribute];
//        if (!$colorhex) {
//            return null;
//        }
//        if (preg_match('/^#[0-9a-fA-F]{6}$/', $colorhex)) {
//            return array_map('hexdec', sscanf($colorhex, '#%02x%02x%02x'));
//        }
//        if (preg_match('/^#[0-9a-fA-F]{3}$/u', $colorhex)) {
//            return array_map('hexdec', sscanf($colorhex, '#%01x%01x%01x'));
//        }
//        throw new InvalidArgumentException(__METHOD__ . '(): Invalid color value [ ' . $colorhex . ' ] for attribute [ ' . $attribute . ' ]');
//    }

    public function getBorder()
    {
        $border = [];
//        if (isset($this->data['border'])) {
//            $border = $this->pdf->getCSSBorderStyle($this->data['border']);
//        }
//        if (isset($this->data['border-width'])) {
//            $border['width'] = $this->pdf->getCSSBorderWidth($this->data['border-width']);
//        }
//        if (isset($this->data['border-style'])) {
//            $border['cap'] = 'square';
//            $border['join'] = 'miter';
//            $border['dash'] = $this->pdf->getCSSBorderDashStyle($this->data['border-style']);
//            if ($border['dash'] < 0) {
//                return array();
//            }
//        }
//        if (isset($this->data['border-color'])) {
//            $border['color'] = TCPDF_COLORS::convertHTMLColorToDec($this->data['border-color'], $this->pdf->getAllSpotColors());
//        }
//
//        return $border;

        if (isset($this->data['border'])) {
            $borderstyle = $this->pdf->getCSSBorderStyle($this->data['border']);
            if (!empty($borderstyle)) {
                $border['LTRB'] = $borderstyle;
            }
        }
        if (isset($this->data['border-color'])) {
            $brd_colors = preg_split('/[\s]+/', trim($this->data['border-color']));
            $border['L']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[3] ?: $brd_colors[0], $this->pdf->getAllSpotColors());
            $border['R']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[1] ?: $brd_colors[0], $this->pdf->getAllSpotColors());
            $border['T']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[0], $this->pdf->getAllSpotColors());
            $border['B']['color'] = TCPDF_COLORS::convertHTMLColorToDec($brd_colors[2] ?: $brd_colors[0], $this->pdf->getAllSpotColors());
        }
        if (isset($this->data['border-width'])) {
            $brd_widths = preg_split('/[\s]+/', trim($this->data['border-width']));
            $border['L']['width'] = $this->pdf->getCSSBorderWidth($brd_widths[3] ?: $brd_widths[0]);
            $border['R']['width'] = $this->pdf->getCSSBorderWidth($brd_widths[1] ?: $brd_widths[0]);
            $border['T']['width'] = $this->pdf->getCSSBorderWidth($brd_widths[0]);
            $border['B']['width'] = $this->pdf->getCSSBorderWidth($brd_widths[2] ?: $brd_widths[0]);
        }
        if (isset($this->data['border-style'])) {
            $brd_styles = preg_split('/[\s]+/', trim($this->data['border-style']));
            if (isset($brd_styles[3]) AND ($brd_styles[3]!='none')) {
                $border['L']['cap'] = 'square';
                $border['L']['join'] = 'miter';
                $border['L']['dash'] = $this->pdf->getCSSBorderDashStyle($brd_styles[3]);
                if ($border['L']['dash'] < 0) {
                    $border['L'] = array();
                }
            }
            if (isset($brd_styles[1])) {
                $border['R']['cap'] = 'square';
                $border['R']['join'] = 'miter';
                $border['R']['dash'] = $this->pdf->getCSSBorderDashStyle($brd_styles[1]);
                if ($border['R']['dash'] < 0) {
                    $border['R'] = array();
                }
            }
            if (isset($brd_styles[0])) {
                $border['T']['cap'] = 'square';
                $border['T']['join'] = 'miter';
                $border['T']['dash'] = $this->pdf->getCSSBorderDashStyle($brd_styles[0]);
                if ($border['T']['dash'] < 0) {
                    $border['T'] = array();
                }
            }
            if (isset($brd_styles[2])) {
                $border['B']['cap'] = 'square';
                $border['B']['join'] = 'miter';
                $border['B']['dash'] = $this->pdf->getCSSBorderDashStyle($brd_styles[2]);
                if ($border['B']['dash'] < 0) {
                    $border['B'] = array();
                }
            }
        }
        $cellside = array('L' => 'left', 'R' => 'right', 'T' => 'top', 'B' => 'bottom');
        foreach ($cellside as $bsk => $bsv) {
            if (isset($this->data['border-'.$bsv])) {
                $borderstyle = $this->pdf->getCSSBorderStyle($this->data['border-'.$bsv]);
                if (!empty($borderstyle)) {
                    $border[$bsk] = $borderstyle;
                }
            }
            if (isset($this->data['border-'.$bsv.'-color'])) {
                $border[$bsk]['color'] = TCPDF_COLORS::convertHTMLColorToDec($this->data['border-'.$bsv.'-color'], $this->pdf->getAllSpotColors());
            }
            if (isset($this->data['border-'.$bsv.'-width'])) {
                $border[$bsk]['width'] = $this->pdf->getCSSBorderWidth($this->data['border-'.$bsv.'-width']);
            }
            if (isset($this->data['border-'.$bsv.'-style'])) {
                $border[$bsk]['dash'] = $this->pdf->getCSSBorderDashStyle($this->data['border-'.$bsv.'-style']);
                if ($border[$bsk]['dash'] < 0) {
                    $border[$bsk] = array();
                }
            }
        }
        return $border;
    }

    public function setFont($family, $style, $size)
    {
        if ($family && !$this->pdf->checkFontExists($family)) {
            throw new InvalidArgumentException(__METHOD__ . '(): Invalid font family [ ' . $family . ' ]');
        }

        $this->old_family = $this->pdf->getFontFamily();
        $this->old_style = $this->pdf->getFontStyle();
        $this->old_size = $this->pdf->getFontSizePt();
        $this->pdf->SetFont($family, $style, $size);
    }

    public function restoreFont()
    {
        $this->pdf->SetFont($this->old_family, $this->old_style, $this->old_size);
    }
}
