<?php




class TaxiDbFirebird extends TaxiObject
{
    
    public $connection;

    
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    
    public function createLine($args)
    {
        foreach ($args as $key => $value) {
            if (is_string($value)) {
                $args[$key] = "'" . ($value) . "'";
            } elseif (is_null($value)) {
                $args[$key] = 'NULL';
            } elseif (is_integer($value)) {
                $args[$key] = $value;
            } elseif (is_bool($value)) {
                $args[$key] = $value ? 'TRUE' : 'FALSE';
            }
        }
        $argsLine = implode(', ', $args);
        return $argsLine;
    }

    
    public function addQuotes($args)
    {
        foreach ($args as $key => $arg) {
            $arg = str_replace('"', '\"', $arg);
            $args[$key] = '"' . $arg . '"';
        }
        return $args;
    }

    
    public function createInsertQuery($toTable, $values)
    {
        $fieldsLine = implode(', ', $this->addQuotes(array_keys($values)));
        $valuesLine = $this->createLine(array_values($values));

        $sql = "insert into \"{$toTable}\" ({$fieldsLine}) values({$valuesLine})";
        return $sql;
    }

    
    public function insert($toTable, $values)
    {
        $sql = $this->createInsertQuery($toTable, $values);
        if ($countRows = $this->connection->query($sql)) {
            return $countRows;
        } else {
            return false;
        }
    }

}
