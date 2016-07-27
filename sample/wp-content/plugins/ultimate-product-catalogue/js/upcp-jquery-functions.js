jQuery(document).ready(function(){
	addClickHandlers();

	var thumbContainerWidth = 0,thumbContainerHeight = 0,thumbHolderWidth = 0,thumbImageWidth = 0,thumbImageHeight = 0,numberOfImages = 0;
	var thumbnailHolderContainer,thumbnailControls;

	jQuery(".jquery-prod-cat-value").change(function(){
		var CatValues = [];
		jQuery('.jquery-prod-cat-value').each(function() {if (jQuery(this).prop('checked')) {CatValues.push(jQuery(this).val());}});
		jQuery('#upcp-selected-categories').val(CatValues);
	});
	jQuery(".jquery-prod-sub-cat-value").change(function(){
		var SubCatValues = [];
		jQuery('.jquery-prod-sub-cat-value').each(function() {if (jQuery(this).prop('checked')) {SubCatValues.push(jQuery(this).val());}});
		jQuery('#upcp-selected-subcategories').val(SubCatValues);
	});
	jQuery(".jquery-prod-tag-value").change(function(){
		var TagValues = [];
		jQuery('.jquery-prod-tag-value').each(function() {if (jQuery(this).prop('checked')) {TagValues.push(jQuery(this).val());}});
		jQuery('#upcp-selected-tags').val(TagValues);
	});
	jQuery(".jquery-prod-name-text").keyup(function(){
		var prod_name = jQuery(this).val();
		jQuery('#upcp-selected-prod-name').val(prod_name);
	});

	screenshotThumbHolderWidth();
	jQuery('.upcp-catalogue-link ').hover(
		function(){jQuery(this).children('.upcp-prod-desc-custom-fields').fadeIn(400);},
		function(){jQuery(this).children('.upcp-prod-desc-custom-fields').fadeOut(400);}
	);
	jQuery('.upcp-minimal-img-div').hover(
		function(){jQuery(this).children('.upcp-prod-desc-custom-fields').fadeIn(400);},
		function(){jQuery(this).children('.upcp-prod-desc-custom-fields').fadeOut(400);}
	);

	var heights = jQuery('.upcp-minimal-product-listing').map(function ()
	{
	    return jQuery(this).height();
	}).get(),
	maxWidgetHeight = Math.max.apply(null, heights);

	jQuery('.upcp-minimal-product-listing').each(function (index, value) {
	jQuery(this).height(maxWidgetHeight);
	});

});


jQuery(document).ready(function() {
	jQuery('.upcp-tab-slide').on('click', function(event) {
		jQuery('.upcp-tabbed-tab').each(function() {jQuery(this).addClass('upcp-Hide-Item');});
		jQuery('.upcp-tabbed-layout-tab').each(function() {jQuery(this).addClass('upcp-tab-layout-tab-unclicked');});
		var TabClass = jQuery(this).data('class');
		jQuery('.'+TabClass).removeClass('upcp-Hide-Item');
		jQuery('.'+TabClass+'-menu').removeClass('upcp-tab-layout-tab-unclicked');
		event.preventDefault;
	});
});

jQuery(document).ready(function() {
	jQuery('.upcp-tabbed-button-left').on('click', function() {
		jQuery('.upcp-scroll-list li:first').before(jQuery('.upcp-scroll-list li:last'));
		jQuery('.upcp-scroll-list').animate({left:'-=117px'}, 0);
		jQuery('.upcp-scroll-list').animate({left:'+=117px'}, 600);
	});
	jQuery('.upcp-tabbed-button-right').on('click', function() {
		jQuery('.upcp-scroll-list').animate({left:'-=117px'}, 600, function() {
			jQuery('.upcp-scroll-list li:last').after(jQuery('.upcp-scroll-list li:first'));
			jQuery('.upcp-scroll-list').animate({left:'+=117px'}, 0);
		});
	});
});

