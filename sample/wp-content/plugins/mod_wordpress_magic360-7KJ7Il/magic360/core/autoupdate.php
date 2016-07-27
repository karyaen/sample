<?php
if ( ! function_exists( 'add_action' ) ) {
    echo "Please enable this plugin from your wp-admin.";
    exit;
}

class WordPress_Magic360_autoupdate {
    private $changelogURL = 'https://www.magictoolbox.com/magic360/modules/wordpress/';

    private $slug = 'magic360';

    public static function init() {
        static $instance;
        if ( empty( $instance ) )
            $instance = new WordPress_Magic360_autoupdate();
        return $instance;
    }
    function WordPress_Magic360_autoupdate() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'checkForUpdates' ), 10, 1 );
        add_action( 'install_plugins_pre_plugin-information', array( $this, 'overrideUpdateInformation' ), 1 );
    }

    function mod_WordPress_Magic360_backup() {
        $fileContetns = file_get_contents(plugin_dir_path(__FILE__).'magic360.js');
        delete_option("WordPress_Magic360_backup");
        add_option("WordPress_Magic360_backup", $fileContetns);
    }


    function checkForUpdates( $value ) {
        global $update_plugin;
        if (!$update_plugin)
            return $value;

        $key = magictoolbox_WordPress_Magic360_get_data_from_db();
        if ($key) { $key = $key->license; }

        $ver = json_decode(mb_convert_encoding(@file_get_contents('http://www.magictoolbox.com/api/platform/wordpress/version/'), 'HTML-ENTITIES', "UTF-8"));

        if (empty($ver))
            return $value;

        $ver = str_replace('v', '', $ver->version);
        $oldVer = plugin_get_version_WordPress_Magic360();


        if ($key) {
            $_url = 'https://www.magictoolbox.com/site/order/'.$key.'/';
            $_package = 'https://www.magictoolbox.com/site/order/'.$key.'/wordpress/magic360.zip';
        } else {
            $this->mod_WordPress_Magic360_backup();
            $_url = 'https://www.magictoolbox.com/static/';
            $_package = 'https://www.magictoolbox.com/static/mod_wordpress_magic360.zip';
        }

        if (version_compare($oldVer, $ver, '<')) {
            $response = new stdClass();
            $response->id = 0;
            $response->slug = 'magic360';
            $response->new_version = $ver;
            $response->plugin = 'mod_wordpress_magic360/mod_wordpress_magic360.php';
            $response->url = $_url;
            $response->package = $_package;

            $value->response['mod_wordpress_magic360/mod_wordpress_magic360.php'] = $response;
        }

        return $value;
    }

    function overrideUpdateInformation() {
        if ( wp_unslash( $_REQUEST['plugin'] ) !== $this->slug )
            return;

        wp_redirect( $this->changelogURL );
        exit;
    }
}

add_action( 'plugins_loaded', array( 'WordPress_Magic360_autoupdate', 'init' ) );
?>
