<?php

class AvantImportPlugin extends Omeka_Plugin_AbstractPlugin
{
    const MEMORY_LIMIT_OPTION_NAME = 'avant_import_memory_limit';
    const PHP_PATH_OPTION_NAME = 'avant_import_php_path';

    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'initialize',
        'install',
        'uninstall',
        'config_form',
        'config',
        'admin_head',
        'define_acl',
        'admin_items_batch_edit_form',
        'items_batch_edit_custom',
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        // With some combinations of Apache/FPM/Varnish, the self constant
        // can't be used as key for properties.
        'avant_import_memory_limit' => '',
        'avant_import_php_path' => '',
        'avant_import_identifier_field' => AvantImport_ColumnMap_IdentifierField::DEFAULT_IDENTIFIER_FIELD,
        'avant_import_column_delimiter' => AvantImport_RowIterator::DEFAULT_COLUMN_DELIMITER,
        'avant_import_enclosure' => AvantImport_RowIterator::DEFAULT_ENCLOSURE,
        'avant_import_element_delimiter' => AvantImport_ColumnMap_Element::DEFAULT_ELEMENT_DELIMITER,
        'avant_import_tag_delimiter' => AvantImport_ColumnMap_Tag::DEFAULT_TAG_DELIMITER,
        'avant_import_file_delimiter' => AvantImport_ColumnMap_File::DEFAULT_FILE_DELIMITER,
        // Option used during the first step only.
        'avant_import_html_elements' => false,
        'avant_import_extra_data' => 'manual',
        // With roles, in particular if Guest User is installed.
        'avant_import_allow_roles' => 'a:1:{i:0;s:5:"super";}',
        'avant_import_slow_process' => 0,
        'avant_import_repeat_amazon_s3' => 100,
    );

    /**
     * Add the translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');

        // Get the backend settings from the security.ini file.
        // This simplifies tests too (use of local paths instead of urls).
        // TODO Probably a better location to set this.
        if (!Zend_Registry::isRegistered('avant_import')) {
            $iniFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'security.ini';
            $settings = new Zend_Config_Ini($iniFile, 'avant-import');
            Zend_Registry::set('avant_import', $settings);
        }
    }

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $db = $this->_db;

        // Create AvantImport table.
        // Note: AvantImport_Import and AvantImport_ImportedRecord are standard Zend
        // records, but not Omeka ones fully.
        $db->query("CREATE TABLE IF NOT EXISTS `{$db->AvantImport_Import}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `format` varchar(255) collate utf8_unicode_ci NOT NULL,
            `delimiter` varchar(1) collate utf8_unicode_ci NOT NULL,
            `enclosure` varchar(1) collate utf8_unicode_ci NOT NULL,
            `status` varchar(255) collate utf8_unicode_ci,
            `row_count` int(10) unsigned NOT NULL,
            `skipped_row_count` int(10) unsigned NOT NULL,
            `skipped_record_count` int(10) unsigned NOT NULL,
            `updated_record_count` int(10) unsigned NOT NULL,
            `file_position` bigint unsigned NOT NULL,
            `original_filename` text collate utf8_unicode_ci NOT NULL,
            `file_path` text collate utf8_unicode_ci NOT NULL,
            `serialized_default_values` text collate utf8_unicode_ci NOT NULL,
            `serialized_column_maps` text collate utf8_unicode_ci NOT NULL,
            `owner_id` int unsigned NOT NULL,
            `added` timestamp NOT NULL default '2000-01-01 00:00:00',
            PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        // Create imported records table.
        $db->query("CREATE TABLE IF NOT EXISTS `{$db->AvantImport_ImportedRecord}` (
            `id` int(10) unsigned NOT NULL auto_increment,
            `import_id` int(10) unsigned NOT NULL,
            `record_type` varchar(50) collate utf8_unicode_ci NOT NULL,
            `record_id` int(10) unsigned NOT NULL,
            `identifier` varchar(255) collate utf8_unicode_ci NOT NULL,
            PRIMARY KEY  (`id`),
            KEY (`import_id`),
            KEY `record_type_record_id` (`record_type`, `record_id`),
            KEY (`identifier`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $db->query("
            CREATE TABLE IF NOT EXISTS `{$db->AvantImport_Log}` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `import_id` int(10) unsigned NOT NULL,
                `priority` tinyint unsigned NOT NULL,
                `created` timestamp DEFAULT CURRENT_TIMESTAMP,
                `message` text NOT NULL,
                `params` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY (`import_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");

        $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;

        // Drop the tables.
        $sql = "DROP TABLE IF EXISTS `{$db->AvantImport_Import}`";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->AvantImport_ImportedRecord}`";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->AvantImport_Log}`";
        $db->query($sql);

        $this->_uninstallOptions();
    }

    /**
     * Shows plugin configuration page.
     */
    public function hookConfigForm($args)
    {
        $view = get_view();
        echo $view->partial(
            'plugins/avant-import-config-form.php'
        );
    }

    /**
     * Saves plugin configuration page.
     *
     * @param array Options set in the config form.
     */
    public function hookConfig($args)
    {
        ImportConfig::saveConfiguration();
    }

    /**
     * Defines the plugin's access control list.
     *
     * @param array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $resource = 'AvantImport_Index';

        // TODO This is currently needed for tests for an undetermined reason.
        if (!$acl->has($resource)) {
            $acl->addResource($resource);
        }
        // Hack to disable CRUD actions.
        $acl->deny(null, $resource, array('show', 'add', 'edit', 'delete'));
        $acl->deny(null, $resource);

        $roles = $acl->getRoles();

        // Only allow the super user to import.
        $allowRoles = array('super');
        $allowRoles = array_intersect($roles, $allowRoles);
        if ($allowRoles) {
            $acl->allow($allowRoles, $resource);
        }

        $denyRoles = array_diff($roles, $allowRoles);
        if ($denyRoles) {
            $acl->deny($denyRoles, $resource);
        }
  }

    /**
     * Configure admin theme header.
     *
     * @param array $args
     */
    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getModuleName() == 'avant-import') {
            queue_css_file('avant-import');
            queue_js_file('avant-import');
        }
    }

    /**
     * Add the AvantImport link to the admin main navigation.
     *
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $label = plugin_is_active('MDIBL') ? 'MDIBL Import' : __('Import CSV File');

        $nav[] = array(
            'label' => $label,
            'uri' => url('avant-import'),
            'resource' => 'AvantImport_Index',
            'privilege' => 'index',
        );
        return $nav;
    }

    /**
     * Add a partial batch edit form.
     *
     * @return void
     */
    public function hookAdminItemsBatchEditForm($args)
    {
        $view = get_view();
        echo $view->partial(
            'forms/avant-import-batch-edit.php'
        );
    }

    /**
     * Process the partial batch edit form.
     *
     * @return void
     */
    public function hookItemsBatchEditCustom($args)
    {
        $item = $args['item'];
        $orderByFilename = $args['custom']['avantimport']['orderByFilename'];
        $mixImages = $args['custom']['avantimport']['mixImages'];

        if ($orderByFilename) {
            $this->_sortFiles($item, (boolean) $mixImages);
        }
    }

    /**
     * Sort all files of an item by name and eventually sort images first.
     *
     * @param Item $item
     * @param boolean $mixImages
     * @return void
     */
    protected function _sortFiles($item, $mixImages = false)
    {
        if ($item->fileCount() < 2) {
            return;
        }

        $list = $item->Files;
        // Make a sort by name before sort by type.
        usort($list, function($fileA, $fileB) {
            return strcmp($fileA->original_filename, $fileB->original_filename);
        });
        // The sort by type doesn't remix all filenames.
        if (!$mixImages) {
            $images = array();
            $nonImages = array();
            foreach ($list as $file) {
                // Image.
                if (strpos($file->mime_type, 'image/') === 0) {
                    $images[] = $file;
                }
                // Non image.
                else {
                    $nonImages[] = $file;
                }
            }
            $list = array_merge($images, $nonImages);
        }

        // To avoid issues with unique index when updating (order should be
        // unique for each file of an item), all orders are reset to null before
        // true process.
        $db = $this->_db;
        $bind = array(
            $item->id,
        );
        $sql = "
            UPDATE `$db->File` files
            SET files.order = NULL
            WHERE files.item_id = ?
        ";
        $db->query($sql, $bind);

        // To avoid multiple updates, a single query is used.
        foreach ($list as &$file) {
            $file = $file->id;
        }
        // The array is made unique, because a file can be repeated.
        $list = implode(',', array_unique($list));
        $sql = "
            UPDATE `$db->File` files
            SET files.order = FIND_IN_SET(files.id, '$list')
            WHERE files.id in ($list)
        ";
        $db->query($sql);
    }
}