jQuery(document).ready(function() {
	var LightboxTrigger = "None";
	jQuery('.upcp-disable-lightgallery').on('click', function(event) {
		if (LightboxTrigger != "Main") {window.disableLightbox = "Yes";}
		else {LightboxTrigger = "None";}
		if (!window.lightGalleryLoaded) {
			event.preventDefault();
			event.stopPropagation();
		}
	});

	if (window.lightGalleryLoaded) {
		var Lightbox = jQuery('.upcp-product-page').lightGallery({
			selector:'.upcp-lightgallery'
		});

		Lightbox.on('onBeforeOpen.lg', function(event, index) {
			Lightbox.data('lightGallery').s.index = jQuery('#upcp-lightgallery-0').data('slide');
			LightboxTrigger = "None";
		});

		jQuery('.prod-cat-addt-details-main').on('click', function(event) {
			event.preventDefault();
			event.stopPropagation();
			LightboxTrigger = "Main";
			jQuery('#upcp-lightgallery-0').trigger('click');
		});
	}
});

jQuery(document).ready(function() {
	addLightboxHandlers();
})

function addLightboxHandlers() {
	jQuery('.prod-cat-item.upcp-lightbox-mode').on('click', function(event) {
		var Item_ID = jQuery(this).data('itemid');
		var Product_Item = jQuery('#prod-cat-list-item-'+Item_ID);
		jQuery('#upcp-lightbox-div').css('display', 'inline');
		jQuery('#upcp-lightbox-background-div').css('display', 'inline');
		jQuery('#upcp-lightbox-div-img').attr('src', jQuery(Product_Item).find('.prod-cat-thumb-image').attr('src'));
		jQuery('#upcp-lightbox-title-div').html(jQuery(Product_Item).find('.prod-cat-title').html());
		jQuery('#upcp-lightbox-price-div').html(jQuery(Product_Item).find('.prod-cat-price').html());
		jQuery('#upcp-lightbox-description-div').html(jQuery(Product_Item).find('.prod-cat-desc').html());
		jQuery('#upcp-lightbox-link-container-div a').attr('href', jQuery(Product_Item).find('.upcp-catalogue-link').attr('href'));
	});

	jQuery('#upcp-lightbox-background-div').on('click.closeLightboxMode', function() {
		jQuery('#upcp-lightbox-div').css('display', 'none');
		jQuery('#upcp-lightbox-background-div').css('display', 'none');
	});
}

function screenshotThumbHolderWidth(){
	var screenshotImage = jQuery('.prod-cat-addt-details-thumbs-div img:first-child');
	var thumbnailHolderContainer = jQuery('.game-thumbnail-holder');

	thumbImageWidth = screenshotImage.width();
	thumbImageHeight = screenshotImage.height();
	numberOfImages = jQuery('.prod-cat-addt-details-thumb').length;
	thumbContainerWidth = (thumbImageWidth+20)*numberOfImages;
	thumbnailHolderContainerW = thumbnailHolderContainer.width();
	thumbnailControls = jQuery('.thumbnail-control');
	//jQuery('.prod-cat-addt-details-thumbs-div').css({width:thumbContainerWidth,height:thumbImageHeight+20,position:"absolute",top:0,left:0});
	//jQuery(thumbnailHolderContainer).css({minHeight:thumbImageHeight+20,width:thumbContainerWidth});

	if(thumbContainerWidth > thumbnailHolderContainerW){
		thumbnailControls.show();
		var tnScrollerW = jQuery(".thumbnail-scroller").width();
		var tnHolderDiv = jQuery(".prod-cat-addt-details-thumbs-div").width();
		var tnScrollLimit = -tnHolderDiv + tnScrollerW + thumbImageWidth;
		jQuery('.thumbnail-nav-left').click(function(){
			var tnContainerPos = thumbnailHolderContainer.position();
			var tnContainerXPos = tnContainerPos.left;
			if(tnContainerXPos >= tnScrollLimit){
				var scrollThumbnails = tnContainerXPos - (thumbImageWidth+20);
				jQuery(thumbnailHolderContainer).animate({left:scrollThumbnails});
				jQuery('.thumbnail-nav-right').show();
			}else if(tnContainerXPos <= tnScrollLimit){
				jQuery(this).hide();
			};
		});
		jQuery('.thumbnail-nav-right').click(function(){
			var tnContainerPos = thumbnailHolderContainer.position();
			var tnContainerXPos = tnContainerPos.left;
			if(tnContainerXPos != 0){
				var scrollThumbnails = tnContainerXPos + (thumbImageWidth+20);
				jQuery(thumbnailHolderContainer).animate({left:scrollThumbnails});
				jQuery('.thumbnail-nav-left').show();
			}else if(tnContainerXPos == 0){
				jQuery(this).hide();
			}
		});
	};
};

