<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/javascript">

var ajaxzoomData = {
	"ajaxUrl"	: '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
	"id_product": <?php echo $product_id; ?>,
	"uri"		: '<?php echo $uri; ?>',
	"image_path": '<?php echo $uri . '/ajaxzoom/image_path.gif'; ?>'
}

function showSuccessMessage( message ) {
	jQuery( 'div#ajaxzoom h2' ).append( '<span class="az-status"> ' + message + '</span>' );
	setTimeout(function() {
		jQuery( 'div#ajaxzoom h2 span.az-status' ).hide(2000, function () {
			jQuery( 'div#ajaxzoom h2 span.az-status' ).remove();
		});
	}, 1000);
}

function showSuccessMessage2d( message ) {
	jQuery( 'div#ajaxzoom2d h2' ).append( '<span class="az-status"> ' + message + '</span>' );
	setTimeout(function() {
		jQuery( 'div#ajaxzoom2d h2 span.az-status' ).hide(2000, function () {
			jQuery( 'div#ajaxzoom2d h2 span.az-status' ).remove();
		});
	}, 1000);
}

function doAdminAjax360( url, data, success_func, error_func ) {
	jQuery.ajax( {
		url : url,
		data : data,
		type : 'POST',
		success : function( data ){
			if ( success_func ) {
				return success_func( data );
			}

			data = jQuery.parseJSON( data );
			if ( data.confirmations.length != 0 ) {
				showSuccessMessage( data.confirmations );
			} else {
				showErrorMessage( data.error );
			}
		},
		error : function( data ) {
			if ( error_func ) {
				return error_func( data );
			}

			alert( "[TECHNICAL ERROR]" );
		}
	} );
}
</script>

<?php require 'tab360-settings.php'; ?>
<?php require 'tab360-sets.php'; ?>
<?php require 'tab360.php'; ?>