<?php

class Ajaxzoom {

	static $axzmh;
	static $zoom;
	static $show_az_save;

	public static function delete_product($post_id) {

		if ( $product = wc_get_product( $post_id ) ) {

			$sets = self::get_sets( $post_id );

			foreach ( $sets as $set ) {
				self::delete_set( $set['id_360set'] );
			}

			$images = self::get_2d_images( $post_id );
			foreach ( $images as $image ) {
				self::delete_image_2d( $image['id'], $post_id );
			}
		}
	}

	public static function delete_image_2d($id, $id_product) {
		
		global $wpdb;

		$image = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom2dimages` WHERE id = " . $id . " ORDER BY sort_order", ARRAY_A );

		$dst = Ajaxzoom::dir() . 'pic/2d/' . $id_product . '/' . $image['image'];
		if ( file_exists( $dst) ) {
			unlink( $dst );
		}

		AjaxZoom::delete_image_az_cache( $image['image'] );

		$wpdb->get_row( "DELETE FROM `{$wpdb->prefix}ajaxzoom2dimages` WHERE id = " . $id );
	}

	public static function meta_box() {
		global $post;

		$product_id = $post->ID;

		if ( $post->filter == 'edit' ) { // only when edit (not new)
			$groups = self::get_groups( $product_id );
			$sets = self::get_sets( $product_id );
			$files = self::get_arc_list();
			$active = self::is_active( $product_id );
			$uri = self::uri();
			$variations = self::get_variations( $product_id );

			include( self::dir() . 'templates/tab.php' );
		}
	}

	public static function meta_box_2d() {
		global $post;

		$product_id = $post->ID;

		if ( $post->filter == 'edit' ) { // only when edit (not new)
			$uri = self::uri();
			$images = self::get_2d_images( $product_id );
			$variations = self::get_variations( $product_id );

			include( self::dir() . 'templates/tab2d.php' );
		}
	}

	public static function get_2d_images( $id_product ) {
		global $wpdb;

		$images = $wpdb->get_results( "SELECT * 
			FROM `{$wpdb->prefix}ajaxzoom2dimages`
			WHERE id_product = '{$id_product}' 
			ORDER BY sort_order", ARRAY_A );

		foreach($images as &$image) {
			$thumb = self::uri().'/ajaxzoom/axZm/zoomLoad.php';
			$thumb .= '?qq=1&azImg=' . self::uri() . '/ajaxzoom/pic/2d/' . $id_product . '/' . $image['image'];
			$thumb .= '&width=100&height=100&thumbMode=contain';
			$image['thumb'] = $thumb;
		}

		return $images;
	}

	public static function backend_output() {
		add_meta_box( 'ajaxzoom', __( 'AJAX-ZOOM 360', 'ajaxzoom' ), 'Ajaxzoom::meta_box', 'product', 'normal' );
		add_meta_box( 'ajaxzoom2d', __( 'AJAX-ZOOM Product Variation Images', 'ajaxzoom' ), 'Ajaxzoom::meta_box_2d', 'product', 'normal' );
	}

	public static function show() {
		global $product;
		if ( ! $product || ! self::show_az()){
			return;
		}

		$config = self::config();
		$ajaxzoom_imagesJSON = self::images_json();
		$ajaxzoom_images360JSON = self::images_360_json( $product->id );
		$axZmPath = self::uri() . '/ajaxzoom/axZm/';
		$variations_json = self::images_360_json_per_variation( $product->id );
		$variations_2d_json = self::images_json_per_variation( $product->id );
		include( self::dir() . 'templates/ajaxzoom.php' );
	}

	public static function get_variations_ids( $product_id ) {
		$variations = array();
		$res = array();

		$args = apply_filters( 'woocommerce_ajax_admin_get_variations_args', array(
			'post_type'      => 'product_variation',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => 100,
			'paged'          => 1,
			'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'DESC' ),
			'post_parent'    => $product_id
		), $product_id );

		$variations = get_posts( $args );
		foreach ( $variations as $variation ) {
			array_push( $res, $variation->ID );
		}

		return $res;
	}

	public static function get_variations( $product_id ) {

		$variations = array();
		
		$args = apply_filters( 'woocommerce_ajax_admin_get_variations_args', array(
			'post_type'      => 'product_variation',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => 100,
			'paged'          => 1,
			'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'DESC' ),
			'post_parent'    => $product_id
		), $product_id );

		$variations = get_posts( $args );

		$loop = 0;

		if ( $variations ) {

			foreach ( $variations as $variation ) {
				$variation_id     = absint( $variation->ID );
				$variation_meta   = get_post_meta( $variation_id );
				$variation_data   = array();
				$shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
				$variation_fields = array(
					'_sku'                   => '',
					'_variation_description' => ''
				);

				foreach ( $variation_fields as $field => $value ) {
					$variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
				}

				$variation_data = array_merge( $variation_data, wc_get_product_variation_attributes( $variation_id ) );

				
				$variation->data = $variation_data;
				$variation->attr = implode('-', wc_get_product_variation_attributes( $variation_id ));

				$loop++;
			}
		}

		$res = array();

		foreach ( $variations as $variation ) {
			$res[ $variation->ID ] = 'Variation #' . $variation->ID . ' ' . $variation->data['_sku'] . ' ' . $variation->attr;
		}

		return $res;
	}

	public static function get_arc_list() {
		$files = array();

		if ( $handle = opendir( self::dir() . 'zip/' ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				if ( $entry != '.' && $entry != '..' && ( strtolower( substr( $entry, -3) ) == 'zip'
					|| is_dir( self::dir() . 'zip/' . $entry ) ) ) {
					array_push( $files, $entry );
				}
			}
			closedir( $handle );
		}

		return $files;
	}

	public static function add_images_arc( $arcfile, $id_product, $id_360, $id_360set, $delete = '' ) {
		global $wpdb;

		set_time_limit( 0 );

		$path = self::dir() . 'zip/' . $arcfile;
		$dst = is_dir( $path ) ? $path : self::extract_arc( $path );

		// when extract zip archive return false
		if ( $dst == false ) {
			return false;
		}

		if( !is_writable( $dst ) ) {
			@chmod( $dst, 0777 );
		}

		$data = self::get_folder_data( $dst );
		
		$name = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360` WHERE id_360 = ".(int)$id_360 )->name;

		$thumb = self::uri().'/ajaxzoom/axZm/zoomLoad.php';
		$thumb .= '?qq=1&azImg360=' . self::uri() . '/ajaxzoom/pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set;
		$thumb .= '&width=100&height=100&thumbMode=contain';

		$sets = array( array(
			'name' => $name,
			'path' => $thumb,
			'id_360set' => $id_360set,
			'id_360' => $id_360,
			'status' => '1'
		) );

		$count_data_folders = count( $data['folders'] );

		$move = is_dir( $path ) ? false : true;

		if ( $count_data_folders == 0 ) { // files (360)
			self::copy_images( $id_product, $id_360, $id_360set, $dst, $move );
		} elseif ( $count_data_folders == 1 ) { // 1 folder (360)
			self::copy_images( $id_product, $id_360, $id_360set, $dst.'/'.$data['folders'][0], $move );
		} else {
			// 3d
			self::copy_images( $id_product, $id_360, $id_360set, $dst.'/'.$data['folders'][0], $move );

			// checkr - $i <= $count_data_folders
			for ( $i = 1; $i < $count_data_folders; $i++ ) {
				$wpdb->query("INSERT INTO `{$wpdb->prefix}ajaxzoom360set` (id_360, sort_order) VALUES('{$id_360}', 0)");

				$id_360set = $wpdb->insert_id;

				self::copy_images( $id_product, $id_360, $id_360set, $dst . '/' . $data['folders'][$i], $move );

				$thumb = self::uri() . '/ajaxzoom/axZm/zoomLoad.php';
				$thumb .= '?qq=1&azImg360=' . self::uri() . '/ajaxzoom/pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set;
				$thumb .= '&width=100&height=100&thumbMode=contain';

				$sets[] = array(
					'name' => $name,
					'path' => $thumb,
					'id_360set' => $id_360set,
					'id_360' => $id_360,
					'status' => '1'
				);
			}
		}

		// delete temp directory which was created when zip extracted
		if ( !is_dir( $path ) ) {
			self::delete_directory( $dst );
		}

		// delete the sourece file (zip/dir) if checkbox is checked
		if ( $delete == 'true' ) {
			if ( is_dir( $path ) ) {
				self::delete_directory( $dst );
			} else {
				unlink( $path );
			}
		}
		return $sets;
	}

	public static function copy_images( $id_product, $id_360, $id_360set, $path, $move ) {
		if ( !$id_360 && !$id_360set ) // useless code to validate
			return;

		$files = self::get_files_from_folder( $path );
		$folder = self::create_product_360_folder( $id_product, $id_360set );

		foreach ( $files as $file ) {
			$name = $id_product . '_' . $id_360set . '_' . self::img_name_filter( $file );
			$dst = $folder.'/'.$name;

			if ( $move ) {
				if ( @!rename( $path.'/'.$file, $dst ) ) {
					copy( $path.'/'.$file, $dst );
				}
			} else {
				copy( $path . '/' . $file, $dst );
			}
		}
	}

	public static function img_name_filter( $filename ) {
		$filename = preg_replace( '/[^A-Za-z0-9_\.-]/', '-', $filename );
		return $filename;
	}

	public static function get_files_from_folder( $path ) {

		$files = array();

		if ( $handle = opendir( $path ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				if ($entry != '.' && $entry != '..' && $entry != '.htaccess' && $entry != '__MACOSX') {
					$files[] = $entry;
				}
			}

			closedir( $handle );
		}

		return $files;
	}

	public static function create_product_360_folder( $id_product, $id_360set ) {

		$id_product = (int)$id_product;
		$id_360set = (int)$id_360set;
		$id_360 = self::get_set_parent( $id_360set );

		$img_dir = self::dir() . 'pic/360/';

		if ( !file_exists( $img_dir . $id_product ) ) {
			mkdir( $img_dir . $id_product, 0775 );
		}
		
		if ( !file_exists( $img_dir . $id_product . '/' . $id_360 ) ) {
			mkdir( $img_dir . $id_product . '/' . $id_360, 0775 );
		}

		$folder = $img_dir . $id_product . '/' . $id_360 . '/' . $id_360set;

		if ( !file_exists( $folder ) ) {
			mkdir( $folder, 0775 );
		} else {
			@chmod( $folder, 0775 );
		}

		if ( !file_exists( $folder . '/.htaccess' ) ) {
			file_put_contents( $folder . '/.htaccess', 'deny from all' );
		}

		return $folder;
	}

	public static function get_folder_data( $path ) {
		$files = array();
		$folders = array();

		if ( $handle = opendir( $path ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				if ( $entry != '.' && $entry != '..' && $entry != '.htaccess' && $entry != '__MACOSX' ) {
					if ( is_dir( $path . '/' . $entry ) ) {
						array_push( $folders, $entry );
					} else {
						array_push( $files, $entry );
					}
				}
			}
			closedir( $handle );
		}

		sort( $folders );
		sort( $files );

		return array(
			'folders' => $folders,
			'files' => $files
		);
	}

