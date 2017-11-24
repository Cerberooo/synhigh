# SynHigh - Syntax Highlight Module

Easy to use php syntax highlight module.

## Supported Languages/Configurations

* HTML
* CSS
* PHP (with PHP `highlight_string`)
* JS (with PHP `highlight_string`)
* HTACCESS

## Features

* Modular
* Expandable

## Using

Download the `dist/synhigh.zip`, unzip and put it into to your project directory. Then load the module in your `.php` file with:  

```php
require_once('synParser.php')
```

Initialize with:  

```php
new synhigh(string $source, string $language, bool $lineNumbers, int $lineBegin, int $lineEnd);
```

* `$source`: string to highlight
* `$language`: language name
   * `php`, for PHP or JS
   * `html`, for HTML and mixed HTML/PHP
   * `css`
   * `htaccess`
* `$lineNumbers`: when `true` synhigh will append line numbers [optional]
* `$lineBegin`: line where the source begins [optional]
* `$lineEnd`: line where the source ends [optional]

Functions:

* `$myVar->parseCode()`, returns the output as string
* `$myVar->setWithinLine(true)`, formats the output for using within a line
* Setter:
   * `$myVar->setSource($source)` string
   * `$myVar->setLang($lang)` string
   * `$myVar->setLineNumbers($lineNumbers)` boolean
   * `$myVar->setLineBegin($lineBegin)` integer
   * `$myVar->setLineEnd($lineEnd)` integer

## License

Check the LICENSE file.
