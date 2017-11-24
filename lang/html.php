<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * HTML Language File.
 */

require_once('abstractLang.php');

class htmlSyn extends abstractLang
{
    private $cssCom, $cssTags, $cssTag, $cssAttr, $cssAttrS;

    /**
     * Constructor
     * Imports The CSS Classes
     */
    public function __construct()
    {
        // CSS Classes
        // Comments
        $this->cssCom   = $this->cssComment;
        // Opening And Closing Tags
        $this->cssTags  = $this->cssFont;
        // HTML Tag
        $this->cssTag   = $this->cssFont;
        // Attribute
        $this->cssAttr  = $this->cssKeyword;
        // Attribute Value
        $this->cssAttrS = $this->cssHighlight;
    }

    // RexEx Rules
    // PHP
    protected $regexPhp   = '/<(\?|%)(?:php|\s|=){1}(.|\s)*?(?:(?:\1)>|$)/';
    // Comments And Doctype
    protected $regexCom   = '/((?:<!--(?:.|\\s)+?-->)|(?:<![\\w ]+>))/';
    // Tags
    protected $regexTags  = '/(<\\/?[^><]+>)/';
    // Tag Parameter
    protected $regexTag   = '/(<[\\/]?)([\\w]*){1}((?:.|\\s)+?)?([\\/]?>{1})/';
    // All Attributes
    protected $regexAttr  = '/[\\s\\w-]+(?:(?:=(["\']))(?:(?:.)+?)?(?:\\1))?/';
    //Single Attribute
    protected $regexAttrS = '/([\\s\\w-]+[=]?)(["\'](?:(?:.)+)?["\'])?/';

    // Buffer For Comments
    protected $comments = array();

    /**
     * Highlights The String
     *
     * @param string    Source
     * @return string   Output
     */
    protected function getCode($source)
    {
        $regex = array(
            // php
            $this->regexPhp => array($this,'phpcode'),
            // Comments and Doctype
            $this->regexCom => array($this,'comment'),
            // Tags
            $this->regexTags => array($this,'tag')
        );

        // RegEX
		foreach($regex as $key=>$value)
			$source = preg_replace_callback($key, $value, $source);

        // Comments
        $source = str_replace(array_keys($this->comments), array_values($this->comments), $source);

        return $source;
    }

    /**
     * Highlights PHP
     *
     * @param array     RegEx Match
     * @return string   ID
     */
    protected function phpcode($source)
    {
        $id = uniqid("#php", true);

        // Language Name
        $fileName = TUCALSYN_LANGDIR . 'php.php';

        // If Language Not Exists
        if (!is_readable($fileName)) {
            $this->comments[$id] = '<span class="'.$this->cssCom.'">' . htmlspecialchars($source[0]) . '</span>';
            return $id;
        }

        // Load Language
        require_once($fileName);

        // Init The Class
        $classSyn = new phpSyn;
        $output = $classSyn->parse($source[0]);

        $this->comments[$id] = $output;
        return $id;
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
        $this->comments[$id] = '<span class="'.$this->cssCom.'">' . htmlspecialchars($source[0]) . '</span>';
        return $id;
    }

    /**
     * Split And Highlights Tags
     *
     * @param array     RegEx Match
     * @return string   Output
     */
    protected function tag($source)
    {
        if (preg_match($this->regexTag, $source[0], $matches)) {
            if (count($matches) == 5) {
                $opening    = '<span class="'.$this->cssTags.'">' . htmlspecialchars($matches[1]) . '</span>';
                $tag        = '<span class="'.$this->cssTag.'">' . htmlspecialchars($matches[2]) . '</span>';
                $attr       = $this->attributes($matches[3]);
                $closing    = '<span class="'.$this->cssTags.'">' . htmlspecialchars($matches[4]) . '</span>';
            }
            return $opening . $tag . $attr . $closing;
        }
    }

    /**
     * Highlight Attributes
     *
     * @param string    RegEx Match
     * @return string   Output
     */
    protected function attributes($source)
    {
        // Separates Attributes
        return preg_replace_callback(
            $this->regexAttr,
            function ($matches) {
                // Highlight Attributes
                return preg_replace_callback(
                    $this->regexAttrS,
                    function ($value) {
                        // Attribute Name
                        $output = '<span class="'.$this->cssAttr.'">' . htmlspecialchars($value[1]) . '</span>';

                        // Attribute Value
                        if (count($value) == 3)
                            $output .= '<span class="'.$this->cssAttrS.'">' . htmlspecialchars($value[2]) . '</span>';

                        return $output;
                    },
                    $matches[0]
                );
            },
            $source
        );
    }
}

?>
