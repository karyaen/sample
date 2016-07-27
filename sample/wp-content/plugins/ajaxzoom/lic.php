<?php

if ( ! isset( $zoom ) ){
	exit;
}

$licenses = array();

// Prevent loading all classes and read licenses stored in db faster!
try {
	$fileStr = php_strip_whitespace( __DIR__ . '/../../../wp-config.php');
	$fileStr = str_replace('require_once(ABSPATH . \'wp-settings.php\');', '', $fileStr);
	$fileStr = str_replace('<?php', '', $fileStr);
	$fileStr = str_replace('?>', '', $fileStr);
	eval($fileStr);
	@ini_set( 'display_errors', 0);
	error_reporting( 0 );

	if (function_exists('mysqli_connect')){
		$mysqli = mysqli_connect((string)DB_HOST, (string)DB_USER, (string)DB_PASSWORD, (string)DB_NAME);
		$data_query = mysqli_query($mysqli, "SELECT `option_value` FROM `" . (string)$table_prefix . "options` WHERE `option_name` = 'AJAXZOOM_LICENSES'");
		$data = mysqli_fetch_array($data_query);
		$licenses = unserialize($data['option_value']);
		mysqli_close($mysqli);
	} else {
		$db_connect = mysql_connect((string)DB_HOST, (string)DB_USER, (string)DB_PASSWORD);
		$db = mysql_select_db((string)DB_NAME, $db_connect);
		$data_query = mysql_query("SELECT `option_value` FROM `" . (string)$table_prefix . "options` WHERE `option_name` = 'AJAXZOOM_LICENSES'");
		$data = mysql_fetch_array($data_query);
		$licenses = unserialize($data['value']);
		mysql_close($db_connect);
	}

} catch (Exception $e) {

	require_once( __DIR__ . '/../../../wp-config.php' );
	@ini_set( 'display_errors', 0);
	error_reporting( 0 );

	$licenses = get_option( 'AJAXZOOM_LICENSES' );
}

if (!empty($licenses)){
	foreach ($licenses as $l){
		$zoom['config']['licenses'][$l['domain']] = array(
			'licenceType' => $l['type'],
			'licenceKey' => $l['key'],
			'error200' => $l['error200'],
			'error300' => $l['error300']
		);
	}
}
?>