function additionalThemeJS() {
	try{
		upcp_style_hover();
	}
	catch(e) {
	}
}

function addClickHandlers() {
	if (typeof maintain_filtering === 'undefined' || maintain_filtering === null) {maintain_filtering = "Yes";}

	if (maintain_filtering != "No") {
		jQuery(".upcp-catalogue-link").click(function(event){
			event.preventDefault();
    		var link = jQuery(this).attr('href');
    		jQuery("#upcp-hidden-filtering-form").attr('action', link);
    		jQuery("#upcp-hidden-filtering-form").submit();
		});
	}
	additionalThemeJS();
}

function FieldFocus (Field) {
		if (Field.value == Field.defaultValue){
			  Field.value = '';
		}
}

function FieldBlur(Field) {
		if (Field.value == '') {
			  Field.value = Field.defaultValue;
		}
}

function UPCPHighlight(Field, Color) {
		if (jQuery(Field.parentNode).hasClass('highlight'+Color)) {
			  jQuery(Field.parentNode).removeClass('highlight'+Color);
		}
		else {
				jQuery(Field.parentNode).addClass('highlight'+Color);
		}
}

function UPCP_DisplayPage(PageNum) {
	jQuery('#upcp-current-page').html(PageNum);
	UPCP_Ajax_Filter();
}

function UPCP_Show_Hide_CF(cf_title) {
	var CFID = jQuery(cf_title).data('cfid');

	jQuery('.prod-cat-sidebar-cf-content').each(function() {
		if (jQuery(this).data('cfid') == CFID) {
			jQuery(this).slideToggle('1000', 'swing');
		}
	});
}

function UPCP_Show_Hide_Sidebar(sidebar_title) {
	var TITLE = jQuery(sidebar_title).data('title');

	jQuery('.prod-cat-sidebar-content').each(function() {
	if(jQuery(this).data('title') == TITLE) {
		jQuery(this).slideToggle('1000', 'swing');
	}
	});
}

function UPCP_Show_Hide_Subcat(sidebar_category) {
	jQuery('#subcat-collapsible-'+sidebar_category).slideToggle('1000', 'swing');
	  jQuery('#cat-collapsible-'+sidebar_category).toggleClass("clicked");
        if ( jQuery('#cat-collapsible-'+sidebar_category).hasClass("clicked") ) {
            jQuery('#cat-collapsible-'+sidebar_category).text("-");
        }
        else {
            jQuery('#cat-collapsible-'+sidebar_category).text("+");
        }
}

