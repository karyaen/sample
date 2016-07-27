<?php

if (!defined("ABSPATH")) exit;

/**
 * Class YoFLA360Woocommerce
 *
 * Adds tab to product settings to specify an (uploaded) 360 view to replace
 * main product image.
 *
 * Inspired by WebRotate 360 Woocommerce plugin structure, thanks!
 * (WebRotate 360, in return, got inspired by my 360 player & software)
 *
 */
class YoFLA360Woocommerce {


    public function __construct()
    {
        if (is_admin())
        {
            add_action("woocommerce_product_write_panel_tabs", array($this, "yofla_360_tab_options_tab"));
            add_action("woocommerce_product_write_panels", array($this, "yofla_360_tab_options"));
            add_action("woocommerce_process_product_meta", array($this, "yofla_360_save_product"));
        }
        else
        {
            add_filter("woocommerce_single_product_image_html", array($this, "yofla_360_replace_product_image"), 100);
        }
    }

    /**
     * HTML of the 360 Product Tab Tab
     *
     */
    public function yofla_360_tab_options_tab()
    {
        echo '<li class="custom_tab"><a href="#yofla360_tab_data" id="yofla360_tab">360&deg; View</a></li>';
    }


    /**
     * HTML of the 360 Product Tab Content
     * 
     */
    public function yofla_360_tab_options()
    {
        echo '<style type="text/css">a#yofla360_tab:before{content: "\e031" !important;}</style>
            <div id="yofla360_tab_data" class="panel woocommerce_options_panel">
            <div class="options_group">
            ';

        //list of 360 views
        $products_list = $this->_get_products_list();
        $upload_url    = admin_url('upload.php?page=yofla-360-media');

        //no product uploaded yet
        if( sizeof($products_list) == 1 )
        {
            echo '<span style="padding: 10px;">No 360&deg; views found. Do you wish to <a href="'.$upload_url.'">add one</a> now?</span>';
        }
        //a product is uploaded
        else
        {

            if( $variants = $this->_is_variable_product() )
            {
                foreach ($variants as $variant){

                    $variant_name = $variant["name"];
                    $variant_id   = $variant["id"];

                    echo "<strong>$variant_name</strong>";

                    woocommerce_wp_select(array(
                        "id" => "_y360path_variant_".$variant_id,
                        "name" => "_y360path_variants[$variant_id]",
                        "label" => "Please choose a 360&deg; view",
                        "placeholder" => "",
                        "desc_tip" => true,
                        "description" => sprintf("Upload new 360 views in Media page."),
                        "options" => $products_list
                    ));
                }
            }
            else
            {
                woocommerce_wp_select(array(
                    "id" => "_y360path",
                    "name" => "_y360path",
                    "label" => "Please choose a 360&deg; view",
                    "placeholder" => "",
                    "desc_tip" => true,
                    "description" => sprintf("Upload new 360 views in Media page."),
                    "options" => $products_list
                ));
            }
        }

        echo "</div></div>";
    }

    /**
     * Saving path parameter when product is saved/updated
     * 
     * @param $post_id
     */
    public function yofla_360_save_product($post_id)
    {
        if (isset($_POST["_y360path"]))
        {
            update_post_meta($post_id, "_y360path", sanitize_text_field($_POST["_y360path"]));
        }
        elseif( isset ($_POST["_y360path_variants"]) ){
            update_post_meta($post_id, "_y360path_variants", $_POST["_y360path_variants"] );
        }
    }


    /**
     * Callback function for the filter that modifies the output for the product image
     * 
     * @param $content
     * @return string
     */
    public function yofla_360_replace_product_image($content)
    {
        global $post;

        if (empty($post->ID)) return ($content);

        $_y360path = (get_post_meta($post->ID, "_y360path", true));
        if (empty($_y360path) ) return ($content);

        $content = $this->_update_product_image($_y360path);

        return $content;
    }


    /**
     * Returns the html code of the 360 view that is used instead of the main product image
     *
     * @param $_y360path
     * @return string
     */
    private function _update_product_image($_y360path)
    {
        $viewData = new YoFLA360ViewData();
        $viewData->set_src($_y360path);
        $viewData->width = '100%';
        $viewData->height = '300px';

        //iframe embedding
        $content = YoFLA360()->Frontend()->get_embed_iframe_html($viewData);

        return YoFLA360()->Frontend()->format_plugin_output($content);
    }

    /**
     * Returns a list of uploaded products, that can be assigned as a 360 view
     * for this product.
     *
     * Output format is an array for the woocommerce_wp_select function
     *
     *
     * @see yofla_360_tab_options
     * @see YoFLA360Utils
     */
    private function  _get_products_list()
    {
        $result = array();
        $result[''] = 'No 360&deg; view is assigned';

        $list_raw = YoFLA360()->Utils()->get_yofla360_directories_list(false);

        foreach( $list_raw as $key => $value )
        {
           $result[YOFLA_360_PRODUCTS_FOLDER.'/'.$value['name']] = $value['name'];
        }
        return $result;
    }

    /**
     * Returns false if product has no variations.
     * Returns array of variation ids, if product has variations
     *
     * @return bool|mixed
     */
    private function _is_variable_product()
    {
        //dev
        return false;
        $_pf = new WC_Product_Factory();
        $product  = $_pf->get_product( get_the_ID() );

        if( get_class($product) == 'WC_Product_Variable')
        {
            $variations = $product->get_available_variations();
            $result = array();
            foreach ($variations as $key => $value) {
                $result[] = array("id" => $value['variation_id'], "name" => reset($value['attributes']));
            }
            return $result;
        }
        return false; //no variable product
    }

}//class
