<?php
function magictoolbox_WordPress_Magic360_get_img_url_with_new_size($options, $images, $size) {
    require_once(preg_replace('/\/constructor_magic360/is', '', dirname(__FILE__)) . '/core/magictoolbox.imagehelper.class.php');
    $result = array();

    if (!$options || !$images || !$size) {
        return $result;
    }

    $url = site_url();
    $shop_dir = ABSPATH;
    $image_dir = 'wp-content/uploads/';

    $imagehelper = new MagicToolboxImageHelperClass($shop_dir, $image_dir.'magictoolbox_cache', $options, null, $url);

    foreach ($images as $value) {
        $medium = $imagehelper->create($value, $size);
        $result[] = $medium;
    }

    return $result;
}

function magictoolbox_WordPress_Magic360_get_option($arr) {
    return empty($arr['value']) ? empty($arr['default']) : $arr['value'];
}

function magictoolbox_WordPress_Magic360_shortcode( $attrs ) {
    global $magictoolbox_Magic360_page_has_shortcode;
    $magictoolbox_Magic360_page_has_shortcode = true;

    if (is_numeric($attrs['id'])) {
        $m_tool = magictoolbox_WordPress_Magic360_get_data("id", $attrs['id']);
    } else {
        $m_tool = magictoolbox_WordPress_Magic360_get_data("shortcode", '"'.$attrs['id'].'"');
    }

    if (!$m_tool || 0 == count($m_tool)) { return ''; }

    $tool_images = array();
    $tool_large_images = array();
    $tool_start_image = '';
    $image_dir = 'wp-content/uploads/';

    $m_images = $m_tool[0]->images;
    $m_images = explode(",", $m_images);
    $m_additionalOptions = $m_tool[0]->additional_options;
    $m_options = $m_tool[0]->options;
    $m_startImg = $m_tool[0]->startimg;

    $m_options = explode(";", $m_options);
    array_pop($m_options);
    $tmp = array();
    foreach ($m_options as $value) {
        $value = trim($value);
        if (!empty($value)) {
            $value = explode(":", $value);
            $tmp[$value[0]] = $value[1];
        }
    }
    $m_options = $tmp;

    $opt = 'columns:'. $m_options['columns'] . ';rows:' . $m_options['rows'] . ';';

    if ('default' == $m_additionalOptions) {
        $m_additionalOptions = array();
        $m_startImg = false;
        $imgIndex = 0;
        $settings = get_option("WordPressMagic360CoreSettings");
        $settings = $settings['default'];

        $c = (int)magictoolbox_WordPress_Magic360_get_option($settings['columns']);
        $r = (int)magictoolbox_WordPress_Magic360_get_option($settings['rows']);

        if ($c > count($m_images) && 1 == $r) {
            $c = count($m_images);
        }

        $sc = magictoolbox_WordPress_Magic360_get_option($settings['start-column']);
        if ('auto' == $sc) {
            $sc = 1;
        }
        $sc = (int)$sc;

        $sr = magictoolbox_WordPress_Magic360_get_option($settings['start-row']);
        if ('auto' == $sr) {
            $sr = 1;
        }
        $sr = (int)$sr;

        if ($r > 1) {
            $imgIndex = $c * $sr - ($c - $sc) - 1;
            $imgIndex += (count($m_images) * $sr);
        } else {
            $imgIndex = $sc;
        }

        $m_startImg = $m_images[$imgIndex];

        if (!$m_startImg) {
            $m_startImg = $m_images[0];
        }

        $tool_start_image = $m_startImg;
    } else {
        $m_additionalOptions = explode(";", $m_additionalOptions);
        $tmp = array();
        foreach ($m_additionalOptions as $value) {
            $value = trim($value);
            if (!empty($value)) {
                $value = explode(":", $value);

                if (('start-row' == $value[0] || 'start-column' == $value[0]) && 'auto' == $value[1]) {
                    $value[1] = 1;
                }

                $tmp[$value[0]] = $value[1];
                $opt .= ($value[0].':'.$value[1].';');
            }
        }
        $m_additionalOptions = $tmp;
    }

    for ($i = 0; $i < count($m_images); $i++) {
        if ('custom' == $m_options['resize-image']) {
            $tmp = wp_get_attachment_metadata($m_images[$i]);
            $tmp = '/'.$image_dir.$tmp['file'];
            $tool_images[] = $tmp;
            $tool_large_images[] = $tmp;
        } else {
            $tool_large_images[] = wp_get_attachment_url($m_images[$i]);
            $tmp = wp_get_attachment_image_src($m_images[$i], $m_options['resize-image']);
            $tool_images[] = $tmp[0];
        }

        if ($m_images[$i] == $m_startImg) {
            $tool_start_image = $i;
        }
    }

    $watermark_options = new MagicToolboxParamsClass();

    if ('' == $m_options['watermark']) {
        $watermark_options->setValue('watermark', '');
    } else {
        $tmp = wp_get_attachment_metadata($m_options['watermark']);
        $tmp = $image_dir.$tmp['file'];
        $watermark_options->setValue('watermark', '/'.$tmp);
    }

    $watermark_options->setValue( 'watermark-max-width',  $m_options['watermark-max-width']  );
    $watermark_options->setValue( 'watermark-max-height', $m_options['watermark-max-height'] );
    $watermark_options->setValue( 'watermark-opacity',    $m_options['watermark-opacity']    );
    $watermark_options->setValue( 'watermark-position',   $m_options['watermark-position']   );
    $watermark_options->setValue( 'watermark-offset-x',   $m_options['watermark-offset-x']   );
    $watermark_options->setValue( 'watermark-offset-y',   $m_options['watermark-offset-y']   );

    if ('custom' == $m_options['resize-image']) {
        $tool_large_images = magictoolbox_WordPress_Magic360_get_img_url_with_new_size($watermark_options, $tool_large_images, 'original');
        $tool_large_images = join(' ', $tool_large_images);
        if ('false' == $m_options['watermark-to-thumbnail']) {
            $watermark_options->setValue('watermark', '');
        }
        $tool_images = magictoolbox_WordPress_Magic360_get_img_url_with_new_size($watermark_options, $tool_images, array($m_options['thumb-max-width'], $m_options['thumb-max-height']));
        $tool_start_image = $tool_images[$tool_start_image];
        $tool_images = join(' ', $tool_images);
    } else {
        $tool_start_image = wp_get_attachment_image_src($m_startImg, $m_options['resize-image']);
        $tool_start_image = $tool_start_image[0];
        $tool_large_images = join(' ', $tool_large_images);
        $tool_images = join(' ', $tool_images);
    }

    $opt .= ' images: '.$tool_images.';';
    $opt .= ' large-images: '.$tool_large_images.';';

    $html = '<a class="Magic360" data-magic360-options="' . $opt . '" href="'.$tool_start_image.'">';
    $html .= '<img src="'.$tool_start_image.'">';
    $html .= '</a>';

    return $html;
}

