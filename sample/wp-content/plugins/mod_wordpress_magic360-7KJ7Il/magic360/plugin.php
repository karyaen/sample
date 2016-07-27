<?php
/*

Copyright 2015 MagicToolbox (email : support@magictoolbox.com)

*/

$error_message = false;
$update_plugin = true;

function WordPress_Magic360_activate () {

    set_transient( 'WordPress_Magic360_welcome_license_activation_redirect', true, 30 );

    if(!function_exists('file_put_contents')) {
        function file_put_contents($filename, $data) {
            $fp = fopen($filename, 'w+');
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            }
        }
    }


    //fix url's in css files
    $fileContents = file_get_contents(dirname(__FILE__) . '/core/magic360.css');
    $cssPath = preg_replace('/https?:\/\/[^\/]*/is', '', get_option("siteurl"));

    $cssPath .= '/wp-content/'.preg_replace('/^.*?\/(plugins\/.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__))).'/core';

    //$pattern = '/url\(\s*(?:\'|")?(?!'.preg_quote($cssPath, '/').')\/?([^\)\s]+?)(?:\'|")?\s*\)/is';
    //$replace = 'url(' . $cssPath . '/$1)';
    $pattern = '#url\(\s*(\'|")?(?!data:|mhtml:|http(?:s)?:|/)([^\)\s\'"]+?)(?(1)\1)\s*\)#is';
    $replace = 'url($1'.$cssPath.'/$2$1)';

    $fixedFileContents = preg_replace($pattern, $replace, $fileContents);
    if($fixedFileContents != $fileContents) {
        file_put_contents(dirname(__FILE__) . '/core/magic360.css', $fixedFileContents);
    }

    magictoolbox_WordPress_Magic360_create_teble();
    magictoolbox_WordPress_Magic360_create_db();

    magictoolbox_WordPress_Magic360_init();

    WordPress_Magic360_send_stat('install');
}

function WordPress_Magic360_deactivate () {
}

function WordPress_Magic360_uninstall() {
    magictoolbox_WordPress_Magic360_remove_teble();

    magictoolbox_WordPress_Magic360_delete_row_from_db();
    if (magictoolbox_WordPress_Magic360_is_empty_db()) {
        magictoolbox_WordPress_Magic360_remove_db();
    }

    delete_option("WordPressMagic360CoreSettings");
    WordPress_Magic360_send_stat('uninstall');
}

function WordPress_Magic360_send_stat($action = '') {

    //NOTE: don't send from working copy
    if('working' == 'v6.1.31' || 'working' == 'v4.6.4') {
        return;
    }

    $hostname = 'www.magictoolbox.com';

    $url = preg_replace('/^https?:\/\//is', '', get_option("siteurl"));
    $url = urlencode(urldecode($url));

    global $wp_version;
    $platformVersion = isset($wp_version) ? $wp_version : '';

    $path = "api/stat/?action={$action}&tool_name=magic360&license=trial&tool_version=v4.6.4&module_version=v6.1.31&platform_name=wordpress&platform_version={$platformVersion}&url={$url}";
    $handle = @fsockopen($hostname, 80, $errno, $errstr, 30);
    if($handle) {
        $headers  = "GET /{$path} HTTP/1.1\r\n";
        $headers .= "Host: {$hostname}\r\n";
        $headers .= "Connection: Close\r\n\r\n";
        fwrite($handle, $headers);
        fclose($handle);
    }

}



function showMessage_WordPress_Magic360($message, $errormsg = false) {
    if ($errormsg) {
        echo '<div id="message" class="error">';
    } else {
        echo '<div id="message" class="updated fade">';
    }
    echo "<p><strong>$message</strong></p></div>";
}


function showAdminMessages_WordPress_Magic360(){
    global $error_message;
    if (current_user_can('manage_options')) {
       showMessage_WordPress_Magic360($error_message,true);
    }
}


