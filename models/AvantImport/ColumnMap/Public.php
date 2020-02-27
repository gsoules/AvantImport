<?php

class AvantImport_ColumnMap_Public extends AvantImport_ColumnMap
{
    const DEFAULT_PUBLIC = false;

    private $_isPublic;

    /**
     * @param string $columnName
     * @param boolean $isPublic
     */
    public function __construct($columnName, $isPublic = null)
    {
        parent::__construct($columnName);
        $this->_type = AvantImport_ColumnMap::TYPE_PUBLIC;
        $filter = new Omeka_Filter_Boolean;
        $this->_isPublic = is_null($isPublic)
            ? self::DEFAULT_PUBLIC
            : $filter->filter($isPublic);
    }

    /**
     * Return the public.
     *
     * @return boolean Public
     */
    public function getIsPublic()
    {
        return $this->_isPublic;
    }

    /**
     * Map a row to whether the row corresponding to a record is public or not.
     *
     * @param array $row The row to map
     * @param array $result
     * @return bool Whether the row corresponding to a record is public or not
     */
    public function map($row, $result)
    {
        $filter = new Omeka_Filter_Boolean;
        $flag = strtolower(trim($row[$this->_columnName]));
        // Don't use empty, because the value can be "0".
        if ($flag === '') {
            return $this->_isPublic;
        }
        if ($flag == 'no') {
            return 0;
        }
        if ($flag == 'yes') {
            return 1;
        }
        return $filter->filter($flag);
    }
}
