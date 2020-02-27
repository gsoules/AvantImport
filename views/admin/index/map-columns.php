<?php
    echo head(array('title' => __('AvantImport')));
?>
<?php echo common('avantimport-nav'); ?>
<div id="primary">
    <h2><?php echo __('Step 2: Map columns to elements, tags, or files'); ?></h2>
    <p><?php echo __('Csv file: %s', $this->csvFile); ?></p>
    <?php echo flash(); ?>
    <?php echo $this->form; ?>
</div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function () {
    Omeka.AvantImport.enableElementMapping();
    Omeka.AvantImport.assistWithMapping();
});
//]]>
</script>
<?php
    echo foot();