function plugin_get_version_WordPress_Magic360() {
    $plugin_data = get_plugin_data(dirname(plugin_dir_path(__FILE__)).'/mod_wordpress_magic360.php');
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

function update_plugin_message_WordPress_Magic360() {
    $ver = json_decode(@file_get_contents('http://www.magictoolbox.com/api/platform/wordpress/version/'));
    if (empty($ver)) return false;
    $ver = str_replace('v','',$ver->version);
    $oldVer = plugin_get_version_WordPress_Magic360();
    if (version_compare($oldVer, $ver, '<')) {
        echo '<div id="message" class="updated fade">
                  <p>New version available! We recommend that you download the <a href="'.WordPressMagic360_url('http://magictoolbox.com/magic360/modules/wordpress/',' plugins page update link ').'">latest version</a> of Magic 360 for WordPress . </p>
              </div>';
    }
}

function get_tool_version_WordPress_Magic360($tool=null) {
    global $wp_filesystem;

    if (!$tool) {
        $tool = 'magic360';
    }

    WP_Filesystem();

    /*$version = 'trial';
    $tool = preg_replace('/magic/', '', 'magic360');
    $mod = '';
    $arr = array('trial', 'working');
    $str = '';

    if ('zoomplus' == $tool) {
        $tool = 'Zoom';
        $mod = 'Plus';
    } else {
        $tool = ucfirst($tool);
    }
    */

    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    $r = $wp_filesystem->get_contents(plugin_dir_path( __FILE__ ).'core/'.$tool.'.js');

    /*preg_match('/Magic\s.*$/m', $r, $str);
    $str = preg_replace('/Magic\s+'.$tool.'\s+'.$mod.'/', '', $str[0]);
    $str = preg_replace('/\s/im', '', $str);

    if (preg_match('/^v[0-9]/i', $str)) {
        $version = 'commercial';
    } else {
        $version = $str;
    }*/
    if (!preg_match('/demo/is',$r)) {
	    $version = 'commercial';
    } else {
	    $version = 'trial';
    }
    return $version;
}

function Magic360_remove_update_nag($value) {
    if (isset($value->response)) {
        unset($value->response[ str_replace('/plugin','',plugin_basename(__FILE__)) ]);
    }
    return $value;
}

function  magictoolbox_WordPress_Magic360_init() {

    add_action( 'admin_init', 'WordPressMagic360_welcome_license_do_redirect' );

    global $error_message;

    $tool_lower = 'magic360';
    switch ($tool_lower) {
        case 'magicthumb':  $priority = '80'; break;
        case 'magic360':    $priority = '90'; break;
    	case 'magiczoom': 	$priority = '100'; break;
    	case 'magiczoomplus': 	$priority = '110'; break;
    	case 'magicscroll': 	$priority = '120'; break;
    	case 'magicslideshow':	$priority = '130'; break;
    	default :		$priority = '90'; break;
    }
    require_once(dirname(__FILE__) . '/constructor_magic360/wordpress_magic360_fns.php');
    require_once(dirname(__FILE__) . '/constructor_magic360/wordpress_magic360_tool_db.php');

    add_action("admin_menu", "magictoolbox_WordPress_Magic360_config_page_menu");
    add_action('admin_enqueue_scripts', 'WordPress_Magic360_load_admin_scripts');
    add_action('wp_enqueue_scripts', 'WordPress_Magic360_load_frontend_scripts');

    add_filter('filesystem_method', create_function('$a', 'return "direct";' ));

    // add_action("wp_head", "magictoolbox_WordPress_Magic360_styles",$priority); //load scripts and styles // 360
    add_filter("the_content", "magictoolbox_WordPress_Magic360_create", 13);

    require_once(dirname(__FILE__)."/core/autoupdate.php");
    require_once(dirname(__FILE__)."/core/view/import_export/export.php");
    add_action('wp_ajax_WordPress_Magic360_import', 'WordPress_Magic360_import');
    add_action('wp_ajax_WordPress_Magic360_export', 'WordPress_Magic360_export');

    add_action('wp_ajax_WordPress_Magic360_save', 'magictoolbox_ajax_WordPress_Magic360_save');
    add_action('wp_ajax_WordPress_Magic360_check_shortcode', 'magictoolbox_ajax_WordPress_Magic360_check_shortcode');
    add_action('wp_ajax_WordPress_Magic360_remove_spins', 'magictoolbox_ajax_WordPress_Magic360_remove_spins');
    add_action('wp_ajax_WordPress_Magic360_copy_spins', 'magictoolbox_ajax_WordPress_Magic360_copy');
    add_action('admin_head', 'magictoolbox_WordPress_Magic360_add_my_tc_button');
    add_action('wp_ajax_WordPress_Magic360_tiny_mce_data', 'magictoolbox_WordPress_Magic360_get_tiny_mce_data');
    add_shortcode('magic360', 'magictoolbox_WordPress_Magic360_shortcode');

    add_action('wp_ajax_magictoolbox_WordPress_Magic360_set_license', 'magictoolbox_WordPress_Magic360_set_license');



    add_filter('site_transient_update_plugins', 'Magic360_remove_update_nag');
    add_filter( 'plugin_action_links', 'magictoolbox_WordPress_Magic360_links', 10, 2 );
    add_filter( 'plugin_row_meta', 'magictoolbox_WordPress_Magic360_plugin_row_meta' , 10, 2 );

    if (!file_exists(dirname(__FILE__) . '/core/magic360.js')) {
        $jsContents = file_get_contents('http://www.magictoolbox.com/static/magic360/trial/magic360.js');
        if (!empty($jsContents) && preg_match('/\/\*.*?\\\*/is',$jsContents)){
            if ( !is_writable(dirname(__FILE__) . '/core/')) {
                $error_message = 'The '.substr(dirname(__FILE__),strpos(dirname(__FILE__),'wp-content')).'/core/magic360.js file is missing. Please re-uplaod it.';
            }
            file_put_contents(dirname(__FILE__) . '/core/magic360.js', $jsContents);
            chmod(dirname(__FILE__) . '/core/magic360.js', 0777);
        } else {
            $error_message = 'The '.substr(dirname(__FILE__),strpos(dirname(__FILE__),'wp-content')).'/core/magic360.js file is missing. Please re-uplaod it.';
        }
    }
    if ($error_message) add_action('admin_notices', 'showAdminMessages_WordPress_Magic360');

    //add_filter("shopp_catalog", "magictoolbox_create", 1); //filter content for SHOPP plugin

    if(!isset($GLOBALS['magictoolbox']['WordPressMagic360'])) {
        require_once(dirname(__FILE__) . '/core/magic360.module.core.class.php');
        $coreClassName = "Magic360ModuleCoreClass";
        $GLOBALS['magictoolbox']['WordPressMagic360'] = new $coreClassName;
        $coreClass = &$GLOBALS['magictoolbox']['WordPressMagic360'];
    }
    $coreClass = &$GLOBALS['magictoolbox']['WordPressMagic360'];
    /* get current settings from db */
    $settings = get_option("WordPressMagic360CoreSettings");
    if($settings !== false && is_array($settings) && isset($settings['default']) && !isset($_GET['reset_settings'])) {
        foreach (WordPressMagic360_getParamsProfiles() as $profile => $name) {
	    if (isset($settings[$profile])) {
		$coreClass->params->appendParams($settings[$profile],$profile);
	    }
	}
    } else { //set defaults
        $allParams = array();
	foreach (WordPressMagic360_getParamsProfiles() as $profile => $name) {
	    $coreClass->params->setParams($coreClass->params->getParams('default'),$profile);
	    $allParams[$profile] = $coreClass->params->getParams('default');
	}
    $coreClass->params->setValue('thumb-max-height',get_option('single_view_image_height'),'product');
	delete_option("WordPressMagic360CoreSettings");
        add_option("WordPressMagic360CoreSettings", $allParams);
    }

    add_action( 'upgrader_process_complete', 'WordPress_Magic360_get_packed_js', 10, 2 );
}

function WordPress_Magic360_init_wp_filesystem($form_url) {
    global $wp_filesystem;
    $creds = request_filesystem_credentials($form_url, '', false, plugin_dir_path( __FILE__ ), false);

    if (!WP_Filesystem($creds)) {
        request_filesystem_credentials($form_url, '', true, plugin_dir_path( __FILE__ ), false);
        return false;
    }
    return true;
}

function WordPress_Magic360_write_file ($url, $content) {
    global $wp_filesystem;
    // if (empty($wp_filesystem)) {
    //     require_once (ABSPATH . '/wp-admin/includes/file.php');
    // }
    WordPress_Magic360_init_wp_filesystem($url);

    $result = $wp_filesystem->put_contents($url, $content, FS_CHMOD_FILE );

    return $result ? null : "Failed to write to file";
}

function WordPress_Magic360_rewrite ($key, $localPath, $tool) {
    $option = "WordPress_Magic360_backup";

    if ($key) {
        $url = 'https://www.magictoolbox.com/site/order/'.$key.'/'.$tool.'.js';
        $response = magictoolbox_WordPress_Magic360_get_file($url);

        $code = $response['status'];
        $response = $response['content'];
    } else {
        $code = 200;
        $response = get_option($option);
    }

    if($code == 200) {
        $result = WordPress_Magic360_write_file($localPath, $response);
    } else if($code == 403) {
        $result = "limit";
    } else if ($code == 404) {
        $result = "license failed";
    } else {
        $result = "Other errors";
    }

    return $result;
}

function WordPress_Magic360_get_packed_js ($upgrader_object, $options) {
    $first_plugin_version = "trial";
    if ('update' == $options['action'] &&  'plugin' == $options['type']) {
        foreach ($options['plugins'] as $pl) {
            $_plugin = explode("/", $pl);
            $_plugin = $_plugin[count($_plugin) - 1];
            if ('mod_wordpress_magic360.php' === $_plugin) {
                $path = plugin_dir_path(__FILE__).'core/magic360.js';
                $plugin_folder = dirname(plugin_dir_path(__FILE__));

                $key = magictoolbox_WordPress_Magic360_get_data_from_db();
                if ($key) { $key = $key->license; }

                if ($first_plugin_version == 'commercial') {
                    if(!$key) {
                        $oldName = str_replace('_commercial', '', $plugin_folder);
                        if (file_exists($oldName)) {
                            chmod($oldName, 0777);
                            rename($oldName, $plugin_folder);
                        }
                    } else {
                        if (file_exists(dirname($plugin_folder).'/magic360')) {
                            chmod(dirname($plugin_folder).'/magic360', 0777);
                            rename(dirname($plugin_folder).'/magic360', $plugin_folder);
                        }
                    }
                } else {
                    if($key) {
                        if (file_exists(dirname($plugin_folder).'/magic360')) {
                            chmod(dirname($plugin_folder).'/magic360', 0777);
                            rename(dirname($plugin_folder).'/magic360', $plugin_folder);
                        }
                    }
                }

                $result = WordPress_Magic360_rewrite($key, $path, 'magic360');

                break;
            }
        }
    }
}

function WordPress_Magic360_load_frontend_scripts () {
    $plugin = $GLOBALS['magictoolbox']['WordPressMagic360'];

    $tool_lower = 'magic360';
    switch ($tool_lower) {
        case 'magicthumb':      $priority = '10'; break;
        case 'magic360':        $priority = '11'; break;
    	case 'magiczoom': 	    $priority = '12'; break;
    	case 'magiczoomplus': 	$priority = '13'; break;
    	case 'magicscroll': 	$priority = '14'; break;
    	case 'magicslideshow':	$priority = '15'; break;
    	default :		        $priority = '11'; break;
    }

    wp_register_style( 'magictoolbox_magic360_style', plugin_dir_url( __FILE__ ).'core/magic360.css', array(), false, 'all');
    wp_register_style( 'magictoolbox_magic360_module_style', plugin_dir_url( __FILE__ ).'core/magic360.module.css', array(), false, 'all');
    wp_register_script( 'magictoolbox_magic360_script', plugin_dir_url( __FILE__ ).'core/magic360.js', array(), false, true);
    add_action("wp_footer", "magictoolbox_WordPress_Magic360_add_src_to_footer", $priority);
    add_action("wp_footer", "magictoolbox_WordPress_Magic360_add_options_script", 10001);
}

function WordPress_Magic360_load_admin_scripts () {
    wp_enqueue_script( 'jquery' ,includes_url('/js/jquery/jquery.js'));
    wp_enqueue_script( 'jquery-ui-core', includes_url('/js/jquery/ui/core.js') );
    wp_enqueue_script( 'jquery-ui-tabs', includes_url('/js/jquery/ui/tabs.js') );

    $ownPage = false;
    if (array_key_exists('page', $_GET)) {
        $ownPage =  "WordPressMagic360-config-page" ==  $_GET["page"]        ||
                    "WordPressMagic360-shortcodes-page" ==  $_GET["page"]    ||
                    "WordPressMagic360-import-export-page" ==  $_GET["page"] ||
                    "WordPressMagic360-license-page" ==  $_GET["page"];
    }

    if (is_admin()) {
        wp_register_script( 'wordpress_Magic360_admin_adminpage_script', plugin_dir_url( __FILE__ ).'core/wordpress_Magic360_adminpage.js', array('jquery', 'jquery-ui-core', 'jquery-ui-tabs'), null );
        if ($ownPage) {
            wp_enqueue_style( 'magictoolbox_wordpress_Magic360_admin_page_style', plugin_dir_url( __FILE__ ).'core/admin.css', array(), null );
        }

        if ($ownPage) {
            wp_enqueue_style( 'WordPress_Magic360_admin_list_style', plugin_dir_url( __FILE__ ).'constructor_magic360/css/list.css', array(), null );
            wp_enqueue_style( 'WordPress_Magic360_admin_spinshortcodes_style', plugin_dir_url( __FILE__ ).'constructor_magic360/css/spinshortcodes.css', array(), null );
            wp_enqueue_style( 'magictoolbox_WordPress_Magic360_tool_style', plugin_dir_url( __FILE__ ).'core/magic360.css', array(), null );
        }

        wp_register_script( 'WordPress_Magic360_admin_list_script', plugin_dir_url( __FILE__ ).'constructor_magic360/js/list.js', array('jquery'), null );
        wp_register_script( 'WordPress_Magic360_admin_spinshortcodes_script', plugin_dir_url( __FILE__ ).'constructor_magic360/js/spinshortcodes.js', array('jquery'), null );
        wp_register_script( 'magictoolbox_WordPress_Magic360_tool_script', plugin_dir_url( __FILE__ ).'core/magic360.js', array('WordPress_Magic360_admin_spinshortcodes_script'), null );
        add_action('admin_print_styles', 'magictoolbox_WordPress_Magic360_button_css');
        if ($ownPage) {
            wp_enqueue_style( 'WordPress_Magic360_admin_import_export_style', plugin_dir_url( __FILE__ ).'core/view/import_export/wordpress_Magic360_import_export.css', array(), null );
            wp_enqueue_style( 'WordPress_Magic360_admin_license_style', plugin_dir_url( __FILE__ ).'core/view/license/wordpress_Magic360_license.css', array(), null );

        }
        wp_register_script( 'WordPress_Magic360_admin_import_export_script', plugin_dir_url( __FILE__ ).'core/view/import_export/wordpress_Magic360_import_export.js', array('jquery'), null );
        wp_register_script( 'WordPress_Magic360_admin_license_script', plugin_dir_url( __FILE__ ).'core/view/license/wordpress_Magic360_license.js', array('jquery'), null );
    }

}


/**
  * Show row meta on the plugin screen.
  *
  * @param	mixed $links Plugin Row Meta
  * @param	mixed $file  Plugin Base file
  * @return	array
  */

function magictoolbox_WordPress_Magic360_plugin_row_meta( $links, $file ) {

	if (strpos(plugin_dir_path(__FILE__),plugin_dir_path($file))) {
		$row_meta = array($links[0],$links[1]);
		$row_meta['Settings'] =	'<a href="admin.php?page=WordPressMagic360-config-page">'.__('Settings').'</a>';
		$row_meta['Support'] =	'<a target="_blank" href="'.WordPressMagic360_url('https://www.magictoolbox.com/contact/','plugins page support link').'">Support</a>';
		$row_meta['Buy'] = '<a target="_blank" href="'.WordPressMagic360_url('https://www.magictoolbox.com/buy/magic360/','plugins page buy link').'">Buy</a>';
		$row_meta['More cool plugins'] = '<a target="_blank" href="'.WordPressMagic360_url('https://www.magictoolbox.com/wordpress/','plugins page more cool plugins link').'">More cool plugins</a>';

		return $row_meta;
	}

	return (array) $links;
}


function WordPressMagic360_config_page() {
    magictoolbox_WordPress_Magic360_config_page('WordPressMagic360');
}

function WordPress_Magic360_add_admin_src_to_menu_page() {
    wp_enqueue_script( 'wordpress_Magic360_admin_adminpage_script' );

    $arr = array(
        'ajax'   => get_site_url().'/wp-admin/admin-ajax.php',
        'nonce'  => wp_create_nonce('magic-everywhere'),
        'mtburl' => 'https://www.magictoolbox.com/site/order/'
    );

    wp_localize_script( 'wordpress_Magic360_admin_adminpage_script', 'magictoolbox_WordPress_Magic360_admin_modal_object', $arr);
}

function WordPress_Magic360_import() {
    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)){
        return;
    }

    $nonce = $_POST['nonce'];
    $tool = 'wordpress_magic360';

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        return;
    }

    $file = $_FILES['file'];

    $arr = (array) simplexml_load_string(file_get_contents($file["tmp_name"]),'SimpleXMLElement', LIBXML_NOCDATA);

    if (array_key_exists('tool', $arr) && $tool == $arr['tool']) {
        if (array_key_exists('license', $arr) && $arr['license'] != 'trial' && strlen($arr['license']) == 7) {
            magictoolbox_WordPress_Magic360_update_db($arr['license']);

            $url = 'https://www.magictoolbox.com/site/order/'.$arr['license'].'/magic360.js';
            $response = magictoolbox_WordPress_Magic360_get_file($url);
            if($response['status'] == 200) {
                WordPress_Magic360_write_file(plugin_dir_path( __FILE__ ).'core/magic360.js', $response['content']);
            }
        }

        if (array_key_exists('core', $arr)) {
            $core = (array) $arr['core'];

            $settings = get_option("WordPressMagic360CoreSettings");

            foreach ($core as $profile => $name) {
                $name = (array) $name;
                foreach ($name as $key => $value) {
                    $value = (array) $value;
                    if ('' != $value[0]) {
                        $settings[$profile][$key]['value'] = $value[0];
                    }
                }
            }

            delete_option("WordPressMagic360CoreSettings");
            add_option("WordPressMagic360CoreSettings", $settings);
        }

        if (array_key_exists('constructor', $arr)) {
            $constructor = (array) $arr['constructor'];
            $constructor = (array) $constructor[$tool];

            if (empty($constructor['id'])) {
                foreach ($constructor as $value) {
                    $value = (array) $value;
                    magictoolbox_WordPress_Magic360_add_data_to_table($value['name'], $value['shortcode'], $value['startimg'], $value['images'], $value['options'], $value['additional_options']);
                }
            } else {
                magictoolbox_WordPress_Magic360_add_data_to_table($constructor['name'], $constructor['shortcode'], $constructor['startimg'], $constructor['images'], $constructor['options'], $constructor['additional_options']);
            }
        }
    }
    // exit;
}