var RequestCount = 0;
function UPCP_Ajax_Filter() {
	var CatValues = [];
	var SubCatValues = [];
	var TagBoxValues = [];
	var CFBoxValues = [];

	var id = jQuery('#upcp-catalogue-id').html();
	var sidebar = jQuery('#upcp-catalogue-sidebar').html();
	var start_layout = jQuery('#upcp-starting-layout').html();
	var current_layout = jQuery('#upcp-current-layout').html();
	var excluded_layouts = jQuery('#upcp-excluded-layouts').html();
	var current_page = jQuery('#upcp-current-page').html();
	var base_url = jQuery('#upcp-base-url').html();
	var default_search_text = jQuery('#upcp-default-search-text').html();

	var values = jQuery('#upcp-price-score-filter').slider("option", "values");
	var min_price = values[0];
	var max_price = values[1];
	if (min_price == undefined) {min_price = 0;}
	if (max_price == undefined) {max_price = 10000000;}

	jQuery('.jquery-prod-cat-value').each(function() {if (jQuery(this).prop('checked')) {CatValues.push(jQuery(this).val());}});
	jQuery('.jquery-prod-sub-cat-value').each(function() {if (jQuery(this).prop('checked')) {SubCatValues.push(jQuery(this).val());}});
	jQuery('.jquery-prod-tag-value').each(function() {if (jQuery(this).prop('checked')) {TagBoxValues.push(jQuery(this).val());}});
	jQuery('.jquery-prod-cf-value').each(function() {if (jQuery(this).prop('checked')) {
		Array_Value = jQuery(this).parent().parent().data('cfid') + "=" + jQuery(this).val();
		CFBoxValues.push(Array_Value);
	}});
	jQuery('.upcp-custom-field-slider').each(function() {
    	var Field_ID = jQuery(this).data("fieldid");

    	var Array_Value = Field_ID + "=" + window['min_'+Field_ID];
    	CFBoxValues.push(Array_Value);
    	var Array_Value = Field_ID + "=" + window['max_'+Field_ID];
    	CFBoxValues.push(Array_Value);
    });

	if (jQuery('.prod-cat-sidebar').css('display') == "none") {var SelectedProdName = jQuery('#upcp-mobile-search').val();}
	else {var SelectedProdName = jQuery('#upcp-name-search').val();}

	if (SelectedProdName == undefined) {SelectedProdName = default_search_text;}

	jQuery('.prod-cat-inner').html('<h3>'+ajax_translations.updating_results_label+'</h3>');
	RequestCount = RequestCount + 1;
	var data = 'id=' + id + '&sidebar=' + sidebar + '&start_layout=' + current_layout + '&excluded_layouts=' + excluded_layouts + '&ajax_url=' + base_url + '&current_page=' + current_page + '&default_search_text=' + default_search_text + '&ajax_reload=Yes' + '&Prod_Name=' + SelectedProdName + '&max_price=' + max_price + '&min_price=' + min_price + '&Category=' + CatValues + '&SubCategory=' + SubCatValues + '&Tags=' + TagBoxValues + '&Custom_Fields=' + encodeURIComponent(CFBoxValues) + '&request_count=' + RequestCount + '&action=update_catalogue';
	jQuery.post(ajaxurl, data, function(response) {
		response = response.substring(0, response.length - 1);
		var parsed_response = jQuery.parseJSON(response);
		if (parsed_response.request_count == RequestCount) {
			jQuery('.prod-cat-inner').html(parsed_response.message);
			if (CatValues.length == 0 && SubCatValues.length == 0 && TagBoxValues.length == 0 && CFBoxValues.length == 0) {jQuery('.prod-cat-category-label').each(function() {jQuery(this).removeClass('Hide-Item');});}
			if (jQuery('#upcp-sort-by').val() != "" && jQuery('#upcp-sort-by').val() != undefined) {UPCP_Sort_By();}
			adjustCatalogueHeight();
			addClickHandlers();
			addLightboxHandlers();
			addProductcomparisonClickHandlers();
		}
	});
}

jQuery(document).ready(function() {
	addProductcomparisonClickHandlers();
});