	public static function extract_arc( $file ) {
		$zip = new ZipArchive;
		$res = $zip->open( $file );

		if ( $res === true ) {
			$folder = uniqid( getmypid() );
			$path = self::dir() . 'pic/tmp/' . $folder;
			mkdir( $path, 0777 );
			$zip->extractTo( $path );
			$zip->close();
			return $path;
		} else {
			return false;
		}
	}

	public static function get_crop_json( $id_360 ) {
		global $wpdb;

		if ( $crop = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360` WHERE id_360 = " . (int)$id_360 )->crop ) {
			if ( !empty( $crop ) ) {
				return stripslashes( $crop );
			}
		}

		return '[]';
	}

	public static function set_crop_json( $id_360, $json ) {
		global $wpdb;

		$aff_rows = $wpdb->query( 
			$wpdb->prepare("UPDATE `{$wpdb->prefix}ajaxzoom360` SET crop = %s WHERE id_360 = %d", $json, $id_360 )
		);

		return json_encode( array('status' => $aff_rows ) );
	}

	public static function get_sets( $id_product ) {
		global $wpdb;

		$sets = $wpdb->get_results( "SELECT s.*, g.name, g.id_360, g.status 
			FROM `{$wpdb->prefix}ajaxzoom360set` s, `{$wpdb->prefix}ajaxzoom360` g 
			WHERE g.id_360 = s.id_360 AND g.id_product = '{$id_product}' 
			ORDER BY g.name, s.id_360set", ARRAY_A ); // ORDER BY g.name, s.sort_order", ARRAY_A );

		foreach ( $sets as &$set ) {
			$thumb = self::uri() . '/ajaxzoom/axZm/zoomLoad.php?';
			$thumb .= '?qq=1&azImg360=' . self::uri() . '/ajaxzoom/pic/360/' . $id_product . '/' . $set['id_360'] . '/' . $set['id_360set'];
			$thumb .= '&width=100&height=100&thumbMode=contain';

			$set_images = self::get_360_images( $id_product, $set['id_360set'] );

			if ( file_exists( self::dir() . 'pic/360/' . $id_product . '/' . $set['id_360'] . '/' . $set['id_360set'] ) && count( $set_images ) > 0 ) {
				$set['path'] = $thumb;
			} else {
				$set['path'] = self::uri() . '/ajaxzoom/no_image-100x100.jpg';
			}
		}

		return $sets;
	}

	public static function get_set_parent( $id_360set ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360set` WHERE id_360set = " . (int)$id_360set )->id_360;
	}

	public static function get_set_product( $id_360 ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360` WHERE id_360 = " . (int)$id_360 )->id_product;
	}

	public static function get_groups( $product_id ) {
		global $wpdb;

		return $wpdb->get_results( "SELECT g.*, COUNT(g.id_360) AS qty, s.id_360set 
			FROM `{$wpdb->prefix}ajaxzoom360` g 
			LEFT JOIN `{$wpdb->prefix}ajaxzoom360set` s ON g.id_360 = s.id_360 
			WHERE g.id_product = '{$product_id}' 
			GROUP BY g.id_360", ARRAY_A );
	}

	public static function is_active( $product_id ) {
		global $wpdb;

		return !$wpdb->get_results( "SELECT * 
			FROM `{$wpdb->prefix}ajaxzoomproducts` 
			WHERE id_product = '{$product_id}'", ARRAY_A );
	}

	public static function set_360_status( $id_360, $status ) {
		global $wpdb;

		$wpdb->query( "UPDATE `{$wpdb->prefix}ajaxzoom360` SET status = '{$status}' WHERE id_360 = '{$id_360}'" );
	}

	public static function delete_set( $id_360set ) {
		global $wpdb;

		$id_360 = self::get_set_parent( $id_360set );
		$id_product = self::get_set_product( $id_360 );

		// clear AZ cache
		$images = self::get_360_images( $id_product, $id_360set );

		foreach ( $images as $image ) {
			self::delete_image_az_cache( $image['filename'] );
		}

		$wpdb->query( "DELETE FROM `{$wpdb->prefix}ajaxzoom360set` WHERE id_360set = " . $id_360set );

		$path = self::dir() . 'pic/360/' . $id_product . '/' . $id_360;

		$tmp = $wpdb->query( "SELECT * FROM `{$wpdb->prefix}ajaxzoom360set` WHERE id_360 = " . $id_360 );
		if ( !$tmp ) {
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}ajaxzoom360` WHERE id_360 = " . $id_360 );
		} else {
			$path .= '/' . $id_360set;
		}

		self::delete_directory($path);
	}

	public static function delete_directory( $dirname, $delete_self = true ) {
		
		$dirname = rtrim( $dirname, '/' ) . '/';

		if ( !strstr($dirname, '/ajaxzoom/') ){
			return false;
		}

		if ( file_exists( $dirname ) ) {
			@chmod( $dirname, 0777 ); // NT ?
			if ( $files = scandir( $dirname ) ) {
				foreach ( $files as $file ) {
					if ( $file != '.' && $file != '..' && $file != '.svn' ) {
						if ( is_dir( $dirname . $file ) ) {
							self::delete_directory( $dirname . $file, true );
						} elseif ( file_exists( $dirname . $file ) ) {
							@chmod( $dirname . $file, 0777 ); // NT ?
							@unlink( $dirname . $file );
						}
					}
				}
				if ( $delete_self && file_exists( $dirname ) ) {
					if ( @!rmdir( $dirname ) ) {
						@chmod( $dirname, 0777 ); // NT ?
						return false;
					}
				}
				return true;
			}
		}
		return false;
	}

	public static function delete_image_az_cache( $file ) {
		
		// Validator issue
		$axzmh = '';
		$zoom = array();

		// Include all classes
		include_once ( self::dir() . 'axZm/zoomInc.inc.php' );

		if ( !Ajaxzoom::$axzmh ) {
			Ajaxzoom::$axzmh = $axzmh; // cannot change name as it come from external app (include above)
			Ajaxzoom::$zoom = $zoom;
		}

		// What to delete
		$arr_del = array( 'In' => true, 'Th' => true, 'tC' => true, 'mO' => true, 'Ti' => true );

		// Remove all cache
		if ( is_object( Ajaxzoom::$axzmh ) ) {
			Ajaxzoom::$axzmh->removeAxZm( Ajaxzoom::$zoom, $file, $arr_del, false );
		}
	}

	public static function get_360_images( $id_product, $id_360set = '' ) {
		$files = array();
		$id_360 = Ajaxzoom::get_set_parent( $id_360set );
		$dir = self::dir() . 'pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set;
		
		if ( file_exists( $dir ) && $handle = opendir( $dir ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				if ( $entry != '.' && $entry != '..' && $entry != '.htaccess' ) {
					$files[] = $entry;
				}
			}

			closedir( $handle );
		}

		sort( $files );

		$res = array();

		foreach ( $files as $entry ) {
			$tmp = explode( '.', $entry );
			$ext = end( $tmp );
			$name = preg_replace( '|\.' . $ext . '$|', '', $entry );
			$thumb = self::uri() . '/ajaxzoom/axZm/zoomLoad.php?';
			$thumb .= 'azImg=' . self::uri() . '/ajaxzoom/pic/360/' . $id_product . '/' . $id_360 . '/' . $id_360set . '/' . $entry . '&width=100&height=100&qual=90';

			$res[] = array(
				'thumb' => $thumb,
				'filename' => $entry,
				'id' => $name,
				'ext' => $ext
			);
		}

		return $res;
	}
	
	public static function images_360_json_per_variation( $product_id ) {

		$js = '';

		$variations_ids = self::get_variations_ids( $product_id );

		foreach ( $variations_ids as $variation_id ) {
			$json = self::images_360_json( $product_id, $variation_id );
			if ( $json != '{}' ) {
				$js .= "ajaxzoom_variations[$variation_id] = " . $json . ";\n";
			}
		}

		return $js;
	}

	public static function images_json_per_variation( $product_id ) {

		$js = '';

		$variations_ids = self::get_variations_ids( $product_id );

		foreach ( $variations_ids as $variation_id ) {
			$json = self::images_2d_json( $product_id, $variation_id );
			if ( $json != '{}' ) {
				$js .= "ajaxzoom_variations_2d[$variation_id] = " . $json . ";\n";
			}
		}

		return $js;
	}	

	public static function images_360_json( $product_id, $combination_id = false ) {

		$tmp = array();
		$variations_all_ids = self::get_variations_ids( $product_id );

		$sets_groups = self::get_sets_groups( $product_id );

		foreach ( $sets_groups as $group ) {
			if ( $combination_id ) {
				$combinations = explode( ',', $group['combinations'] );
				if ( count( $combinations ) > 0 && !in_array( $combination_id, $combinations ) ) {
					continue;
				}
			} else {
				// by default show 360 with checked/unchecked all variations
				if ( !( $group['combinations'] == '' || $group['combinations'] == implode(',', $variations_all_ids) ) ) {
					continue;
				}
			}

			if ( $group['status'] == 0 ) {
				continue;
			}

			$settings = self::prepare_settings( $group['settings'] );

			if ( !empty( $settings ) ) {
				$settings = ", $settings";
			}

			if ( $group['qty'] > 0 ) {

				$crop = empty( $group['crop'] ) ? '[]' : trim( preg_replace( '/\s+/', ' ', stripslashes( $group['crop'] ) ) );

				$jsonStr = '';

				if ( $group['qty'] == 1 ) {
					$jsonStr = '"' . $group['id_360'] . '"' . ':  {"path": "' . self::uri() . "/ajaxzoom/pic/360/" . $product_id . "/" . $group['id_360'] . "/" . $group['id_360set'] . '"' . $settings . ', "combinations": [' . $group['combinations'] . ']';
				} else {
					$jsonStr = '"' . $group['id_360'] . '"' . ':  {"path": "' . self::uri() . "/ajaxzoom/pic/360/" . $product_id . "/" . $group['id_360'] . '"' . $settings . ', "combinations": [' . $group['combinations'] . ']';
				}
				
				if ( $crop && $crop != '[]' ) {
					$jsonStr .= ', "crop": ' . $crop;
				}
				
				$jsonStr .= '}';
				
				$tmp[] = $jsonStr;
			}
		}

		return '{' . implode( ',', $tmp ) . '}';
	}

