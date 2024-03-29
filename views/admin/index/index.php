<?php
    echo head(array('title' => __('AvantImport')));

    if (plugin_is_active('AvantElasticsearch'))
    {
        echo '<div class="error">The AvantImport plugin cannot be used while AvantElasticsearch is active.</div>';
        echo '<p>Deactive AvantElasticsearch, perform the import, and then activate AvantElasticsearch.</p>';
        echo foot();
        return;
    }
?>
<?php echo common('avantimport-nav'); ?>
<div id="primary">
    <?php echo flash(); ?>
    <h2><?php if (!(plugin_is_active('MDIBL') && isset($_GET[MDIBL::BUILD_LISTS]))) echo __('Step 1: Choose CSV file to import'); ?></h2>
    <?php echo $this->form; ?>
</div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function () {
    jQuery('#column_delimiter_name').click(Omeka.AvantImport.updateColumnDelimiterField);
    jQuery('#enclosure_name').click(Omeka.AvantImport.updateEnclosureField);
    jQuery('#element_delimiter_name').click(Omeka.AvantImport.updateElementDelimiterField);
    jQuery('#tag_delimiter_name').click(Omeka.AvantImport.updateTagDelimiterField);
    jQuery('#file_delimiter_name').click(Omeka.AvantImport.updateFileDelimiterField);
    Omeka.AvantImport.updateOnLoad(); // Need this to reset invalid forms.
});
//]]>
</script>
<?php
    echo foot();
