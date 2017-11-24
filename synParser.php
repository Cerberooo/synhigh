<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * Main Class.
 */

// Root Directory
if (!defined('SYNHIGH_ROOT'))
    define('SYNHIGH_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
// Language File Directory
if (!defined('SYNHIGH_LANGDIR'))
    define('SYNHIGH_LANGDIR', SYNHIGH_ROOT . 'lang' . DIRECTORY_SEPARATOR);

class synhigh
{

    // Source
    private $source = '';
    // Language
    private $lang = '';
    // Line Numbers
    private $lineNumbers = false;
    // Line Begin
    private $lineBegin = 0;
    // Line End
    private $lineEnd = 0;
    // Language File Directory
    private $langPath = SYNHIGH_LANGDIR;
    // Inline Output
    private $withinLine = false;

    /**
     * Constructor
     *
     * @param string    Source
     * @param string    Language
     * @param bool      Optional: Line Numbers
     * @param integer   Optional: Line Where The Source Should Begin
     * @param integer   Optional: Line Where The Source Should End
     */
    function __construct($source = '', $lang = '', $lineNumbers = false, $lineBegin = 0, $lineEnd = 0)
    {
        $this->setSource($source);
        $this->setLang($lang);
        $this->setLineNumbers($lineNumbers);
        $this->setLineBegin($lineBegin);
        $this->setLineEnd($lineEnd);
    }

    /*
     * SETTER
     */

    /**
     * Sets The Source
     *
     * @param string    Source
     * @return void
     */
    public function setSource($source)
    {
        if (is_string($source) && ($source !== '')) {
            $this->source = $this->normalizeNewlines($source);
        }
    }

    /**
    * Sets The Language
    *
    * @param string    Language
    */
    public function setLang($lang)
    {
        if (is_string($lang) && ($lang !== '')) {
            // Präpariert den Sprachnamen
            $lang = preg_replace('/[^\w\-]/', '', $lang);
            $lang = strtolower($lang);

            // Setzt den Sprachnamen
            $fileName = $this->langPath . $lang . '.php';
            $lang = ucfirst($lang);
            $this->lang = $lang . 'Syn';

            if (!is_readable($fileName)) {
                throw new \Exception('Unable to load the language file!');
            }

            require_once($fileName);
        }
    }

    /**
    * Activates Line Numbers
    *
    * @param bool
    * @return void
    */
    public function setLineNumbers($lineNumbers)
    {
        if (is_bool($lineNumbers)) {
            $this->lineNumbers = $lineNumbers;
        }
    }

    /**
     * Set Line Begin
     *
     * @param integer
     * @return void
     */
    public function setLineBegin($lineBegin)
    {
        if (is_int($lineBegin)) {
            $this->lineBegin = $lineBegin;
        }
    }

    /**
     * Set End Line
     *
     * @param integer
     * @return void
     */
    public function setLineEnd($lineEnd)
    {
        if (is_int($lineEnd)) {
            $this->lineEnd = $lineEnd;
        }
    }

    /**
     * Set Within Line
     *
     * @param bool
     * @return void
     */
    public function setWithinLine($withinLine)
    {
        if (is_bool($withinLine)) {
            $this->withinLine = $withinLine;
        }
    }

    /*
     * FUNCTIONS
     */

    /**
     * Converts All Line Breaks To UNIX Format
     *
     * @param string    Source
     * @return string   Source
     */
    function normalizeNewlines($source)
    {
        $source = str_replace("\r\n", "\n", $source);
        $source = str_replace("\r", "\n", $source);

        return $source;
    }

    /**
     * Limits The Source
     *
     * @param integer   Line Where The Source Begins
     * @param integer   Line Where The Source Ends
     * @return void
     */
    function limitSource()
    {
        // Split
        $sourcetemp = explode("\n", $this->source);

        if ($this->lineBegin <= 0 || $this->lineBegin > count($sourcetemp)) {
            $this->lineBegin = 0;
        } else {
            $this->lineBegin -= 1; // Arrays starts with 0
        }

        // Limit
        if ($this->lineBegin > $this->lineEnd || $this->lineEnd > count($sourcetemp)) {
            $sourcetemp = array_slice($sourcetemp, $this->lineBegin);
        } else {
            $sourcetemp = array_slice($sourcetemp, $this->lineBegin, ($this->lineEnd - 1) - (count($sourcetemp) - 1));
        }

        // Melting
        $this->source = implode("\n", $sourcetemp);
    }

    /**
     * Highlights The Source
     *
     * @return string
     */
    function parseCode()
    {
        // Limit
        if ($this->lineEnd || $this->lineBegin) {
            $this->limitSource();
        }

        // Language
        $classSyn = new $this->lang();

        // Create Output
        $output = $classSyn->parse($this->source);

        // Within Line
        if ($this->withinLine) {
            return '<code class="wl">' . $output . '</code>';
        }
        // Linenumbers
        if (!$this->lineNumbers) {
            return '<pre><code>' . $output . '</code></pre>';
        } else {
            $linenb = '';
            for ($i = substr_count($output, "\n") + 1; $i > 0; $i--) {
                $linenb .= "<span></span>";
            }
            return '<pre class="ln"><code>' . $output . '<span class="ln">' . $linenb . '</span></code></pre>';
        }
    }
}

?>
