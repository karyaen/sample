<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="az-form">
	<label><?php echo __( "AJAX ZOOM enabled for this product's detail view", 'ajaxzoom' ) ?></label>
	<input type="radio" name="az_active" id="az_active_on" value="1" <?php if( $active == 1 ): ?>checked="checked"<?php endif ?>/>
	<label for="az_active_on"><?php echo __( 'Yes', 'ajaxzoom' ); ?></label>
	<input type="radio" name="az_active" id="az_active_off" value="0" <?php if( $active == 0 ): ?>checked="checked"<?php endif ?>/>
	<label for="az_active_off"><?php echo __( 'No', 'ajaxzoom' ); ?></label>
	</div>
<br>
<br>
<div class="entry-edit">
	<strong class="az-title"><?php echo __( 'Settings for existing 360/3D', 'ajaxzoom' ); ?></strong>
	
	<div class="fieldset fieldset-wide" id="group_fields9">
		<div class="hor-scroll">
			<table cellspacing="0" class="az-form-list">
				<tbody>
					<tr>
						<td class="value">
							<?php foreach ( $groups as $group ): ?>
							<input type="hidden" name="settings[<?php echo $group['id_360'] ?>]" id="settings_<?php echo $group['id_360'] ?>" value="<?php echo urlencode( $group['settings'] ) ?>">
							<input type="hidden" name="comb[<?php echo $group['id_360'] ?>]" id="settings_comb_<?php echo $group['id_360'] ?>" value="<?php echo urlencode( $group['combinations'] ) ?>">
							<?php endforeach; ?>
							<select id="id_360" name="id_360" style="min-width: 100px">
								<option value=""><?php echo __( 'Select 3D/360 View', 'ajaxzoom' ); ?></option>
								<?php foreach ( $groups as $group ): ?>
								<option value="<?php echo $group['id_360'] ?>" data-settings="<?php echo urlencode( $group['settings'] ) ?>" data-combinations="[<?php echo urlencode( $group['combinations'] ) ?>]"><?php echo $group['name'] ?></option>
								<?php endforeach; ?>
							</select>						
						</td>
						<td class="scope-label"><span class="nobr"></span></td>
					</tr>
				</tbody>
			</table>

			<div class="az-settings-form az-form">
				<div id="pairs" style="display:none;">
					<table>
						<thead>
							<tr>
								<td><?php echo __( 'Name', 'ajaxzoom' ); ?></td>
								<td></td>
								<td><?php echo __( 'Value', 'ajaxzoom' ); ?></td>
								<td></td>
							</tr>
						</thead>
						<tbody id="az-pair-rows">
						</tbody>
						<tfoot>
							<tr>
								<td colspan="4">
									<div>
										<button class="button add link_add_option">
											<?php echo __( 'Add an option', 'ajaxzoom' ); ?>
										</button>
									</div>
								</td>
							</tr>
						</tfoot>	
					</table>
					<table id="az-pair-template" style="display: none">
						<tr>
							<td><input type="text" name="name[]" value="name_placeholder" class="pair-names"></td>
							<td>&nbsp; : &nbsp;</td>
							<td><input type="text" name="value[]" value="value_placeholder" class="pair-values"></td>
							<td>
								<a class="link_textarea_option" href="#">
									<?php echo __( 'Edit', 'ajaxzoom' ); ?>
								</a>
								&nbsp;&nbsp;
								<a class="link_remove_option" href="#">
									<?php echo __( 'Delete', 'ajaxzoom' ); ?>
								</a>
							</td>
						</tr>
					</table>
				</div>

				<?php if ( count( $variations ) ): ?>
				<div id="comb" style="display:none;">
						<br>
						<div class="az-label"><?php echo __( 'Variations', 'ajaxzoom' ) ?></div>

						<a href="#" class="comb-check-all" style="margin-bottom: 10px;"><?php echo __( 'check all', 'ajaxzoom' ) ?></a><br>

						<?php foreach ( $variations as $id => $name ): ?>
						<input type="checkbox" name="combinations[]" value="<?php echo $id ?>" class="settings-combinations"> <?php echo $name ?><br>
						<?php endforeach; ?>
						
						<p class="note">
							<?php echo __( 'Same as with images you can define which 360 should be shown in conjunction with which combinations.', 'ajaxzoom' ) ?>
							<?php echo __( 'If you do not select any this 360 will be shown for all combinations.', 'ajaxzoom' ) ?>
						</p>
					
				</div>
				<?php endif; ?>

				<button id="save_settings" class="button button-primary"><?php echo __( 'Save Settings', 'ajaxzoom' ) ?></button>
				<button id="cancel_settings" class="button"><?php echo __( 'Cancel', 'ajaxzoom' ) ?></button>	
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery( function ( $ ) {
	
	function pairLine( name, value ) {
		var line = $( "#az-pair-template" ).html();
		line = line.replace( /name_placeholder/g, name );
		line = line.replace( /value_placeholder/g, value );
		line = line.replace( /<tbody>/gi, "" );
		line = line.replace( /<\/tbody>/gi, "" );
		$( "#az-pair-rows" ).append( line );
	}	

	function afterSaveSettings( data ) { 
		$( '#id_360' ).replaceWith( data.select );
		$( '#pairs' ).hide();
		$( '#comb' ).hide();
		$( '.az-settings-form' ).hide();
		if( data.id_360 > 0 ) {
			$( 'select#id_360' ).val( data.id_360 ).change();
		}
		showSuccessMessage( data.confirmations );
	}

	function getFieldValues( class1 ) {
		var inputs = document.getElementsByClassName( class1 );
		var res = [];
		for ( var i = 0; i < inputs.length; i++ ) {
			res.push( inputs[i].value );
		}
    	return res;
	}

	function setPairString() {
    	var names = getFieldValues( 'pair-names' );
    	var values = getFieldValues( 'pair-values' );
    	var res = {};
    	for ( var i = 0; i < names.length; i++ ) {
    		if( names[i] == 'name_placeholder' ) continue;
    		res[ names[i] ] = values[i];
    	};
    	
    	$( '#settings_' + $( 'select#id_360' ).val() ).val( encodeURIComponent( JSON.stringify( res ) ) );
	}

	function saveSettings() {
		var active = $( 'input[name=az_active]:checked' ).val();

		var inputs = document.getElementsByClassName( 'pair-names' ), 
			names  = [].map.call( inputs, function( input ) { 
        		return input.value;
    		} ).join( '|' );

		var inputs = document.getElementsByClassName( 'pair-values' ), 
			values  = [].map.call( inputs, function( input ) {
        		return input.value;
    		} ).join( '|' );

		var tmp = [];
		$( '.settings-combinations' ).each( function() {
		    if ( $( this ).is( ':checked' ) ) {
		        tmp.push( $( this ).val() );
		    }
		} );

		var combinations = tmp.join( '|' );
		var id_360 = $('select#id_360').val();
		var params = {
			'action': 'ajaxzoom_save_settings', 
			"id_product" : ajaxzoomData.id_product,
			"id_360" : id_360,
			"names" : names,
			"combinations" : combinations,
			"values" : values,
			"active" : active,
			'mode': 'single'
		};

		doAdminAjax360( ajaxzoomData.ajaxUrl, params, afterSaveSettings );
	}

	function setComb() {
    	var values = [];
    	$( '.settings-combinations:checked' ).each( function () {
    		values.push( $( this ).val() );
    	} );
    	
    	$( '#settings_comb_' + $( 'select#id_360').val() ).val( encodeURIComponent( JSON.stringify( values ) ) );
	}

	$( 'body' ).on( 'change', '.pair-names, .pair-values', function( e ) {
		setPairString();
	} );

	$( '.settings-combinations' ).on( 'change', function( e ) {
		setComb();
	} );

	$( '.link_add_option' ).click( function ( e ) {
		e.preventDefault();
		pairLine( '', '' );
	} );

	$( 'body' ).on( 'click', '.link_remove_option', function( e ) {
		e.preventDefault();
		$( this ).parent().parent().remove();
		setPairString();
	} );

	$( '#save_settings' ).click( function ( e ) {
		e.preventDefault();
		saveSettings();
	} );

	$( '#cancel_settings' ).click( function ( e ) {
		e.preventDefault();
		$( 'select#id_360' ).val( '' ).change();
	} );

	$( 'body' ).on( 'click', '.link_textarea_option', function( e ) {
		e.preventDefault();

		var td = $( this ).parent().prev();
		if ( $( 'input', td ).length == 1 ) { 
			var val = $( 'input', td ).val();
			$( 'input', td ).replaceWith( '<textarea class="pair-values" type="text" name="value[]">' + val + '</textarea>' );
		} else if ( $( 'textarea', td ).length == 1 ) { 
			var val = $( 'textarea', td ).val();
			$( 'textarea', td ).replaceWith( '<input class="pair-values" type="text" value="' + val + '" name="value[]">' );
		}
	} );

	$( 'body' ).on( 'change', 'select#id_360', function( e ) {
		
		$( '#az-pair-rows' ).html( '' );

		if( $( this ).val() != '' ) { 

			// set pairs name:value
			var settings = $.parseJSON( unescape( $( 'option:selected', $( this ) ).attr( 'data-settings' ) ) );
			for( var k in settings ) { 
				pairLine( k, settings[k] );
			}

			// set combinations checkboxes
			var combinations = $.parseJSON( unescape( $( 'option:selected', $( this ) ).attr( 'data-combinations' ) ) );

			$( 'input.settings-combinations' ).attr( 'checked', false );
			if( combinations && combinations.length ) {
				for ( var i = combinations.length - 1; i >= 0; i-- ) {
					$( 'input.settings-combinations[value=' + combinations[i] + ']' ).attr( 'checked', true );
				};
			}

			$( '#pairs' ).show();
			$( '#comb' ).show();
			$( '.az-settings-form' ).show();
		} else {
			$( '#pairs' ).hide();
			$( '#comb' ).hide();
			$( '.az-settings-form' ).hide();
		}
	} );

    $( 'a.comb-check-all' ).toggle( function() { 
        $( 'input.settings-combinations' ).attr( 'checked', 'checked' );
        $( this ).html( 'uncheck all' );
    }, function() { 
        $( 'input.settings-combinations' ).removeAttr( 'checked' );
        $( this ).html( 'check all' );
    } );


    $( 'input[name=az_active]' ).change( function ( e ) {
    	saveSettings();
    } );
});
</script>