	public static function images_2d_json( $product_id, $variation_id = false ) {
		global $wpdb;

		$tmp = array();

		$images = $wpdb->get_results( "SELECT * 
			FROM `{$wpdb->prefix}ajaxzoom2dimages` 
			WHERE id_product = $product_id AND FIND_IN_SET($variation_id, variations) 
			ORDER BY sort_order", ARRAY_A );

		$cnt = 0;

		foreach($images as $image) {
			$cnt++;
			$tmp[] = '"' . $cnt . '"' . ': {"img": "' . self::uri() . "/ajaxzoom/pic/2d/" . $product_id . '/' . $image['image'] . '", "title": ""}';
		}


		return '{' . implode( ',', $tmp ) . '}';
	}

	public static function get_sets_groups( $product_id ) {
		global $wpdb;

		return $wpdb->get_results( "SELECT g.*, COUNT(g.id_360) AS qty, s.id_360set 
			FROM `{$wpdb->prefix}ajaxzoom360` g 
			LEFT JOIN `{$wpdb->prefix}ajaxzoom360set` s ON g.id_360 = s.id_360 
			WHERE g.id_product = '{$product_id}' 
			GROUP BY g.id_360", ARRAY_A );
	}

	public static function prepare_settings( $str ) {

		$res = array();
		  
		$settings = (array)json_decode( $str );
		foreach ( $settings as $key => $value ) {
			if ( $value == 'false' || $value == 'true' || $value == 'null' || is_numeric( $value ) ||  substr( $value, 0, 1 ) == '{' ||  substr( $value, 0, 1 ) == '[' ) {
				$res[] = '"' . $key . '": ' . $value;
			} else {
				$res[] = '"' . $key . '": "' . $value . '"';
			}
		}
		
		return implode( ', ', $res );
	}

	public static function images_json() {
		global $post, $woocommerce, $product;

		// main image
		$images = array();
		if ( has_post_thumbnail() ) {
			$images[] = wp_get_attachment_url( get_post_thumbnail_id() );
		} else {
			$images[] = wc_placeholder_img_src();
		}
		
		// thumbnails
		$attachment_ids = $product->get_gallery_attachment_ids();
		if ( $attachment_ids ) {

			foreach ( $attachment_ids as $attachment_id ) {
				$images[] = wp_get_attachment_url( $attachment_id );
			}
		}
		
		$tmp = array();
		$cnt = 1;
		foreach( $images as $image ) {
			$p = parse_url( $image );
			$tmp[] = '"' . $cnt. '"' . ': {"img": "' . $p['path'] . '", "title": ""}';
			$cnt++;
		}

		return '{' . implode(',', $tmp) . '}';
	}

	public static function media_admin() {
		global $post;
		
		$dir = self::dir();

		if ( isset( $post ) && $post->post_type == 'product' ) {
			wp_register_style( 'axZm_fancybox_css', plugins_url( 'ajaxzoom/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.css', $dir ) );
			wp_enqueue_style( 'axZm_fancybox_css' );

			wp_register_script( 'axZm_fancybox_js', plugins_url( 'ajaxzoom/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_fancybox_js' );

			wp_register_script( 'axZm_openAjaxZoomInFancyBox_js', plugins_url( 'ajaxzoom/axZm/extensions/jquery.axZm.openAjaxZoomInFancyBox.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_openAjaxZoomInFancyBox_js' );
			
			wp_register_script( 'jquery_scrollTo_min_js', plugins_url( 'ajaxzoom/axZm/plugins/jquery.scrollTo.min.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'jquery_scrollTo_min_js' );

			wp_register_style( 'axZm-WP-backend', plugins_url( 'ajaxzoom/axZm-WP-backend.css', $dir ) );
			wp_enqueue_style( 'axZm-WP-backend' );
		} elseif ( isset( $_GET['page'] ) &&  $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'ajaxzoom' ) {
			wp_register_script( 'axZm-WP-backend_js', plugins_url( 'ajaxzoom/axZm-WP-backend.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm-WP-backend_js' );
		
			wp_register_style( 'axZm-WP-backend', plugins_url( 'ajaxzoom/axZm-WP-backend.css', $dir ) );
			wp_enqueue_style( 'axZm-WP-backend' );
		}
		
	}
	
	public static function show_az() {
		global $post, $product;

		if ( !$post || !$post->ID || !$product ) {
			return false;
		}
		
		// returne saved value
		if (self::$show_az_save === false || self::$show_az_save === true) {
			return self::$show_az_save;
		}
		
		$display_only_for_this_product =  get_option ('AJAXZOOM_DISPLAYONLYFORTHISPRODUCTID');
		
		if ( $display_only_for_this_product ) {
			$display_only_for_this_product = array_map ( 'trim', explode(',', $display_only_for_this_product) );

			if ( !empty( $display_only_for_this_product ) ) {
				if ( !in_array( $post->ID, $display_only_for_this_product) ) {
					self::$show_az_save = false;
					return false;
				}
			}
		}

		if ( !self::is_active( $post->ID ) ){
			self::$show_az_save = false;
			return false;
		}

		self::$show_az_save = true;
		return true;
	}

	public static function media() {
		global $product;

		if ( empty( $product ) || ! self::show_az() ) {
			return;
		}

		$dir = self::dir();
		$config = self::config();

		wp_register_style( 'axZm_WC_css', plugins_url( 'ajaxzoom/axZm_WC.css', $dir ) );
		wp_enqueue_style( 'axZm_WC_css' );

		wp_register_style( 'axZm_css', plugins_url( 'ajaxzoom/axZm/axZm.css', $dir ) );
		wp_enqueue_style( 'axZm_css' );

		wp_register_script( 'axZm_js', plugins_url( 'ajaxzoom/axZm/jquery.axZm.js', $dir ), array( 'jquery' ) );
		wp_enqueue_script( 'axZm_js' );
		
		wp_register_style( 'axZm_expButton_css', plugins_url( 'ajaxzoom/axZm/extensions/jquery.axZm.expButton.css', $dir ) );
		wp_enqueue_style( 'axZm_expButton_css' );
		
		wp_register_script( 'axZm_expButton_js', plugins_url( 'ajaxzoom/axZm/extensions/jquery.axZm.expButton.min.js', $dir ), array( 'jquery' ) );
		wp_enqueue_script( 'axZm_expButton_js' );
		
		wp_register_script( 'axZm_imageCropLoad_js', plugins_url( 'ajaxzoom/axZm/extensions/jquery.axZm.imageCropLoad.min.js', $dir ), array( 'jquery' ) );
		wp_enqueue_script( 'axZm_imageCropLoad_js' );
		

		if ( $config['AJAXZOOM_GALLERYAXZMTHUMBSLIDER'] == 'true' ) {
			wp_register_script( 'axZm_mousewheel_js', plugins_url( 'ajaxzoom/axZm/extensions/axZmThumbSlider/lib/jquery.mousewheel.min.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_mousewheel_js' );

			wp_register_style( 'axZm_thumbSlider_css', plugins_url( 'ajaxzoom/axZm/extensions/axZmThumbSlider/skins/default/jquery.axZm.thumbSlider.css', $dir ) );
			wp_enqueue_style( 'axZm_thumbSlider_css' );
			
			wp_register_script( 'axZm_thumbSlider_js', plugins_url( 'ajaxzoom/axZm/extensions/axZmThumbSlider/lib/jquery.axZm.thumbSlider.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_thumbSlider_js' );
		}

		if ( $config['AJAXZOOM_SPINNER'] == 'true' ) {
			wp_register_script( 'axZm_spin_js', plugins_url( 'ajaxzoom/axZm/plugins/spin/spin.min.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_spin_js' );
		}

		wp_register_style( 'axZm_mouseOverZoom_css', plugins_url( 'ajaxzoom/axZm/extensions/axZmMouseOverZoom/jquery.axZm.mouseOverZoom.4.css', $dir ) );
		wp_enqueue_style( 'axZm_mouseOverZoom_css' );

		wp_register_script( 'axZm_mouseOverZoom_js', plugins_url( 'ajaxzoom/axZm/extensions/axZmMouseOverZoom/jquery.axZm.mouseOverZoom.4.js', $dir ), array( 'jquery' ) );
		wp_enqueue_script( 'axZm_mouseOverZoom_js' );

		wp_register_script( 'axZm_mouseOverZoomInit_js', plugins_url( 'ajaxzoom/axZm/extensions/axZmMouseOverZoom/jquery.axZm.mouseOverZoomInit.4.js', $dir ), array( 'jquery' ) );
		wp_enqueue_script( 'axZm_mouseOverZoomInit_js' );

		if ( $config['AJAXZOOM_AJAXZOOMOPENMODE'] == 'fancyboxFullscreen' || $config['AJAXZOOM_AJAXZOOMOPENMODE'] == 'fancybox' ) {
			wp_register_style( 'axZm_fancybox_css', plugins_url( 'ajaxzoom/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.css', $dir ) );
			wp_enqueue_style( 'axZm_fancybox_css' );

			wp_register_script( 'axZm_fancybox_js', plugins_url( 'ajaxzoom/axZm/plugins/demo/jquery.fancybox/jquery.fancybox-1.3.4.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_fancybox_js' );

			wp_register_script( 'axZm_openAjaxZoomInFancyBox_js', plugins_url( 'ajaxzoom/axZm/extensions/jquery.axZm.openAjaxZoomInFancyBox.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_openAjaxZoomInFancyBox_js' );
		}

		if ( $config['AJAXZOOM_AJAXZOOMOPENMODE'] == 'colorbox' ) {

			wp_register_style( 'axZm_colorbox_css', plugins_url( 'ajaxzoom/axZm/plugins/demo/colorbox/example2/colorbox.css', $dir ) );
			wp_enqueue_style( 'axZm_colorbox_css' );

			wp_register_script( 'axZm_colorbox_js', plugins_url( 'ajaxzoom/axZm/plugins/demo/colorbox/jquery.colorbox-min.js', $dir ), array( 'jquery' ) );
			wp_enqueue_script( 'axZm_colorbox_js' );
		}

		wp_register_script( 'axZm_JSON_js', plugins_url( 'ajaxzoom/axZm/plugins/JSON/jquery.json-2.3.min.js', $dir ), array( 'jquery' ) );
		wp_enqueue_script( 'axZm_JSON_js' );
	}

	public static function woocommerce_get_settings_pages( $settings ) {

		$settings[] = include( 'settings/class-wc-settings-ajaxzoom.php' );
		
		return $settings;
	}

	public static function save_ajaxzoom_licenses() {

		$licenses = array();
		// Fixed deleting license when saving other options
		if ( isset( $_POST['ajaxzoom_licenses'] ) ) {
			$licenses = $_POST['ajaxzoom_licenses'];
			update_option( 'ajaxzoom_licenses', $licenses );
		}
	}

	public static function install() {
		self::install_db();
		self::install_dir();
		self::install_axzm();
		self::install_config();
	}

	public static function install_db() {
		global $wpdb;
		global $ajaxzoom_db_version;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ajaxzoom360` (
				`id_360` int(11) NOT NULL AUTO_INCREMENT,  
				`id_product` int(11) NOT NULL,  `name` varchar(255) NOT NULL,  
				`num` int(11) NOT NULL DEFAULT '1',  
				`settings` text NOT NULL,  
				`status` tinyint(1) NOT NULL DEFAULT '0',  
				`combinations` text NOT NULL, 
				`crop` text NOT NULL,
				PRIMARY KEY (`id_360`)) $charset_collate;";
		dbDelta( $sql );
		

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ajaxzoom360set` (
				`id_360set` int(11) NOT NULL AUTO_INCREMENT,  
				`id_360` int(11) NOT NULL,  
				`sort_order` int(11) NOT NULL, 
				PRIMARY KEY (`id_360set`)) $charset_collate;";
		dbDelta( $sql );

		// ok?
		$sql = "CREATE TABLE `{$wpdb->prefix}ajaxzoom2dimages` (
  				`id` int(11) NOT NULL AUTO_INCREMENT,
  				`id_product` int(11) NOT NULL,
  				`image` varchar(255) NOT NULL,
  				`sort_order` int(11) NOT NULL,
  				`variations` text NOT NULL,
  				PRIMARY KEY (`id`)) $charset_collate;";
		dbDelta( $sql );


		add_option( 'ajaxzoom_db_version', $ajaxzoom_db_version );
	}

	public static function install_dir() {
		
		$dir = self::dir();

		foreach ( array( '2d', '360', 'cache', 'zoomgallery', 'zoommap', 'zoomthumb', 'zoomtiles_80', 'tmp' ) as $folder ) {
			$path = $dir . 'pic/' . $folder;
			if ( ! file_exists( $path )) {
				mkdir( $path, 0777 );
			} else {
				chmod( $path, 0777 );
			}
		}
	}

	public static function install_config() {
		$settings = self::settings_data();
		foreach ( $settings as $key => $option ) {
			add_option( $key, $option['default'] );
		}
	}

	public static function config() {
		global $wpdb;
		$res = array();
		$rows = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name LIKE 'AJAXZOOM_%'" );
		foreach ( $rows as $row ) {
			$res[ $row->option_name ] = $row->option_value;
		}
		return $res;
	}

	public static function install_axzm() {

		$dir = self::dir();
		if ( ! file_exists( $dir . 'axZm' ) && ini_get( 'allow_url_fopen' ) ) {
			$remoteFileContents = file_get_contents( 'http://www.ajax-zoom.com/download.php?ver=latest&module=woo' );
			$localFilePath = $dir . 'pic/tmp/jquery.ajaxZoom_ver_latest.zip';

			file_put_contents( $localFilePath, $remoteFileContents );

			$zip = new \ZipArchive();
			$res = $zip->open( $localFilePath );
			$zip->extractTo( $dir . 'pic/tmp/' );
			$zip->close();

			rename( $dir . 'pic/tmp/axZm', $dir . 'axZm' );
		}
	}

	public static function dir() {
		return preg_replace( '|includes/$|', '', plugin_dir_path( __FILE__ ) );
	}

	public static function uri() {
		$url = plugins_url();
		$p = parse_url( $url );
		return $p['path'];
	}

	public static function woocommerce_admin_field_ajaxzoom_licenses( $value ) {

		$licenses = get_option( 'ajaxzoom_licenses' );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Licenses', 'ajaxzoom' ); ?>:</th>
			<td class="forminp" id="ajaxzoom_licenses">
				<table class="widefat wc_input_table sortable" cellspacing="0">
					<thead>
						<tr>
							<th class="sort">&nbsp;</th>
							<th style="min-width: 250px;"><?php _e( 'Domain', 'ajaxzoom' ); ?></th>
							<th style="width: 120px;"><?php _e( 'License Type', 'ajaxzoom' ); ?></th>
							<th><i class="az-icon-key" style="margin-right: 5px;"></i><?php _e( 'License Key', 'ajaxzoom' ); ?></th>
							<th><i class="az-icon-key" style="margin-right: 5px;"></i><?php _e( 'Error200', 'ajaxzoom' ); ?></th>
							<th><i class="az-icon-key" style="margin-right: 5px;"></i><?php _e( 'Error300', 'ajaxzoom' ); ?></th>
						</tr>
					</thead>
					<tbody class="licenses">
						<?php
						$i = -1;
						if ( $licenses ) {
							foreach ( $licenses as $license ) {
								$i++;

								echo '<tr class="license">
									<td>&nbsp;</td>
									<td><input type="text" value="' . esc_attr( $license['domain'] ) . '" name="ajaxzoom_licenses[' . $i . '][domain]" /></td>
									<td style="width: 120px;">
										<select name="ajaxzoom_licenses[' . $i . '][type]" style="width: 100%; box-sizing: border-box;">
											<option value="evaluation" ' . ( $license['type'] == 'evaluation' ? 'selected' : '' ) . '>evaluation</option>
											<option value="developer" ' . ( $license['type'] == 'developer' ? 'selected' : '' ) . '>developer</option>
											<option value="basic" ' . ( $license['type'] == 'basic' ? 'selected' : '' ) . '>basic</option>
											<option value="standard" ' . ( $license['type'] == 'standard' ? 'selected' : '' ) . '>standard</option>
											<option value="business" ' . ( $license['type'] == 'business' ? 'selected' : '' ) . '>business</option>
											<option value="corporate" ' . ( $license['type'] == 'corporate' ? 'selected' : '' ) . '>corporate</option>
											<option value="enterprise" ' . ( $license['type'] == 'enterprise' ? 'selected' : '' ) . '>enterprise</option>
											<option value="unlimited" ' . ( $license['type'] == 'unlimited' ? 'selected' : '' ) . '>unlimited</option>
										</select>
									</td>
									<td><input type="text" value="' . esc_attr( $license['key'] ) . '" name="ajaxzoom_licenses[' . $i . '][key]" /></td>
									<td><input type="text" value="' . esc_attr( $license['error200'] ) . '" name="ajaxzoom_licenses[' . $i . '][error200]" /></td>
									<td><input type="text" value="' . esc_attr( $license['error300'] ) . '" name="ajaxzoom_licenses[' . $i . '][error300]" /></td>
								</tr>';
							}
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="6"><a href="#" class="add button"><?php _e( '+ Add License', 'ajaxzoom' ); ?></a> <a href="#" class="remove_rows button"><?php _e( 'Remove selected license(s)', 'ajaxzoom' ); ?></a></th>
						</tr>
					</tfoot>
				</table>
				<script type="text/javascript">
					jQuery( function() {
						jQuery( '#ajaxzoom_licenses' ).on( 'click', 'a.add', function() {

							var size = jQuery( '#ajaxzoom_licenses' ).find( 'tbody .license' ).size();

							jQuery( '<tr class="license">\
									<td>&nbsp;</td>\
									<td><input type="text" name="ajaxzoom_licenses[' + size + '][domain]" /></td>\
									<td>\
										<select name="ajaxzoom_licenses[' + size + '][type]">\
											<option value="evaluation">evaluation</option>\
											<option value="developer">developer</option>\
											<option value="basic">basic</option>\
											<option value="standard">standard</option>\
											<option value="business">business</option>\
											<option value="corporate">corporate</option>\
											<option value="enterprise">enterprise</option>\
											<option value="unlimited">unlimited</option>\
										</select>\
									</td>\
									<td><input type="text" name="ajaxzoom_licenses[' + size + '][key]" /></td>\
									<td><input type="text" name="ajaxzoom_licenses[' + size + '][error200]" /></td>\
									<td><input type="text" name="ajaxzoom_licenses[' + size + '][error300]" /></td>\
								</tr>' ).appendTo( '#ajaxzoom_licenses table tbody' );

							return false;
						} );
					} );
				</script>
			</td>
		</tr>
		<?php
	}

	public static function settings_data() {
		
		return array(
			'AJAXZOOM_LICENSES' => array(
				'category' => 'license',
				'comment' => '',
				'default' => '',
				'type' => 'ajaxzoom_licenses',
				'title' => 'asdsa'
				),

			'AJAXZOOM_DISPLAYONLYFORTHISPRODUCTID' => array(
				'type' => 'textarea',
				'default' => '',
				'category' => 'products',
				'comment' => __( 'CSV with product IDs for which AJAX-ZOOM will be <b>only</b> enabled. 
					Leave blank to have it enabled for all products! 
					This option can be usefull e.g. if you want to make A/B tests and enable 
					AJAX-ZOOM only for certain products, e.g. 7,15 would 
					enable AJAX-ZOOM only for products with ID 7 and 15
				', 'ajaxzoom' ),
				'title' => __( 'displayOnlyForThisProductID', 'ajaxzoom' )
			),

			'AJAXZOOM_DISABLEALLMSG' => array(
				'default' => 'false',
				'type' => 'switch',
				'title' => __( 'disableAllMsg', 'ajaxzoom' ),
				'comment' => __( 'AJAX-ZOOM produces some notifications within the player 
					telling that image tiles or other files are generating and returns the result. 
					This happens only when an image or 360 images are opened for the first time. This is also the reason why preloading 
					is slow at first. With this switch you can disable these notifications in the front office.
				', 'ajaxzoom')
			),

			'AJAXZOOM_SHOWDEFAULTFORVARIATION' => array(
				'default' => 'false',
				'type' => 'switch',
				'title' => __( 'showDefaultForVariation', 'ajaxzoom' ),
				'comment' => __( 'Show default images for selected variation', 'ajaxzoom')
			),

			'AJAXZOOM_DIVID' => array(
				'default' => 'az_mouseOverZoomContainer',
				'title' => __( 'divID', 'ajaxzoom' ),
				'comment' => __( 'DIV (container) ID for mouseover zoom :-)', 'ajaxzoom' )
			),

			'AJAXZOOM_GALLERYDIVID' => array(
				'default' => 'az_mouseOverZoomGallery',
				'title' => __( 'galleryDivID', 'ajaxzoom' ),
				'comment' => __( 'DIV (container) id of the gallery, set to false to disable gallery', 'ajaxzoom' )
			),

			// galleryPosition changes the layout / template in this module
			// it is not an option for mouseover zoom
			'AJAXZOOM_GALLERYPOSITION' => array(
				'title' => __( 'galleryPosition', 'ajaxzoom' ),
				'type' => 'select',
				'useful' => true,
				'default' => 'bottom',
				'comment' => __( '
					Position of the gallery in the template (/modules/ajaxzoom/views/templates/front/ajaxzoom.tpl)
					, possible values: "top", "right", "bottom", "left"; 
					"left" and "right" will instantly change the gallery to vertical!
				', 'ajaxzoom' ),
				'values' => array(
					'top' => 'top',
					'right' => 'right',
					'bottom' => 'bottom',
					'left' => 'left',
				)
			),

			'AJAXZOOM_HIDEGALLERYONEIMAGE' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Instantly hide gallery if there is only one image or one 360/3D', 'ajaxzoom' ),
				'title' => __( 'hideGalleryOneImage', 'ajaxzoom' )
			),

			'AJAXZOOM_HIDEGALLERYADDCLASS' => array(
				'default' => 'axZm_mouseOverNoMargin',
				'comment' => __( 'This option is mainly for the layout with vertical gallery 
					which is located next (left or right) to mouseover area. 
					The most solid css layout for vertical gallery is when "divID" is wrapped by a div 
					which has a left or right margin. This margin corresponds to vertical gallery width + 
					some space inbetween. So if "hideGalleryOneImage" option is activated and there is only one image, 
					only one 360 or no images / 360s at all, then the container 
					represented by "galleryDivID" option is hidden and there is more space which can be filled. 
					To do that we simply add a css class with margin 0 to the parent of "divID" 
					overriding the left or right margin which is not needed for the gallery. 
					You can change the "hideGalleryAddClass" 
					to your own class name or set it to false to prevent this action. 
				', 'ajaxzoom' ),
				'title' => __( 'hideGalleryAddClass', 'ajaxzoom' )
			),

			'AJAXZOOM_GALLERYHOVER' => array(
				//'type' => 'switch',
				'default' => 'false',
				'comment' => __( 'Use mouseenter (mouseover) for switching between images. 
					You can specify an integer which will represent the time in ms to wait 
					for switching after the mouse enters the thumb; true defaults to 200', 'ajaxzoom' ),
				'title' => __( 'galleryHover', 'ajaxzoom' )
			),

			'AJAXZOOM_GALLERYAXZMTHUMBSLIDER' => array(
				'type' => 'switch',
				'default' => 'true',
				'useful' => true,
				'comment' => __( 'Use $.axZmThumbSlider on gallery thumbnails or not', 'ajaxzoom' ),
				'title' => __( 'galleryAxZmThumbSlider', 'ajaxzoom' )
			),

			'AJAXZOOM_GALLERYAXZMTHUMBSLIDERPARAM' => array(
				'type' => 'textarea',
				'textareaHeight' => '250px',
				'useful' => true,
				'isJsObject' => true,
				'title' => __( 'galleryAxZmThumbSliderParam', 'ajaxzoom' ),
				'default' => '
{
	orientation: "horizontal",
	scrollBy: 1,
	btn: true,
	btnClass: "axZmThumbSlider_button_new",
	btnBwdStyle: {
		marginLeft: 0, 
		marginRight: 0
	},
	btnFwdStyle: {
		marginLeft: 0, 
		marginRight: 0
	},
	btnLeftText: null, 
	btnRightText: null,
	btnHidden: true,
	pressScrollSnap: true,
	centerNoScroll: true,
	wrapStyle: {
		borderWidth: 0
	},
	thumbImgStyle: {
		maxHeight: "64px",
		maxWidth: "64px"
	},
	thumbLiStyle: {
		width: 64, 
		height: 64, 
		lineHeight: "62px",
		marginBottom: 2,
		marginLeft: 3,
		marginRight: 3,
		borderRadius: 3
	}
}
					',
				'comment' => __( '$.axZmThumbSlider parametrs if "galleryAxZmThumbSlider" is enabled 
					<span style="font-weight: bold;">and "galleryPosition" option above is set to top or bottom.</span> <br>
					For full list of options see under: <br>
					<a href=http://www.ajax-zoom.com/axZm/extensions/axZmThumbSlider/ target=_blank>
					http://www.ajax-zoom.com/axZm/extensions/axZmThumbSlider/ 
					<i class="icon-external-link-sign"></i>
					</a>
				', 'ajaxzoom' )
			),

			'AJAXZOOM_GALLERYAXZMTHUMBSLIDERPARAM_V' => array(
				'type' => 'textarea',
				'textareaHeight' => '250px',
				'useful' => true,
				'isJsObject' => true,
				'title' => __( 'galleryAxZmThumbSliderParamVertical', 'ajaxzoom' ),
				'default' => ('
{
	orientation: "vertical",
	scrollBy: 3,
	smoothMove: 6,
	quickerStop: true,
	pressScrollSnap: true,
	btn: true,
	btnClass: "axZmThumbSlider_button_new",
	btnBwdStyle: {marginTop: 0, marginBottom: 0},
	btnFwdStyle: {marginTop: 0, marginBottom: 0},
	btnLeftText: null, 
	btnRightText: null, 
	btnTopText: null, 
	btnBottomText: null,
	btnHidden: true,
	mouseWheelScrollBy: 1,
	wrapStyle: {
		 borderLeftWidth: 0, 
		 borderRightWidth: 0
	},
	scrollbar: false,

	thumbLiSubClass: {
		 first: null,
		 last: null 
	},
	thumbImgStyle:{
		 maxHeight: "64px",
		 maxWidth: "64px",
		 borderRadius: 3
	},
	thumbLiStyle: {
		width: 64, 
		height: 64, 
		lineHeight: "62px",
		marginBottom: 2,
		marginLeft: 3,
		marginRight: 3,
		borderRadius: 3
	}
}
				' ),

				'comment' => __( '$.axZmThumbSlider parametrs if "galleryAxZmThumbSlider" is enabled 
					<span style="font-weight: bold;">and "galleryPosition" option above is set to left or right!</span> 
					The reason this option exists is that in case that the gallery is places to the left or 
					right from mouseover zoom, it should be vertical and we need default parameters for it. <br>
					For full list of options see under: <br>
					<a href=http://www.ajax-zoom.com/axZm/extensions/axZmThumbSlider/ target=_blank>
					http://www.ajax-zoom.com/axZm/extensions/axZmThumbSlider/ 
					</a>', 'ajaxzoom' )
			),

			'AJAXZOOM_THUMBW' => array(
				'title' => __( 'thumbW', 'ajaxzoom' ),
				'useful' => true,
				'default' => 64,
				'comment' => __( 'Gallery image thumb width; please note that in many examples, e.g. when "galleryAxZmThumbSlider" is enabled, 
					the final thumbnail width and height are determined by css set over "galleryAxZmThumbSliderParam"
				', 'ajaxzoom' )
			),

			'AJAXZOOM_THUMBH' => array(
				'title' => __( 'thumbH', 'ajaxzoom' ),
				'useful' => true,
				'default' => 64,
				'comment' => __( 'Gallery image thumb height', 'ajaxzoom' )
			),

			'AJAXZOOM_THUMBRETINA' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Double resolution of the thumb image', 'ajaxzoom' ),
				'title' => __( 'thumbRetina', 'ajaxzoom' )
			),

			'AJAXZOOM_QUALITYTHUMB' => array(
				'title' => __( 'qualityThumb', 'ajaxzoom' ),
				'default' => '100',
				'comment' => __( 'Jpeg quality of the gallery thumbs', 'ajaxzoom' ),
			),

			'AJAXZOOM_QUALITY' => array(
				'title' => __( 'quality', 'ajaxzoom' ),
				'default' => '90',
				'comment' => __( 'Jpeg quality of the preview image', 'ajaxzoom' ),
			),

			'AJAXZOOM_QUALITYZOOM' => array(
				'title' => __( 'qualityZoom', 'ajaxzoom' ),
				'default' => '80',
				'comment' => __( 'Jpeg quality of the zoom image shown in the flyout window', 'ajaxzoom' ),
			),

			'AJAXZOOM_FIRSTIMAGETOLOAD' => array(
				'title' => __( 'firstImageToLoad', 'ajaxzoom' ),
				'default' => 1,
				'comment' => __( 'Image from "images" option which should be loaded at first; see also "images360firstToLoad" option below', 'ajaxzoom' )
			),

			'AJAXZOOM_IMAGES360FIRSTTOLOAD' => array(
				'type' => 'switch',
				'default' => 'false',
				'comment' => __( 'In case present load 360 from "images360" first and not an image from "images"', 'ajaxzoom' ),
				'title' => __( 'images360firstToLoad', 'ajaxzoom' )
			),

			'AJAXZOOM_IMAGES360THUMB' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Show first image of the spin as thumb', 'ajaxzoom' ),
				'title' => __( 'images360Thumb', 'ajaxzoom' )
			),

			'AJAXZOOM_IMAGES360OVERLAY' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Add a div with class "spinOverlImg" or "spinOverl" over the gallery thumb. 
					On default it has a 360 icon as background.', 'ajaxzoom' ),
				'title' => __( 'images360Overlay', 'ajaxzoom' )
			),

			'AJAXZOOM_IMAGES360PREVIEW' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Normally plain images are opened in some kind of lightbox or fullscreen; 
				By setting this option to true the 360\'s will load into "divID" at first and can be expanded to responsive fancybox or fullscreen.', 'ajaxzoom' ),
				'title' => __( 'images360Preview', 'ajaxzoom' )
			),

			'AJAXZOOM_IMAGES360PREVIEWRESPONSIVE' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'This option is set to true for convinience reasons; 
					In case your "divID" has fixed width and height, 
					set $zoom[\'config\'][\'picDim\'] option in \'/axZm/zoomConfigCustom.inc.php\' 
				after <code>elseif ($_GET[\'example\'] == \'mouseOverExtension360\')</code>', 'ajaxzoom' ),
				'title' => __( 'images360PreviewResponsive', 'ajaxzoom' )
			),

			'AJAXZOOM_IMAGES360EXAMPLEPREVIEW' => array(
				'title' => __( 'images360examplePreview', 'ajaxzoom' ),
				'default' => 'mouseOverExtension360',
				'comment' => __( 'In case "images360Preview" is set to true, 
				the value of this parameter will be sent to AJAX-ZOOM as "options set" (example=mouseOverExtension360)', 'ajaxzoom' )
			),

			'AJAXZOOM_ZOOMMSG360' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'default' => '
{
	"en" : "Drag to spin 360°, scroll to zoom in and out, right click and drag to pan",
	"de" : "Ziehen um 360° zu drehen, zoomen mit dem mausrad, rechte maustaste ziehen verschiebt die Ansicht",
	"fr" : "Faites glisser pour tourner à 360 °, faites défiler pour zoomer dans et hors, cliquer et faire glisser à droite pour vous déplacer",
	"es" : "Arrastrar para girar en 360º, Rueda del ratón para utilizar el Zoom, botón derecho para mover la imagen"
}
				',
				'comment' => __( 'Message displayed under mouse over zoom when 360 is loaded, e.g. "Drag to spin 360, scroll to zoom"', 'ajaxzoom' ),
				'title' => __( 'zoomMsg360', 'ajaxzoom' )
			),

			'AJAXZOOM_ZOOMMSG360_TOUCH' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'default' => '
{
	"en" : "Drag to spin 360°, pinch to zoom in and out",
	"de" : "Ziehen um 360° zu drehen, zoomen mit zwei fingern",
	"fr" : "Faites glisser pour tourner à 360 ° , pincer pour zoomer et dézoomer",
	"es" : "Arrastrar para girar en 360º, pellizcar para ampliar y reducir"
}
				',
				'comment' => __( ' Message displayed under mouse over zoom when 360 is loaded on touch devices, 
default: "Drag to spin 360°, pinch to zoom in and out". 
Can be Javascript object e.g. {"en": "text english...", "de": "text german...", "fr":"text french..."} ', 'ajaxzoom' ),
				'title' => __( 'zoomMsg360_touch', 'ajaxzoom' )
			),

			'AJAXZOOM_PRELOADMOUSEOVERIMAGES' => array(
				'default' => 'oneByOne',
				'comment' => __( 'Preload all preview and mouseover images, possible values: false, true, \'oneByOne\'', 'ajaxzoom' ),
				'title' => __( 'preloadMouseOverImages', 'ajaxzoom' )
			),

			'AJAXZOOM_NOIMAGEAVAILABLECLASS' => array(
				'title' => __( 'noImageAvailableClass', 'ajaxzoom' ),
				'default' => 'axZm_mouseOverNoImage',
				'comment' => __( 'In case there are no images in "images", nor there are any in "images360", 
				a div with some image as background can be appended to the container and receive this options value as css class', 'ajaxzoom' )
			),

			'AJAXZOOM_WIDTH' => array(
				'title' => __( 'width', 'ajaxzoom' ),
				'default' => 'auto',
				'comment' => __( 'Width of the preview image or "auto" (depending on parent container size - "divID", see above); 
					this is also the value which will be passed to your AJAX-ZOOM imaging server to generate this image on-the-fly. 
					If width or height are set to "auto" and "responsive" feature is enabled, both width and height of the preview image 
					are set to 50% of the value taken from "mouseOverZoomWidth", so on default it is 600px;
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_HEIGHT' => array(
				'title' => __( 'height', 'ajaxzoom' ),
				'default' => 'auto',
				'comment' => __( 'Height of the preview image or "auto" (depending on parent container size - "divID", see above); 
					this is also the value which will be passed to your AJAX-ZOOM imaging server to generate this image on-the-fly. 
					If width or height are set to "auto" and "responsive" feature is enabled, both width and height of the preview image 
					are set to 50% of the value taken from "mouseOverZoomWidth", so on default it is 600px;
				', 'ajaxzoom' )
			),

			'AJAXZOOM_RESPONSIVE' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Set this to true for responsive layouts', 'ajaxzoom' ),
				'title' => __( 'responsive', 'ajaxzoom' )
			),

			'AJAXZOOM_ONESRCIMG' => array(
				'type' => 'switch',
				'default' => 'false',
				'comment' => __( 'Use single image as "preview image" - the image which is hovered and the big "flyout image".', 'ajaxzoom' ),
				'title' => __( 'oneSrcImg', 'ajaxzoom' )
			),

			'AJAXZOOM_HEIGHTRATIO' => array(
				'default' => '1.0',
				'useful' => true,
				'comment' => __( 'If "responsive" option is enabled, "heightRatio" with instantly adjust the height of mouseover 
					container depending on width calculated by the browser, e.g. 1.0 will always (only limited by "maxSizePrc" option) 
					make height same as width; 
					a value of 1.5 will make the preview like a portrait. You can also set "heightRatio" to \'auto\'. 
					In this case the height will be adjusted to cover available space instantly! 
					Please note that when your images are not always same proportion, 
					then the container will also change the size when the user switches to a different image.
				', 'ajaxzoom' ),
				'title' => __( 'heightRatio', 'ajaxzoom' )
			),

			'AJAXZOOM_HEIGHTMAXWIDTHRATIO' => array(
				'default' => 'false',
				'comment' => __( 'Similar as you would set max-width: someValue @media only screen condition you can define "heightRatio" 
				depending on the width of the browser, e.g. ["960|0.8", "700|0.7"]', 'ajaxzoom' ),
				'title' => __( 'heightMaxWidthRatio', 'ajaxzoom' )
			),

			'AJAXZOOM_WIDTHRATIO' => array(
				'default' => 'false',
				'comment' => __( 'Oposit of "heightRatio"', 'ajaxzoom' ),
				'title' => __( 'widthRatio', 'ajaxzoom' ),
			),

			'AJAXZOOM_WIDTHMAXHEIGHTRATIO' => array(
				'default' => 'false',
				'comment' => __( 'Oposit of "heightMaxWidthRatio"', 'ajaxzoom' ),
				'title' => __( 'widthMaxHeightRatio', 'ajaxzoom' ),
			),

			'AJAXZOOM_MAXSIZEPRC' => array(
				'title' => __( 'maxSizePrc', 'ajaxzoom' ),
				'useful' => true,
				'default' => '1.0|-120',
				'comment' => __( 'Limit the height if "responsive" and "heightRatio" options are set. 
					Setting "heightRatio" option may result in that the height of the mouseover zoom is bigger than window height 
					and the image is not fully visible. To prevent this you can limit the calculated height with this "maxSizePrc" option. 
					The value of 1.0 would limit the height to 100% of window height; a value of 0.8 to 80% of window height; 
					you can also define two values, e.g. \'1.0|-120\' which would be window height minus 120px.
				', 'ajaxzoom' )
			),

			'AJAXZOOM_MOUSEOVERZOOMWIDTH' => array(
				'title' => __( 'mouseOverZoomWidth', 'ajaxzoom' ),
				'default' => '1200',
				'useful' => true,
				'comment' => __( 'Max width of the image that will be shown in the zoom window; 
					this is the value which will be passed to your AJAX-ZOOM imaging server to generate this image on-the-fly. 
					Please note that the size is limited by $zoom[\'config\'][\'allowDynamicThumbsMaxSize\'] which is can be set 
					in \'/axZm/zoomConfig.inc.php\'. You can also specify a link to the image, see "images" option above. 
				To set the width of the fly out window see "zoomWidth" under "mouseOverZoomParam".', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOUSEOVERZOOMHEIGHT' => array(
				'title' => __( 'mouseOverZoomHeight' ),
				'default' => '1200',
				'useful' => true,
				'comment' => __( 'Max height of the image that will be shown in the zoom window; 
					this is the value which will be passed to your AJAX-ZOOM imaging server to generate this image on-the-fly. 
					Please note that the size is limited by $zoom[\'config\'][\'allowDynamicThumbsMaxSize\'] which is can be set in 
					\'/axZm/zoomConfig.inc.php\'. You can also specify a link to the image, see "images" option above. 
					To set the height of the fly out window see "zoomHeight" under "mouseOverZoomParam".
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_AJAXZOOMOPENMODE' => array(
				'title' => __( 'ajaxZoomOpenMode', 'ajaxzoom' ),
				'default' => 'fancyboxFullscreen',
				'useful' => true,
				'type' => 'select',
				'values' => array(
					'fancyboxFullscreen' => 'fancyboxFullscreen (responsive fancybox)',
					'fullscreen' => 'fullscreen (browser window or screen)',
					'fancybox' => 'fancybox (regular fancybox)',
					'colorbox' => 'colorbox (an other "lightbox")'
				),
				'comment' => __( 'Determines how AJAX-ZOOM is opened when the user clicks on preview images / lens, 
					possible values: \'fullscreen\' (see also "fullScreenApi" option below), \'fancyboxFullscreen\', \'fancybox\', \'colorbox\'; 
					By editing $.mouseOverZoomInit you can extend the plugin to be used with different types of modal boxes to load AJAX-ZOOM into.
				', 'ajaxzoom' )
			),

			'AJAXZOOM_FANCYBOXPARAM' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'title' => __( 'fancyBoxParam', 'ajaxzoom' ),
				'default' => '
{
	boxMargin: 0,
	boxPadding: 10,
	boxCenterOnScroll: true,
	boxOverlayShow: true,
	boxOverlayOpacity: 0.75,
	boxOverlayColor: "#777",
	boxTransitionIn: "fade",  
	boxTransitionOut: "fade",  
	boxSpeedIn: 300,
	boxSpeedOut: 300,
	boxEasingIn: "swing",
	boxEasingOut: "swing",
	boxShowCloseButton: true, 
	boxEnableEscapeButton: true,
	boxOnComplete: function(){},
	boxTitleShow: false,
	boxTitlePosition: "float", 
	boxTitleFormat: null
}
				',
				'comment' => __( 'If fancybox is used in "ajaxZoomOpenMode" option, Fancybox options', 'ajaxzoom' ),
			),

			'AJAXZOOM_COLORBOXPARAM' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'title' => __( 'colorBoxParam', 'ajaxzoom' ),
				'default' => '
{
	transition: "elastic",
	speed: 300,
	scrolling: true,
	title: true,
	opacity: 0.9,
	className: false,
	current: "image {current} of {total}",
	previous: "previous",
	next: "next",
	close: "close",
	onOpen: false,
	onLoad: false,
	onComplete: false,
	onClosed: false,
	overlayClose: true,
	escKey: true
}
				',
				'comment' => __( 'If colorbox is used in "ajaxZoomOpenMode" option, Colorbox options', 'ajaxzoom' ),
			),

			'AJAXZOOM_EXAMPLE' => array(
				'title' => __( 'example', 'ajaxzoom' ),
				'default' => 'mouseOverExtension',
				'comment' => __( 'Configuration set which is passed to ajax-zoom when ajaxZoomOpenMode is \'fullscreen\'', 'ajaxzoom' ),
			),

			'AJAXZOOM_EXAMPLEFANCYBOXFULLSCREEN' => array(
				'title' => __( 'exampleFancyboxFullscreen', 'ajaxzoom' ),
				'default' => 'mouseOverExtension',
				'comment' => __( 'Configuration set which is passed to ajax-zoom when ajaxZoomOpenMode is \'fancyboxFullscreen\'', 'ajaxzoom' ),
			),

			'AJAXZOOM_EXAMPLEFANCYBOX' => array(
				'title' => __( 'exampleFancybox', 'ajaxzoom' ),
				'default' => 'modal',
				'comment' => __( 'Configuration set which is passed to ajax-zoom when ajaxZoomOpenMode is \'fancybox\'', 'ajaxzoom' ),
			),

			'AJAXZOOM_EXAMPLECOLORBOX' => array(
				'title' => __( 'exampleColorbox', 'ajaxzoom' ),
				'default' => 'modal',
				'comment' => __( 'Configuration set which is passed to ajax-zoom when ajaxZoomOpenMode is \'colorbox\'', 'ajaxzoom' ),
			),

			'AJAXZOOM_ENFORCEFULLSCREENRES' => array(
				'title' => __( 'enforceFullScreenRes', 'ajaxzoom' ),
				'default' => '768',
				'comment' => __( 'Enforce "ajaxZoomOpenMode" to be "fullscreen" if screen width is less than this value', 'ajaxzoom' ),
			),

			'AJAXZOOM_PREVNEXTARROWS' => array(
				'title' => __( 'prevNextArrows', 'ajaxzoom' ),
				'default' => 'false',
				'comment' => __( 'Put prev / next buttons over mouseover zoom. CSS: .axZm_mouseOverPrevNextArrows', 'ajaxzoom' ),
			),

			'AJAXZOOM_DISABLESCROLLANM' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Disable animation while zooming with AJAX-ZOOM', 'ajaxzoom' ),
				'title' => __( 'disableScrollAnm', 'ajaxzoom' ),
			),

			'AJAXZOOM_FULLSCREENAPI' => array(
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Try to open AJAX-ZOOM at browsers fullscreen mode, possible on modern browsers except IE < 10 and mobile', 'ajaxzoom' ),
				'title' => __( 'fullScreenApi', 'ajaxzoom' ),
			),

			'AJAXZOOM_AXZMCALLBACKS' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'textareaHeight' => '100px',
				'title' => __( 'axZmCallBacks', 'ajaxzoom' ),
				'default' => '
{
	onFullScreenReady: function() {
	// Here you can place you custom code
	}
}				
				',
				'comment' => __( 'AJAX-ZOOM has several callbacks, 
					<a href=http://www.ajax-zoom.com/index.php?cid=docs#onBeforeStart target=_blank>
					http://www.ajax-zoom.com/index.php?cid=docs#onBeforeStart <i class="icon-external-link-sign">
					</i></a>
				', 'ajaxzoom' )
			),

			'AJAXZOOM_AZOPTIONS' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'useful' => true,
				'textareaHeight' => '100px',
				'title' => __( 'azOptions', 'ajaxzoom' ),
				'default' => '{}',
				'comment' => __( 'Some AJAX-ZOOM options can be set with JS when AJAX-ZOOM is inited. 
					Normally you would be defining them in /axZm/zoomConfig.inc.php or /axZm/zoomConfigCustom.inc.php; 
					this field is for convinience reasons. Example: 
					<code>{fullScreenCornerButton: false}</code> - this would disable the button for fullscreen 
				', 'ajaxzoom' )
			),

			'AJAXZOOM_AZOPTIONS360' => array(
				'type' => 'textarea',
				'useful' => true,
				'isJsObject' => true,
				'textareaHeight' => '100px',
				'title' => __( 'azOptions360', 'ajaxzoom' ),
				'default' => '{}',
				'comment' => __( 'Same as above but specifically for 360/3D', 'ajaxzoom' )
			),

			'AJAXZOOM_POSTMODE' => array(
				'type' => 'switch',
				'title' => __( 'postMode', 'ajaxzoom' ),
				'default' => 'false',
				'comment' => __( 'Set AJAX-ZOOM to use POST instead of GET', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_ENABLED' => array(
				'category' => 'pinterest',
				'type' => 'switch',
				'default' => 'false',
				'useful' => true,
				'comment' => __( 'Experimental feature - enable Pinterest button. 
					Pinterest allows to collect visual bookmarks in the form of images collected on the internet.
				', 'ajaxzoom' ),
				'title' => __( 'enabled', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_BUILD' => array(
				'category' => 'pinterest',
				'title' => __( 'build', 'ajaxzoom' ),
				'default' => 'parsePinBtns',
				'comment' => __( 'Since images are changed the button needs to be repainted [...] 
					In order to accomplish this the Pinterest API should be exposed to window object. 
					This is done by setting data-pin-build attribute value in the script tag when pinterest JavaScript is included. 
					&lt;script data-pin-build="parsePinBtns" type="text/javascript" src="//assets.pinterest.com/js/pinit.js"&gt;&lt;/script&gt; 
					So if you do not have the data-pin-build, please add it to the script tag and if it is different from default (\'parsePinBtns\'), 
					then change this "build" value. Otherwise the pinterest button will not work.
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_WRAPCLASS' => array(
				'category' => 'pinterest',
				'title' => __( 'wrapClass', 'ajaxzoom' ),
				'default' => 'axZm_mouseOverPinterest',
				'comment' => __( 'This is the class of the container where the button will be put into. 
					On default it is at bottom right. To place the button somewhere else you can either change 
					the css of the default class or define a different class.
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_HREF' => array(
				'category' => 'pinterest',
				'title' => __( 'href', 'ajaxzoom' ),
				'default' => '//en.pinterest.com/pin/create/button/',
				'comment' => __( 'href attribute before the button is build.', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_DESCRIPTION' => array(
				'category' => 'pinterest',
				'title' => __( 'description', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Page title if null', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_URL' => array(
				'category' => 'pinterest',
				'title' => __( 'url', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Will be set instantly if null', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_MEDIA' => array(
				'category' => 'pinterest',
				'title' => __( 'media', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Will be set to current selected image if null', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_BTNSRC' => array(
				'category' => 'pinterest',
				'title' => __( 'btnSrc', 'ajaxzoom' ),
				'default' => '//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png',
				'comment' => __( 'Source for the button', 'ajaxzoom' ),
			),

			'AJAXZOOM_PINTEREST_DATA' => array(
				'category' => 'pinterest',
				'title' => __( 'data', 'ajaxzoom' ),
				'default' => '{}',
				'comment' => __( 'Data attributes attached to the button before it is built. See pinterest API...', 'ajaxzoom' ),
			),

			
			/////////////////////////////////////
			// cropGallery (360 "Product Tour" //
			/////////////////////////////////////

			'AZ_CROPAXZMTHUMBSLIDERPARAM' => array(
				'category' => 'cropgallery',
				'title' => __( 'cropAxZmThumbSliderParam', 'ajaxzoom' ),
				'type' => 'textarea',
				'textareaHeight' => '250px',
				'isJsObject' => true,
				'default' => '',
				'comment' => __( 'Slider settings for 360° "Product Tour". Can be kept empty. See also "galleryAxZmThumbSliderParam" option for more info.', 'ajaxzoom' )
			),

			'AZ_CROPSLIDERPOSITION' => array(
				'category' => 'cropgallery',
				'title' => __( 'cropSliderPosition', 'ajaxzoom' ),
				'default' => 'left',
				'useful' => true,
				'type' => 'select',
				'comment' => __( 'Position of the crop slider, possible values: "top", "right", "bottom", "left"', 'ajaxzoom' ),
				'values' => array(
					'top' => 'top',
					'right' => 'right',
					'bottom' => 'bottom',
					'left' => 'left'
				)
			),

			'AZ_CROPSLIDERDIMENSION' => array(
				'category' => 'cropgallery',
				'title' => __( 'cropSliderDimension', 'ajaxzoom' ),
				'useful' => true,
				'default' => '86',
				'comment' => __( 'Width or height (depending on position) of the instantly created container for the 360° "Product Tour" thumb slider', 'ajaxzoom' ),
			),

			'AZ_CROPSLIDERTHUMBAUTOSIZE' => array(
				'category' => 'cropgallery',
				'title' => __( 'cropSliderThumbAutoSize', 'ajaxzoom' ),
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Thumb CSS size will be set instantly depending on "cropSliderDimension" option', 'ajaxzoom' )
			),

			'AZ_CROPSLIDERTHUMBAUTOMARGIN' => array(
				'category' => 'cropgallery',
				'title' => __( 'cropSliderThumbAutoMargin', 'ajaxzoom' ),
				'default' => '7',
				'comment' => __( 'Thumb CSS size will be set instantly depending on "cropSliderDimension" option', 'ajaxzoom' ),
			),

			'AZ_CROPSLIDERTHUMBDESCR' => array(
				'category' => 'cropgallery',
				'title' => __( 'cropSliderThumbDescr', 'ajaxzoom' ),
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( '	Enable descriptions for the thumbs in the slider for 360° "Product Tour"', 'ajaxzoom' )
			),

			////////////////////////
			// mouseOverZoomParam //
			////////////////////////

			'AJAXZOOM_MOZP_POSITION' => array(
				'title' => __( 'position', 'ajaxzoom' ),
				'category' => 'mouseoverzoom',
				'type' => 'select',
				'useful' => true,
				'default' => 'right',
				'comment' => __( 'Position of the flyout zoom window, possible values: "inside", "top", "right", "bottom", "left"', 'ajaxzoom' ),
				'values' => array(
					'inside' => 'inside',
					'top' => 'top',
					'right' => 'right',
					'bottom' => 'bottom',
					'left' => 'left',
				)
			),

			'AJAXZOOM_MOZP_POSAUTOINSIDE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'posAutoInside', 'ajaxzoom' ),
				'default' => '150',
				'comment' => __( 'applies when width (left, right) or height (top, bottom) 
					of zoom window are less than this px value (zoomWidth || zoomHeight are set to auto); 
					if zoomWidth || zoomHeight are fixed, applies when zoom window is out of page border
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOZP_POSINSIDEAREA' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'posInsideArea', 'ajaxzoom' ),
				'default' => '0.2',
				'comment' => __( 'When "posAutoInside" is enabled and inner zoom fires or "position" 
					option is set to \'inside\' right away - there is no lens. 
					However you will notice that the reaction to mouse movements occure somewhere in the middle of the image; 
					at the edges mostly nothing happens in simmilar scripts. 
					With this option you can adjust how far from the edge mouse movements should be captured. 
					The range is between 0 and 0.5;
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOZP_TOUCHSCROLL' => array(
				'category' => 'mouseoverzoom',
				'useful' => true,
				'title' => __( 'touchScroll', 'ajaxzoom' ),
				'default' => '0.8',
				'comment' => __( 'If width of the mouseover zoom container is more than 80% (0.8) of the widnow width, 
					then for touch devises the inner zoom will be not triggered and the user can scroll down. 
					Click for open AJAX-ZOOM remains. Set this value to 0 if you want to enable the slider for touch devices only. 
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOZP_NOMOUSEOVERZOOM' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'noMouseOverZoom', 'ajaxzoom' ),
				'useful' => true,
				'type' => 'switch',
				'default' => 'false',
				'comment' => __( 'If width of the mouseover zoom container is more than 80% (0.8) of the widnow width, 
					then for touch devises the inner zoom will be not triggered and the user can scroll down. 
					Click for open AJAX-ZOOM remains. Set this value to 0 if you want to enable the slider for touch devices only. 
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOZP_AUTOFLIP' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'autoFlip', 'ajaxzoom' ),
				'default' => '120',
				'comment' => __( 'Flip right to left and bottom to top if less than int px value or false to disable', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_BIGGESTSPACE' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'false',
				'title' => __( 'biggestSpace', 'ajaxzoom' ),
				'comment' => __( 'Overrides position option and instantly chooses the direction, 
					disables autoFlip; playes nicely when zoomWidth and zoomHeight are set to \'auto\'
				', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMFULLSPACE' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'false',
				'title' => __( 'zoomFullSpace', 'ajaxzoom' ),
				'comment' => __( 'Uses full screen height (does not align to the map / disables adjustY) 
					if position is right or left || uses full screen width (does not align to the map / disables adjustX) if position is top or bottom
				', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMWIDTH' => array(
				'category' => 'mouseoverzoom',
				'important' => true,
				'title' => __( 'zoomWidth', 'ajaxzoom' ),
				'default' => '.summary|+40',
				'comment' => __( 'Width of the zoom window e.g. 540 or \'auto\' or 
					jQuery selector|correction value, e.g. \'#refWidthTest|+20\'; 
					so if you want to have a width of the zoom window same as for example a responsive container to the right (so it is fully covered) 
					and max possible height, then define the id of this container to the right, 
					e.g. \'myArticleData\', set "zoomWidth" to \'#myArticleData|+10\' and "zoomHeight" to \'auto\'. 
					New in Ver. b4: if you have a three column design and want to cover both containers to the right, 
					then just define both containers in the jQuery selector, e.g. \'.pb-center-column,.pb-right-column|+20\'; 
					the margin between the containers is not taken into account but you can adjust the result with the second value after vertical bar.
				', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMHEIGHT' => array(
				'category' => 'mouseoverzoom',
				'important' => true,
				'title' => __( 'zoomHeight', 'ajaxzoom' ),
				'default' => '.summary',
				'comment' => __( 'Height of the zoom window e.g. 375, or \'auto\' or jQuery selector|correction value, 
					e.g. \'#refWidthTest|+20\'; if your selector matches more than one element, 
					e.g. \'.pb-center-column,.pb-right-column|+20\', then 
					the highest value will be choosen. This is different from the multiple selector in "zoomWidth", where the values are added.
				', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_AUTOMARGIN' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'autoMargin', 'ajaxzoom' ),
				'default' => '15',
				'comment' => __( 'If zoomWidth or zoomHeight are set to \'auto\', the margin to the edge of the screen', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ADJUSTX' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'adjustX', 'ajaxzoom' ),
				'default' => '15',
				'comment' => __( 'Horizontal margin of the zoom window', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ADJUSTY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'adjustY', 'ajaxzoom' ),
				'default' => '-1',
				'comment' => __( 'Vertical margin of the zoom window', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LENSOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'lensOpacity', 'ajaxzoom' ),
				'useful' => true,
				'default' => '0.30',
				'comment' => __( 'Opacity of the selector lens', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LENSSTYLE' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'isJsObject' => true,
				'title' => __( 'lensStyle', 'ajaxzoom' ),
				'default' => '{}',
				'comment' => __( 'Quickly override css of the lens', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LENSCLASS' => array(
				'category' => 'mouseoverzoom',
				'default' => 'false',
				'comment' => __( 'Set css class for the lens', 'ajaxzoom' ),
				'title' => __( 'lensClass', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMAREABORDERWIDTH' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'zoomAreaBorderWidth', 'ajaxzoom' ),
				'default' => '1',
				'comment' => __( 'Border thickness of the zoom window', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_GALLERYFADE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'galleryFade', 'ajaxzoom' ),
				'default' => '300',
				'comment' => __( 'Speed of inner fade or false', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SHUTTERSPEED' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'shutterSpeed', 'ajaxzoom' ),
				'default' => '150',
				'comment' => __( 'Speed of shutter fadein or false; applies only if image proportions are different from container', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SHOWFADE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'showFade', 'ajaxzoom' ),
				'default' => '300',
				'comment' => __( 'Speed of fade in for mouse over', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_HIDEFADE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'hideFade', 'ajaxzoom' ),
				'default' => '300',
				'comment' => __( 'Speed of fade out for mouse over', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_FLYOUTSPEED' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'flyOutSpeed', 'ajaxzoom' ),
				'default' => 'false',
				'comment' => __( 'Speed for flyout or false to disable', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_FLYOUTTRANSITION' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'flyOutTransition', 'ajaxzoom' ),
				'default' => 'linear',
				'comment' => __( 'Transition of the flyout', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_FLYOUTOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'flyOutOpacity', 'ajaxzoom' ),
				'default' => '0.6',
				'comment' => __( 'Initial opacity for flyout', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_FLYBACKSPEED' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'flyBackSpeed', 'ajaxzoom' ),
				'default' => 'false',
				'comment' => __( 'Speed for fly back or false to disable', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_FLYBACKTRANSITION' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'flyBackTransition', 'ajaxzoom' ),
				'default' => 'linear',
				'comment' => __( 'Transition type of the fly back', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_FLYBACKOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'flyBackOpacity', 'ajaxzoom' ),
				'default' => '0.2',
				'comment' => __( 'Final opacity of fly back', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_AUTOSCROLL' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'false',
				'comment' => __( 'Scroll page when clicked on the thumb and the mouse over preview image is not fully visible', 'ajaxzoom' ),
				'title' => __( 'autoScroll', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOZP_SMOOTHMOVE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'smoothMove', 'ajaxzoom' ),
				'useful' => true,
				'default' => '6',
				'comment' => __( 'Integer bigger than 1 indicates smoother movements; set 0 to disable', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_TINT' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'tint', 'ajaxzoom' ),
				'default' => 'false',
				'comment' => __( 'Color value around the lens or false', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_TINTOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'tintOpacity', 'ajaxzoom' ),
				'default' => '0.3',
				'comment' => __( 'Opacity of the area around the lens when "tint" option is set to some color value', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_TINTFILTER' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'tintFilter', 'ajaxzoom' ),
				'default' => 'false',
				'comment' => __( 'Apply filter to the image, 
					e.g. "blur", "grayscale", "sepia", "invert", "saturate"; see also .axZm_mouseOverEffect>img CSS
				', 'ajaxzoom' ),
			),

			'AJAXZOOM_MOZP_TINTLENSBACK' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Show background image in the lens', 'ajaxzoom' ),
				'title' => __( 'tintLensBack', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SHOWTITLE' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Enable / disable title on zoom window', 'ajaxzoom' ),
				'title' => __( 'showTitle', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_TITLEOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'titleOpacity', 'ajaxzoom' ),
				'default' => '0.5',
				'comment' => __( 'Opacity of the title container', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_TITLEPOSITION' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'titlePosition', 'ajaxzoom' ),
				'default' => 'top',
				'comment' => __( 'Position of the title, top or bottom', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_CURSORPOSITIONX' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'cursorPositionX', 'ajaxzoom' ),
				'default' => '0.5',
				'comment' => __( 'Cursor over lens horizontal offset, 0.5 is middle', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_CURSORPOSITIONY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'cursorPositionY', 'ajaxzoom' ),
				'default' => '0.55',
				'comment' => __( 'Cursor over lens vertical offset, 0.5 is middle', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_TOUCHCLICKABORT' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'touchClickAbort', 'ajaxzoom' ),
				'default' => '500',
				'comment' => __( 'Time in ms after which click is aborted without touch movement and mousehover is initialized', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LOADING' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Display loading information, CSS .mouseOverLoading'),
				'title' => __( 'loading', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LOADING_MESSAGE' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'category' => 'mouseoverzoom',
				'title' => __( 'loadingMessage', 'ajaxzoom' ),
				'default' => '
{
	"en": "Loading...",
	"de": "Loading...",
	"fr": "Loading...",
	"es": "Loading..."
}
				',
				'comment' => __( 'Loading message, not needed, can be just the spinner - see below', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LOADINGWIDTH' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'loadingWidth', 'ajaxzoom' ),
				'default' => '90',
				'comment' => __( 'Width of loading container', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LOADINGHEIGHT' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'loadingHeight', 'ajaxzoom' ),
				'default' => '20',
				'comment' => __( 'Height of loading container', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_LOADINGOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'loadingOpacity', 'ajaxzoom' ),
				'default' => '1.0',
				'comment' => __( 'Opacity of the loading container (the transparent background is set via png image on default, see css class)', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMHINTENABLE' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'true',
				'title' => __( 'zoomHintEnable', 'ajaxzoom' ),
				'comment' => __( 'Enable zoom icon which disappears on mouse hover; 
					css class: .axZm_mouseOverZoomHint; If you want to change the position or the icon simply change the css class;
				', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMHINTTEXT' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'category' => 'mouseoverzoom',
				'title' => __( 'zoomHintText', 'ajaxzoom' ),
				'default' => '
{
	"en" : "Zoom",
	"de" : "Zoom",
	"fr" : "Zoom",
	"es" : "Zoom"
}
				',
				'comment' => __( 'Text which will be appended next to the icon enabled by "zoomHintEnable"', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMMSGHOVER' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'category' => 'mouseoverzoom',
				'title' => __( 'zoomMsgHover', 'ajaxzoom' ),
				'default' => '
				{  
				"en" : "Roll over the image to zoom in",
				"de" : "Für größere Ansicht mit der Maus über das Bild ziehen",
				"fr" : "Survolez l\'image pour zoomer",
				"es" : "Pase el cursor sbore la imagen para hacer zoom con la rueda del ratón"
				}
				',
				'comment' => __( 'Message which can appear under the mouse over zoom, css class: .axZm_mouseOverZoomMsg', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_ZOOMMSGCLICK' => array(
				'type' => 'textarea',
				'isJsObject' => true,
				'category' => 'mouseoverzoom',
				'title' => __( 'zoomMsgClick', 'ajaxzoom' ),
				'default' => '
{
	"en" : "Click to open expanded view",
	"de" : "Klicken Sie auf das Bild, um erweiterte Ansicht zu öffnen",
	"fr" : "Cliquez sur l\'image pour ouvrir la vue élargie",
	"es" : "Haga clic para ampliar la imagen"
}
				',
				'comment' => __( 'Message which can appear under the mouse over zoom when the mouse enters it.', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEINTIME' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideInTime', 'ajaxzoom' ),
				'default' => '200',
				'comment' => __( 'Slide in time if "noMouseOverZoom" is enabled or "touchScroll" option enables for touch devices', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEINEASINGCSS3' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideInEasingCSS3', 'ajaxzoom' ),
				'default' => 'easeOutExpo',
				'comment' => __( 'jQuery equivalent of easing or own function (string), e.g. "cubic-bezier(0.21,0.51,0.4,2.02)", see also cubic-bezier.com', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEINEASING' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideInEasing', 'ajaxzoom' ),
				'default' => 'easeOutExpo',
				'comment' => __( 'jQuery easing function for sliding in (fallback if CSS3 animation is not supported)', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEINSCALE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideInScale'),
				'default' => '0.8',
				'comment' => __( 'Scale initial size (goes to 1.0 while animation)', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEOUTSCALE' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideOutScale', 'ajaxzoom' ),
				'default' => '0.8',
				'comment' => __( 'Scale slideout size', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEOUTOPACITY' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideOutOpacity', 'ajaxzoom' ),
				'default' => '0',
				'comment' => __( 'Slideout opacity', 'ajaxzoom' )
			),

			'AJAXZOOM_MOZP_SLIDEOUTDEST' => array(
				'category' => 'mouseoverzoom',
				'title' => __( 'slideOutDest', 'ajaxzoom' ),
				'default' => '1',
				'comment' => __( 'Target slideout position, possible values: 1, 2 or 3', 'ajaxzoom' )
			),

			'AJAXZOOM_ONINIT' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'title' => __( 'onInit', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Callback function', 'ajaxzoom' ),
			),

			'AJAXZOOM_ONLOAD' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'title' => __( 'onLoad', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Callback function', 'ajaxzoom' ),
			),

			'AJAXZOOM_ONIMAGECHANGE' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'title' => __( 'onImageChange', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Callback function', 'ajaxzoom' ),
			),

			'AJAXZOOM_ONMOUSEOVER' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'title' => __( 'onMouseOver', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Callback function', 'ajaxzoom' ),
			),

			'AJAXZOOM_ONMOUSEOUT' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'title' => __( 'onMouseOut', 'ajaxzoom' ),
				'default' => 'null',
				'comment' => __( 'Callback function', 'ajaxzoom' ),
			),

			'AJAXZOOM_SPINNER' => array(
				'category' => 'mouseoverzoom',
				'type' => 'switch',
				'default' => 'true',
				'comment' => __( 'Use ajax loading spinner without gif files etc', 'ajaxzoom' ),
				'title' => __( 'spinner', 'ajaxzoom' ),
			),

			'AJAXZOOM_SPINNERPARAM' => array(
				'category' => 'mouseoverzoom',
				'type' => 'textarea',
				'isJsObject' => true,
				'textareaHeight' => '250px',
				'title' => __( 'spinnerParam', 'ajaxzoom' ),
				'default' => '
{ 
	lines: 11,
	length: 3,
	width: 3,
	radius: 4,
	corners: 1,
	rotate: 0, 
	color: "#FFFFFF",
	speed: 1,
	trail: 90, 
	shadow: false,
	hwaccel: false,
	className: "spinner", 
	zIndex: 2e9,
	top: 0, 
	left: 1 
}
				',
				'comment' => __( 'Spinner options, for more info see: 
					<a href=http://fgnass.github.com/spin.js/ target=_blank>http://fgnass.github.com/spin.js/ <i class="icon-external-link-sign"></i></a>
				', 'ajaxzoom' ),
			)

		);
	}	
}

Ajaxzoom::$axzmh;
Ajaxzoom::$zoom;