function WordPress_Magic360_export() {
    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)){
        return;
    }

    $nonce = $_POST['nonce'];
    $value = $_POST['value'];
    $secret_data = null;

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        return;
    }

    if (function_exists('get_magicslideshow_saved_data')) {
        $secret_data = get_magicslideshow_saved_data();
    }

    if (function_exists('magictoolbox_WordPress_Magic360_get_data')) {
        $secret_data = magictoolbox_WordPress_Magic360_get_data();
    }

    WordPress_Magic360_wp_export($value, get_option("WordPressMagic360CoreSettings"), $secret_data);
    exit;
}

function magictoolbox_WordPress_Magic360_add_src_to_footer() {
    global $magictoolbox_Magic360_page_has_shortcode,
            $magictoolbox_Magic360_page_has_tool,
            $magictoolbox_Magic360_page_added_script;
            
    if (!$magictoolbox_Magic360_page_has_tool) {
        $plugin = $GLOBALS['magictoolbox']['WordPressMagic360'];
        if ($plugin->params->checkValue('include-headers','yes')) {
            $magictoolbox_Magic360_page_has_tool = true; // add footers for all pages
        }
    }

    if (!$magictoolbox_Magic360_page_added_script) {
        $magictoolbox_Magic360_page_added_script = true;
        if ($magictoolbox_Magic360_page_has_shortcode || $magictoolbox_Magic360_page_has_tool) {
            wp_enqueue_style('magictoolbox_magic360_style');
            wp_enqueue_style('magictoolbox_magic360_module_style');
            wp_enqueue_script('magictoolbox_magic360_script');
        }


    }
}

