<?php
    echo head(array('title' => __('AvantImport')));
?>
<?php echo common('avantimport-nav'); ?>
<div id="primary">
    <h2><?php echo __("Step 2: Map '%s' columns to elements or files", $this->csvFile); ?></h2>
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
