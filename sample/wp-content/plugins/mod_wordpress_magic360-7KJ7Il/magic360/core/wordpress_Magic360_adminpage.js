(function($) {
    'use strict';
    $(document).ready(function() {
        $( "#tabs" ).tabs({
          activate: function(event, ui){
              $('.nav-tab').removeClass('nav-tab-active');
              $(ui.newTab).children().addClass('nav-tab-active');
          }
        });
    });
})(jQuery);

jQuery(document).ready(function() {
    var headingTop = jQuery('#set-main-settings').position().top - jQuery( window ).height() + 120;
    if(!jQuery('#set-main-settings').hasClass('fixed')) {
	jQuery('#set-main-settings').addClass('fixed');
    }
    //console.log(headingTop);
    jQuery(window).scroll(function() {
	//console.log(headingTop + ' -- ' + jQuery(window).scrollTop());
        if(headingTop <= jQuery(window).scrollTop()) {
            if(jQuery('#set-main-settings').hasClass('fixed')) {
                jQuery('#set-main-settings').removeClass('fixed');
            }
        } else { 
            if(!jQuery('#set-main-settings').hasClass('fixed')) {
                jQuery('#set-main-settings').addClass('fixed');
            }
        }
    });
});