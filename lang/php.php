<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * PHP Language File.
 */

require_once('abstractLang.php');

class phpSyn extends abstractLang
{
    private $cssCom, $cssTxt, $cssDef, $cssStr;

    /**
     * Constructor
     * Imports The CSS Classes
     */
    public function __construct()
    {
        // CSS Classes
        // Comments
        $this->cssCom = $this->cssComment;
        // Text
        $this->cssTxt = $this->cssKeyword;
        // Definitions
        $this->cssDef = $this->cssFont;
        // Strings
        $this->cssStr = $this->cssHighlight;
    }

    /**
     * Highlights The String
     *
     * @param string    Source
     * @return string   Output
     */
    protected function getCode($source)
    {
        // PHP Syntaxhighlighting
        $source = trim($source);
        // add <?php
        $openercheck = (preg_match("/^(?:<\?php)/", $source) === 1) ? true : false;
        $source = ($openercheck) ? $source : "<?php " . $source;
        // Highlight
        $source = highlight_string($source, true);
        $source = trim($source);
        // remove <code> and <span>
        $source = preg_replace('/^\<code\>\<span style\="color\: #[a-fA-F0-9]{0,6}"\>/', '', $source, 1);
        $source = preg_replace('/\<\/code\>$/', '', $source, 1);
        $source = preg_replace('/\<\/span\>$/', '', $source, 1);
        $source = trim($source);
        // remove <?php
        $source = ($openercheck) ? $source : preg_replace('/^(<span style="color: #[a-fA-F0-9]{0,6}">)(&lt;\?php&nbsp;)(.*?)(<\/span>)/', '$1$3$4', $source);
        $source = str_replace("<br />", "\n", $source);

        // Convert Colors
        $regex = array(
            // Comment
            '#FF8000' => $this->cssCom,
            // Text
            '#007700' => $this->cssTxt,
            // Definitions
            '#0000BB' => $this->cssDef,
            // Strings
            '#DD0000' => $this->cssStr
        );

        //RegEX
        foreach($regex as $key=>$value)
            $source = preg_replace('/(<span[\s]+)(style="color:[\s]+'.$key.'(?:.)*?")(>)/', '$1class="'.$value.'"$3', $source);

        return $source;
    }
}

?>
