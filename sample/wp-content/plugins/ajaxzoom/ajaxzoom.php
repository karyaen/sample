<?php
/**
 * @package Ajaxzoom
 * @version 1.0.3
 */
/*
Plugin Name: AJAX-ZOOM
Plugin URI: http://www.ajax-zoom.com/index.php?cid=modules&module=woocommerce
Description: Combination of responsive mouseover zoom, 360 degree player with deep zoom, thumbnail slider and 360 degree "Product Tour" for WooCommerce product details view.
Author: AJAX-ZOOM
Text Domain: ajaxzoom
Domain Path: /languages
Version: 1.0.3
Author URI: http://www.ajax-zoom.com/
*/
/*
License: Commercial, http://www.ajax-zoom.com/index.php?cid=download
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if not admin
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function elog( $str ) {
		error_log( "\n$str", 3, '_log.txt' );
	}

	global $ajaxzoom_db_version;

	$ajaxzoom_db_version = '1.0';

	include_once( 'includes/class.ajaxzoom.php' );
	include_once( 'includes/class.ajaxzoom-ajax.php' );

	$path = ABSPATH . 'wp-content/plugins/ajaxzoom/includes/admin';
	set_include_path( get_include_path() . PATH_SEPARATOR . $path );

	/**
	 * Install db schema
	 */
	register_activation_hook( __FILE__, array( 'ajaxzoom', 'install' ) );

	/**
	 * Create Ajaxzoom settings in woocommerce
	 */
	add_filter( 'woocommerce_get_settings_pages', array( 'ajaxzoom', 'woocommerce_get_settings_pages' ) );

	/**
	 * Special field for licenses
	 */
	add_action( 'woocommerce_admin_field_ajaxzoom_licenses', array( 'ajaxzoom', 'woocommerce_admin_field_ajaxzoom_licenses' ) );

	/**
	 * Save licenses
	 */
	add_action( 'woocommerce_update_options_ajaxzoom', array( 'ajaxzoom', 'save_ajaxzoom_licenses' ) );

	/**
	 * Load JS/CSS
	 */
	add_action( 'admin_enqueue_scripts', array( 'ajaxzoom', 'media_admin' ) );

	/**
	 * Load JS/CSS
	 */
	add_action( 'wp_enqueue_scripts', array( 'ajaxzoom', 'media' ) ); 

	/**
	 * Inject the AJAX-ZOOM into product page on the Front-End
	 */
	add_action( 'woocommerce_product_thumbnails', array('ajaxzoom', 'show' ), 30);

	/**
	 * Inject the AJAX-ZOOM into product page on the Backend-End
	 */
	add_action( 'add_meta_boxes', array( 'ajaxzoom', 'backend_output' ), 40 );

	/**
	 * Delete Product
	 */
	add_action( 'before_delete_post', array( 'ajaxzoom', 'delete_product' ) );

	/**
	 * Add links to plugin page
	 */
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );

	function add_action_links( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=ajaxzoom' ) . '">Settings</a>'
		);
		return array_merge( $links, $mylinks );
	}

	/**
	 * Add links to plugin page
	 */
	add_filter( 'plugin_row_meta', 'add_meta_links', 10, 2 );

	function loader_installed(){
		$extensions = get_loaded_extensions();
		$ioncube = false;
		$sourceguardian = false;

		foreach ($extensions as $v) {
			if ( stristr($v, 'ioncube') ) {
				$ioncube = true;
			}

			if ( stristr($v, 'sourceguardian') ) {
				$sourceguardian = true;
			}
		}

		if ( $ioncube || $sourceguardian ) {
			return true;
		}

		return false;
	}

	function add_meta_links( $links, $file ) {
		$my_plugin = plugin_basename(__FILE__);
		
		if ( $file == $my_plugin ) {
			$has_loader = loader_installed();
			$has_axzm = is_dir( dirname(__FILE__) . '/axZm' );
			$err = '';
			
			if ( ! $has_loader || ! $has_axzm ) {
				$err = '<div class="update-nag" style="border-left-color: red;">';

				$err .= '<h2 style="margin-top: 0; margin-bottom: 5px;">Some minor problems. You can solve them, please read!</h2>';

				$err .= '<ol style="font-size: inherit;">';

				if ( ! $has_loader ) {
					$err .= '<li>Ioncube loaders are not installed on this server. Please install 
						<a href="https://www.ioncube.com/loaders.php" target="_blank">Ioncube loaders</a> to make AJAX-ZOOM work!</li>';
				}

				if ( ! $has_axzm ) {
					$err .= '<li>During activation the plugin should have downloaded the latest version of AJAX-ZOOM main scripts instantly. For some reason this did not happen. 
						Do not worry, please <a href="http://www.ajax-zoom.com/index.php?cid=download" target="_blank">download</a> 
						AJAX-ZOOM on your own and extract (e.g. upload over FTP) the contents of /axZm folder into 
						/wp-content/plugins/ajaxzoom/axZm manually!</li> 
					';
				}

				$err .= '</ol>';

				$err .= '<span style="display: block; margin-top: 5px;">If you have any questions do not hesitate to 
					<a href="http://www.ajax-zoom.com/index.php?cid=contact" target="_blank">contact</a> AJAX-ZOOM support. Thanks.</span>';

				$err .= '</div>';
			}

			$meta_links = array(
				'<a href="http://www.ajax-zoom.com/index.php?cid=download#buyLicense">License</a>', 
				'<a href="http://www.ajax-zoom.com/index.php?cid=contact">Support</a>'
			);

			if ($err) {
				array_push( $meta_links, $err );
			}
			
			return array_merge( $links, $meta_links );
		}
		return $links;
	}

}