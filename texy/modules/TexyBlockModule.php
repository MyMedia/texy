<?php

/**
 * This file is part of the Texy! formatter (http://texy.info/)
 *
 * Copyright (c) 2004-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version    $Revision$ $Date$
 * @package    Texy
 */

// security - include texy.php, not this file
if (!defined('TEXY')) die();



/**
 * Special blocks module
 */
class TexyBlockModule extends TexyModule implements ITexyPreBlock
{
    protected $default = array(
        'blocks' => TRUE,
        'block/default' => TRUE,
        'block/pre' => TRUE,
        'block/code' => TRUE,
        'block/html' => TRUE,
        'block/text' => TRUE,
        'block/texysource' => TRUE,
        'block/comment' => TRUE,
        'block/div' => TRUE,
    );



    public function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'pattern'),
            '#^/--++ *+(.*)'.TEXY_MODIFIER_H.'?$((?:\n(?0)|\n.*+)*)(?:\n\\\\--.*$|\z)#mUi',
            'blocks'
        );
    }


    /**
     * Full text pre-processing
     * @param string
     * @return string
     */
    public function preBlock($text)
    {
        // autoclose exclusive blocks
        $text = preg_replace(
            '#^(/--++ *+(?!div|texysource).*)$((?:\n.*+)*?)(?:\n\\\\--.*$|(?=(\n/--.*$)))#mi',
            "\$1\$2\n\\--",
            $text
        );
        return $text;
    }


    /**
     * Callback for:
     *   /-----code html .(title)[class]{style}
     *     ....
     *     ....
     *   \----
     *
     * @param TexyBlockParser
     * @param array      regexp matches
     * @param string     pattern name
     * @return TexyHtml|string|FALSE
     */
    public function pattern($parser, $matches)
    {
        list(, $mParam, $mMod, $mContent) = $matches;
        //    [1] => code | text | ...
        //    [2] => ... additional parameters
        //    [3] => .(title)[class]{style}<>
        //    [4] => ... content

        $mod = new TexyModifier($mMod);
        $parts = preg_split('#\s+#u', $mParam, 2);
        $blocktype = empty($parts[0]) ? 'block/default' : 'block/' . $parts[0];
        $param = empty($parts[1]) ? NULL : $parts[1];

        // event wrapper
        if (is_callable(array($this->texy->handler, 'block'))) {
            $res = $this->texy->handler->block($parser, $blocktype, $mContent, $param, $mod);
            if ($res !== Texy::PROCEED) return $res;
        }

        return $this->solve($blocktype, $mContent, $param, $mod);
    }


    public function outdent($s)
    {
        $s = trim($s, "\n");
        $spaces = strspn($s, ' ');
        if ($spaces) return preg_replace("#^ {1,$spaces}#m", '', $s);
        return $s;
    }


    /**
     * Finish invocation
     *
     * @param string   blocktype
     * @param string   content
     * @param string   additional parameter
     * @param TexyModifier
     * @return TexyHtml|string|FALSE
     */
    public function solve($blocktype, $s, $param, $mod)
    {
        $tx = $this->texy;

        if ($blocktype === 'block/texy') {
            $el = TexyHtml::el();
            $el->parseBlock($tx, $s);
            return $el;
        }

        if (empty($tx->allowed[$blocktype])) return FALSE;

        if ($blocktype === 'block/texysource') {
            $s = $this->outdent($s);
            if ($s==='') return "\n";
            $el = TexyHtml::el();
            if ($param === 'line') $el->parseLine($tx, $s);
            else $el->parseBlock($tx, $s);
            $s = $tx->_toHtml( $el->export($tx) );
            $blocktype = 'block/code'; $param = 'html'; // to be continue (as block/code)
        }

        if ($blocktype === 'block/code') {
            $s = $this->outdent($s);
            if ($s==='') return "\n";
            $el = TexyHtml::el('pre');
            $mod->decorate($tx, $el);
            $el->class[] = $param; // lang
            $el->childNodes[0] = TexyHtml::el('code');
            $s = Texy::escapeHtml($s);
            $s = $tx->protect($s);
            $el->childNodes[0]->setContent($s);
            return $el;
        }

        if ($blocktype === 'block/default') {
            $s = $this->outdent($s);
            if ($s==='') return "\n";
            $el = TexyHtml::el('pre');
            $mod->decorate($tx, $el);
            $el->class[] = $param; // lang
            $s = Texy::escapeHtml($s);
            $s = $tx->protect($s);
            $el->setContent($s);
            return $el;
        }

        if ($blocktype === 'block/pre') {
            $s = $this->outdent($s);
            if ($s==='') return "\n";
            $el = TexyHtml::el('pre');
            $mod->decorate($tx, $el);
            $lineParser = new TexyLineParser($tx);
            $lineParser->onlyHtml = TRUE;
            $s = $lineParser->parse($s);
            $s = Texy::unescapeHtml($s);
            $s = Texy::escapeHtml($s);
            $s = $tx->unprotect($s);
            $s = $tx->protect($s);
            $el->setContent($s);
            return $el;
        }

        if ($blocktype === 'block/html') {
            $s = trim($s, "\n");
            if ($s==='') return "\n";
            $lineParser = new TexyLineParser($tx);
            $lineParser->onlyHtml = TRUE;
            $s = $lineParser->parse($s);
            $s = Texy::unescapeHtml($s);
            $s = Texy::escapeHtml($s);
            $s = $tx->unprotect($s);
            return $tx->protect($s) . "\n";
        }

        if ($blocktype === 'block/text') {
            $s = trim($s, "\n");
            if ($s==='') return "\n";
            $s = Texy::escapeHtml($s);
            $s = str_replace("\n", TexyHtml::el('br')->startTag() , $s); // nl2br
            return $tx->protect($s) . "\n";
        }

        if ($blocktype === 'block/comment') {
            return "\n";
        }

        if ($blocktype === 'block/div') {
            $s = $this->outdent($s);
            if ($s==='') return "\n";
            $el = TexyHtml::el('div');
            $mod->decorate($tx, $el);
            $el->parseBlock($tx, $s);
            return $el;
        }

        return FALSE;
    }

} // TexyBlockModule