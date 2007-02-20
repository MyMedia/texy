<?php

/**
 * Texy! universal text -> html converter
 * --------------------------------------
 *
 * This source file is subject to the GNU GPL license.
 *
 * @author     David Grudl aka -dgx- <dave@dgx.cz>
 * @link       http://texy.info/
 * @copyright  Copyright (c) 2004-2007 David Grudl
 * @license    GNU GENERAL PUBLIC LICENSE v2
 * @package    Texy
 * @category   Text
 * @version    $Revision$ $Date$
 */

// security - include texy.php, not this file
if (!defined('TEXY')) die();






/**
 * AUTOMATIC REPLACEMENTS MODULE CLASS
 */
class TexySmiliesModule extends TexyModule
{
    protected $allow = array('Image.smilies');

    public $icons = array (
        ':-)'  =>  'smile.gif',
        ':-('  =>  'sad.gif',
        ';-)'  =>  'wink.gif',
        ':-D'  =>  'biggrin.gif',
        '8-O'  =>  'eek.gif',
        '8-)'  =>  'cool.gif',
        ':-?'  =>  'confused.gif',
        ':-x'  =>  'mad.gif',
        ':-P'  =>  'razz.gif',
        ':-|'  =>  'neutral.gif',
    );
    public $root = NULL;
    public $class = '';



    /**
     * Module initialization.
     */
    public function init()
    {
        if (empty($this->texy->allowed['Image.smilies'])) return;

        krsort($this->icons);

        $pattern = array();
        foreach ($this->icons as $key => $foo)
            $pattern[] = preg_quote($key, '#') . '+';

        $crazyRE = '#(?<=^|[\\x00-\\x20])(' . implode('|', $pattern) . ')#';

        $this->texy->registerLinePattern($this, 'processLine', $crazyRE, 'Image.smilies');
    }






    /**
     * Callback function: :-)
     * @return string
     */
    public function processLine($parser, $matches)
    {
        $match = $matches[0];
        //    [1] => **
        //    [2] => ...
        //    [3] => (title)
        //    [4] => [class]
        //    [5] => {style}
        //    [6] => LINK

        $texy =  $this->texy;
        $el = new TexyImageElement($texy);
        $el->modifier->title = $match;
        $el->modifier->classes[] = $this->class;

         // find the closest match
        foreach ($this->icons as $key => $value)
            if (substr($match, 0, strlen($key)) === $key) {
                $root = $this->root === NULL ? $this->texy->imageModule->root :  $this->root;
                //$el->image->set($value, $root, TRUE); // different ROOT !!!
                break;
            }

        return $this->texy->mark($el->__toString(), Texy::CONTENT_NONE); // !!!
    }



} // TexySmiliesModule