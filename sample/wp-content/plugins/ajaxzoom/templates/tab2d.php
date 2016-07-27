<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php $maxImageSize = ini_get( 'upload_max_filesize' ); ?>

<style>
	.popup-variations {
		position: relative;
	}
	#template-variations {
		display: none;
	}
	#imageList2d {
		cursor: move;
	}
</style>

<div id="product-images2d" class="entry-edit">
	<div class="row">
		<div class="form-group">
			<div class="az-form">
			<?php echo __( ' This function is only needed if this product is "Variable product" and you want to assign / upload more than one images to the variations. <br />
				(WooCommerce supports only one image on default without additional plugins)' ); ?>
			</div>
			<label class="control-label col-lg-3 file_upload_label">
				<span class="label-tooltip" data-toggle="tooltip" title="<?php echo __( 'Format: JPG, GIF, PNG. Filesize: '.$maxImageSize.' max', 'ajaxzoom' ); ?>">
					<?php echo __( 'Add a new image', 'ajaxzoom' ) ?><br />
					<?php echo __( 'Format: JPG, GIF, PNG. Filesize: '.$maxImageSize.' max', 'ajaxzoom' ); ?>
				</span>
			</label>
			<div class="col-lg-9">
				<?php require "uploader2d.php"; ?>
			</div>
		</div>
	</div>
	<div class="grid">
		<table class="wp-list-table widefat fixed striped posts" id="imageTable2d">
			<thead>
				<tr class="headings">
					<th class="data-grid-th"><?php echo __( 'Image', 'ajaxzoom' ) ?></th>
					<th class="data-grid-th"><?php echo __( 'Actions', 'ajaxzoom' ) ?></th>
				</tr>
			</thead>
			<tbody id="imageList2d">
				<?php foreach($images as $image): ?>
				<tr data-id="<?php echo $image['id'] ?>">
					<td>
						<img src="<?php echo $image['thumb'] ?>" alt="" title="" class="img-thumbnail" />
					</td>
					<td >
						<a type="button" class="delete_product_image2d scalable delete" href="">
							<?php echo __( 'Delete this image', 'ajaxzoom' ) ?>
						</a>
						&nbsp;|&nbsp;
						<a type="button" class="btn-variations scalable" data-variations="<?php echo $image['variations'] ?>" href="">
							<?php echo __( 'Variations', 'ajaxzoom' ) ?>
						</a>
						<div class="popup-variations"></div>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
		<table id="lineType2d" style="display:none;">
			<tr data-id="image_id">
				<td>
					<img src="<?php echo $uri ?>/ajaxzoom/image_path.gif" alt="legend" title="legend" class="img-thumbnail" />
				</td>
				<td>
					<a type="button" class="delete_product_image2d scalable delete" href="">
						<?php echo __( 'Delete this image', 'ajaxzoom' ) ?>
					</a>
					&nbsp;|&nbsp;
					<a type="button" class="btn-variations scalable" data-variations="data_variations" href="">
						<?php echo __( 'Variations', 'ajaxzoom' ) ?>
					</a>
					<div class="popup-variations"></div>
				</td>
			</tr>
		</table>
	</div>	
</div>

<div id="template-variations">
	<ul>
		<?php foreach ($variations as $key => $value): ?>
		<li><input type="checkbox" name="variations[]" value="<?php echo $key ?>"><?php echo $value; ?></li>
		<?php endforeach; ?>
	</ul>
	<button class="button button-primary variations-save">Save</button> <button class="button variations-cancel">Cancel</button>
</div>


<script type="text/javascript">
	function imageLine2d( id, path ) {
		var line = jQuery( "#lineType2d" ).html();
		line = line.replace( /image_id/g, id );
		line = line.replace( /"(.*?)path\.gif"/g, path );
		line = line.replace( /<tbody>/gi, "" );
		line = line.replace( /<\/tbody>/gi, "" );
		line = line.replace( /data_variations/gi, "" );
		
		jQuery( "#imageList2d" ).append( line );
	}

	jQuery(function ( $ ) {
		
		$('#imageList2d').sortable({
			stop: function( event, ui ) {
				var sort = [];
				$("#imageList2d tr").each(function(i, el){
					sort.push($(this).data('id'));
				});

				var params = {
					"action": "ajaxzoom_save_2d_sort",
					"sort": sort,
					"id_product": ajaxzoomData.id_product
				};

				doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterSave2dSort );				
			}
		});

		function afterDeleteProductImage2d( data ) {
			
			if ( data ) {
				var id = data.content.id;
				if ( data.status == 'ok' ) {
					$( "#imageTable2d").find('tr[data-id=' + id + ']').remove();
				}

				showSuccessMessage( data.confirmations );
			}
		}

		function afterSave2dVariations( data ) {
			$('#imageList2d tr[data-id=' + data.id + '] td .popup-variations').hide('slow').html('');
			showSuccessMessage2d( data.confirmations );
		}

		function afterSave2dSort( data ) {
			showSuccessMessage2d( data.confirmations );
		}

		$( 'body' ).on( 'click', '.btn-variations', function( e ) {
			e.preventDefault();
			var values =$(this).data('variations').toString().split(',');

			var v = $( "#template-variations" ).html();
			var popup = $(this).parent().find('.popup-variations');
			if(popup.html() == '') {
				popup.html(v).show();

				$.each(popup.find('input[type=checkbox]'), function(index, checkbox) {
					if($.inArray($(this).val(), values) != -1) {
						$(this).prop('checked', true);
					}
				});

			} else {
				popup.html('');
			}

		} );

		$( 'body' ).on( 'click', '.variations-cancel', function(e) {
			e.preventDefault();
			
			$(this).parent().html('');
			
		} );

		$( 'body' ).on( 'click', '.variations-save', function(e) {
			e.preventDefault();
			
			var parent = $(this).parent().parent().parent();
			var id = parent.data('id');
			var variations = [];

			$.each(parent.find('input[name="variations[]"]:checked'), function () {
				variations.push($(this).val())
			});

			parent.find('.btn-variations').data('variations', variations.join(','))
			
			var params = {
				"action": "ajaxzoom_save_2d_variations",
				"id_image": id,
				"variations": variations,
				"id_product": ajaxzoomData.id_product
			};

			doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterSave2dVariations );
			
		} );
		

		$( 'body' ).on( 'click', '.delete_product_image2d', function(e) { 
			e.preventDefault();
			var id = $(this).parent().parent().data( 'id' );
			
			var params = {
				"action": "ajaxzoom_delete_product_image_2d",
				"id_image": id,
				"id_product": ajaxzoomData.id_product
			};

			if ( confirm( "<?php echo __( 'Are you sure?', 'ajaxzoom' ) ?>" ) ) {
				doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterDeleteProductImage2d );
			}
		} );

		$( '.fancybox' ).fancybox();
	});
</script>