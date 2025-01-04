<?php

class AvantImport_Form_Main extends Omeka_Form
{
    private $_columnDelimiter;
    private $_enclosure;
    private $_elementDelimiter;
    private $_tagDelimiter;
    private $_fileDelimiter;
    private $_fileDestinationDir;
    private $_maxFileSize;

    public function init()
    {
        parent::init();

        $this->_columnDelimiter = AvantImport_RowIterator::getDefaultColumnDelimiter();
        $this->_enclosure = AvantImport_RowIterator::getDefaultEnclosure();
        $this->_elementDelimiter = AvantImport_ColumnMap_Element::getDefaultElementDelimiter();
        $this->_tagDelimiter = AvantImport_ColumnMap_Tag::getDefaultTagDelimiter();
        $this->_fileDelimiter = AvantImport_ColumnMap_File::getDefaultFileDelimiter();

        $this->setAttrib('id', 'avantimport');
        $this->setMethod('post');

        $this->addElement('checkbox', 'records_are_public', array(
            'label' => __('Check to make imported items public'),
            'description' => __('Make items public'),
            'value' => (bool)get_option('avant_import_public') == true
        ));

        $this->addElement('checkbox', 'dryrun', array(
            'label' => __('Analyze import file without importing'),
            'description' => __('Dry Run'),
            'value' => true
        ));

        $this->_addFileElement();

        $this->addDisplayGroup(
            array(
                'column_mapping',
                'records_are_public',
                'dryrun'
            ),
            'default_values'
        );

        $this->addDisplayGroup(
            array(
                'csv_file',
            ),
            'file_type'
        );

        $submit = $this->createElement(
            'submit', 'submit',
            array('label' => __('Next'),
                'class' => 'submit submit-medium'));

        $submit->setDecorators(
            array('ViewHelper',
                array('HtmlTag',
                    array('tag' => 'div',
                        'class' => 'avantimportnext'))));

        $this->addElement($submit);

        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
    }

    protected function _addFileElement()
    {
        $size = $this->getMaxFileSize();
        $byteSize = $this->getMaxFileSize();
        $byteSize->setType(Zend_Measure_Binary::BYTE);

        $fileValidators = array(
            new Zend_Validate_File_Size(array('max' => $byteSize->getValue())),
            new Zend_Validate_File_Count(1),
        );

        $byteSize->setType(Zend_Measure_Binary::MEGABYTE);

        if ($this->_requiredExtensions) {
            $fileValidators[] =
                new Omeka_Validate_File_Extension($this->_requiredExtensions);
        }
        if ($this->_requiredMimeTypes) {
            $fileValidators[] =
                new Omeka_Validate_File_MimeType($this->_requiredMimeTypes);
        }
        // Random filename in the temporary directory to prevent race condition.
        $filter = new Zend_Filter_File_Rename($this->_fileDestinationDir
                    . '/' . md5(mt_rand() + microtime(true)));
        $this->addElement('file', 'csv_file', array(
            'label' => __('Choose a CSV file to be imported'),
            'required' => true,
            'validators' => $fileValidators,
            'destination' => $this->_fileDestinationDir,
            'description' => __("Maximum file size is %s.", $size->toString()),
        ));
        $this->csv_file->addFilter($filter);
    }

    protected function _getHumanDelimiterText($delimiter)
    {
        $delimitersList = AvantImport_IndexController::getDelimitersList();

        return in_array($delimiter, $delimitersList)
            ? array_search($delimiter, $delimitersList)
            : $delimiter;
    }

    protected function _getDelimitersMenu()
    {
        $delimitersListKeys = array_keys(AvantImport_IndexController::getDelimitersList());
        $values = array_combine($delimitersListKeys, $delimitersListKeys);
        $values['custom'] = 'custom';
        return $values;
    }

