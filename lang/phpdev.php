<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * PHPdev Language File.
 */

require_once('abstractLang.php');

class phpdevSyn extends abstractLang
{
    private $cssCom, $cssNS, $cssRef, $cssCN;

    /**
     * Constructor
     * Imports The CSS Classes
     */
    public function __construct()
    {
        // CSS Classes
        // Comments
        $this->cssCom = $this->cssComment;
        // namespace, use, class and other keywords
        $this->cssNS  = $this->cssKeyword;
        // (stuff-> || stuff::) and variables
        $this->cssRef = $this->cssKeyword;
        // Class Names
        $this->cssCN  = $this->cssFont;
    }

    // RexEx Rules
    // opening and closing tags
    protected $regexTags  = '/(<(\\?|%){1}(?:php|=)?)(?:.|\\s)+?((?:\\2)>)|(<(?:\\?|%){1}(?:php|=)?)/';
    // comments
    protected $regexCom   = '/(?:\\/\\*(?:.|\\s)+?\\*\\/)|(?:\\/\\/(?:.)+)|(?:#(?:.)+)/';
    // namespace
    protected $regexNS    = '/(namespace|use)([\\s]+[\\w\\\\]+)/i';
    // functions: [stuff::][$]stuff([content])
    protected $regexFunc  = '/([\w]+::)?([$\w]+)[\s]*\(((?:[^()]|(?R))*)\)/';
    // class
    protected $regexCN    = '/(class)([\s]+[\w\\\\]+(?2)?)([\s]+{)/';
    // variables
    protected $regexVar   = '/((?:[\w]+::)?\$[\w\d]+[\s]*(?:-&gt;[\w]*)?)([+-]?=)?/';
    // buffer for comments
    protected $comments = array();

    /**
     * Highlights The String
     *
     * @param string    Source
     * @return string   Output
     */
    protected function getCode($source)
    {
        $source = htmlspecialchars($source);

        $regex = array(
            // opening and closing tags
            $this->regexTags => array($this,'tag'),
            // comments
            $this->regexCom => array($this,'comment'),
            // namespace
            $this->regexNS => array($this,'names'),
            // functions
            $this->regexFunc => array($this,'func'),
            // class
            $this->regexCN => array($this,'classmatch'),
            // variables
            $this->regexVar => array($this,'varmatch')
        );

        // RegEX
        foreach($regex as $key=>$value)
            $source = preg_replace_callback($key, $value, $source);

        // Comments
        $source = str_replace(array_keys($this->comments), array_values($this->comments), $source);

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
        $id = uniqid("#", true);
        $this->comments[$id] = '<span class="'.$this->cssCom.'">' . $source[0] . '</span>';
        return $id;
    }

    /**
     * Highlights namespace & use
     *
     * @param array     RegEx Match
     * @return string   Output
     */
    protected function names($source)
    {
        return '<span class="'.$this->cssNS.'">'.$source[1].'</span>'.$source[2];
    }

    /**
     * Highlights Functions
     *
     * @param array     RegEx Match
     * @return string   Output
     */
    protected function func($source)
    {
        //print_r($source);
        //echo "<br><br>";

        $ref = ($source[1] !== '') ? '<span class="'.$this->cssRef.'">'.$source[1].'</span>' : '';
        $name = '<span class="'.$this->cssNS.'">'.$source[2].'</span>';

        $content = '';
        if($source[3] !== '') {
            $regex = array(
                // functions
                $this->regexFunc => array($this,'func')
                // TODO: Content
            );

            foreach($regex as $key=>$value)
                $content = preg_replace_callback($key, $value, $source[3]);
        }

        return $ref.$name.'('.$content.')';
    }

    /**
     * Highlights Classes
     *
     * @param array     RegEx Match
     * @return string   Output
     */
    protected function classmatch($source)
    {
        $init = '<span class="'.$this->cssNS.'">'.$source[1].'</span>';

        $mid = $source[2];
        if($source[2] !== '') {
            if(preg_match_all('/[\w\\\\]+\b(?<!\bextends|implements)/',$mid,$matches)) {
                foreach ($matches[0] as $value) {
                    $mid = str_replace($value,'<span class="'.$this->cssCN.'">'.$value.'</span>',$mid);
                }
            }
            if(preg_match_all('/extends|implements/',$mid,$matches)) {
                foreach ($matches[0] as $value) {
                    $mid = str_replace($value,'<span class="'.$this->cssNS.'">'.$value.'</span>',$mid);
                }
            }
        }

        return $init.$mid.$source[3];
    }

    /**
     * Highlights Variables
     *
     * @param array     RegEx Match
     * @return string   Output
     */
    protected function varmatch($source)
    {
        $var = '<span class="'.$this->cssRef.'">'.$source[1].'</span>';

        return $var.$source[2];
    }
}

?>
