<?php

class AvantImport_ColumnMap_Set
{
    private $_maps = array();

    /**
     * @param array $maps The array of column mappings.
     */
    public function __construct(array $maps)
    {
        $this->_maps = $maps;
    }

    /**
     * Adds a column map to the set.
     *
     * @param AvantImport_ColumnMap $map The column map
     */
    public function add(AvantImport_ColumnMap $map)
    {
        $this->_maps[] = $map;
    }

    /**
     * Map a row to an associative array of mappings indexed by column mapping
     * type, and where each mapping can be parsed by insert_item() or
     * insert_files_for_item().
     *
     * @param array $row The row to map
     * @return array The associative array of mappings
     */
    public function map(array $row)
    {
        $allResults = array(
            AvantImport_ColumnMap::TYPE_ACTION => null,
            AvantImport_ColumnMap::TYPE_IDENTIFIER => null,
            AvantImport_ColumnMap::TYPE_IDENTIFIER_FIELD => null,
            AvantImport_ColumnMap::TYPE_RECORD_TYPE => null,
            AvantImport_ColumnMap::TYPE_ITEM => null,
            AvantImport_ColumnMap::TYPE_ITEM_TYPE => null,
            AvantImport_ColumnMap::TYPE_COLLECTION => null,
            AvantImport_ColumnMap::TYPE_PUBLIC => null,
            AvantImport_ColumnMap::TYPE_FEATURED => null,
            AvantImport_ColumnMap::TYPE_ELEMENT => array(),
            AvantImport_ColumnMap::TYPE_EXTRA_DATA => array(),
            AvantImport_ColumnMap::TYPE_TAG => array(),
            AvantImport_ColumnMap::TYPE_FILE => null,
        );
        foreach ($this->_maps as $map) {
            $subset = $allResults[$map->getType()];
            $allResults[$map->getType()] = $map->map($row, $subset);
        }
        return $allResults;
    }
}