    protected function _addEnclosureElement()
    {
        $enclosure = $this->_enclosure;
        $enclosuresList = AvantImport_IndexController::getEnclosuresList();
        $enclosureCurrent = in_array($enclosure, $enclosuresList)
            ? array_search($enclosure, $enclosuresList)
            : $enclosure;

        $this->addElement('select', 'enclosure_name', array(
            'label' => __('Enclosure'),
            'description' => __('A zero or single character that will be used to separate columns '
                . 'clearly. It allows to use the column delimiter as a character in a field. By default, '
                . 'the quotation mark « " » is used. Enclosure can be omitted in the csv file.'),
            'multiOptions' => array(
                'double-quote' => __('" (double quote)'),
                'quote' => __(" ' (single quote)"),
                'empty' => __('(empty)'),
                'custom' => __('Custom'),
            ),
            'value' => $enclosureCurrent,
        ));
        $this->addElement('text', 'enclosure', array(
            'value' => $enclosure,
            'required' => false,
            'size' => '1',
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(
                    'min' => 0,
                    'max' => 1,
                    'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG =>
                            __('Enclosure must be zero or one character long.'),
                    ),
                )),
            ),
        ));
    }

    public function isValid($post)
    {
        $isValid = true;
        // Too much POST data, return with an error.
        if (empty($post) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
            $maxSize = $this->getMaxFileSize()->toString();
            $this->csv_file->addError(
                __('The file you have uploaded exceeds the maximum post size '
                . 'allowed by the server. Please upload a file smaller '
                . 'than %s.', $maxSize));
            $isValid = false;
        }

        if (!$isValid) {
            return false;
        }

        return parent::isValid($post);
    }

    public function setFileDestination($dest)
    {
        $this->_fileDestinationDir = $dest;
    }

    /**
     * Set the maximum size for an uploaded CSV file.
     *
     * If this is not set in the plugin configuration, defaults to the smaller
     * of 'upload_max_filesize' and 'post_max_size' settings in php.
     *
     * If this is set but it exceeds the aforementioned php setting, the size
     * will be reduced to that lower setting.
     *
     * @param string|null $size The maximum file size
     */
    public function setMaxFileSize($size = null)
    {
        $postMaxSize = $this->_getBinarySize(ini_get('post_max_size'));
        $fileMaxSize = $this->_getBinarySize(ini_get('upload_max_filesize'));

        // Start with the max size as the lower of the two php ini settings.
        $strictMaxSize = $postMaxSize->compare($fileMaxSize) > 0
            ? $fileMaxSize
            : $postMaxSize;

        // If the plugin max file size setting is lower, choose it as the strict
        // max size.
        $pluginMaxSizeRaw = trim(get_option(AvantImportPlugin::MEMORY_LIMIT_OPTION_NAME));
        if ($pluginMaxSizeRaw != '') {
            $pluginMaxSize = $this->_getBinarySize($pluginMaxSizeRaw);
            if ($pluginMaxSize) {
                $strictMaxSize = $strictMaxSize->compare($pluginMaxSize) > 0
                    ? $pluginMaxSize
                    : $strictMaxSize;
            }
        }

        if ($size === null) {
            $maxSize = $this->_maxFileSize;
        } else {
            $maxSize = $this->_getBinarySize($size);
        }

        if ($maxSize === false
                || $maxSize === null
                || $maxSize->compare($strictMaxSize) > 0
            ) {
            $maxSize = $strictMaxSize;
        }

        $this->_maxFileSize = $maxSize;
    }

    public function getMaxFileSize()
    {
        if (!$this->_maxFileSize) {
            $this->setMaxFileSize();
        }
        return $this->_maxFileSize;
    }

    protected function _getBinarySize($size)
    {
        if (!preg_match('/(\d+)([KMG]?)/i', $size, $matches)) {
            return false;
        }

        $sizeType = Zend_Measure_Binary::BYTE;

        $sizeTypes = array(
            'K' => Zend_Measure_Binary::KILOBYTE,
            'M' => Zend_Measure_Binary::MEGABYTE,
            'G' => Zend_Measure_Binary::GIGABYTE,
        );

        if (count($matches) == 3 && array_key_exists($matches[2], $sizeTypes)) {
            $sizeType = $sizeTypes[$matches[2]];
        }

        return new Zend_Measure_Binary($matches[1], $sizeType);
    }
}
