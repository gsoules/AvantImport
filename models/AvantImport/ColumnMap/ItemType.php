<?php

class AvantImport_ColumnMap_ItemType extends AvantImport_ColumnMap
{
    const DEFAULT_ITEM_TYPE = null;

    private $_itemTypeId;

    /**
     * @param string $columnName
     * @param string $itemType
     */
    public function __construct($columnName, $itemTypeId = null)
    {
        parent::__construct($columnName);
        $this->_type = AvantImport_ColumnMap::TYPE_ITEM_TYPE;
        $this->_itemTypeId = empty($itemTypeId)
            ? self::DEFAULT_ITEM_TYPE
            : $itemTypeId;
    }

    /**
     * Return the item type id.
     *
     * @return int Item type id
     */
    public function getItemTypeId()
    {
        return $this->_itemTypeId;
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
        return empty($result)
            ? $this->_itemTypeId
            : $result;
    }
}