function magictoolbox_WordPress_Magic360_add_options_script () {
    global $magictoolbox_Magic360_page_added_options,
            $magictoolbox_Magic360_page_has_shortcode,
            $magictoolbox_Magic360_page_has_tool;
    $footers = '';

    if (!$magictoolbox_Magic360_page_added_options) {
        $magictoolbox_Magic360_page_added_options = true;
        if ($magictoolbox_Magic360_page_has_shortcode || $magictoolbox_Magic360_page_has_tool) {
        $plugin = $GLOBALS['magictoolbox']['WordPressMagic360'];
        $footers = $plugin->getOptionsTemplate();
        }


        echo $footers;
    }
}

function magictoolbox_WordPress_Magic360_get_file($url) {
    $result = array( 'content' => '', 'status' => 0);

    if ($url && is_string($url)) {
        $url = trim($url);
        if ('' != $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $result['content'] = $response;
            $result['status'] = $code;
        }
    }

    return $result;
}

function magictoolbox_WordPress_Magic360_set_license() {
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
    }

    WP_Filesystem();
    // ob_end_clean();

    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)){
        return;
    }

    $nonce = $_POST['nonce'];
    $key = $_POST['key'];
    $extra_param = $_POST['param'];
    $result = '{"error": "error"}';

    if (!$extra_param || 'null' == $extra_param) {
        $extra_param = null;
        $tool_name = 'magic360';
    } else {
        $tool_name = $extra_param;
        $extra_param = 'WordPress_Magic360_'.$extra_param;
    }

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        $result = '{"error": "verification failed"}';
    } else {
        if ($key && '' != $key) {
            $url = 'https://www.magictoolbox.com/site/order/'.$key.'/'.$tool_name.'.js';
            $response = magictoolbox_WordPress_Magic360_get_file($url);

            $code = $response['status'];
            $response = $response['content'];

            if($code == 200) {
                $result = WordPress_Magic360_write_file(plugin_dir_path( __FILE__ ).'core/'.$tool_name.'.js', $response);
                if (!$result) {
                    magictoolbox_WordPress_Magic360_update_db($key, $extra_param);
                    $result = 'null';
                }
                $result = '{"error": '.$result.'}';
            } else if($code == 403) {
                $result = '{"error": "limit"}';
                //Download limit reached
                //Your license has been downloaded 10 times already.
                //If you wish to download your license again, please contact us.
            } else if ($code == 404) {
                $result = '{"error": "license failed"}';
            } else {
                $result = '{"error": "Other errors"}';
            }
        }
    }
    ob_end_clean();
    echo $result;
    wp_die();
}