function addProductcomparisonClickHandlers() {
	jQuery('.upcp-product-comparison-button').on('click', function() {
		var thisComparisonButton = jQuery(this);
		var Prod_ID = jQuery(this).data('prodid');
		var Prod_Name = jQuery(this).data('prodname');

		var input = jQuery('#upcp-product-comparison-form').find('input#upcp-pc-id'+Prod_ID);
		if (!thisComparisonButton.hasClass('comparisonClicked')) {
			thisComparisonButton.addClass('comparisonClicked');
			if (input.length == 0) {
				jQuery('<input>').attr({
				    type: 'hidden',
				    id: 'upcp-pc-id'+Prod_ID,
				    name: 'Comparison_Product_ID[]',
				    value: Prod_ID
				}).data('prodname', Prod_Name).appendTo('#upcp-product-comparison-form');
			}
		}
		else {
			thisComparisonButton.removeClass('comparisonClicked');
			jQuery(input.selector).remove();
			jQuery('#upcp-product-comparison-form .upcp-prod-comp-submit-instructions').remove();
		}

		if (jQuery('#upcp-product-comparison-form').find('input').length >= 2) {
			jQuery('.upcp-prod-comp-submit-instructions').remove();
			var Product_Name_String = "";
			jQuery('#upcp-product-comparison-form input').each(function(index, el) {
				Product_Name_String += jQuery(this).data('prodname') + ", ";
			});
			Product_Name_String = Product_Name_String.slice(0, -2);
			var replacement = " and";
			Product_Name_String = Product_Name_String.replace(/,([^,]*)$/,replacement+'$1');
			jQuery('#upcp-product-comparison-form').append('<div class="upcp-prod-comp-submit-instructions">Compare ' + Product_Name_String + ' side by side!<input type="submit" value="Compare" /></div>');

			adjustCatalogueHeight();
		}
	})
}

