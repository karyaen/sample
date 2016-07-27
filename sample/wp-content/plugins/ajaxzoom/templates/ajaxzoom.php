<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style type="text/css">
	.woocommerce-main-image {
		display: none !important;
	}
	.thumbnails {
		display: none !important;
	}
	.images>img[alt="Placeholder"]{
		display: none !important;
	}
	/* Fixes for some great written themes... */
	.single-product div.product{
		overflow: visible !important;
	}
	.images img{
		background: transparent !important;
	}
</style>

<?php if( $config['AJAXZOOM_GALLERYPOSITION'] == 'bottom' ): ?>
	<!-- AJAX-ZOOM mouseover block gallery bottom -->
	<div id="az_mouseOverZoomParent" style="margin-bottom: 20px;">
	
		<!-- Container for mouse over image -->
		<div id="az_mouseOverZoomContainer" style="position: relative; border: #AAA 1px solid;">
			Mouseover Zoom loading...
		</div>

		<!-- gallery with thumbs (will be filled with thumbs by javascript) -->
		<div id="az_mouseOverZoomGallery" style="position: relative; margin-top: 20px; height: 76px; width: 100%; display: none;">
			Gellery loading...
		</div>
	</div>

<?php elseif( $config['AJAXZOOM_GALLERYPOSITION'] == 'top' ): ?>
	<!-- AJAX-ZOOM mouseover block gallery top -->
	<div id="az_mouseOverZoomParent" style="margin-bottom: 20px;">
	
		<!-- gallery with thumbs (will be filled with thumbs by javascript) -->
		<div id="az_mouseOverZoomGallery" style="position: relative; margin-bottom: 10px; height: 76px; width: 100%;">
			Gellery loading...
		</div>
		
		<!-- Container for mouse over image -->
		<div id="az_mouseOverZoomContainer" style="position: relative; border: #AAA 1px solid;">
			Mouseover Zoom loading...
		</div>
	</div>

<?php elseif( $config['AJAXZOOM_GALLERYPOSITION'] == 'left' ): ?>
	<!-- AJAX-ZOOM mouseover block gallery left -->
	<div id="az_mouseOverZoomParent" style="position: relative; width: 100%; margin-bottom: 20px;">

		<!-- gallery with thumbs (will be filled with thumbs by javascript) -->
		<div id="az_mouseOverZoomGallery" style="position: absolute; width: 72px; z-index: 1; height: 100%;">
			Gellery loading...
		</div>

		<!-- Parent container for offset to the left or right -->
		<div id="az_mouseOverZoomContainerParentGalleryLeft" style="margin-left: 80px; min-height: 100px;">
			
			<!-- Container for mouse over image -->
			<div id="az_mouseOverZoomContainer" style="position: relative; border: #AAA 1px solid; background-color: #FFFFFF; padding: 0;">
				Mouseover Zoom loading...
			</div>
		</div>
	</div>

<?php elseif( $config['AJAXZOOM_GALLERYPOSITION'] == 'right' ): ?>
	<!-- AJAX-ZOOM mouseover block gallery right -->
	<div id="az_mouseOverZoomParent" style="position: relative; width: 100%; margin-bottom: 20px;">
		<!-- gallery with thumbs (will be filled with thumbs by javascript) -->
		<div id="az_mouseOverZoomGallery" style="position: absolute; right: 0; width: 72px; z-index: 1; height: 100%;">
			Gellery loading...
		</div>

		<!-- Parent container for offset to the left or right -->
		<div id="az_mouseOverZoomContainerParentGalleryRight" style="margin-right: 80px; min-height: 100px;">
			
			<!-- Container for mouse over image -->
			<div id="az_mouseOverZoomContainer" style="position: relative; border: #AAA 1px solid; background-color: #FFFFFF; padding: 0;">
				Mouseover Zoom loading...
			</div>
		</div>
	</div>

<?php endif; ?>

<script type="text/javascript">

