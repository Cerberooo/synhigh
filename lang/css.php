<?php

/**
 * SynHigh - Syntax Highlight Module
 *
 * CSS Language File
 */

require_once('abstractLang.php');

class cssSyn extends abstractLang
{
    private $cssCom, $cssID, $cssClass, $cssTag, $cssPsd, $cssSAttr, $cssSAt;
    private $cssAttr, $cssAttrI, $cssQuo, $cssHC, $cssFnc, $cssUnts, $cssImp;
    private $cssAt;

    /**
     * Constructor
     * Imports The CSS Classes
     */
    public function __construct()
    {
        // CSS Classes
        // Comments
        $this->cssCom       = $this->cssComment;
        // ID:                  #id
        $this->cssID        = $this->cssHighlight;
        // Class:               .class
        $this->cssClass     = $this->cssKeyword;
        // HTML Tags:           hmtl, body, ...
        $this->cssTag       = $this->cssFont;
        // Pseudo Selectors     :hover
        $this->cssPsd       = $this->cssHighlight;
        // Attribute Selectors  [href="..."]
        $this->cssSAttr     = $this->cssFont;
        // @ Selectors          @media (...)
        $this->cssSAt       = $this->cssBold;

        // Properties:          Property: Value;
        $this->cssAttr      = '';
        // Properties           Property
        $this->cssAttrI     = $this->cssBold;
        // Quotes               "Value"
        $this->cssQuo       = $this->cssHighlight;
        // HEX Colors:          #000000, #000
        $this->cssHC        = $this->cssHighlight;
        // Functions Etc.       name(...)
        $this->cssFnc       = $this->cssBold;
        // Numbers And Units    1px
        $this->cssUnts      = $this->cssBold;
        // !important
        $this->cssImp       = $this->cssBold;
        // @-Rules
        $this->cssAt        = $this->cssKeyword;
    }


    // RexEx Rules
    // Comments:          /* */
    protected $regexCom     = '/\/\*(?:.|\s)+?\*\//';

    // Style:               Selector { Property [, Selector { Property, [, Property]}]}
    protected $regexStyle   = '/([^{}\n,]+(?:,[\s]*.+)*)([\s]{)((?:[^{}]|(?R))+?)?(})/';
    // ID:                  #id
    protected $regexID      = '/#[\w-]+(?![\w# ]*")/';
    // Class:               .class
    protected $regexClass   = '/\.[\w-]+(?![\w# ]*")/';
    // HTML Tags:           html, body, ...
    protected $regexTag     = '/(?:^[a-z-]+|(?<=[\s,])[a-z-]+)/';
    // Pseudo Selectors     :hover
    protected $regexPsd     = '/[:]+[\w-]+/';
    // Attribute Selectors  [href=...]
    protected $regexSAttr   = '/(\[)(\w+)([~|^$*=]*)("?[\w.-]*"?)(\])/';
    // @ Selectors          @media (...)
    protected $regexSAt     = '/(@[\w-]+\s*)(?:(\()(.+?)(\))|(\w+))?/';

    // Properties:          Property: Value;
    protected $regexAttr    =  '/(?<=[\s;{])([\w-]+)([\s]*:[\s]*)(.+?)(?=;|\^id|\s*})/';
    // Quotes               "Value"
    protected $regexQuo     = '/(["\']).*?(\1)/';
    // HEX Colors:          #000000, #000
    protected $regexHC      = '/^#[\w]{0,6}/';
    // Functions Etc.       name(...)
    protected $regexFnc     = '/^([\w\-]+)(\(.+?\))/';
    // Numbers and units    1px
    protected $regexUnts    = '/([\d.]*[\d]+)([a-z%]+)/';
    // !important
    protected $regexImp     = '/^!important/';

    // @-Rules              @import ...
    protected $regexAt      = '/(@[\w\-]+)(.+?)(;)/';

    // Buffer For Comments
    protected $comments = array();

