<?php

class Table_AvantImport_ImportedRecord extends Omeka_Db_Table
{
    /**
     * Return the total of imported records of the specified import.
     *
     * @uses Omeka_Db_Table::count()
     *
     * @param int $import_id
     * @return integer
     */
    public function getTotal($import_id)
    {
        return $this->count(array('import_id' => $import_id));
    }
}
