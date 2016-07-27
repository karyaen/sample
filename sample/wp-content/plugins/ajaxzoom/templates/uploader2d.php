<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="filelist2d">Your browser doesn't have Flash, Silverlight or HTML5 support.</div>
<br />

<div id="container2d">
	<a id="pickfiles2d" class="button" href="javascript:;"><?php echo __( 'Add Files', 'ajaxzoom' ); ?></a>
	<a id="uploadfiles2d" href="javascript:;" style="display:none;">[Upload files]</a>
</div>
 
<br />
<pre id="console"></pre>

<script type="text/javascript">

var uploader2d = new plupload.Uploader( {
	runtimes : 'html5,flash,silverlight,html4',

	browse_button : 'pickfiles2d',
	container: document.getElementById( 'container2d' ),

	url : ajaxzoomData.ajaxUrl,
	 
	filters : {
		max_file_size : '50mb',
		mime_types: [
			{ title : "Image files", extensions : "jpg,gif,png" }
		]
	},

	multipart_params : {
		"action" : "ajaxzoom_upload_image2d",
		"id_product": ajaxzoomData.id_product
	},
 
	flash_swf_url : '<?php echo includes_url( 'js/plupload/plupload.flash.swf' ); ?>',
 
	silverlight_xap_url : '<?php echo includes_url( 'js/plupload/plupload.silverlight.xap' ); ?>',
	 
	init: {
		PostInit: function() {
			document.getElementById( 'filelist2d' ).innerHTML = '';
 
			document.getElementById( 'uploadfiles2d' ).onclick = function() {
				uploader2d.start();
				return false;
			};
		},
 
		FilesAdded: function( up, files ) {
			document.getElementById( 'filelist2d' ).innerHTML = '';
			plupload.each( files, function( file ) {
				document.getElementById( 'filelist2d' ).innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize( file.size ) + ') <b></b></div>';
			} );
			up.start();
		},

		UploadComplete:  function( up, files ) {
			document.getElementById( 'filelist2d' ).innerHTML = '<span class="az-status"><?php echo __( 'The files has been uploaded successfully', 'ajaxzoom' ); ?></span>';
		},

		FileUploaded: function( up, file, response ) {
			var r = jQuery.parseJSON( response.response );

			imageLine2d( r.id, r.path );
		},
 
		UploadProgress: function( up, file ) {
			document.getElementById( file.id ).getElementsByTagName( 'b' )[0].innerHTML = '<span>' + file.percent + "%</span>";
		},
 
		Error: function( up, err ) {
			document.getElementById( 'console' ).innerHTML += "\nError #" + err.code + ": " + err.message;
		}
	}
});
 
uploader2d.init();
 
</script>