jQuery(document).ready(function() {
	if (typeof max_price === 'undefined' || max_price === null) {max_price = 10000000;}
	if (typeof min_price === 'undefined' || min_price === null) {min_price = 0;}

	max_price_int = parseInt(max_price);
	min_price_int = parseInt(min_price);

	jQuery("#upcp-price-score-filter").slider({
    	range: true,
    	min: min_price_int,
    	max: max_price_int,
    	values: [ min_price_int, max_price_int ],
        change: function( event, ui ) {
           jQuery('#upcp-current-page').html("1");
           jQuery("#upcp-price-range").text( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
           UPCP_Ajax_Filter();
        }
    });

    jQuery('.upcp-custom-field-slider').each(function() {
    	var Field_ID = jQuery(this).data("fieldid");

    	if (typeof window['max_'+Field_ID] === 'undefined' || window['max_'+Field_ID] === null) {window['max_'+Field_ID] = 10000000;}
    	if (typeof window['min_'+Field_ID] === 'undefined' || window['min_'+Field_ID] === null) {window['min_'+Field_ID] = 0;}

    	max_value_int = parseInt(window['max_'+Field_ID]);
		min_value_int = parseInt(window['min_'+Field_ID]);

    	jQuery("#upcp-custom-field-"+Field_ID).slider({
    		range: true,
    		min: min_value_int,
    		max: max_value_int,
    		values: [ min_value_int, max_value_int ],
    	    change: function( event, ui ) {
    	       jQuery("#upcp-custom-field-range-"+Field_ID).text( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			   window['min_'+Field_ID] = ui.values[0];
    	       window['max_'+Field_ID] = ui.values[1];
    	       UPCP_Ajax_Filter();
    	    }
    	});
    })
});

/* Used in the bare-list layout to show or hide extra product details */
function ToggleItem(Item_ID) {
	if (jQuery('#prod-cat-details-'+Item_ID).css('display') == "none") {
		jQuery('#prod-cat-details-'+Item_ID).removeClass('hidden-field');
		adjustCatalogueHeight();
	}
	else {
		jQuery('#prod-cat-details-'+Item_ID).addClass('hidden-field');
		adjustCatalogueHeight();
	}
}

/* Used to track the number of times that a product is clicked in all catalogues */
function RecordView(Item_ID) {
		var data = 'Item_ID=' + Item_ID + '&action=record_view';
		jQuery.post(ajaxurl, data, function(response) {});
}

function ToggleView(DisplayType) {

		if (DisplayType == "Thumbnail" && jQuery('.thumb-display').hasClass('hidden-field')) {
			  jQuery('.list-display').animate({opacity: 0}, 500);
				jQuery('.detail-display').animate({opacity: 0}, 500);
				setTimeout(function(){jQuery('.thumb-display').animate({opacity: 1}, 500);})
				jQuery('.thumb-display').removeClass('hidden-field');
				jQuery('#upcp-current-layout').html('Thumbnail');
				setTimeout(function(){jQuery('.list-display').addClass('hidden-field');})
				setTimeout(function(){jQuery('.detail-display').addClass('hidden-field');})
		}
		if (DisplayType == "List" && jQuery('.list-display').hasClass('hidden-field')) {
			  jQuery('.thumb-display').animate({opacity: 0}, 500);
				jQuery('.detail-display').animate({opacity: 0}, 500);
				setTimeout(function(){jQuery('.list-display').animate({opacity: 1}, 500);})
				jQuery('.list-display').removeClass('hidden-field');
				jQuery('#upcp-current-layout').html('List');
				setTimeout(function(){jQuery('.thumb-display').addClass('hidden-field');})
				setTimeout(function(){jQuery('.detail-display').addClass('hidden-field');})
		}
		if (DisplayType == "Detail" && jQuery('.detail-display').hasClass('hidden-field')) {
			  jQuery('.list-display').animate({opacity: 0}, 500);
				jQuery('.thumb-display').animate({opacity: 0}, 500);
				setTimeout(function(){jQuery('.detail-display').animate({opacity: 1}, 500);})
				jQuery('.detail-display').removeClass('hidden-field');
				jQuery('#upcp-current-layout').html('Detail');
				setTimeout(function(){jQuery('.list-display').addClass('hidden-field');})
				setTimeout(function(){jQuery('.thumb-display').addClass('hidden-field');})
		}

		setTimeout(function(){adjustCatalogueHeight();})

		return false;
}

function ZoomImage(ProdID, ItemID) {
	jQuery('.upcp-tabbed-video-container').addClass('upcp-Hide-Item');
	jQuery('.upcp-tabbed-image-container').removeClass('upcp-Hide-Item');
	if (ItemID == 0) {
	  PhotoSRC = jQuery('#prod-cat-addt-details-thumb-P'+ProdID).attr('src');
	}
	else {
		PhotoSRC = jQuery('#prod-cat-addt-details-thumb-'+ItemID).attr('src');
	}
	jQuery('.prod-cat-addt-details-main').each(function() {jQuery(this).attr('src', PhotoSRC)});
	jQuery('.prod-cat-addt-details-link-a').each(function() {jQuery(this).attr('href', PhotoSRC)});

	var Slide_Index = jQuery('#prod-cat-addt-details-thumb-'+ItemID).data('slideindex');
	jQuery('#upcp-lightgallery-0').data('slide', Slide_Index);
}

jQuery(document).ready(function() {
	jQuery('.upcp-thumb-video').on('click', function(){
		var videoID = jQuery(this).data('videoid');
		jQuery('.upcp-main-video').attr('src', 'http://www.youtube.com/embed/'+videoID);
		jQuery('.upcp-tabbed-image-container').addClass('upcp-Hide-Item');
		jQuery('.upcp-tabbed-video-container').removeClass('upcp-Hide-Item');
	})
});

jQuery(document).ready(function()
{
    adjustCatalogueHeight();
    jQuery(window).resize(function() {
    	adjustCatalogueHeight();
    });
});

function adjustCatalogueHeight() {
 	var objHeight = 0;

    jQuery.each(jQuery('.prod-cat-inner').children(), function(){
     if (!jQuery(this).hasClass('hidden-field')) {
		objHeight = Math.max(jQuery(this).height(), objHeight);
		}
    });

	objHeight = objHeight + 120;
    jQuery('.prod-cat-inner').height(objHeight);

    if (jQuery(window).width() <= 715) {
    	objHeight = jQuery('.prod-cat-inner').height() + jQuery('.prod-cat-sidebar').height();
    	jQuery('.prod-cat-container').height(objHeight);
	}
	else {
		objHeight = Math.max(jQuery('.prod-cat-inner').height(), jQuery('.prod-cat-sidebar').height());
		jQuery('.prod-cat-container').height(objHeight);
	}
}

/* Sort by price or by name */
jQuery.fn.sortElements = (function(){

    var sort = [].sort;

    return function(comparator, getSortable) {

        getSortable = getSortable || function(){return this;};

        var placements = this.map(function(){

            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,

                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );

            return function() {

                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }

                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);

            };

        });

        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });

    };

})();

