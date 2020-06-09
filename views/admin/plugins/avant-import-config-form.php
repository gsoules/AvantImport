<?php
$view = get_view();

$adapterOptions = Zend_Registry::get('storage')->getAdapter()->getOptions();
echo '<strong>Import Folder</strong>: ' . $adapterOptions['localDir'] . '/import';
echo '<br/>';
echo '<br/>';

$columnMappingField = ImportConfig::getOptionTextForColumnMappingField();
$columnMappingFieldRows = max(2, count(explode(PHP_EOL, $columnMappingField)));

?>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_COLUMN_MAPPING; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Mappings from CSV column names to element names"); ?></p>
        <?php echo $view->formTextarea(ImportConfig::OPTION_COLUMN_MAPPING, $columnMappingField, array('rows' => $columnMappingFieldRows)); ?>
    </div>
</div>