function magictoolbox_WordPress_Magic360_get_tiny_mce_data() {
    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)){
        return;
    }

    $nonce = $_POST['nonce'];
    $result = '{"error": "error"}';

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        $result = '{"error": "verification failed"}';
    } else {
        $table_data = magictoolbox_WordPress_Magic360_get_data();
        if ($table_data && count($table_data) > 0) {
            $result = '[';
            foreach($table_data as $value) {
                $sc = $value->shortcode;
                if (empty($sc)) {
                    $sc = "null";
                }
                $result .= '{';
                $result .= '"id":"'.$value->id.'",';
                $result .= '"name":"'.$value->name.'",';
                $result .= '"shortcode":"'.$sc.'"';
                $result .= '},';
            }
            $result = preg_replace('/,$/is', '', $result);
            $result .= ']';
        } else {
            $result = '{"error": "empty"}';
        }
    }
    echo $result;
    wp_die();
}

function magictoolbox_WordPress_Magic360_add_my_tc_button() {
    global $typenow;
    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
        return;
    }

    // verify the post type
    if( ! in_array( $typenow, array( 'post', 'page', 'product' , 'wpsc-product', 'tcp_product' ) ) ) {
        return;
    }

    // check if WYSIWYG is enabled
    if ( get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", "magictoolbox_WordPress_Magic360_add_tinymce_plugin");
        add_filter('mce_buttons', 'magictoolbox_WordPress_Magic360_register_tinymce_button');
        echo '<script>'.
            'var magictoolbox_WordPress_Magic360_admin_modal_object = {'.
                'ajax: "'.(get_site_url().'/wp-admin/admin-ajax.php').'",'.
                'nonce: "'.wp_create_nonce('magic-everywhere'). '"'.
            '};'.
        '</script>';
    }
}

