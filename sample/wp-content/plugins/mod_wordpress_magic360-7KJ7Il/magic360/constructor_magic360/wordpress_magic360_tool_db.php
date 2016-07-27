<?php
function magictoolbox_WordPress_Magic360_create_teble() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magic360_store';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
          id int unsigned NOT NULL auto_increment,
          name varchar(300) DEFAULT NULL,
          shortcode varchar(50) DEFAULT NULL,
          startimg varchar(10) DEFAULT NULL,
          images text DEFAULT NULL,
          options text DEFAULT NULL,
          additional_options text DEFAULT NULL,
          UNIQUE KEY id (id));";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

function magictoolbox_WordPress_Magic360_remove_teble() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magic360_store';

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
    }
}

function magictoolbox_WordPress_Magic360_remove_element($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magic360_store';

    return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ));
}

function magictoolbox_WordPress_Magic360_add_data_to_table($name, $shortcode, $startimg, $images, $options, $additional_options) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magic360_store';

    $r = $wpdb->insert($table_name, array('name' => $name, 'shortcode' => $shortcode, 'startimg' => $startimg, 'images' => $images, 'options' => $options, 'additional_options' => $additional_options));

    if ($r) {
        $r = $wpdb->insert_id;
    }

    return $r;
}

function magictoolbox_WordPress_Magic360_get_data($field=false, $value=false) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'magic360_store';

    if (!$field) {
        return $wpdb->get_results("SELECT * FROM ".$table_name);
    } else {
        return $wpdb->get_results("SELECT * FROM ".$table_name." WHERE ".$field." = ".$value);
    }
}

?>
