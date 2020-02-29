<form id="avantimport" method="post" action="">
<?php
    $colNames = $this->columnNames;
    $colExamples = $this->columnExamples;
?>
    <table id="column-mappings" class="simple">
    <thead>
    <tr>
        <th><?php echo __('CSV column name'); ?></th>
        <th><?php echo __('First data row from CSV file'); ?></th>
        <th><?php echo __('Map to element'); ?></th>
        <th><?php echo __('Special values'); ?></th>
    </tr>
    </thead>
    <tbody>
<?php for ($i = 0; $i < count($colNames); $i++): ?>
        <tr>
        <td><strong><?php echo html_escape($colNames[$i]); ?></strong></td>
        <?php $exampleString = $colExamples[$colNames[$i]]; ?>
        <td><code><?php echo html_escape(substr($exampleString, 0, 47)); ?><?php if (strlen($exampleString) > 47) { echo '&hellip;';} ?></code></td>
        <?php echo $this->form->getSubForm("row$i"); ?>
        </tr>
<?php endfor; ?>
    </tbody>
    </table>
    <fieldset>
    <?php echo $this->form->submit; ?>
    </fieldset>
</form>
