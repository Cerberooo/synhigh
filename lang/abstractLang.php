<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * Abstract Class For All Language Files
 */

abstract class abstractLang
{
    // Required Functions

    abstract protected function getCode($source);

    // CSS Variables

    // Fonts
    protected $cssFont      = 's';
    // Comments
    protected $cssComment   = 'c';
    // Keywords
    protected $cssKeyword   = 'k';
    // Highlighted
    protected $cssHighlight = 'h';
    // Bold
    protected $cssBold      = 'b';
    // Underline
    protected $cssUnderline = 'u';

    // Common Methods

    /**
     * Gets The Code
     *
     * @param string    Source
     * @return string   Highlighted Source
     */
    public function parse($source) {
        return $this->getCode($source);
    }
}

?>
