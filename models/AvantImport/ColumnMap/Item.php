<?php

class AvantImport_ColumnMap_Item extends AvantImport_ColumnMap
{
    /**
     * @param string $columnName
     */
    public function __construct($columnName)
    {
        parent::__construct($columnName);
        $this->_type = AvantImport_ColumnMap::TYPE_ITEM;
    }

    /**
     * Map a row to an array that can be parsed by insert_item() or
     * insert_files_for_item().
     *
     * @param array $row The row to map
     * @param array $result
     * @return string The result
     */
    public function map($row, $result)
    {
        $result = trim($row[$this->_columnName]);
        return $result;
    }
}
