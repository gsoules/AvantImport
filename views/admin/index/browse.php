<?php
    echo head(array('title' => __('AvantImport')));
?>
<?php echo common('avantimport-nav'); ?>
<div id="primary">
    <h2><?php echo __('Status'); ?></h2>
    <?php echo flash(); ?>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    <?php if (iterator_count(loop('AvantImport_Import'))): ?>
    <table class="simple">
        <thead>
            <tr>
                <?php
                $browseHeadings[__('Import date / Log')] = 'added';
                $browseHeadings[__('CSV file')] = 'original_filename';
                $browseHeadings[__('Row count')] = 'row_count';
                $browseHeadings[__('Skipped rows')] = 'skipped_row_count';
                $browseHeadings[__('Imported records')] = null;
                $browseHeadings[__('Updated records')] = 'updated_record_count';
                $browseHeadings[__('Skipped records')] = 'skipped_record_count';
                $browseHeadings[__('Status')] = 'status';
                $browseHeadings[__('Action')] = null;
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => ''));
                ?>
            </tr>
        </thead>
        <tbody>
            <?php $key = 0; ?>
            <?php foreach (loop('AvantImport_Import') as $avantImport): ?>
            <tr class="<?php if (++$key%2 == 1) echo 'odd'; else echo 'even'; ?>">
                <td>
                    <?php
                        $importDate = html_escape(format_date($avantImport->added, Zend_Date::DATETIME_SHORT));
                        $logs = get_db()->getTable('AvantImport_Log')->findByImportId($avantImport->id);
                        if (empty($logs)):
                            echo $importDate;
                        else:
                            $logsUrl = $this->url(array(
                                'action' => 'logs',
                                'id' => $avantImport->id
                            ), 'default');
                        ?>
                    <a href="<?php echo html_escape($logsUrl);  ?>" class="csv-logs delete-button"><?php echo $importDate; ?></a>
                    <?php endif; ?>
                </td>
                <td><?php echo html_escape($avantImport->original_filename); ?></td>
                <?php $importedRecordCount = $avantImport->getImportedRecordCount(); ?>
                <td><?php echo html_escape($avantImport->row_count); ?></td>
                <td><?php echo html_escape($avantImport->skipped_row_count); ?></td>
                <td><?php echo html_escape($importedRecordCount); ?></td>
                <td><?php echo html_escape($avantImport->updated_record_count); ?></td>
                <td><?php echo html_escape($avantImport->skipped_record_count); ?></td>

                <td><?php echo html_escape(__(Inflector::humanize($avantImport->status, 'all'))); ?></td>
                <td>
                <?php
                    // Kept to manage the display of old imports.
                    if (!in_array($avantImport->format, array('File', 'Update'))
                        && (($avantImport->isCompleted() && $importedRecordCount > 0)
                            || $avantImport->isStopped()
                            || ($avantImport->isImportError() && $importedRecordCount > 0))):
                        $undoImportUrl = $this->url(array(
                                'action' => 'undo-import',
                                'id' => $avantImport->id,
                            ),
                            'default');
                ?>
                    <a href="<?php echo html_escape($undoImportUrl); ?>" class="csv-undo-import button red"><?php echo html_escape(__('Undo Import')); ?></a>
                <?php
                    elseif (
                        ($avantImport->isUndone()
                            || $avantImport->isUndoImportError()
                            || $avantImport->isOtherError()
                            || ($avantImport->isCompleted() && $importedRecordCount == 0)
                            || ($avantImport->isImportError() && $importedRecordCount == 0))):
                        $clearHistoryImportUrl = $this->url(array(
                                'action' => 'clear-history',
                                'id' => $avantImport->id,
                            ),
                            'default');
                ?>
                    <a href="<?php echo html_escape($clearHistoryImportUrl); ?>" class="csv-clear-history button green"><?php echo html_escape(__('Clear History')); ?></a>
                <?php
                    elseif ($avantImport->isQueuedOrProcessing()):
                        $clearHistoryImportUrl = $this->url(array(
                                'action' => 'stop-process',
                                'id' => $avantImport->id,
                            ),
                            'default');
                ?>
                    <a href="<?php echo html_escape($clearHistoryImportUrl); ?>" class="csv-stop-process button blue"><?php echo html_escape(__('Stop Process')); ?></a>
                <?php
                    else:
                        echo __('No action');
                    endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p><?php echo __('You have no imports yet.'); ?></p>
    <?php endif; ?>

</div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function () {
    Omeka.AvantImport.confirm();
});
//]]>
</script>
<?php
    echo foot();
