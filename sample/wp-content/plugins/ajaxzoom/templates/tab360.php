<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $maxImageSize = ini_get( 'upload_max_filesize' ); ?>

<input type="hidden" name="id_360set" id="id_360set" value="" />

<div id="product-images360" class="entry-edit" style="display:none">
	<div class="fieldset-wrapper">
		<div class="fieldset-wrapper-title admin__fieldset-wrapper-title">
			<strong class="title"><?php echo __( 'Images', 'ajaxzoom' ) ?></strong>
		</div>
	</div>
	<div class="fieldset fieldset-wide" id="group_fields9">
		<div class="hor-scroll">
			<input type="hidden" name="submitted_tabs[]" value="Images360" />

			<div class="row">
				<div class="form-group">
					<label class="control-label col-lg-3 file_upload_label">
						<span class="label-tooltip" data-toggle="tooltip" title="<?php echo __( 'Format: JPG, GIF, PNG. Filesize: '.$maxImageSize.' max', 'ajaxzoom' ); ?>">
							<?php echo __( 'Add a new image to this image set', 'ajaxzoom' ) ?><br />
							<?php echo __( 'Format: JPG, GIF, PNG. Filesize: '.$maxImageSize.' max', 'ajaxzoom' ); ?>
						</span>
					</label>
					<div class="col-lg-9">
						<?php require "uploader.php"; ?>
					</div>
				</div>
			</div>
			<div class="grid">
				<table class="wp-list-table widefat fixed striped posts" id="imageTable360">
					<thead>
						<tr class="headings">
							<th class="data-grid-th"><?php echo __( 'Image', 'ajaxzoom' ) ?></th>
							<th class="data-grid-th"><?php echo __( 'Actions', 'ajaxzoom' ) ?></th>
						</tr>
					</thead>
					<tbody id="imageList360">
					</tbody>
				</table>
			</div>
			<table id="lineType360" style="display:none;">
				<tr id="image_id">
					<td>
						<img src="<?php echo $uri ?>/ajaxzoom/image_path.gif" alt="legend" title="legend" class="img-thumbnail" />
					</td>
					<td style="width:75px;">
						<a type="button" class="delete_product_image360 scalable delete" href="">
							<?php echo __( 'Delete this image', 'ajaxzoom' ) ?>
						</a>
					</td>
				</tr>
			</table>
			<div class="az-panel-footer">
				<button class="button btn_cancel"><?php echo __( 'Cancel', 'ajaxzoom' ) ?></button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function imageLine360(id, path) {
		line = jQuery( "#lineType360" ).html();
		line = line.replace( /image_id/g, id );
		line = line.replace( /"(.*?)path\.gif"/g, path );
		line = line.replace( /<tbody>/gi, "" );
		line = line.replace( /<\/tbody>/gi, "" );
		
		jQuery( "#imageList360" ).append( line );
	}

	jQuery(function ($) {
		
		function afterDeleteProductImage360(data) {
			
			if ( data ) {
				id = data.content.id;
				if ( data.status == 'ok' ) {
					$( "#" + id ).remove();
				}

				showSuccessMessage( data.confirmations );
			}
		}

		$( '.btn_cancel' ).on( 'click', function(e) {
			e.preventDefault();
			$('#product-images360').hide();
		} );

		$( 'body' ).on( 'click', '.delete_product_image360', function(e) { 
			e.preventDefault();
			var id = $(this).parent().parent().attr( 'id' );
			var id_360set = $( '#id_360set' ).val();
			var ext = $( this ).parent().parent().find( 'img' ).attr( 'src' ).split( '.' ).pop();
			var params = {
				"action": "ajaxzoom_delete_product_image_360",
				"id_image": id,
				'id_360set': id_360set,
				"ext": ext,
				"id_product": ajaxzoomData.id_product
			};

			if ( confirm( "<?php echo __( 'Are you sure?', 'ajaxzoom' ) ?>" ) ) {
				doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterDeleteProductImage360 );
			}
		} );

		$( '.fancybox' ).fancybox();
	});
</script>