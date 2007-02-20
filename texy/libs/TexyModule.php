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
 * Texy! modules base class
 */
abstract class TexyModule
{
    /** @var Texy */
    protected $texy;

    /** @var array  list of syntax to allow */
    protected $allow = array();


    public function __construct($texy)
    {
        $this->texy = $texy;
        $texy->registerModule($this);

        foreach ($this->allow as $item)
            $texy->allowed[$item] = TRUE;
    }


    /**
     * Registers all line & block patterns
     */
    public function init()
    {
    }


    /**
     * Full text pre-processing
     * @param string
     * @return string
     */
    public function preProcess($text)
    {
        return $text;
    }


    /**
     * Single line post-processing
     * @param string
     * @return string
     */
    public function linePostProcess($line)
    {
        return $line;
    }


    /**
     * Undefined property usage prevention
     */
    function __get($nm) { throw new Exception("Undefined property '" . get_class($this) . "::$$nm'"); }
    function __set($nm, $val) { $this->__get($nm); }
    private function __unset($nm) { $this->__get($nm); }
    private function __isset($nm) { $this->__get($nm); }

} // TexyModule