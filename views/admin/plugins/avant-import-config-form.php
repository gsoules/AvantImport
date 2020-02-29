<?php
$view = get_view();

$adapterOptions = Zend_Registry::get('storage')->getAdapter()->getOptions();
echo 'Import Folder = ' . $adapterOptions['localDir'] . '/import';
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
        <p class="explanation"><?php echo __("Elements that should display as a text field."); ?></p>
        <?php echo $view->formTextarea(ImportConfig::OPTION_COLUMN_MAPPING, $columnMappingField, array('rows' => $columnMappingFieldRows)); ?>
    </div>
</div>

<fieldset id="fieldset-avant-import-rights"><legend><?php echo __('Rights and Roles'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('avant_import_allow_roles', __('Roles that can use AvantImport')); ?>
        </div>
        <div class="inputs five columns omega">
            <div class="input-block">
                <?php
                    $currentRoles = unserialize(get_option('avant_import_allow_roles')) ?: array();
                    $userRoles = get_user_roles();
                    echo '<ul style="padding-left: 0;">';
                    foreach ($userRoles as $role => $label) {
                        echo '<li style="list-style: none;">';
                        echo $this->formCheckbox('avant_import_allow_roles[]', $role,
                            array('checked' => in_array($role, $currentRoles) ? 'checked' : ''));
                        echo '&nbsp;&nbsp;' . $label;
                        echo '</li>';
                    }
                    echo '</ul>';
                ?>
            </div>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('avant_import_slow_process', __('Slow the process')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formText('avant_import_slow_process', get_option('avant_import_slow_process'), null); ?>
            <p class="explanation">
                <?php echo __('Some providers check if too many files are uploaded in one shot and prevent the import.'); ?>
                <?php echo __('This option sleeps the process during this number of seconds to avoid such a limit.'); ?>
            </p>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('avant_import_repeat_amazon_s3', __('Repeat for Amazon S3')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formText('avant_import_repeat_amazon_s3', get_option('avant_import_repeat_amazon_s3'), null); ?>
            <p class="explanation">
                <?php echo __('This option is used only when files are stored on Amazon S3.'); ?>
                <?php echo __('Amazon S3 may stop the process randomly (about every 20 to 200 files), when too many files are imported in one bucket.'); ?>
                <?php echo __('This option allows to relaunch the process from the last imported row this number of times.'); ?>
                <?php echo __('Big imports succeed with "slow" = 10, "repeat" = 100 and the option "plugins.AvantImport.batchSize" = "10", but it may vary with your plan.'); ?>
            </p>
        </div>
    </div>
</fieldset>
