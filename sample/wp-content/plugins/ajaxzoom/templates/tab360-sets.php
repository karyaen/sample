<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<br>
<br>
<div id="product-images360sets" class="entry-edit">
	<div class="fieldset-wrapper">
		<div class="fieldset-wrapper-title admin__fieldset-wrapper-title">
			<strong class="az-title"><?php echo __( '360/3D Views', 'ajaxzoom' ) ?></strong>
		</div>
	</div>
	<div class="fieldset fieldset-wide" id="group_fields9">
		<div class="hor-scroll">
			<div class="az-button-add360-wrapper">
				<a class="button link_add" href="#"> <i class="icon-plus"></i> <?php echo __( 'Add a new 360/3D view', 'ajaxzoom' ) ?></a>
			</div>

			<div class="az-form" id="newForm" style="display:none;">
				<table cellspacing="0" class="az-form-list">
					<tbody>
						<tr>
							<td class="label"><label for="set_name"><?php echo __( 'Create a new', 'ajaxzoom' ) ?></label></td>
							<td class="value">
								<input type="text" id="set_name" name="set_name" value="" />
								<p class="note"><?php echo __( 'Please enter any name', 'ajaxzoom' ) ?></p>
							</td>
							<td class="scope-label"><span class="nobr"></span></td>
						</tr>
						<?php if ( $groups ): ?>
						<tr>
							<td colspan="3"><b><?php echo __( 'OR', 'ajaxzoom' ) ?></b></td>
						</tr>
						<tr>
							<td class="label"><label for="existing"><?php echo __( 'Add to existing 3D as next row', 'ajaxzoom' ) ?></label></td>
							<td class="value">
								<select name="existing" id="existing">
									<option value="" style="min-width: 100px"><?php echo __( 'Select', 'ajaxzoom' ) ?></option>
									<?php foreach ( $groups as $group ): ?>
									<option value="<?php echo $group['id_360'] ?>"><?php echo $group['name'] ?></option>
									<?php endforeach ?>
								</select>
								<p class="note"><?php echo __( 'You should not select anything here unless you want to create 3D (not 360) which contains more than one row!', 'ajaxzoom' ) ?></p>
							</td>
							<td class="scope-label"><span class="nobr"></span></td>
						</tr>
						<?php endif; ?>
						
						<tr>
							<td class="label"><label for="zip"><?php echo __( 'Add images from ZIP archive', 'ajaxzoom' ) ?></label></td>
							<td class="value">
								<input type="checkbox" id="zip" name="zip" value="1" />
								<p class="note"><?php echo __( 'This is the most easy and quick way of adding 360 views to your product! Upload over FTP your 360\'s zipped (each images set in one zip file) to ' . $uri . '/ajaxzoom/zip/ directory. After you did so these zip files will instantly appear in the select field below. All you have to do then is select one of the zip files and press \'add\' button. Images from the selected zip file will be instantly imported.', 'ajaxzoom' ) ?></p>
							</td>
							<td class="scope-label"><span class="nobr"></span></td>
						</tr>
						<tr class="field-arcfile" style="display:none;">
							<td class="label"><label for="arcfile"><?php echo __( 'Select ZIP archive or folder', 'ajaxzoom' ) ?></label></td>
							<td class="value">
								<?php if ( isset( $files ) && count( $files ) > 0 ): ?>
								<select name="arcfile" id="arcfile">
									<option value=""><?php echo __( 'Select', 'ajaxzoom' ) ?></option>
									<?php foreach ( $files as $file ): ?>
									<option value="<?php echo $file ?>"><?php echo $file ?></option>
									<?php endforeach; ?>
								</select>
								<?php else: ?>
								<p><b><?php echo __( 'There are no files found in the "/wp-content/plugins/axaxzoom/zip" folder', 'ajaxzoom' ) ?></b></p>
								<?php endif; ?>
							</td>
							<td class="scope-label"><span class="nobr"></span></td>
						</tr>
						<tr class="field-arcfile" style="display:none;">
							<td class="label"><label for="zip"><?php echo __( 'Delete Zip/Dir after import', 'ajaxzoom' ) ?></label></td>
							<td class="value">
								<input type="checkbox" id="delete" name="delete" value="1" />
								<p class="note"><?php echo __( '', 'ajaxzoom' ) ?></p>
							</td>
							<td class="scope-label"><span class="nobr"></span></td>
						</tr>

						<tr>
							<td></td>
							<td>
								<button type="button" class="button button-primary save save_set"><?php echo __( 'Add', 'ajaxzoom' ) ?></button>
								<button type="button" class="button" id="btn-cancel-set"><?php echo __( 'Cancel', 'ajaxzoom' ) ?></button>
							</td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="row">
				<div class="grid">
					<table class="wp-list-table widefat fixed striped posts" id="az-image-table-sets" >
						<thead>
							<tr class="headings">
								<th class="data-grid-th"><?php echo __( 'Cover Image', 'ajaxzoom' ) ?></th>
								<th class="data-grid-th"><?php echo __( 'Name', 'ajaxzoom' ) ?></th>
								<th class="data-grid-th"><?php echo __( 'Active', 'ajaxzoom' ) ?></th>
							</tr>
						</thead>
						<tbody id="az-image-table-sets-rows">
						</tbody>
					</table>
				</div>

				<table id="lineSet" style="display:none;">
					<tr id="set_id" data-group="group_id">
						<td><img src="<?php echo $uri ?>/ajaxzoom/image_path.gif" alt="legend" title="legend" class="img-thumbnail" /></td>
						<td valign="top">
							legend
							<div class="row-actions">
								<a class="delete_set scalable delete trash" href=""><?php echo __( 'Delete' ) ?></a>
								&nbsp;|&nbsp;
								<a class="images_set scalable" href=""><?php echo __( 'Images', 'ajaxzoom' ) ?></a>
								<span class="hide_class">
								&nbsp;|&nbsp;
								<a class="scalable preview_set" href=""><?php echo __( 'Preview', 'ajaxzoom' ) ?></a>
								&nbsp;|&nbsp;
								<a class="crop_set scalable" href="#"><?php echo __( '360 Product Tour', 'ajaxzoom'); ?></a>
								</span>
							</div>
						</td>
						<td valign="top">
							<span class="hide_class switch-status">
								<input type="radio" name="status_field" id="status_field_on" value="1" checked_on />
								<label for="status_field_on"><?php echo __( 'Yes', 'ajaxzoom' ) ?></label>
								<input type="radio" name="status_field" id="status_field_off" value="0" checked_off />
								<label for="status_field_off"><?php echo __( 'No', 'ajaxzoom' ) ?></label>
								<a class="slide-button btn"></a>
							</span>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery( function ( $ ) {

		function setLine( id, path, position, legend, status, group_id ) {

			line = $( "#lineSet" ).html();
			line = line.replace( /set_id/g, id );
			line = line.replace( /group_id/g, group_id );
			line = line.replace( /legend/g, legend );
			line = line.replace( /status_field/g, 'status_' + id );
			var re = new RegExp( ajaxzoomData.image_path, 'g' );
			line = line.replace( re, path );
			line = line.replace( /<tbody>/gi, "" );
			line = line.replace( /<\/tbody>/gi, "" );

			if( status == '1' ) {
				line = line.replace( /checked_on/g, 'checked' );
				line = line.replace( /checked_off/g, '' );
			} else {
				line = line.replace( /checked_on/g, '' );
				line = line.replace( /checked_off/g, 'checked' );
			}

			if( $( 'tr[data-group=' + group_id + ']' ).length ) {
				line = line.replace( /hide_class/g, 'hide' );
			}

			$( "#az-image-table-sets-rows" ).append( line );
		}

		function afterUpdateStatus( data ) {
			showSuccessMessage( data.confirmations );
		}

		function afterDeleteSet( data ) {
			$( 'tr#' + data.id_360set ).remove();
			showSuccessMessage( data.confirmations );

			// remove set option from the dropdowns
			if( data.removed == '1' ) {
				$( "select#id_360 option[value='" + data.id_360 + "']" ).remove();
				$( "select#existing option[value='" + data.id_360 + "']" ).remove();
			}
		}

		function afterAddSet( data ) {

			if( data.sets.length > 0 ) {
				for ( var i = 0; i < data.sets.length; i++ ) {
					var set = data.sets[i];
					setLine( set.id_360set, set.path, "", set.name, set.status, set.id_360 );
				}
			} else {
				setLine( data.id_360set, data.path, "", data.name, data.status, data.id_360 );
			}

			$( '.link_add' ).find( 'i' ).removeClass( 'icon-minus' ).addClass( 'icon-plus' );
			$( '#newForm' ).hide();
			$( '#set_name' ).val( '' );
			$( '#existing' ).val( '' );

			if( data.new_id != '' ) {
				$( 'select#id_360' )
					.append( $( "<option></option>" )
						.attr( 'value', data.new_id )
						.attr( 'data-settings', data.new_settings )
						.attr( 'data-combinations', '[]' )
						.text( data.new_name ) ); 
				$( 'select#existing' ).append( $( "<option></option>" ).attr( 'value', data.new_id ).text( data.new_name ) ); 
			}
			showSuccessMessage( data.confirmations );
		}

		function afterGetImages( data ) {
			for ( var i = 0; i < data.images.length; i++ ) {
				imageLine360( data.images[i]['id'], data.images[i]['thumb'], '', "", "", "" );
			};

            var pppPos = $('#product-images360').offset();

            if ($.scrollTo && pppPos && pppPos.top){
            	$.scrollTo(pppPos.top - 50);
            }

		}

		$( '#zip' ).change( function () {
			if( $( this ).is( ':checked' ) ) {
				$( '.field-arcfile' ).show();
			} else {
				$( '.field-arcfile' ).hide();
			}
		});

		$( '.link_add' ).click( function ( e ) {
			e.preventDefault();

			var icon = $( this ).find( 'i' );

			if( icon.hasClass( 'icon-plus' ) ) {
				icon.removeClass( 'icon-plus' ).addClass( 'icon-minus' );
				$( '#newForm' ).show();
			} else {
				icon.removeClass( 'icon-minus' ).addClass( 'icon-plus' );
				$( '#newForm' ).hide();
			}
		});

		$( 'body' ).on( 'change', '.switch-status input', function( e ) {
			e.preventDefault();
			var status = $( this ).val();
			var group_id = $( this ).parent().parent().parent().data( 'group' );
			var params = {
				"action": "ajaxzoom_set_360_status",
				"id_product": ajaxzoomData.id_product,
				"id_360": group_id,
				"status": status
			};
			doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterUpdateStatus );
			
		});

		$( 'body' ).on( 'click', '.preview_set', function( e ) {
			e.preventDefault();

			var id360 = $( this ).parents( 'tr' ).first().data( 'group' );
			var id360set = $( this ).parents( 'tr' ).first().attr( 'id' );
			var url = ajaxzoomData.uri + '/ajaxzoom/preview/preview.php?3dDir=' + ajaxzoomData.uri + 
				'/ajaxzoom/pic/360/' + ajaxzoomData.id_product + '/' + id360 + 
				'&group=' + id360 + '&id=' + id360set;

			$.openAjaxZoomInFancyBox( {href: url, iframe: true, scrolling: false, boxMargin: 50} );
		});

		$( '.crop_set' ).die().live( 'click', function(e) {
			e.preventDefault();

			var id360 = $( this ).parents( 'tr' ).first().data( 'group' );
			var id360set = $( this ).parents( 'tr' ).first().attr( 'id' );
			var url = ajaxzoomData.uri + '/ajaxzoom/preview/cropeditor.php?3dDir=' + ajaxzoomData.uri + 
				'/ajaxzoom/pic/360/' + ajaxzoomData.id_product + '/' + id360 + 
				'&group=' + id360 + '&id=' + id360set;

			$.openAjaxZoomInFancyBox( {href: url, iframe: true, scrolling: 1, boxMargin: 50} );
		} );

		$( 'body' ).on( 'click', '.images_set', function(e) { 
			e.preventDefault();
			
			$( '#az-image-table-sets-rows' ).find( 'tr' ).removeClass( 'active' );
			$( this ).parent().parent().addClass( 'active' );
			$( '#imageList360' ).html( '' );
			$( '#file360-success' ).parent().hide();

			var id = $( this ).parent().parent().parent().attr( 'id' );
			var params = {
				"action" : "ajaxzoom_get_images",
				"id_product" : ajaxzoomData.id_product,
				"id_360set" : id
			};

			doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterGetImages );
			
			$( '#id_360set' ).val( id );
			$( '#product-images360' ).show();
			
			$('#container360_upload>div').remove();
			uploader360 = new plupload.Uploader(uploader360Obj());
			uploader360.init();
		} );

		$( '.save_set' ).click( function ( e ) {
			e.preventDefault();	
			
			var params = {
				"action": "ajaxzoom_add_set",
				"name": $('#set_name').val(),
				"existing": $('#existing').val(),
				"zip": $('#zip').is(':checked'),
				"delete": $('#delete').is(':checked'),
				"arcfile": $('#arcfile').val(),
				"id_product": ajaxzoomData.id_product
			};

			doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterAddSet );
		});

		$( 'body' ).on( 'click', '.delete_set', function( e ) {

			e.preventDefault();

			$( '#product-images360' ).hide();
			$( '#imageList360' ).html( '' );

			var id = $( this ).parent().parent().parent().attr( 'id' );
			var params = {
				"action": "ajaxzoom_delete_set",
				"id_360set":id,
				"id_product" : ajaxzoomData.id_product
			};

			if ( confirm( "<?php echo __( 'Are you sure?', 'ajaxzoom' ) ?>" ) ) {
				doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterDeleteSet );
			}
		} );

		$( '#btn-cancel-set' ).click( function ( e ) {
			e.preventDefault();
			$( '.link_add' ).click();
		} );

		<?php foreach ( $sets as $set ): ?>
			setLine( "<?php echo $set['id_360set'] ?>", "<?php echo $set['path'] ?>", "", "<?php echo $set['name'] ?>", "<?php echo $set['status'] ?>", "<?php echo $set['id_360'] ?>" );
		<?php endforeach; ?>
	} );
</script>