function magictoolbox_WordPress_Magic360_add_tinymce_plugin($plugin_array) {
    $plugin_array["magictoolbox_WordPress_Magic360_shortcode"] = plugins_url( 'js/tiny_mce_button.js', __FILE__ );
    return $plugin_array;
}

function magictoolbox_WordPress_Magic360_register_tinymce_button($buttons) {
   array_push($buttons, "magictoolbox_WordPress_Magic360_shortcode");
   return $buttons;
}

function magictoolbox_WordPress_Magic360_button_css() {
    $screen = get_current_screen();

    if ( $screen->id == 'page' || $screen->id == 'post' || $screen->id == 'product' || $screen->id == 'wpsc-product' || $screen->id == 'tcp_product') {
        wp_register_style('magictoolbox_WordPress_Magic360_tinymce_button_css', plugin_dir_url( __FILE__ ).('css/tiny_mce_button.css'), array());
        wp_enqueue_style('magictoolbox_WordPress_Magic360_tinymce_button_css');
    }
}

function magictoolbox_WordPress_Magic360_replase_post_shortcode($arr, $on, $nn, $r) {
    if ($arr && count($arr) > 0) {
        foreach($arr as $value) {
            if (!$r) {
                $value->post_content = preg_replace('/(\[\s*magic360\s*id\s*=\s*")\s*'.$on.'\s*("\s*\])/is', '${1}'.$nn.'${2}', $value->post_content);
            } else {
                $value->post_content = preg_replace('/\[\s*magic360\s*id\s*=\s*"\s*'.$on.'\s*"\s*\]/is', '', $value->post_content);
            }
            wp_update_post(array('ID' => $value->ID, 'post_content' => $value->post_content));
        }
    }
}

function magictoolbox_WordPress_Magic360_edit_posts_and_pages($oldname = 'empty', $newname = 'empty', $remove_short_code = false) {
    $args = array( 'numberposts' => -1 );
    magictoolbox_WordPress_Magic360_replase_post_shortcode(get_posts( $args ), $oldname, $newname, $remove_short_code);

    $args = array( 'number' => '' );
    magictoolbox_WordPress_Magic360_replase_post_shortcode(get_pages( $args ), $oldname, $newname, $remove_short_code);

    $args = array( 'post_type' => 'product');
    $loop = new WP_Query( $args );
    if (!empty($loop->posts)) {
        magictoolbox_WordPress_Magic360_replase_post_shortcode($loop->posts, $oldname, $newname, $remove_short_code);
    }

    $args = array( 'post_type' => 'wpsc-product');
    $loop = new WP_Query( $args );
    if (!empty($loop->posts)) {
        magictoolbox_WordPress_Magic360_replase_post_shortcode($loop->posts, $oldname, $newname, $remove_short_code);
    }

    $args = array( 'post_type' => 'tcp_product');
    $loop = new WP_Query( $args );
    if (!empty($loop->posts)) {
        magictoolbox_WordPress_Magic360_replase_post_shortcode($loop->posts, $oldname, $newname, $remove_short_code);
    }
}

