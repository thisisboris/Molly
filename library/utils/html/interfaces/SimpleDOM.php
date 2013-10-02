<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\utils\html\interfaces;

use \Molly\library\utils\html\DOMNode as DOMNode;

if (!defined('HDOM_TYPE_ELEMENT')) {
    define('HDOM_TYPE_ELEMENT', 1);
    define('HDOM_TYPE_COMMENT', 2);
    define('HDOM_TYPE_TEXT',    3);
    define('HDOM_TYPE_ENDTAG',  4);
    define('HDOM_TYPE_ROOT',    5);
    define('HDOM_TYPE_UNKNOWN', 6);
    define('HDOM_QUOTE_DOUBLE', 0);
    define('HDOM_QUOTE_SINGLE', 1);
    define('HDOM_QUOTE_NO',     3);
    define('HDOM_INFO_BEGIN',   0);
    define('HDOM_INFO_END',     1);
    define('HDOM_INFO_QUOTE',   2);
    define('HDOM_INFO_SPACE',   3);
    define('HDOM_INFO_TEXT',    4);
    define('HDOM_INFO_INNER',   5);
    define('HDOM_INFO_OUTER',   6);
    define('HDOM_INFO_ENDSPACE',7);
    define('DEFAULT_TARGET_CHARSET', 'UTF-8');
    define('DEFAULT_BR_TEXT', "\r\n");
    define('DEFAULT_SPAN_TEXT', " ");
    define('MAX_FILE_SIZE', 600000);
}

interface SimpleDOM
{
    function dump();

    function createElement($tag, $contents);
    function deleteElement(DOMNode &$node);

    function &getParent();
    function setParent(DOMNode &$node);

    function setChildId($id);

    function addChildNode(DOMNode &$node);
    function removeChildNode(DOMNode &$node);

    function hasChildNodes();

    function &getChildNodes();
    function &getChildNode($id = -1);

    function &getFirstChild();
    function &getLastChild();

    function getElementById($id);
    function getElementsById($id, $idx=null);

    function getElementByTagName($name);
    function getElementsByTagName($name, $idx=null);
}
