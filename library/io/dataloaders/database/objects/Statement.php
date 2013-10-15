<?php
/**
 * Statement.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\io\dataloaders\database\objects;

use Molly\library\exceptions\IllegalArgumentException;
use \Molly\library\exceptions\InvalidConstructorException;
use Molly\library\io\dataloaders\database\abstracts\AbstractStatement;


/**
 * Class Statement
 * @package Molly\library\io\database\node
 *
 * By encapsuling a PDOstatement in our own class, we can effectively send events to trigger actions during our database interaction.
 */
class Statement extends AbstractStatement {

    private $statement;

    function __construct($pdostatement) {
        if ($pdostatement instanceof \PDOStatement) {
            $this->statement = $pdostatement;
        } else {
            throw new InvalidConstructorException(new IllegalArgumentException($pdostatement, "PDOStatement"));
        }
    }

    function bindColumn($column, &$param)
    {
        return $this->statement->bindColumn($column, $param);
    }

    function bindParam($parameter, &$variable)
    {
        return $this->statement->bindParam($parameter, $variable);
    }

    function bindValue($parameter, $value)
    {
        return $this->statement->bindValue($parameter, $value);
    }

    function errorCode()
    {
        return $this->statement->errorCode();
    }

    function errorInfo()
    {
        return $this->statement->errorInfo();
    }

    function execute($inputParameters = null)
    {
        return $this->statement->execute($inputParameters);
    }

    function fetch($fetch_style = 0, $cursor_orientation = 0, $cursor_offset = 0)
    {
        return $this->statement->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    function fetchAll($fetch_style = 0)
    {
        return $this->statement->fetchAll($fetch_style);
    }

    function fetchColumn($column_number = 0)
    {
        return $this->statement->fetchColumn($column_number);
    }

    function nextRowset()
    {
        return $this->statement->nextRowset();
    }

    function rowCount()
    {
        return $this->statement->rowCount();
    }

    function setAttribute($attribute, $value)
    {
        return $this->statement->setAttribute($attribute, $value);
    }

    function setFetchMode($mode)
    {
        return $this->statement->setFetchMode($mode);
    }
}