jQuery(function ($) {
 	var ajaxzoom_variations = [];
 	var ajaxzoom_variations_2d = [];
	<?php echo $variations_json; ?>
	<?php echo $variations_2d_json; ?>
	var ajaxzoom_imagesJSON = '<?php echo $ajaxzoom_imagesJSON; ?>';
	var ajaxzoom_images360JSON = '<?php echo $ajaxzoom_images360JSON; ?>';
	var ajaxzoom_imagesJSON_current = $.parseJSON(ajaxzoom_imagesJSON);
	var ajaxzoom_images360JSON_current = $.parseJSON(ajaxzoom_images360JSON);

<?php
foreach ($config as $key => $value){
	if ($key != 'AJAXZOOM_LICENSES') { 
		if ($value == 'false' || $value == 'true' || $value == 'null' || is_numeric($value) || substr(trim($value), 0, 1) == '{' || substr(trim($value), 0, 1) == '[') {
			echo $key . ' = ' . $value . ";\n ";
		} else {
			echo $key . ' = "' . str_replace('"', '&#34;', $value) . "\";\n ";
		}
	}
}
?>

	var getSliderParam = function() {
		if ( AJAXZOOM_GALLERYPOSITION == 'top' || AJAXZOOM_GALLERYPOSITION == 'bottom' ) {
			return AJAXZOOM_GALLERYAXZMTHUMBSLIDERPARAM;
		} else {
			return AJAXZOOM_GALLERYAXZMTHUMBSLIDERPARAM_V;
		}
	};

	// Change layout
	//$('.woocommerce-main-image').remove();
	//$('.thumbnails').remove();
	
	// Start config
	var axzm_main_config = {
		axZmPath: '<?php echo $axZmPath; ?>',
		divID: AJAXZOOM_DIVID,
		galleryDivID: AJAXZOOM_GALLERYDIVID,
		lang: '<?php echo substr(get_bloginfo( 'language' ), 0, 2); ?>',
		hideGalleryOneImage: AJAXZOOM_HIDEGALLERYONEIMAGE,
		hideGalleryAddClass: AJAXZOOM_HIDEGALLERYADDCLASS,
		galleryHover: AJAXZOOM_GALLERYHOVER,
		galleryAxZmThumbSlider: AJAXZOOM_GALLERYAXZMTHUMBSLIDER,
		galleryAxZmThumbSliderParam: getSliderParam(),
		thumbW: AJAXZOOM_THUMBW,
		thumbH: AJAXZOOM_THUMBH,
		thumbRetina: AJAXZOOM_THUMBRETINA,
		qualityThumb: AJAXZOOM_QUALITYTHUMB,
		quality: AJAXZOOM_QUALITY,
		qualityZoom: AJAXZOOM_QUALITYZOOM,
		images: ajaxzoom_imagesJSON_current,
		firstImageToLoad: AJAXZOOM_FIRSTIMAGETOLOAD,
		disableAllMsg: AJAXZOOM_DISABLEALLMSG,
		images360: ajaxzoom_images360JSON_current,
		images360firstToLoad: AJAXZOOM_IMAGES360FIRSTTOLOAD,
		images360Thumb: AJAXZOOM_IMAGES360THUMB,
		images360Overlay: AJAXZOOM_IMAGES360OVERLAY,
		images360Preview: AJAXZOOM_IMAGES360PREVIEW,
		images360PreviewResponsive: AJAXZOOM_IMAGES360PREVIEWRESPONSIVE,
		images360examplePreview: AJAXZOOM_IMAGES360EXAMPLEPREVIEW,
		zoomMsg360: AJAXZOOM_ZOOMMSG360,
		zoomMsg360_touch: AJAXZOOM_ZOOMMSG360_TOUCH,
		preloadMouseOverImages: AJAXZOOM_PRELOADMOUSEOVERIMAGES,
		noImageAvailableClass: AJAXZOOM_NOIMAGEAVAILABLECLASS,
		width: AJAXZOOM_WIDTH,
		height: AJAXZOOM_HEIGHT,
		responsive: AJAXZOOM_RESPONSIVE,
		oneSrcImg: AJAXZOOM_ONESRCIMG,
		heightRatio: AJAXZOOM_HEIGHTRATIO,
		heightMaxWidthRatio: AJAXZOOM_HEIGHTMAXWIDTHRATIO,
		widthMaxHeightRatio: AJAXZOOM_WIDTHMAXHEIGHTRATIO,
		maxSizePrc: AJAXZOOM_MAXSIZEPRC,
		mouseOverZoomWidth: AJAXZOOM_MOUSEOVERZOOMWIDTH,
		mouseOverZoomHeight: AJAXZOOM_MOUSEOVERZOOMHEIGHT,
		ajaxZoomOpenMode: AJAXZOOM_AJAXZOOMOPENMODE,
		fancyBoxParam: AJAXZOOM_FANCYBOXPARAM,
		colorBoxParam: AJAXZOOM_COLORBOXPARAM,
		example: AJAXZOOM_EXAMPLE,
		exampleFancyboxFullscreen: AJAXZOOM_EXAMPLEFANCYBOXFULLSCREEN,
		exampleFancybox: AJAXZOOM_EXAMPLEFANCYBOX,
		exampleColorbox: AJAXZOOM_EXAMPLECOLORBOX,
		enforceFullScreenRes: AJAXZOOM_ENFORCEFULLSCREENRES,
		prevNextArrows: AJAXZOOM_PREVNEXTARROWS,
		disableScrollAnm: AJAXZOOM_DISABLESCROLLANM,
		fullScreenApi: AJAXZOOM_FULLSCREENAPI,
		axZmCallBacks: AJAXZOOM_AXZMCALLBACKS,
		azOptions: AJAXZOOM_AZOPTIONS,
		azOptions360: AJAXZOOM_AZOPTIONS360,
		postMode: AJAXZOOM_POSTMODE,
		pinterest: {
			enabled: AJAXZOOM_PINTEREST_ENABLED,
			build: AJAXZOOM_PINTEREST_BUILD,
			btnSrc: AJAXZOOM_PINTEREST_BTNSRC,
			data: AJAXZOOM_PINTEREST_DATA 
		},
		
		// Mouse hover zoom parameters
		mouseOverZoomParam: {
			position: AJAXZOOM_MOZP_POSITION,
			posAutoInside: AJAXZOOM_MOZP_POSAUTOINSIDE,
			posInsideArea: AJAXZOOM_MOZP_POSINSIDEAREA,
			touchScroll: AJAXZOOM_MOZP_TOUCHSCROLL,
			noMouseOverZoom: AJAXZOOM_MOZP_NOMOUSEOVERZOOM,
			autoFlip: AJAXZOOM_MOZP_AUTOFLIP,
			biggestSpace: AJAXZOOM_MOZP_BIGGESTSPACE,
			zoomFullSpace: AJAXZOOM_MOZP_ZOOMFULLSPACE,
			zoomWidth: AJAXZOOM_MOZP_ZOOMWIDTH,
			zoomHeight: AJAXZOOM_MOZP_ZOOMHEIGHT,
			autoMargin: AJAXZOOM_MOZP_AUTOMARGIN,
			adjustX: AJAXZOOM_MOZP_ADJUSTX,
			adjustY: AJAXZOOM_MOZP_ADJUSTY,
			lensOpacity: AJAXZOOM_MOZP_LENSOPACITY,
			lensStyle: AJAXZOOM_MOZP_LENSSTYLE,
			lensClass: AJAXZOOM_MOZP_LENSCLASS,
			zoomAreaBorderWidth: AJAXZOOM_MOZP_ZOOMAREABORDERWIDTH,
			galleryFade: AJAXZOOM_MOZP_GALLERYFADE,
			shutterSpeed: AJAXZOOM_MOZP_SHUTTERSPEED,
			showFade: AJAXZOOM_MOZP_SHOWFADE,
			hideFade: AJAXZOOM_MOZP_HIDEFADE,
			flyOutSpeed: AJAXZOOM_MOZP_FLYOUTSPEED,
			flyOutTransition: AJAXZOOM_MOZP_FLYOUTTRANSITION,
			flyOutOpacity: AJAXZOOM_MOZP_FLYOUTOPACITY,
			flyBackSpeed: AJAXZOOM_MOZP_FLYBACKSPEED,
			flyBackTransition: AJAXZOOM_MOZP_FLYBACKTRANSITION,
			flyBackOpacity: AJAXZOOM_MOZP_FLYBACKOPACITY,
			autoScroll: AJAXZOOM_MOZP_AUTOSCROLL,
			smoothMove: AJAXZOOM_MOZP_SMOOTHMOVE,
			tint: AJAXZOOM_MOZP_TINT,
			tintOpacity: AJAXZOOM_MOZP_TINTOPACITY,
			tintFilter: AJAXZOOM_MOZP_TINTFILTER,
			tintLensBack: AJAXZOOM_MOZP_TINTLENSBACK,
			showTitle: AJAXZOOM_MOZP_SHOWTITLE,
			titleOpacity: AJAXZOOM_MOZP_TITLEOPACITY,
			titlePosition: AJAXZOOM_MOZP_TITLEPOSITION,
			cursorPositionX: AJAXZOOM_MOZP_CURSORPOSITIONX,
			cursorPositionY: AJAXZOOM_MOZP_CURSORPOSITIONY,
			touchClickAbort: AJAXZOOM_MOZP_TOUCHCLICKABORT,
			loading: AJAXZOOM_MOZP_LOADING,
			loadingMessage: AJAXZOOM_MOZP_LOADING_MESSAGE,
			loadingWidth: AJAXZOOM_MOZP_LOADINGWIDTH,
			loadingHeight: AJAXZOOM_MOZP_LOADINGHEIGHT,
			loadingOpacity: AJAXZOOM_MOZP_LOADINGOPACITY,
			zoomHintEnable: AJAXZOOM_MOZP_ZOOMHINTENABLE,
			zoomHintText: AJAXZOOM_MOZP_ZOOMHINTTEXT,
			zoomMsgHover: AJAXZOOM_MOZP_ZOOMMSGHOVER,
			zoomMsgClick: AJAXZOOM_MOZP_ZOOMMSGCLICK,
			slideInTime: AJAXZOOM_MOZP_SLIDEINTIME,
			slideInEasingCSS3: AJAXZOOM_MOZP_SLIDEINEASINGCSS3,
			slideInEasing: AJAXZOOM_MOZP_SLIDEINEASING,
			slideInScale: AJAXZOOM_MOZP_SLIDEINSCALE,
			slideOutScale: AJAXZOOM_MOZP_SLIDEOUTSCALE,
			slideOutOpacity: AJAXZOOM_MOZP_SLIDEOUTOPACITY,
			slideOutDest: AJAXZOOM_MOZP_SLIDEOUTDEST,
			onInit: AJAXZOOM_ONINIT,
			onLoad: AJAXZOOM_ONLOAD,
			onImageChange: AJAXZOOM_ONIMAGECHANGE,
			onMouseOver: AJAXZOOM_ONMOUSEOVER,
			onMouseOut: AJAXZOOM_ONMOUSEOUT,
			spinner: AJAXZOOM_SPINNER,
			spinnerParam: AJAXZOOM_SPINNERPARAM
		}
	};
	
	var start_mouseOverZoomInit = function() {
		$.mouseOverZoomInit( axzm_main_config );
	};
	
	var parseUrl = function( url ) {
		var a = document.createElement('a');
		a.href = url;
		return a;
	};
	
	var start_load = false;

	/**
	 * Override WC native function
	 * Sets product images for the chosen variation
	 */
	$.fn.wc_variations_image_update = function( variation ) {
		var new_images = $.parseJSON( ajaxzoom_imagesJSON );
		var new_360_images = $.parseJSON( ajaxzoom_images360JSON );

		// Remove default images and 360
		if ( AJAXZOOM_SHOWDEFAULTFORVARIATION == false && variation ) {
			new_images = {};
			new_360_images = {};
		} 
		
		// Reindex default images
		if (!$.isEmptyObject(new_images)){
			var nn = 100,
				new_images_copy = {};

			$.each( new_images, function(k, v) { 
				nn++;
				new_images_copy[nn] = v;
			} );

			new_images = new_images_copy;
		}

		// 360
		if ( variation && ajaxzoom_variations[variation.variation_id] ) {
			$.extend(new_360_images, ajaxzoom_variations[variation.variation_id] );
		}

		// Woo variation image
		if ( variation && variation.image_src && variation.image_src.length > 1 ) {
			// add an image from variation
			var p = parseUrl( variation.image_link );
			var new_image_object = {"0": {'img': p.pathname, 'title': ''}};
			$.extend( new_images, new_image_object );
		}

		// Additional AJAX-ZOOM variation images
		if( variation && ajaxzoom_variations_2d[variation.variation_id] ) {
			$.extend( new_images, ajaxzoom_variations_2d[variation.variation_id] );
		}

		if( $.toJSON( new_images ) != $.toJSON( ajaxzoom_imagesJSON_current )
			||  $.toJSON( new_360_images ) != $.toJSON( ajaxzoom_images360JSON_current )
			) {	
			
			var to_load_first = true;
			if (start_load){
				to_load_first = false;
			}
			
			start_load = 1;
			
			setTimeout( function() {
				if (to_load_first) {
					axzm_main_config.images = new_images;
					axzm_main_config.images360 = new_360_images;
					start_mouseOverZoomInit();
				} else {
					$.mouseOverZoomInit.replaceImages( { 
						divID: AJAXZOOM_DIVID,
						galleryDivID: AJAXZOOM_GALLERYDIVID,
						images: new_images,
						images360: new_360_images
					} );
				}

				ajaxzoom_imagesJSON_current = $.extend( true, {}, new_images );
				ajaxzoom_images360JSON_current = $.extend( true, {}, new_360_images );
			}, 1);
		}
	};
	
	setTimeout(function(){
		if (!start_load){
			start_load = 1;
			start_mouseOverZoomInit();
		}
	}, 150);
	
});
</script>