    /**
     * Highlighting The String
     *
     * @param string    Source
     * @return string   Output
     */
    protected function getCode($source)
    {
        // Remove HTML Specialchars
        $source = htmlspecialchars($source, ENT_NOQUOTES);

        $regex = array(
            // Comments
            $this->regexCom => array($this,'comment'),
            // Style
            $this->regexStyle => array($this,'style'),
            // Properties
            $this->regexAttr => array($this,'attr'),
            // @-Rules
            $this->regexAt => array($this,'atrule')
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
        $id = uniqid("^id", true);
        $this->comments[$id] = '<span class="'.$this->cssCom.'">' . htmlspecialchars($source[0]) . '</span>';

        return $id;
    }

    /**
     * Highlights Style: selector { content }
     *
     * @param array     RegEx Match
     * @return string   Output
     */
    protected function style($source)
    {
        // Selectors
        $ident = $source[1];

        // Lines With @
        if(preg_match("/^@.+/", $ident)) {
            preg_match($this->regexSAt, $ident, $matches);

            // @value
            $pre = '<span class="'.$this->cssSAt.'">'.$matches[1].'</span>';
            $ident = $pre;
            $mid = '';
            if(count($matches) == 5) {
                // { Property : Value }
                $mid = $matches[2].preg_replace_callback('/([\w-]+)([\s]*:[\s]*)([^;\n]*)/', array($this,'attr'), $matches[3]).$matches[4];
            }
            if (count($matches) == 6) {
                // [@value] name
                $mid = '<span class="'.$this->cssSAt.'">'.$matches[5].'</span>';
            }

            $ident .= $mid;
        }
        // Split Selectors
        else {
            $ident = preg_replace_callback('/[\w.#"\'\[\]\-=~|^$*:;()]+/', array($this,'ident'), $ident);
        }

        // Nesting: [ Selector { ] Selector { Property : Value ; } [ } ]
        $content = preg_replace_callback($this->regexStyle, array($this,'style'), $source[3]);

        return $ident.$source[2].$content.$source[4];
    }

    /**
    * Highlights Selectors
    *
    * @param array     RegEx Match
    * @return string   Output
    */
    protected function ident($source)
    {
        // Check if >
        if ($source[0] == 'gt;')
            return 'gt;';
        // Check if <
        if ($source[0] == 'lt;')
            return 'lt;';

        $regex = array(
            // HTML Tags
            $this->regexTag => '<span class="'.$this->cssTag.'">$0</span>',
            // ID
            $this->regexID => '<span class="'.$this->cssID.'">$0</span>',
            // Class
            $this->regexClass => '<span class="'.$this->cssClass.'">$0</span>',
            // Pseudo Stuff
            $this->regexPsd => '<span class="'.$this->cssPsd.'">$0</span>',
            // Attribute
            $this->regexSAttr => '$1<span class="'.$this->cssSAttr.'">$2</span>$3<span class="'.$this->cssSAttr.'">$4</span>$5',
        );

        foreach($regex as $key=>$value)
            $source[0] = preg_replace($key, $value, $source[0]);

        return $source[0];
    }

    /**
    * Highlights Properties
    *
    * @param array      RegEx Match
    * @return string    Output
    */
    protected function attr($source)
    {
        // Property
        $attr = '<span class="'.$this->cssAttrI.'">'.$source[1].'</span>';

        // Split Values
        $valuet = preg_replace_callback('/(?:[\w\-]+)?(["\']|\().+?(?:\1|\))|(?:[#\w\-.!]+)/', array($this,'value'), $source[3]);

        // Style
        if ($this->cssAttr)
            $valuet = '<span class="'.$this->cssAttr.'">'.$valuet.'</span>';

        return $attr.$source[2].$valuet.$source[4];
    }

    /**
    * Highlights Values
    *
    * @param array      RegEx Match
    * @return string    Output
    */
    protected function value($source)
    {
        $regex = array(
            // Quotes
            $this->regexQuo => '<span class="'.$this->cssQuo.'">$0</span>',
            // Hex-Color
            $this->regexHC => '<span class="'.$this->cssHC.'">$0</span>',
            // Functions
            $this->regexFnc => '<span class="'.$this->cssFnc.'">$1</span>$2',
            // Units
            $this->regexUnts => '$1<span class="'.$this->cssUnts.'">$2</span>',
            // !important
            $this->regexImp => '<span class="'.$this->cssImp.'">$0</span>',
        );

        foreach($regex as $key=>$value)
            $source[0] = preg_replace($key, $value, $source[0]);

        return $source[0];
    }

    /**
    * @-Rules
    *
    * @param array      RegEx Match
    * @return string    Output
    */
    protected function atrule($source)
    {
        // Property
        $pref = '<span class="'.$this->cssAt.'">'.$source[1].'</span>';

        // Split Values
        $valuet = preg_replace_callback('/(?:[\w\-]+)?(["\']|\().+?(?:\1|\))|(?:[#\w\-.!]+)/', array($this,'value'), $source[2]);

        return $pref.$valuet.$source[3];
    }
}

?>