function magictoolbox_WordPress_Magic360_create_db() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'magictoolbox_store';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
          id int unsigned NOT NULL auto_increment,
          name varchar(50) DEFAULT NULL,
          license varchar(50) DEFAULT NULL,
          UNIQUE KEY id (id));";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

function magictoolbox_WordPress_Magic360_remove_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magictoolbox_store';

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
    }
}

function magictoolbox_WordPress_Magic360_update_db($key, $name=null) {
    global $wpdb;
    $result = false;

    if (!$name || !is_string($name)) {
        $name = 'WordPress_Magic360';
    }

    if ($key && is_string($key)) {
        $table_name = $wpdb->prefix . 'magictoolbox_store';

        $data = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE name = '" . $name . "'");

        if ($data && count($data) > 0) {
            $result = $wpdb->update($table_name, array('license' => $key), array('name' => $name), array( '%s' ), array( '%s' ));
            $result = !!$result;
        } else {
            $result = $wpdb->insert($table_name, array('name' => $name, 'license' => $key));
        }
    }

    return $result;
}

function magictoolbox_WordPress_Magic360_delete_row_from_db($name=null) {
    global $wpdb;

    if (!$name || !is_string($name)) {
        $name = 'WordPress_Magic360';
    }

    $table_name = $wpdb->prefix . 'magictoolbox_store';
    return $wpdb->delete( $table_name, array( 'name' => $name ) );
}

function magictoolbox_WordPress_Magic360_is_empty_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magictoolbox_store';
    $result = $wpdb->get_results("SELECT * FROM ".$table_name);
    return !(count($result) > 0);
}

function magictoolbox_WordPress_Magic360_get_data_from_db($name=null) {
    global $wpdb;

    if (!$name || !is_string($name)) {
        $name = 'WordPress_Magic360';
    }

    $table_name = $wpdb->prefix . 'magictoolbox_store';
    $result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE name = '".$name."'");
    if ($result && count($result) > 0) {
        return $result[0];
    } else {
        return null;
    }
}

function magictoolbox_WordPress_Magic360_links( $links, $file ) {
    $fileName = 'mod_wordpress_magic360_trial/mod_wordpress_magic360.php';
    $fileName = preg_replace('/\_trial\//', '/', $fileName);
    if ($file == $fileName) {
        $settings_link = '<a href="admin.php?page=WordPressMagic360-config-page">'.__('Settings').'</a>';
        array_unshift( $links, $settings_link );
        $spins_link = '<a href="admin.php?page=WordPressMagic360-shortcodes-page">Spins</a>';
        array_push( $links, $spins_link );
    }
    return $links;
}

function magictoolbox_WordPress_Magic360_config_page_menu() {
    if(function_exists("add_menu_page")) {
        //$page = add_submenu_page("admin.php", __("Magic 360 Plugin Configuration"), __("Magic 360 Configuration"), "manage_options", "WordPressMagic360-config-page", "WordPressMagic360_config_page");
        $page = add_menu_page( __("Magic 360"), __("Magic 360"), "manage_options", "WordPressMagic360-config-page", "WordPressMagic360_config_page", plugin_dir_url( __FILE__ )."/core/admin_graphics/icon.png");
        add_submenu_page( "WordPressMagic360-config-page", 'Settings', 'Settings', 'manage_options', "WordPressMagic360-config-page" );
        add_action('admin_print_scripts-' . $page, 'WordPress_Magic360_add_admin_src_to_menu_page');
    }

    if(function_exists("add_submenu_page")) {
        $submenu_page = add_submenu_page("WordPressMagic360-config-page", "Spins", "Spins", "manage_options", "WordPressMagic360-shortcodes-page", "WordPress_Magic360_submenu_page");
        add_action('admin_print_scripts-' . $submenu_page, 'WordPress_Magic360_add_admin_src_to_submenu_page');
        $license_page = add_submenu_page("WordPressMagic360-config-page", "License", "License", "manage_options", "WordPressMagic360-license-page", "WordPress_Magic360_license_page");
        add_action('admin_print_scripts-' . $license_page, 'WordPress_Magic360_add_admin_src_to_license_page');
        $import_export_page = add_submenu_page("WordPressMagic360-config-page", "Backup / Restore", "Backup / Restore", "manage_options", "WordPressMagic360-import-export-page", "WordPress_Magic360_import_export_page");
        add_action('admin_print_scripts-' . $import_export_page, 'WordPress_Magic360_add_admin_src_to_import_export_page');
        global $submenu;
        $tmp = $submenu['WordPressMagic360-config-page'][0];
        $submenu['WordPressMagic360-config-page'][0] = $submenu['WordPressMagic360-config-page'][1];
        $submenu['WordPressMagic360-config-page'][1] = $tmp;
    }
}

function WordPress_Magic360_submenu_page() {
    if (array_key_exists('id', $_GET)) {
        include 'constructor_magic360/view/spinshortcodes.php';
    } else {
        include 'constructor_magic360/view/list.php';
    }
}

function WordPress_Magic360_add_admin_src_to_submenu_page() {
    if (array_key_exists('id', $_GET)) {
        wp_enqueue_script( 'WordPress_Magic360_admin_spinshortcodes_script' );
        wp_enqueue_script( 'magictoolbox_WordPress_Magic360_tool_script'    );
        wp_localize_script( 'WordPress_Magic360_admin_spinshortcodes_script', 'magictoolbox_WordPress_Magic360_admin_modal_object', array('ajax' =>  get_site_url().'/wp-admin/admin-ajax.php', 'nonce' => wp_create_nonce('magic-everywhere')) );
        wp_enqueue_media();
    } else {
        wp_enqueue_script( 'WordPress_Magic360_admin_list_script');
        wp_localize_script( 'WordPress_Magic360_admin_list_script', 'magictoolbox_WordPress_Magic360_admin_modal_object', array('ajax' =>  get_site_url().'/wp-admin/admin-ajax.php', 'nonce' => wp_create_nonce('magic-everywhere')) );
    }
}

function WordPress_Magic360_import_export_page() {
    include 'core/view/import_export/wordpress_Magic360_import_export.php';
}

