<?php

class AvantImport_ColumnMap_Action extends AvantImport_ColumnMap
{
    const ACTION_UPDATE_ELSE_CREATE = 'Update else create';
    const ACTION_CREATE = 'Create';
    const ACTION_UPDATE = 'Update';
    const ACTION_ADD = 'Add';
    const ACTION_REPLACE = 'Replace';
    const ACTION_DELETE = 'Delete';
    const ACTION_SKIP = 'Skip';

    const ACTION_OPTION_NAME = 'avant_import_action';
    const DEFAULT_ACTION = 'Update else create';

    private $_action;

    /**
     * @param string $columnName
     * @param string $action Default action
     */
    public function __construct($columnName, $action = null)
    {
        parent::__construct($columnName);
        $this->_type = AvantImport_ColumnMap::TYPE_ACTION;
        $this->_action = $this->_checkAction($action)
            ? $action
            : self::DEFAULT_ACTION;
    }

    /**
     * Map a row to set the action for a record.
     *
     * @param array $row The row to map
     * @param array $result
     * @return string Action for a record.
     */
    public function map($row, $result)
    {
        $result = ucfirst(strtolower(trim($row[$this->_columnName])));
        return $this->_checkAction($result)
            ? $result
            : $this->_action;
    }

    /**
     * Return the action.
     *
     * @return string The action
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Return action if it exists, else return the default action.
     *
     * @param string $action
     * @return string|boolean
     */
    private function _checkAction($action)
    {
        return in_array($action, array(
            self::ACTION_UPDATE_ELSE_CREATE,
            self::ACTION_CREATE,
            self::ACTION_UPDATE,
            self::ACTION_ADD,
            self::ACTION_REPLACE,
            self::ACTION_DELETE,
            self::ACTION_SKIP,
        ));
    }
}
