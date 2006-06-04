<?php

/**
 * ------------------------------------------
 *   DEFINITION LIST - TEXY! DEFAULT MODULE
 * ------------------------------------------
 *
 * Version 1 Release Candidate
 *
 * DEPENDENCES: tm_list.php
 *
 * Copyright (c) 2004-2005, David Grudl <dave@dgx.cz>
 * Web: http://www.texy.info/
 *
 * Modules for parsing text into blocks
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */

// security - include texy.php, not this file
if (!defined('TEXY')) die();
require_once('tm-list.php');




/**
 * DEFINITION LIST MODULE CLASS
 */
class TexyDefinitionListModule extends TexyListModule {
  var $allowed = array(
         '*'            => true,
         '-'            => true,
         '+'            => true,
  );

  // private
  var $translate = array(    //  rexexp  class
         '*'            => array('\*',   ''),
         '-'            => array('\-',   ''),
         '+'            => array('\+',   ''),
      );



  /***
   * Module initialization.
   */
  function init()
  {
    $bullets = array();
    foreach ($this->allowed as $bullet => $allowed)
      if ($allowed) $bullets[] = $this->translate[$bullet][0];

    $this->registerBlockPattern('processBlock', '#^(?:MODIFIER_H\n)?'                              // .{color:red}
                                              . '(\S.*)\:\ *MODIFIER_H?\n'                         // Term:
                                              . '(\ +)('.implode('|', $bullets).')\ +\S.*$#mU');   //    - description
  }



  /***
   * Callback function (for blocks)
   *
   *            Term: .(title)[class]{style}>
   *              - description 1
   *              - description 2
   *              - description 3
   *
   */
  function processBlock(&$blockParser, &$matches)
  {
    list($match, $mMod1, $mMod2, $mMod3, $mMod4,
                 $mContentTerm, $mModTerm1, $mModTerm2, $mModTerm3, $mModTerm4,
                 $mSpaces, $mBullet) = $matches;
    //    [1] => (title)
    //    [2] => [class]
    //    [3] => {style}
    //    [4] => >

    //    [5] => ...
    //    [6] => (title)
    //    [7] => [class]
    //    [8] => {style}
    //    [9] => >

    //   [10] => space
    //   [11] => - * +

    $texy = & $this->texy;
    $el = &new TexyListElement($texy);
    $el->modifier->setProperties($mMod1, $mMod2, $mMod3, $mMod4);
    $el->tag = 'dl';

    $bullet = '';
    foreach ($this->translate as $type)
      if (preg_match('#'.$type[0].'#A', $mBullet)) {
        $bullet = $type[0];
        $el->modifier->classes[] = $type[1];
        break;
      }

    $blockParser->addChildren($el);

    $blockParser->moveBackward(2);

    $patternTerm = $texy->translatePattern('#^\n?(\S.*)\:\ *MODIFIER_H?()$#mUA');
    $bullet = preg_quote($mBullet);

    while (true) {
      if ($elItem = &$this->processItem($blockParser, preg_quote($mBullet), true)) {
        $elItem->tag = 'dd';
        $el->children[] = & $elItem;
        continue;
      }

      if ($blockParser->receiveNext($patternTerm, $matches)) {
        list($match, $mContent, $mMod1, $mMod2, $mMod3, $mMod4) = $matches;
        //    [1] => ...
        //    [2] => (title)
        //    [3] => [class]
        //    [4] => {style}
        //    [5] => >
        $elItem = &new TexyTextualElement($texy);
        $elItem->tag = 'dt';
        $elItem->modifier->setProperties($mMod1, $mMod2, $mMod3, $mMod4);
        $elItem->parse($mContent);
        $el->children[] = & $elItem;
        continue;
      }

      break;
    }
  }

} // TexyDefinitionListModule








?>