function UPCP_Sort_By() {
		jQuery('.prod-cat-category-label').each(function() {jQuery(this).addClass('Hide-Item');});
		var SortBy = jQuery('#upcp-sort-by').val();
		if (SortBy == "name_asc") {SortByNameASC();}
		else if (SortBy == "name_desc") {SortByNameDESC();}
		else if (SortBy == "price_asc") {SortByPriceASC();}
		else {SortByPriceDESC();}
}

function SortByNameASC() {
        jQuery('.thumb-display div .prod-cat-title').sortElements(function(a, b){
						return jQuery(a).text() > jQuery(b).text() ? 1 : -1;
        }, function() {
						return this.parentNode; //here
				});
				jQuery('.list-display div .prod-cat-title').sortElements(function(a, b){
						return jQuery(a).text() > jQuery(b).text() ? 1 : -1;
        }, function() {
						return this.parentNode;
				});
				jQuery('.detail-display div .prod-cat-title').sortElements(function(a, b){
						return jQuery(a).text() > jQuery(b).text() ? 1 : -1;
        }, function() {
						return this.parentNode.parentNode.parentNode; //here
				});
}

function SortByNameDESC() {
        jQuery('.thumb-display div .prod-cat-title').sortElements(function(a, b){
						return jQuery(a).text() < jQuery(b).text() ? 1 : -1;
        }, function() {
						return this.parentNode; //here
				});
				jQuery('.list-display div .prod-cat-title').sortElements(function(a, b){
						return jQuery(a).text() < jQuery(b).text() ? 1 : -1;
        }, function() {
						return this.parentNode;
				});
				jQuery('.detail-display div .prod-cat-title').sortElements(function(a, b){
						return jQuery(a).text() < jQuery(b).text() ? 1 : -1;
        }, function() {
						return this.parentNode.parentNode.parentNode; //here
				});
}

function SortByPriceASC() {
        var first;
				var second;
				jQuery('.thumb-display div .prod-cat-price').sortElements(function(a, b){
						first = jQuery(a).text().replace(/\D/g,'');
						second = jQuery(b).text().replace(/\D/g,'');
						return Number(first) > Number(second) ? 1 : -1;
        }, function() {
						return this.parentNode;
				});
				jQuery('.list-display div .prod-cat-price').sortElements(function(a, b){
						first = jQuery(a).text().replace(/\D/g,'');
						second = jQuery(b).text().replace(/\D/g,'');
						return Number(first) > Number(second) ? 1 : -1;
        }, function() {
						return this.parentNode;
				});
				jQuery('.detail-display div .prod-cat-price').sortElements(function(a, b){
						first = jQuery(a).text().replace(/\D/g,'');
						second = jQuery(b).text().replace(/\D/g,'');
						return Number(first) > Number(second) ? 1 : -1;
        }, function() {
						return this.parentNode.parentNode;
				});
}

function SortByPriceDESC() {
				var first;
				var second;
				jQuery('.thumb-display div .prod-cat-price').sortElements(function(a, b){
						first = jQuery(a).text().replace(/\D/g,'');
						second = jQuery(b).text().replace(/\D/g,'');
						return Number(first) < Number(second) ? 1 : -1;
        }, function() {
						return this.parentNode;
				});
				jQuery('.list-display div .prod-cat-price').sortElements(function(a, b){
						first = jQuery(a).text().replace(/\D/g,'');
						second = jQuery(b).text().replace(/\D/g,'');
						return Number(first) < Number(second) ? 1 : -1;
        }, function() {
						return this.parentNode;
				});
				jQuery('.detail-display div .prod-cat-price').sortElements(function(a, b){
						first = jQuery(a).text().replace(/\D/g,'');
						second = jQuery(b).text().replace(/\D/g,'');
						return Number(first) < Number(second) ? 1 : -1;
        }, function() {
						return this.parentNode.parentNode;
				});
}
