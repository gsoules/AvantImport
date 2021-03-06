<?php

class AvantImport_ColumnMap_File extends AvantImport_ColumnMap
{
    const FILE_DELIMITER_OPTION_NAME = 'avant_import_file_delimiter';
    const DEFAULT_FILE_DELIMITER = ';';

    private $_fileDelimiter;

    /**
     * @param string $columnName
     * @param string $fileDelimiter
     */
    public function __construct($columnName, $fileDelimiter = null)
    {
        parent::__construct($columnName);
        $this->_type = AvantImport_ColumnMap::TYPE_FILE;
        $this->_fileDelimiter = is_null($fileDelimiter)
            ? self::getDefaultFileDelimiter()
            : $fileDelimiter;
    }

    /**
     * Map a row to an array that can be parsed by insert_item() or
     * insert_files_for_item().
     *
     * @param array $row The row to map
     * @param array $result
     * @return array|null The result
     */
    public function map($row, $result)
    {
        $urlString = trim($row[$this->_columnName]);
        if ($urlString) {
            if ($this->_fileDelimiter == '') {
                $rawUrls = array($urlString);
            }
            else {
                $rawUrls = explode($this->_fileDelimiter, $urlString);
            }
            $trimmedUrls = array_map('trim', $rawUrls);
            $cleanedUrls = array_diff($trimmedUrls, array(''));
            if (!is_array($result)) {
                $result = array($result);
            }
            $result = array_merge($result, $cleanedUrls);
            $result = array_filter($result);
            $result = array_unique($result);
        }
        // No value (but there is a column, else it is null).
        else {
            $result = array();
        }

        return $result;
    }

    /**
     * Return the file delimiter.
     *
     * @return string The file delimiter
     */
    public function getFileDelimiter()
    {
        return $this->_fileDelimiter;
    }

    /**
     * Returns the default file delimiter.
     * Uses the default file delimiter specified in the options table if
     * available.
     *
     * @return string The default file delimiter
     */
    static public function getDefaultFileDelimiter()
    {
        if (!($delimiter = get_option(self::FILE_DELIMITER_OPTION_NAME))) {
            $delimiter = self::DEFAULT_FILE_DELIMITER;
        }
        return $delimiter;
    }
}
