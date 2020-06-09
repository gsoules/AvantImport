<?php

define('CONFIG_LABEL_COLUMN_MAPPING', __('Mappings'));

class ImportConfig extends ConfigOptions
{
    const OPTION_COLUMN_MAPPING = 'avant_import_column_mapping';

    public static function getOptionDataForColumnMappingField()
    {
        $rawData = self::getRawData(self::OPTION_COLUMN_MAPPING);
        $optionData = array();

        foreach ($rawData as $elementId => $data)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            if (empty($elementName))
            {
                if ($elementId == '<files>')
                {
                    $elementName = $elementId;
                }
                else
                {
                    // This element must have been deleted since the AvantElements configuration was last saved.
                    continue;
                }
            }
            $data['name'] = $elementName;
            $optionData[$elementId] = $data;
        }

        return $optionData;
    }

    public static function getOptionTextForColumnMappingField()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_COLUMN_MAPPING];
        }
        else
        {
            $data = self::getOptionDataForColumnMappingField();
            $text = '';

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $definition['name'];
                $column = $definition['column'];
                $text .= "$column: $name";
            }
        }
        return $text;
    }

    public static function saveConfiguration()
    {
        self::saveOptionDataForColumnMappingField();
    }

    public static function saveOptionDataForColumnMappingField()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_COLUMN_MAPPING]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <csv-column-name> ":" <element-name>
            $parts = array_map('trim', explode(':', $definition));

            $sourceColumName = $parts[0];
            $targetColumName = $parts[1];

            self::errorIf(count($parts) > 2, CONFIG_LABEL_COLUMN_MAPPING, __("Mapping for column '%s' has too many parameters", $sourceColumName));
            self::errorIf(count($parts) != 2, CONFIG_LABEL_COLUMN_MAPPING, __("Element name for column '%s' is missing", $sourceColumName));

            $unusedElementsData = CommonConfig::getOptionDataForUnusedElements();
            self::errorIf(in_array($targetColumName, $unusedElementsData), CONFIG_LABEL_COLUMN_MAPPING, __("Element '%s' is unused and cannot be imported into", $targetColumName));

            if (isset($parts[1]))
            {
                $elementName = $parts[1];
                if ($elementName == '<files>')
                {
                    $elementId = $elementName;
                }
                else
                {
                    $elementId = ItemMetadata::getElementIdForElementName($elementName);
                    self::errorIfNotElement($elementId, CONFIG_LABEL_COLUMN_MAPPING, $elementName);
                }
            }

            $data[$elementId] = array('column' => $sourceColumName);
        }

        set_option(self::OPTION_COLUMN_MAPPING, json_encode($data));
    }
}