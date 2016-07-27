<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Ajaxzoom_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_ajaxzoom_ajax' ), 0 );
		self::add_ajax_events();
	}


	/**
	 * Set AJAXZOOM AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['ajaxzoom-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'AJAXZOOM_DOING_AJAX' ) ) {
				define( 'AJAXZOOM_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for AJAXZOOM Ajax Requests
	 * @since 2.5.0
	 */
	private static function ajaxzoom_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for AJAXZOOM Ajax request and fire action.
	 */
	public static function do_ajaxzoom_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['ajaxzoom-ajax'] ) ) {
			$wp_query->set( 'ajaxzoom-ajax', sanitize_text_field( $_GET['ajaxzoom-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'ajaxzoom-ajax' ) ) {
			self::ajaxzoom_ajax_headers();
			do_action( 'ajaxzoom_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// ajaxzoom_EVENT => nopriv
		$ajax_events = array(
			'add_set'		=> false,
			'get_images'	=> false,
			'delete_set'	=> false,
			'set_360_status'=> false,
			'save_settings'	=> false,
			'upload_image'	=> false,
			'upload_image2d'=> false,
			'get_crop_json'	=> false,
			'save_crop_json' => false,
			'delete_product_image_360' => false,
			'delete_product_image_2d' => false,
			'save_2d_variations' => false,
			'save_2d_sort'	=> false
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_ajaxzoom_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_ajaxzoom_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// AJAXZOOM AJAX can be used for frontend ajax requests
				add_action( 'ajaxzoom_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function save_crop_json() {		
		echo Ajaxzoom::set_crop_json( $_GET['id_360'], $_POST['json'] );
		exit;
	}

	public static function get_crop_json() {
		echo AjaxZoom::get_crop_json( absint( $_GET['id_360'] ) );
		exit;
	}

	public static function delete_set() {
		global $wpdb;

		ob_start();

		$id_360set = absint( $_POST['id_360set'] );
		$id_360 = Ajaxzoom::get_set_parent( $id_360set );
		$id_product = Ajaxzoom::get_set_product( $id_360 );

		Ajaxzoom::delete_set( $id_360set );

		wp_send_json( array(
			'id_360set' => $id_360set,
			'id_360' => $id_360,
			'path' => Ajaxzoom::uri() . '/ajaxzoom/pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set,
			'removed' => ( $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360` WHERE id_360 = " . $id_360 ) ? 0 : 1 ),
			'confirmations' => array( __( 'The 360 image set was successfully removed.', 'ajaxzoom' ) )
		) );		
	}

	public static function set_360_status() {
		global $wpdb;

		ob_start();

		$status = absint( $_POST['status'] );
		$id_360 = absint( $_POST['id_360'] );
		Ajaxzoom::set_360_status( $id_360, $status );

		wp_send_json( array(
			'status' => 'ok',
			'confirmations' => array( __( 'The status has been updated.', 'ajaxzoom' ) )
		) );		
	}

	public static function upload_image() {
		global $wpdb;

		ob_start();

		$id_product = absint( $_POST['id_product'] );
		$id_360set = absint( $_POST['id_360set'] );
		
		$id_360 = Ajaxzoom::get_set_parent( $id_360set );
		$folder = Ajaxzoom::create_product_360_folder( $id_product, $id_360set );

		$file = $_FILES['file'];

		$file['name'] = $id_product . '_' . $id_360set . '_' . $file['name'];
		$file['name'] = Ajaxzoom::img_name_filter( $file['name'] );

		$tmp = explode( '.', $file['name'] );
		$ext = end( $tmp );
		$name = preg_replace( '|\.' . $ext . '$|', '', $file['name'] );
		$dst = $folder . '/' . $file['name'];
		rename( $file['tmp_name'], $dst );

		$thumb = Ajaxzoom::uri() . '/ajaxzoom/axZm/zoomLoad.php';
		$thumb .= '?azImg=' . Ajaxzoom::uri() .'/ajaxzoom/pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set . '/' . $file['name'] . '&width=100&height=100&qual=90';

		wp_send_json( array(
			'status' 		=> 'ok',
			'id' 			=> $name,
			'id_product' 	=> $id_product,
			'id_360' 		=> $id_360,
			'id_360set' 	=> $id_360set,
			'path' 			=> $thumb,
			'confirmations' => array( __( 'The file has been uploaded.', 'ajaxzoom' ) )
		) );
	}

	public static function upload_image2d() {
		global $wpdb;

		ob_start();

		$id_product = absint( $_POST['id_product'] );

		$wpdb->query( "INSERT INTO `{$wpdb->prefix}ajaxzoom2dimages` (id_product) VALUES('$id_product')" );
		$id = $wpdb->insert_id;

		$file = $_FILES['file'];

		$file['name'] = $id . '_' . $id_product . '_' . $file['name'];
		$file['name'] = Ajaxzoom::img_name_filter( $file['name'] );

		$dst_dir = Ajaxzoom::dir() . 'pic/2d/' . $id_product;

		if ( !is_dir( $dst_dir ) ) {
			mkdir( $dst_dir );
			@chmod( $dst_dir, 0775 );
			file_put_contents( $dst_dir . '/.htaccess', 'deny from all' );
		}

		$dst = $dst_dir . '/' . $file['name'];
		rename( $file['tmp_name'], $dst );

		$thumb = Ajaxzoom::uri() . '/ajaxzoom/axZm/zoomLoad.php';
		$thumb .= '?azImg=' . Ajaxzoom::uri() .'/ajaxzoom/pic/2d/' . $id_product . '/' . $file['name'] . '&width=100&height=100&qual=90';

		$wpdb->query( "UPDATE `{$wpdb->prefix}ajaxzoom2dimages` SET image =  '" . $file['name'] . "' WHERE id = $id" );

		wp_send_json( array(
			'status' 		=> 'ok',
			'id' 			=> $id,
			'id_product' 	=> $id_product,
			'path' 			=> $thumb,
			'confirmations' => array( __( 'The file has been uploaded.', 'ajaxzoom' ) )
		) );
	}

	public static function get_images() {
		global $wpdb;

		$id_product = absint( $_POST['id_product'] );
		$id_360set = absint( $_POST['id_360set'] );
		$images = Ajaxzoom::get_360_images( $id_product, $id_360set );

		wp_send_json( array( 
			'status' => 'ok',
			'id_product' => $id_product,
			'id_360set' => $id_360set,
			'images' => $images
		) );		
	}

	public static function add_set() {
		global $wpdb;

		ob_start();

		$id_product = absint( $_POST['id_product'] );
		$name = sanitize_text_field( $_POST['name'] );
		
		if ( empty( $name ) ) {
			$name = 'Unnamed '.uniqid( getmypid() );
		}

		$existing = isset( $_POST['existing'] ) ? absint( $_POST['existing'] ) : 0;
		$zip = sanitize_text_field( $_POST['zip'] );
		$delete = isset( $_POST['delete'] ) ? $_POST['delete'] : '';
		$arcfile = isset( $_POST['arcfile'] ) ? $_POST['arcfile'] : '';
		$new_id = '';
		$new_name = '';
		$new_settings = '';

		if ( !empty( $existing ) ) {
			$id_360 = $existing;
			$tmp = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360` WHERE id_360 = '{$id_360}'" );
			$name = $tmp['name'];
		} else {

			$new_settings = $settings = '{"position":"first","spinReverse":"true","spinBounce":"false","spinDemoRounds":"3","spinDemoTime":"4500"}';

			$wpdb->query( "INSERT INTO `{$wpdb->prefix}ajaxzoom360` (id_product, name, settings, status) 
				VALUES('{$id_product}', '" . esc_sql( $name ) . "', '{$settings}', '" . ( $zip == 'true' ? 1 : 0 ) . "')" );

			$id_360 = $wpdb->insert_id;
			$new_id = $id_360;
			$new_name = $name;
		}

		$wpdb->query( "INSERT INTO `{$wpdb->prefix}ajaxzoom360set` (id_360, sort_order) 
			VALUES('{$id_360}', 0)" );

		$id_360set = $wpdb->insert_id;

		$sets = array();

		if ( $zip == 'true' ) {
			$sets = Ajaxzoom::add_images_arc( $arcfile, $id_product, $id_360, $id_360set, $delete );
		}

		wp_send_json( array(
			'status' => '0',
			'name' => $name,
			'path' => Ajaxzoom::uri() . '/ajaxzoom/no_image-100x100.jpg',
			'sets' => $sets,
			'id_360' => $id_360,
			'id_product' => $id_product,
			'id_360set' => $id_360set,
			'confirmations' => array( __( 'The image set was successfully added.' , 'ajaxzoom' ) ),
			'new_id' => $new_id,
			'new_name' => $new_name,
			'new_settings' => ''
		) );

		die();
	}

	public static function save_settings() {
		global $wpdb;

		ob_start();

		$id_product 	= absint( $_POST['id_product'] );
		$id_360 		= absint( $_POST['id_360'] );
		$active 		= absint( $_POST['active'] );
		$names 			= explode( '|', sanitize_text_field( $_POST['names'] ) );
		$values 		= explode( '|', sanitize_text_field( $_POST['values'] ) );
		$combinations 	= explode( '|', sanitize_text_field( $_POST['combinations'] ) );
		$count_names 	= count( $names );
		$settings 		= array();

		for ( $i = 0; $i < $count_names; $i++ ) {
			$key = $names[$i];
			$value = $values[$i];
			
			if ( $key != 'name_placeholder' && !empty( $key ) ) {
				$settings[ $key ] = $value;
			}
		}

		$wpdb->query( "UPDATE `{$wpdb->prefix}ajaxzoom360` 
			SET settings = '" . json_encode($settings) . "', combinations = '" . implode( ',', $combinations ) . "' 
			WHERE id_360 = $id_360" );

		// update dropdown
		$sets_groups = Ajaxzoom::get_groups( $id_product );
		$select = '<select id="id_360" name="id_360"><option value="">Select</option>';
		foreach ( $sets_groups as $group ) {
			$select .= '<option value="' . $group['id_360'] . '" ';
			$select .= 'data-settings="' . urlencode( $group['settings'] ) . '" ';
			$select .= 'data-combinations="[' . urlencode( $group['combinations'] ) . ']">' . $group['name'] . '</option>';
		}
		$select .= '</select>';

		// active/not active
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}ajaxzoomproducts` WHERE id_product = $id_product" );

		if ( $active == 0 ) {
			$wpdb->query( "INSERT INTO  `{$wpdb->prefix}ajaxzoomproducts` (id_product) VALUES ($id_product)" );
		}

		wp_send_json( array(
			'status' => 'ok',
			'select' => $select,
			'id_product' => $id_product,
			'id_360' => $id_360,
			'confirmations' => array( __( 'The settings has been updated.', 'ajaxzoom' ) )
		) );

		die();
	}

	public static function delete_product_image_360() {

		$id_image = sanitize_text_field( $_POST['id_image'] );
		$id_product = absint( $_POST['id_product'] );
		$id_360set = absint( $_POST['id_360set'] );
		$id_360 = Ajaxzoom::get_set_parent( $id_360set );
		$tmp = explode( '&', $_POST['ext'] );
		$ext = reset( $tmp );
		$filename = $id_image.'.'.$ext;

		$dst = Ajaxzoom::dir() . 'pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set . '/' . $filename;
		unlink( $dst );

		AjaxZoom::delete_image_az_cache( $filename );

		wp_send_json( array(
			'status' => 'ok',
			'content' => (object)array( 'id' => $id_image ),
			'confirmations' => array( __( 'The image was successfully deleted.', 'ajaxzoom' ) )
		) );
	}

	public static function delete_product_image_2d() {
		
		global $wpdb;

		$id = intval( $_POST['id_image'] );
		$id_product = absint( $_POST['id_product'] );

		$image = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom2dimages` WHERE id = " . $id . " ORDER BY sort_order", ARRAY_A );

		$dst = Ajaxzoom::dir() . 'pic/2d/' . $id_product . '/' . $image['image'];
		if ( file_exists( $dst) ) {
			unlink( $dst );
		}

		AjaxZoom::delete_image_az_cache( $image['image'] );

		$wpdb->get_row( "DELETE FROM `{$wpdb->prefix}ajaxzoom2dimages` WHERE id = " . $id );

		wp_send_json( array(
			'status' => 'ok',
			'content' => (object)array( 'id' => $id ),
			'confirmations' => array( __( 'The image was successfully deleted.', 'ajaxzoom' ) )
		) );
	}

	public static function save_2d_variations() {
		global $wpdb;

		$id = intval( $_POST['id_image'] );
		$id_product = absint( $_POST['id_product'] );

		$wpdb->get_row( "UPDATE `{$wpdb->prefix}ajaxzoom2dimages` SET variations = '" . implode( ',', $_POST['variations'] ) . "' WHERE id = " . $id);

		wp_send_json( array(
			'status' => 'ok',
			'id' => $id,
			'confirmations' => array( __( 'The variations has been updated for this image.', 'ajaxzoom' ) )
		) );
	}

	public static function save_2d_sort() {
		global $wpdb;
		
		$i = 0;
		foreach ($_POST['sort'] as $id) {
			$wpdb->get_row( "UPDATE `{$wpdb->prefix}ajaxzoom2dimages` SET sort_order = '$i' WHERE id = " . $id);
			$i++;
		}

		wp_send_json( array(
			'status' => 'ok',
			'id' => $id,
			'confirmations' => array( __( 'The sort order has been updated.', 'ajaxzoom' ) )
		) );
	}
}

Ajaxzoom_AJAX::init();
