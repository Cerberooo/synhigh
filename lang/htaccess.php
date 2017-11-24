<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * HTACCESS Language File
 */

require_once('abstractLang.php');

class htaccessSyn extends abstractLang
{
    private $cssCom, $cssOpCl, $cssSec, $cssRules, $cssQuotes;

    /**
     * Constructor
     * Import The CSS Classes
     */
    public function __construct()
    {
        // CSS Classes
        // Comments
        $this->cssCom       = $this->cssComment;
        // Opening & Closing
        $this->cssOpCl      = $this->cssFont;
        // Value
        $this->cssSec       = $this->cssFont;
        // Rules & Chains
        $this->cssRules     = $this->cssBold;
        // Quotes
        $this->cssQuotes    = $this->cssHighlight;
    }

    // RexEx Rules
    // Comments:            #...
    protected $regexCom     = '/(?<!\\\\)#.+/';
    // Section              <...>
    protected $regexSec     = '/(<\/?)([\w]+)([^>]*)(>)/';
    // Rules                Value
    protected $regexRules   = '/^([\t ]*)([A-Z]{1}\w+)/m';
    // Quotes               "..." '...'
    protected $regexQuotes  = '/(["\'])(?:(?:.)+?)?(?:\1)/';
    // Chains               value value ...
    protected $regexChain   = '/^([\t ]*)([a-z]{1}[\w ]+)/m';


    // Buffer
    protected $buffer       = array();

    /**
     * Highlighting The String
     *
     * @param string    Source
     * @return string   Output
     */
    protected function getCode($source)
    {
        $regex = array(
            // Comments
            $this->regexCom => array($this,'comment'),
            // Quotes
            $this->regexQuotes => array($this,'quotes'),
            // Section
            $this->regexSec => array($this,'section'),
            // Rules
            $this->regexRules => array($this,'rules'),
            // Chains
            $this->regexChain => array($this,'chain')
        );

        // RegEX
        foreach($regex as $key=>$value)
            $source = preg_replace_callback($key, $value, $source);

        // Remove HTML Special Chars
        $source = htmlspecialchars($source);

        // Load From Buffer
        $this->buffer = array_reverse($this->buffer, true);
        $source = str_replace(array_keys($this->buffer), array_values($this->buffer), $source);

        return $source;
    }

    /**
     * Saves The Comments
     *
     * @param array     RegEx Match
     * @return string   ID
     */
    protected function comment($source)
    {
        $id = uniqid("^idc", true);
        $this->buffer[$id] = '<span class="'.$this->cssCom.'">' . htmlspecialchars($source[0]) . '</span>';

        return $id;
    }

    /**
     * Highlighting Quotes
     *
     * @param array     RegEx Match
     * @return string   ID
     */
    protected function quotes($source)
    {
        $id = uniqid("^idq", true);
        $this->buffer[$id] = '<span class="'.$this->cssQuotes.'">' . htmlspecialchars($source[0]) . '</span>';

        return $id;
    }

    /**
     * Highlighting Sections
     *
     * @param array     RegEx Match
     * @return string   ID
     */
    protected function section($source)
    {
        $opening = '<span class="'.$this->cssOpCl.'">' . htmlspecialchars($source[1]) . '</span>';
        $value = '<span class="'.$this->cssSec.'">' . htmlspecialchars($source[2]) . '</span>';
        $closing = '<span class="'.$this->cssOpCl.'">' . htmlspecialchars($source[4]) . '</span>';

        $id = uniqid("^ids", true);
        $this->buffer[$id] = $opening.$value.htmlspecialchars($source[3]).$closing;

        return $id;
    }

    /**
     * Highlighting Rules
     *
     * @param array     RegEx Match
     * @return string   ID
     */
    protected function rules($source)
    {
        $id = uniqid("^idr", true);
        $this->buffer[$id] = $source[1].'<span class="'.$this->cssRules.'">' . htmlspecialchars($source[2]) . '</span>';

        return $id;
    }

    /**
     * Highlighting Chains
     *
     * @param array     RegEx Match
     * @return string   ID
     */
    protected function chain($source)
    {
        $id = uniqid("^idch", true);
        $this->buffer[$id] = '<span class="'.$this->cssRules.'">' . htmlspecialchars($source[0]) . '</span>';

        return $id;
    }

}

?>