function WordPress_Magic360_add_admin_src_to_import_export_page() {
    wp_enqueue_script( 'WordPress_Magic360_admin_import_export_script' );
    wp_localize_script( 'WordPress_Magic360_admin_import_export_script', 'magictoolbox_WordPress_Magic360_admin_modal_object', array('ajax' =>  get_site_url().'/wp-admin/admin-ajax.php', 'nonce' => wp_create_nonce('magic-everywhere')) );
}

function WordPress_Magic360_license_page() {
    include 'core/view/license/wordpress_Magic360_license.php';
}

function WordPress_Magic360_add_admin_src_to_license_page() {
    wp_enqueue_script( 'WordPress_Magic360_admin_license_script' );
    wp_localize_script( 'WordPress_Magic360_admin_license_script', 'magictoolbox_WordPress_Magic360_admin_modal_object', array('ajax' =>  get_site_url().'/wp-admin/admin-ajax.php', 'nonce' => wp_create_nonce('magic-everywhere')) );
}

function  magictoolbox_WordPress_Magic360_config_page($id) {
    // update_plugin_message_WordPress_Magic360();
    $settings = get_option("WordPressMagic360CoreSettings");
    $map = WordPressMagic360_getParamsMap();

    if(isset($_POST["submit"])) {
        $allSettings = array();
        /* save settings */
        foreach (WordPressMagic360_getParamsProfiles() as $profile => $name) {
            $GLOBALS['magictoolbox'][$id]->params->setProfile($profile);
            foreach($_POST as $name => $value) {
                if(preg_match('/magic360settings_'.ucwords($profile).'_(.*)/is',$name,$matches)) {
                    $GLOBALS['magictoolbox'][$id]->params->setValue($matches[1],$value);
                }
            }
            $allSettings[$profile] = $GLOBALS['magictoolbox'][$id]->params->getParams($profile);
        }

        update_option($id . "CoreSettings", $allSettings);
        $settings = $allSettings;
    }

    $corePath = preg_replace('/https?:\/\/[^\/]*/is', '', get_option("siteurl"));
    $corePath .= '/wp-content/'.preg_replace('/^.*?\/(plugins\/.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__))).'/core';

    ?>

    <div class="icon32" id="icon-options-general"><br></div>

    <h1>Magic 360 settings</h1>
    <p>Choose the default settings for your spins.</p>
    <br/>
    <p style="margin-right:20px; float:right; font-size:15px;">
        Resources:
            &nbsp;<a href="<?php echo admin_url().'admin.php?page=WordPressMagic360-shortcodes-page'; ?>" target="_self">Spins</a>&nbsp;|
            &nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/magic360/integration/',' configuration page resources settings link'); ?>" target="_blank">Documentation<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>&nbsp;|
            &nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/magic360/examples/',' configuration page resources examples link'); ?>" target="_blank">Examples<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>&nbsp;|
            &nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/contact/','configuration page resources support link'); ?>" target="_blank">Support<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>&nbsp;
            |&nbsp;<a href="<?php echo WordPressMagic360_url('http://www.magictoolbox.com/buy/magic360/','configuration page resources buy link'); ?>" target="_blank">Buy<span class="dashicons dashicons-share-alt2" style="text-decoration: none;line-height:1.3;margin-left:5px;"></span></a>
    </p>
    <form action="" method="post" id="magic360-config-form">
        <div id="tabs">

            <?php foreach (WordPressMagic360_getParamsProfiles() as $block_id => $block_name) {
                ?>
                <div id="tab-<?php echo $block_id; ?>">
                    <?php echo WordPressMagic360_get_options_groups($settings, $block_id, $map, $id); ?>
                </div>
            <?php } ?>
        </div>

        <p id="set-main-settings"><input type="submit" name="submit" class="button-primary" value="Save settings" />&nbsp;<a id="resetLink" style="color:red; margin-left:25px;" href="admin.php?page=WordPressMagic360-config-page&reset_settings=true">Reset to defaults</a></p>
    </form>

    <div style="font-size:12px;margin:5px auto;text-align:center;">Learn more about the <a href="http://www.magictoolbox.com/magic360_integration/" target="_blank">customisation options</a></div>

    <?php
}

function WordPressMagic360_get_options_groups ($settings, $profile = 'default', $map, $id) {
    $html = '';
    $toolAbr = '';
    $abr = explode(" ", strtolower("Magic 360"));

    foreach ($abr as $word) $toolAbr .= $word{0};

    $corePath = preg_replace('/https?:\/\/[^\/]*/is', '', get_option("siteurl"));
    $corePath .= '/wp-content/'.preg_replace('/^.*?\/(plugins\/.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__))).'/core';

    if (!isset($settings[$profile])) return false;
    $settings = $settings[$profile];

    $groups = array();
    $imgArray = array('zoom & expand','zoom&expand','yes','zoom','expand','swap images only','original','expanded','no','left','top left','top','top right', 'right', 'bottom right', 'bottom', 'bottom left'); //array for the images ordering

    $result = '';

    foreach($settings as $name => $s) {
        if (!isset($map[$profile][$s['group']]) || !in_array($s['id'], $map[$profile][$s['group']])) continue;
        if (preg_match('/watermark/', $name)) continue;
        if ($profile == 'product') {
            if ($s['id'] == 'page-status' && !isset($s['value'])) {
                $s['default'] = 'Yes';
            }
        }

        if (!isset($s['value'])) $s['value'] = $s['default'];

        if ($profile == 'product') {
            if ($s['id'] == 'page-status' && !isset($s['value'])) {
                $s['default'] = 'Yes';
            }
        }
        
        if (strtolower($s['id']) == 'direction') continue;
        if (strtolower($s['id']) == 'class') continue;
        if (strtolower($s['id']) == 'enabled-effect' || strtolower($s['id']) == 'class' || strtolower($s['id']) == 'include-headers' || strtolower($s['id']) == 'nextgen-gallery'  ) {
            $s['group'] = 'top';
        }
        

        if (!isset($groups[$s['group']])) {
            $groups[$s['group']] = array();
        }

        //$s['value'] = $GLOBALS['magictoolbox'][$id]->params->getValue($name);

        if (strpos($s["label"],'(')) {
            $before = substr($s["label"],0,strpos($s["label"],'('));
            $after = ' '.str_replace(')','',substr($s["label"],strpos($s["label"],'(')+1));
        } else {
            $before = $s["label"];
            $after = '';
        }
        if (strpos($after,'%')) $after = ' %';
        if (strpos($after,'in pixels')) $after = ' pixels';
        if (strpos($after,'milliseconds')) $after = ' milliseconds';

        if (isset($s["description"]) && trim($s["description"]) != '') {
            $description = $s["description"];
        } else {
            $description = '';
        }

        $html  .= '<tr>';
        $html  .= '<th width="30%">';
        $html  .= '<label for="magic360settings'.'_'.ucwords($profile).'_'. $name.'">'.$before.'</label>';

        if(($s['type'] != 'array') && isset($s['values'])) $html .= '<br/> <span class="afterText">' . implode(', ',$s['values']).'</span>';

        $html .= '</th>';
        $html .= '<td width="70%">';

        switch($s["type"]) {
            case "array":
                $rButtons = array();
                foreach($s["values"] as $p) {
                    $rButtons[strtolower($p)] = '<label><input type="radio" value="'.$p.'"'. ($s["value"]==$p?"checked=\"checked\"":"").' name="magic360settings'.'_'.ucwords($profile).'_'.$name.'" id="magic360settings'.'_'.ucwords($profile).'_'. $name.$p.'">';
                    $pName = ucwords($p);
                    if(strtolower($p) == "yes")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/yes.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "no")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/no.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "left")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/left.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "right")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/right.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "top")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/top.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "bottom")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/bottom.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "bottom left")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/bottom-left.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "bottom right")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/bottom-right.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "top left")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/top-left.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    elseif(strtolower($p) == "top right")
                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/top-right.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                    else {
                        // if (strtolower($p) == 'load,hover' || strtolower($p) == 'load,click') {
                        //     if (strtolower($p) == 'load,hover') $pl = 'Load & hover';
                        //     if (strtolower($p) == 'load,click') $pl = 'Load & click';
                        //         $rButtons[strtolower($p)] .= '<span>'.ucwords($pl).'</span></label>';
                        // } else {
                        //     $rButtons[strtolower($p)] .= '<span>'.ucwords($p).'</span></label>';
                        // } //TODO

                        // if (strtolower($p) == 'load,hover') $p = 'Load & hover';
                        // if (strtolower($p) == 'load,click') $p = 'Load & click';
                        // $rButtons[strtolower($p)] .= '<span>'.ucwords($p).'</span></label>';
                        $rButtons[strtolower($p)] .= '<span>'.ucwords(('load,hover' == $p || 'load,click' == $p) ? str_replace(',', ' & ', $p) : $p).'</span></label>';
                    }
                }
                foreach ($imgArray as $img){
                    if (isset($rButtons[$img])) {
                        $html .= $rButtons[$img];
                        unset($rButtons[$img]);
                    }
                }
                $html .= implode('',$rButtons);
                break;
            case "num":
                $html .= '<input  style="width:60px;" type="text" name="magic360settings'.'_'.ucwords($profile).'_'.$name.'" id="magic360settings'.'_'.ucwords($profile).'_'. $name.'" value="'.$s["value"].'" />';
                break;
            case "text":
                if (strtolower($s["value"]) == 'auto' ||
                    strtolower($s["value"]) == 'fit' ||
                    strpos($s["value"],'%') !== false ||
                    ctype_digit($s["value"])) {
                        $width = 'style="width:60px;"';
                } else {
                    $width = '';
                }
                if (strtolower($name) == 'message' || strtolower($name) == 'selector-path' || strtolower($name) == 'watermark') {
                    $width = 'style="width:95%;"';
                }
                $html .= '<input '.$width.' type="text" name="magic360settings'.'_'.ucwords($profile).'_'.$name.'" id="magic360settings'.'_'.ucwords($profile).'_'. $name.'" value="'.$s["value"].'" />';
                break;
            default:
                if (strtolower($name) == 'message' || strtolower($name) == 'selector-path') {
                    $width = 'style="width:95%;"';
                } else {
                    $width = '';
                }
                $html .= '<input '.$width.' type="text" name="magic360settings'.'_'.ucwords($profile).'_'.$name.'" id="magic360settings'.'_'.ucwords($profile).'_'. $name.'" value="'.$s["value"].'" />';
                break;
        }
        if ('autospin-speed' == $name && 'ms' == trim($after)) {
            $after = '<acronym title="milliseconds">'.$after.'</acronym>';
        }
        $html .= '<span class="afterText">'.$after.'</span>';
        if (!empty($description)) $html .= '<span class="help-block">'.$description.'</span>';
        $html .= '</td>';
        $html .= '</tr>';
        $groups[$s['group']][] = $html;
        $html = '';
    }
    $result .= '<div class="'.$toolAbr.'params">
                <table class="params" cellspacing="0">';
    $i = 0;
    $keys = array_keys($groups);

    if (isset($groups['top'])) { //move 'top' group to the top
        $top = $groups['top'];
        unset($groups['top']);
        array_unshift($groups, $top);
    }

    if (isset($groups['Miscellaneous'])) {
        $misc = $groups['Miscellaneous'];
        unset($groups['Miscellaneous']);
        $groups['Miscellaneous'] = $misc; //move Miscellaneous to bottom
    }

    foreach ($groups as $name => $group) {
        if ($name == '0') {
            $name = '';
            $group = preg_replace('/(^.*)(Class\sName)(.*?<span>)(All)(<\/span>.*?<span>)(Magic360)(<\/span>.*)/is','$1Apply effect to all image links$3Yes$5No$7',$group);
        }
        if ($name == $keys[count($keys)-1]) {
            $group[count($group)-1] = str_replace('<tr','<tr class="last"',$group[count($group)-1]); //set "last" class
        }
        if (is_array($group)) {
            foreach ($group as $g) {
                if (++$i%2==0) { //set stripes
                    if (strpos($g,'class="last"')) {
                        $g = str_replace('class="last"','class="back last"',$g);
                    } else {
                        $g = str_replace('<tr','<tr class="back"',$g);
                    }
                }
                $result .= $g;
            }
        }
    }
    $result .= '</table> </div>';

    return $result;
}



