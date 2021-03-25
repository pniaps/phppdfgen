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
    private static $version = '1.0';

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
        $this->tcpdflink = false;
        $this->document = $document;

        if(isset($layout['document']['margin']) && !is_array($layout['document']['margin'])){
            //convert '10' => [10, 10, 10, 10]
            $layout['document']['margin']= [
                (float)$layout['document']['margin'],
                (float)$layout['document']['margin'],
                (float)$layout['document']['margin'],
                (float)$layout['document']['margin']
            ];
        }

        $this->SetMargins(
            $layout['document']['margin'][0], //left
            $layout['document']['margin'][1], //top
            $layout['document']['margin'][2] //right
        );
        $this->SetHeaderMargin($layout['document']['margin'][1]); //top
        $this->SetFooterMargin($layout['document']['margin'][3]); //bottom

        $this->SetAutoPageBreak(true, $layout['document']['margin'][3]); //bottom

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
        $old_cwd = getcwd();
        chdir($this->document->getLayoutPath());

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

        $this->SetTopMargin($this->y + (float)($this->layout['header']['margin'] ?? $this->layout['document']['margin'][1]));

        chdir($old_cwd);
    }

    public function Footer()
    {
        $old_cwd = getcwd();
        chdir($this->document->getLayoutPath());

        //restore global padding
        $this->configurePadding();

        if ($this->layout['footer'] && is_array($this->layout['footer']['objects'])) {
            foreach ($this->layout['footer']['objects'] as $data) {
                $object = Element::create($data, $this->document, $this);
                $object->render();
            }
        }

        chdir($old_cwd);
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

    private function getProducer()
    {
        return 'PHPPdfGen v'.static::$version.' ('.TCPDF_STATIC::getTCPDFVersion().')';
    }

    protected function _putinfo() {
        $oid = $this->_newobj();
        $out = '<<';
        // store current isunicode value
        $prev_isunicode = $this->isunicode;
        if ($this->docinfounicode) {
            $this->isunicode = true;
        }
        if (!TCPDF_STATIC::empty_string($this->title)) {
            // The document's title.
            $out .= ' /Title '.$this->_textstring($this->title, $oid);
        }
        if (!TCPDF_STATIC::empty_string($this->author)) {
            // The name of the person who created the document.
            $out .= ' /Author '.$this->_textstring($this->author, $oid);
        }
        if (!TCPDF_STATIC::empty_string($this->subject)) {
            // The subject of the document.
            $out .= ' /Subject '.$this->_textstring($this->subject, $oid);
        }
        if (!TCPDF_STATIC::empty_string($this->keywords)) {
            // Keywords associated with the document.
            $out .= ' /Keywords '.$this->_textstring($this->keywords, $oid);
        }
        if (!TCPDF_STATIC::empty_string($this->creator)) {
            // If the document was converted to PDF from another format, the name of the conforming product that created the original document from which it was converted.
            $out .= ' /Creator '.$this->_textstring($this->creator, $oid);
        }
        // restore previous isunicode value
        $this->isunicode = $prev_isunicode;
        // default producer
        $out .= ' /Producer '.$this->_textstring($this->getProducer(), $oid);
        // The date and time the document was created, in human-readable form
        $out .= ' /CreationDate '.$this->_datestring(0, $this->doc_creation_timestamp);
        // The date and time the document was most recently modified, in human-readable form
        $out .= ' /ModDate '.$this->_datestring(0, $this->doc_modification_timestamp);
        // A name object indicating whether the document has been modified to include trapping information
        $out .= ' /Trapped /False';
        $out .= ' >>';
        $out .= "\n".'endobj';
        $this->_out($out);
        return $oid;
    }

    protected function _putXMP() {
        $oid = $this->_newobj();
        // store current isunicode value
        $prev_isunicode = $this->isunicode;
        $this->isunicode = true;
        $prev_encrypted = $this->encrypted;
        $this->encrypted = false;
        // set XMP data
        $xmp = '<?xpacket begin="'.TCPDF_FONTS::unichr(0xfeff, $this->isunicode).'" id="W5M0MpCehiHzreSzNTczkc9d"?>'."\n";
        $xmp .= '<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">'."\n";
        $xmp .= "\t".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
        $xmp .= "\t\t\t".'<dc:format>application/pdf</dc:format>'."\n";
        $xmp .= "\t\t\t".'<dc:title>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.TCPDF_STATIC::_escapeXML($this->title).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
        $xmp .= "\t\t\t".'</dc:title>'."\n";
        $xmp .= "\t\t\t".'<dc:creator>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li>'.TCPDF_STATIC::_escapeXML($this->author).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t".'</dc:creator>'."\n";
        $xmp .= "\t\t\t".'<dc:description>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.TCPDF_STATIC::_escapeXML($this->subject).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
        $xmp .= "\t\t\t".'</dc:description>'."\n";
        $xmp .= "\t\t\t".'<dc:subject>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li>'.TCPDF_STATIC::_escapeXML($this->keywords).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
        $xmp .= "\t\t\t".'</dc:subject>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        // convert doc creation date format
        $dcdate = TCPDF_STATIC::getFormattedDate($this->doc_creation_timestamp);
        $doccreationdate = substr($dcdate, 0, 4).'-'.substr($dcdate, 4, 2).'-'.substr($dcdate, 6, 2);
        $doccreationdate .= 'T'.substr($dcdate, 8, 2).':'.substr($dcdate, 10, 2).':'.substr($dcdate, 12, 2);
        $doccreationdate .= substr($dcdate, 14, 3).':'.substr($dcdate, 18, 2);
        $doccreationdate = TCPDF_STATIC::_escapeXML($doccreationdate);
        // convert doc modification date format
        $dmdate = TCPDF_STATIC::getFormattedDate($this->doc_modification_timestamp);
        $docmoddate = substr($dmdate, 0, 4).'-'.substr($dmdate, 4, 2).'-'.substr($dmdate, 6, 2);
        $docmoddate .= 'T'.substr($dmdate, 8, 2).':'.substr($dmdate, 10, 2).':'.substr($dmdate, 12, 2);
        $docmoddate .= substr($dmdate, 14, 3).':'.substr($dmdate, 18, 2);
        $docmoddate = TCPDF_STATIC::_escapeXML($docmoddate);
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'."\n";
        $xmp .= "\t\t\t".'<xmp:CreateDate>'.$doccreationdate.'</xmp:CreateDate>'."\n";
        $xmp .= "\t\t\t".'<xmp:CreatorTool>'.$this->creator.'</xmp:CreatorTool>'."\n";
        $xmp .= "\t\t\t".'<xmp:ModifyDate>'.$docmoddate.'</xmp:ModifyDate>'."\n";
        $xmp .= "\t\t\t".'<xmp:MetadataDate>'.$doccreationdate.'</xmp:MetadataDate>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'."\n";
        $xmp .= "\t\t\t".'<pdf:Keywords>'.TCPDF_STATIC::_escapeXML($this->keywords).'</pdf:Keywords>'."\n";
        $xmp .= "\t\t\t".'<pdf:Producer>'.TCPDF_STATIC::_escapeXML($this->getProducer()).'</pdf:Producer>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'."\n";
        $uuid = 'uuid:'.substr($this->file_id, 0, 8).'-'.substr($this->file_id, 8, 4).'-'.substr($this->file_id, 12, 4).'-'.substr($this->file_id, 16, 4).'-'.substr($this->file_id, 20, 12);
        $xmp .= "\t\t\t".'<xmpMM:DocumentID>'.$uuid.'</xmpMM:DocumentID>'."\n";
        $xmp .= "\t\t\t".'<xmpMM:InstanceID>'.$uuid.'</xmpMM:InstanceID>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        if ($this->pdfa_mode) {
            $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">'."\n";
            $xmp .= "\t\t\t".'<pdfaid:part>'.$this->pdfa_version.'</pdfaid:part>'."\n";
            $xmp .= "\t\t\t".'<pdfaid:conformance>B</pdfaid:conformance>'."\n";
            $xmp .= "\t\t".'</rdf:Description>'."\n";
        }
        // XMP extension schemas
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/" xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#" xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'."\n";
        $xmp .= "\t\t\t".'<pdfaExtension:schemas>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/pdf/1.3/</pdfaSchema:namespaceURI>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdf</pdfaSchema:prefix>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Adobe PDF Schema</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/xap/1.0/mm/</pdfaSchema:namespaceURI>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>xmpMM</pdfaSchema:prefix>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>XMP Media Management Schema</pdfaSchema:schema>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>UUID based identifier for specific incarnation of a document</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://www.aiim.org/pdfa/ns/id/</pdfaSchema:namespaceURI>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>PDF/A ID Schema</pdfaSchema:schema>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Part of PDF/A standard</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>part</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Integer</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Amendment of PDF/A standard</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>amd</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Conformance level of PDF/A standard</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>conformance</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
        $xmp .= "\t\t\t".'</pdfaExtension:schemas>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        $xmp .= $this->custom_xmp_rdf;
        $xmp .= "\t".'</rdf:RDF>'."\n";
        $xmp .= $this->custom_xmp;
        $xmp .= '</x:xmpmeta>'."\n";
        $xmp .= '<?xpacket end="w"?>';
        $out = '<< /Type /Metadata /Subtype /XML /Length '.strlen($xmp).' >> stream'."\n".$xmp."\n".'endstream'."\n".'endobj';
        // restore previous isunicode value
        $this->isunicode = $prev_isunicode;
        $this->encrypted = $prev_encrypted;
        $this->_out($out);
        return $oid;
    }
}
