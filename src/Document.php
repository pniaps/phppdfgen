<?php


namespace ppg;


use ArrayAccess;
use InvalidArgumentException;
use TCPDF_STATIC;

class Document implements ArrayAccess
{
    protected $layout = null;

    /**
     * @var PDF null
     */
    protected $pdf = null;

    protected $data = null;

    protected $saved_y = 0;

    protected $filename = 'document.pdf';

    public function __construct()
    {
    }

    /**
     * @param $layout
     * @return static
     */
    public static function loadLayoutFile($layout)
    {
        $document = new static();
        $document->loadLayout($layout);
        return $document;
    }

    /**
     * @param $layout
     * @return $this
     */
    public function loadLayout($layout)
    {
        if (!file_exists($layout)) {
            throw new InvalidArgumentException('Layout File [ ' . $layout . ' ] Not Found');
        }

        $json = json_decode(file_get_contents($layout), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(__METHOD__ . '(): Layout File [ ' . $layout . ' ] is not valid json');
        }

        $this->layout = $json;

        if(isset($this->layout['header']) && !is_array($this->layout['header'])){
            throw new InvalidArgumentException(__METHOD__ . '(): Layout File [ ' . $layout . ' ] "header" must be array');
        }

        if(isset($this->layout['footer']) && !is_array($this->layout['footer'])){
            throw new InvalidArgumentException(__METHOD__ . '(): Layout File [ ' . $layout . ' ] "footer" must be array');
        }

        return $this;
    }

    /**
     * @return PDF
     */
    public function getPdf()
    {
        if (!$this->pdf) {

            if (!isset(TCPDF_STATIC::$page_formats[$this->layout['document']['page-format']])) {
                throw new InvalidArgumentException(__METHOD__ . '(): Invalid page-format [ ' . $this->layout['document']['page-format'] . ' ]');
            }

            $this->pdf = new PDF(
                $this->layout['document']['orientation'] ?: 'portrait',
                $this->layout['document']['unit'] ?: 'mm', //TODO: Document
                $this->layout['document']['page-format'] ?: 'A4'
            );

            $this->pdf->configure($this->layout, $this);
        }
        return $this->pdf;
    }

    public function assign()
    {
        return $this;
    }

    public function render()
    {
        $this->getPdf()->AddPage();
        if($this->layout['body'] && is_array($this->layout['body']['objects'])){
            foreach ($this->layout['body']['objects'] as $data) {
                if($data=='saveY'){
                    $this->saved_y = $this->pdf->GetY();
                }else if($data=='restoreY'){
                    $this->pdf->SetY($this->saved_y);
                }else {
                    $object = Element::create($data, $this, $this->getPdf());
                    $object->render($this);
                }
            }
        }
        return $this;
    }

    public function setFileName($name)
    {
        $this->filename = $name;

        return $this;
    }

    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * return the document as a string.
     *
     * @return string
     */
    public function string()
    {
        return $this->getPdf()->Output('', 'S');
    }

    /**
     * send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.
     *
     * @param string $name The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
     */
    public function inline($name = null)
    {
        if (!$name){
            $name = $this->filename;
        }
        $this->getPdf()->Output($name, 'I');
        die();
    }

    /**
     * send to the browser and force a file download with the name given by name
     *
     * @param string $name The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
     */
    public function download($name = null)
    {
        if (!$name){
            $name = $this->filename;
        }
        $this->getPdf()->Output($name, 'D');
        die();
    }

    /**
     * save to a local server file with the name given by name
     *
     * @param string $name The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
     */
    public function save($path = null)
    {
        if($path && is_dir($path)){
            $path .= DIRECTORY_SEPARATOR . $this->filename;
        }
        $this->getPdf()->Output($path, 'F');
    }


    /**
     * Determine if a piece of data is assigned.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->layout);
    }

    /**
     * Get a piece of data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        return $this->layout[$key];
    }

    /**
     * Assigns a piece of data.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->layout[$key] = $value;
    }

    /**
     * Removes a piece of data.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->layout[$key]);
    }

    /**
     * Get a piece of data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->layout[$key];
    }

    /**
     * Assigns a piece of data.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->layout[$key] = $value;
    }

    /**
     * Check if a piece of data is assigned.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->layout[$key]);
    }

    /**
     * Removes a piece of data.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->layout[$key]);
    }

    /**
     * Get / set the specified data.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function data($key = null, $value = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                $this->setData($innerKey, $innerValue);
            }
            return null;
        }else if(!is_null($value)){
            $this->setData($key, $value);
            return null;
        }

        return $this->getData($key);
    }

    /**
     * Sets data value using "dot notation".
     *
     * @param   array|ArrayAccess $array Array you want to modify
     * @param   string $path Array path
     * @param   mixed $value Value to set
     */
    public function setData($path, $value = null)
    {
        if(is_null($value)){
            $this->data = $path;
        }else{
            $array = &$this->data;

            $segments = explode('.', $path);

            while (count($segments) > 1) {

                $segment = array_shift($segments);

                if (!isset($array[$segment]) || !(is_array($array[$segment]) || $array[$segment] instanceof ArrayAccess)) {
                    $array[$segment] = [];
                }

                $array =& $array[$segment];
            }

            $array[array_shift($segments)] = $value;
        }

        return $this;
    }

    /**
     * Returns data value using "dot notation".
     *
     * @param   array|ArrayAccess $array Array we're going to search
     * @param   string $path Array path
     * @param   mixed $default Default return value
     * @return  mixed
     */
    public function getData($path, $default = null)
    {
        $array = $this->data;

        $segments = explode('.', $path);

        foreach ($segments as $segment) {

            if (!(is_array($array) || $array instanceof ArrayAccess) || !isset($array[$segment])) {
                return is_callable($default) ? $default() : $default;
            }

            $array = $array[$segment];
        }
        return $array;
    }
}