function magictoolbox_ajax_WordPress_Magic360_copy() {
    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)){
        return;
    }

    $nonce = $_POST['nonce'];
    $id = (int)$_POST['id'];
    $result = "null";
    $tableId = "null";

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        $result = "\"verification failed\"";
    } else {
        $res = magictoolbox_WordPress_Magic360_get_data("id", $id);
        if (!$res) {
            $result = "\"error\"";
        } else {
            $tableId = magictoolbox_WordPress_Magic360_add_data_to_table($res[0]->name.' (copy)', '', $res[0]->startimg, $res[0]->images, $res[0]->options, $res[0]->additional_options);
        }
    }
    ob_end_clean();
    echo "{\"error\":".$result.",\"id\":".$tableId."}";
    wp_die();
}

function magictoolbox_ajax_WordPress_Magic360_remove_spins() {
    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)){
        return;
    }

    $nonce = $_POST['nonce'];
    $ids = $_POST['ids'];
    $result = 'null';

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        $result = "\"verification failed\"";
    } else {
        foreach ($ids as $value) {

            /*
            // turn off to searching shortcodes
            $spin_data = magictoolbox_WordPress_Magic360_get_data("id", $value);
            $shortcodeId = empty($spin_data[0]->shortcode) ? $spin_data[0]->id : $spin_data[0]->shortcode;
            magictoolbox_WordPress_Magic360_edit_posts_and_pages($shortcodeId, null, true);
            */

            magictoolbox_WordPress_Magic360_remove_element($value);
        }
    }

    ob_end_clean();
    echo "{\"error\":".$result."}";
    wp_die();
}

function magictoolbox_ajax_WordPress_Magic360_check_shortcode() {
    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)) { return; }

    $result = "null";
    $nonce = $_POST['nonce'];
    $shortcode = $_POST['shortcode'];

    $id = $_POST['id'];
    if ('null' != $id) {
        $id = (int)$id;
    }

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        $result = "\"verification failed\"";
    } else {
        $data = magictoolbox_WordPress_Magic360_get_data();

        foreach ($data as $spin) {
            if ('null' == $id || $id != $spin->id) {
                if ($spin->shortcode == $shortcode) {
                    $result = "\"not unique\"";
                    break;
                }
            }
        }
    }

    ob_end_clean();
    echo "{\"error\":".$result."}";
    wp_die();
}

function magictoolbox_ajax_WordPress_Magic360_save() {
    global $wpdb;

    if(!(is_array($_POST) && defined('DOING_AJAX') && DOING_AJAX)) { return; }

    $result = "null";
    $tableId = "null";
    $nonce = $_POST['nonce'];
    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $shortcode = $_POST['shortcode'];
    $startimg = $_POST['startimg'];
    $images = $_POST['images'];
    $options = $_POST['options'];
    $additional_options = $_POST['additional_options'];
    $shortcodeId = $id;

    if ( !wp_verify_nonce( $nonce, 'magic-everywhere' ) ) {
        $result = "\"verification failed\"";
    } else {
        $oldData = magictoolbox_WordPress_Magic360_get_data("id", $id);
        if (!count($oldData)) {
            $tableId = magictoolbox_WordPress_Magic360_add_data_to_table($name, $shortcode, $startimg, $images, $options, $additional_options);
            if (false == $tableId) {
                $result = "\"db insert failed\"";
                $tableId = "null";
            }
        } else {
            $table_name = $wpdb->prefix . 'magic360_store';

            /*
            // turn off to searching shortcodes

            if (!empty($shortcode)) { $shortcodeId = $shortcode; }
            $old_shortcodeId = $oldData[0]->id;
            if (!empty($oldData[0]->shortcode)) { $old_shortcodeId = $oldData[0]->shortcode; }
            magictoolbox_WordPress_Magic360_edit_posts_and_pages($old_shortcodeId, $shortcodeId);
            */

            $res = $wpdb->update($table_name, array(
                    'name' => $name,
                    'shortcode' => $shortcode,
                    'startimg' => $startimg,
                    'images' => $images,
                    'options' => $options,
                    'additional_options' => $additional_options
                ),
                array('id' => $id),
                array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );

            if (false == $res) {
                $result = "\"db update failed\"";
            }
        }
    }

    ob_end_clean();
    echo "{\"error\":".$result.",\"id\":".$tableId."}";
    wp_die();
}

?>
