<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array(
        array(
            'label' => 'Import',
            'action' => 'index',
            'module' => 'avant-import',
        ),
        array(
            'label' => 'Status',
            'action' => 'browse',
            'module' => 'avant-import',
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>