function magictoolbox_WordPress_Magic360_styles() {
    if(!defined('MAGICTOOLBOX_MAGIC360_HEADERS_LOADED')) {
        $plugin = $GLOBALS['magictoolbox']['WordPressMagic360'];

		if (function_exists('plugins_url')) {
			$core_url = plugins_url();
		} else {
			$core_url = get_option("siteurl").'/wp-content/plugins';
		}


        $path = preg_replace('/^.*?\/plugins\/(.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__)));

        $headers = $plugin->getHeadersTemplate($core_url."/{$path}/core");

        echo $headers;
        define('MAGICTOOLBOX_MAGIC360_HEADERS_LOADED', true);
    }
}




function  magictoolbox_WordPress_Magic360_create($content) {
    global $magictoolbox_Magic360_page_has_tool;

    $plugin = $GLOBALS['magictoolbox']['WordPressMagic360'];


    /*set watermark options for all profiles START */
    $defaultParams = $plugin->params->getParams('default');
    $wm = array();
    $profiles = $plugin->params->getProfiles();
    foreach ($defaultParams as $id => $values) {
	if (($values['group']) == 'Watermark') {
	    $wm[$id] = $values;
	}
    }
    foreach ($profiles as $profile) {
	$plugin->params->appendParams($wm,$profile);
    }
    /*set watermark options for all profiles END */

    $toolPatern = "<a\s+[^>]*class\s*=[^>]*\"Magic360[^>]*\"[^>]*>\s*<img[^>]*>\s*<\s*\/\s*a>";

    /*$pattern = "<img([^>]*)(?:>)(?:[^<]*<\/img>)?";
    $pattern = "(?:<a([^>]*)>.*?){$pattern}(.*?)(?:<\/a>)";*/
    $pattern = "(?:<a([^>]*)>)[^<]*<img([^>]*)(?:>)(?:[^<]*<\/img>)?(.*?)[^<]*?<\/a>";


    $oldContent = $content;






    if (!$plugin->params->checkValue('template','original') && $plugin->type == 'standard' && isset($GLOBALS['magictoolbox']['Magic360']['main'])) {
        // template helper class
        require_once(dirname(__FILE__) . '/core/magictoolbox.templatehelper.class.php');
        MagicToolboxTemplateHelperClass::setPath(dirname(__FILE__).'/core/templates');
        MagicToolboxTemplateHelperClass::setOptions($plugin->params);
        if (!WordPress_Magic360_page_check('WordPress')) { //do not render thumbs on category pages
            $thumbs = WordPress_Magic360_get_prepared_selectors();
        } else {
            $thumbs = array();
        }

        if (isset($GLOBALS['MAGICTOOLBOX_'.strtoupper('Magic360').'_SELECTORS']) && is_array($GLOBALS['MAGICTOOLBOX_'.strtoupper('Magic360').'_SELECTORS'])) {
	    $thumbs = array_merge($thumbs,$GLOBALS['MAGICTOOLBOX_'.strtoupper('Magic360').'_SELECTORS']);
        }



        if (!isset($GLOBALS['magictoolbox']['prods_info']['product_id']) && isset($post_id)) {
	    $GLOBALS['magictoolbox']['prods_info']['product_id'] = $post_id;
	} else if (!isset($GLOBALS['magictoolbox']['prods_info']['product_id']) && !isset($post_id)) {
	    $GLOBALS['magictoolbox']['prods_info']['product_id'] = '';
	}
        $scroll = WordPress_Magic360_LoadScroll($plugin);
        $html = MagicToolboxTemplateHelperClass::render(array(
            'main' => $GLOBALS['magictoolbox']['Magic360']['main'],
            'thumbs' => (count($thumbs) >= 1) ? $thumbs : array(),
            'magicscrollOptions' => $scroll ? $scroll->params->serialize(false, '', 'product-magicscroll-options') : '',
            'pid' => $GLOBALS['magictoolbox']['prods_info']['product_id'],
        ));



        $content = str_replace('MAGICTOOLBOX_PLACEHOLDER', $html, $content);
    } else if ($plugin->params->checkValue('template','original') || $plugin->type != 'standard') {
        if (isset($GLOBALS['magictoolbox']['Magic360'])) {
            $html = $GLOBALS['magictoolbox']['Magic360']['main'];
            $content = str_replace('MAGICTOOLBOX_PLACEHOLDER', $html, $content);
        }
    }




    if (!$magictoolbox_Magic360_page_has_tool) {
        if (preg_match("/{$toolPatern}/is", $content)) {
            $magictoolbox_Magic360_page_has_tool = true;
        }
    }

    return $content;
}

function magictoolbox_WordPress_Magic360_key_sort($a, $b){
    return strnatcasecmp(basename($a['img']),basename($b['img']));
}




function  magictoolbox_WordPress_Magic360_callback($matches) {
    $plugin = $GLOBALS['magictoolbox']['WordPressMagic360'];
    $title = "";
    $float = "";






    return $result;

}





function WordPress_Magic360_get_post_attachments()  {
    $args = array(
            'post_type' => 'attachment',
            'numberposts' => '-1',
            'post_status' => null,
            'post_parent' => $post_id
        );

    $attachments = get_posts($args);
    return $attachments;
}








function WordPressMagic360_url ($url,$position) {

    if ('commercial' == get_tool_version_WordPress_Magic360()) {
	$utm_source = 'CommercialVerison';
    } else {
	if (magictoolbox_WordPress_Magic360_get_data_from_db()) {
	    $utm_source = 'CommercialVersion';
	} else {
	    $utm_source = 'TrialVersion';
	}
    }

    $utm_medium = 'WordPress';
    $utm_content = preg_replace('/\s+/is','-',trim($position));
    $utm_campaign = 'Magic360';

    $link = $url.'?utm_source='.$utm_source.'&utm_medium='.$utm_medium.'&utm_content='.$position.'&utm_campaign='.$utm_campaign;

    return $link;
}

function WordPressMagic360_params_map_check ($profile = 'default', $group, $parameter) {
    $map = WordPressMagic360_getParamsMap();
    if (isset($map[$profile][$group][$parameter])) return true;
    return false;
}
function WordPressMagic360_getParamsMap () {
    $map = array(
		'default' => array(
			'Magic 360' => array(
				'rows',
				'columns',
				'magnifier-shape',
				'magnify',
				'magnifier-width',
				'fullscreen',
				'spin',
				'autospin',
				'autospin-direction',
				'autospin-speed',
				'autospin-start',
				'autospin-stop',
				'sensitivityX',
				'sensitivityY',
				'mousewheel-step',
				'smoothing',
				'initialize-on',
				'reverse-column',
				'reverse-row',
				'column-increment',
				'row-increment',
			),
			'Positioning and Geometry' => array(
				'square-images',
			),
			'Miscellaneous' => array(
				'include-headers',
				'class',
				'show-message',
				'message',
				'loading-text',
				'fullscreen-loading-text',
				'hint',
				'hint-text',
				'mobile-hint-text',
				'start-column',
				'start-row',
				'loop-column',
				'loop-row',
				'imagemagick',
				'image-quality',
			),
			'Watermark' => array(
				'watermark',
				'watermark-max-width',
				'watermark-max-height',
				'watermark-opacity',
				'watermark-position',
				'watermark-offset-x',
				'watermark-offset-y',
			),
		),
	);
    return $map;
}

function WordPressMagic360_getParamsProfiles () {

    $blocks = array(
		'default' => 'General',
	);

    return $blocks;
}

function WordPressMagic360_welcome_license_do_redirect() {
  // Bail if no activation redirect
    if ( ! get_transient( 'WordPress_Magic360_welcome_license_activation_redirect' ) ) {
    return;
  }

  // Delete the redirect transient
  delete_transient( 'WordPress_Magic360_welcome_license_activation_redirect' );

  // Bail if activating from network, or bulk
  if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
    return;
  }

  // Redirect to bbPress about page
  wp_safe_redirect( add_query_arg( array( 'page' => 'WordPressMagic360-license-page' ), admin_url( 'admin.php' ) ) );

}

?>
