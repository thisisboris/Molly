<?php
/**
 * Statement.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\io\database\interfaces;
/**
 * Class Statement
 * This copies PDO-function to an extent.
 * @package Molly\library\io\database\interfaces
 */
interface Statement {
    function bindColumn ($column , &$param);
    function bindParam ($parameter , &$variable);
    function bindValue ($parameter , $value);

    function errorCode ();
    function errorInfo ();

    function execute ($inputParameters = null);

    function fetch ($fetch_style = 0, $cursor_orientation = 0, $cursor_offset = 0);
    function fetchAll ($fetch_style = 0);
    function fetchColumn ($column_number = 0);

    function nextRowset ();
    function rowCount ();
    function setAttribute ($attribute , $value);
    function setFetchMode ($mode);
}