<?php
/**
 * SKT Movers Packers About Theme
 *
 * @package SKT Movers Packers
 */
 
//about theme info
add_action( 'admin_menu', 'movers_packers_abouttheme' );
function movers_packers_abouttheme() {    	
	add_theme_page( __('About Theme', 'movers-packers'), __('About Theme', 'movers-packers'), 'edit_theme_options', 'movers_packers_guide', 'movers_packers_mostrar_guide');   
} 

//guidline for about theme
function movers_packers_mostrar_guide() { 
	//custom function about theme customizer
	$return = add_query_arg( array()) ;
?>

<style type="text/css">
@media screen and (min-width: 800px) {
.col-left {float:left; width: 65%; padding: 1%;}
.col-right {float:right; width: 30%; padding:1%; border-left:1px solid #DDDDDD; }
}
</style>

<div class="wrapper-info">
	<div class="col-left">
   		   <div style="font-size:16px; font-weight:bold; padding-bottom:5px; border-bottom:1px solid #ccc;">
			  <?php esc_attr_e('About Theme Info', 'movers-packers'); ?>
		   </div>
          <p><?php esc_attr_e('SKT Movers and Packers is a responsive WordPress theme which is multipurpose and can be used for corporate, business, sales, landing page, product launch and various other website usage like portfolio, personal, architect, consulting, construction, real estate etc. Compatible with contact form 7, WooCommerce, NextGen Gallery and is translation ready.','movers-packers'); ?></p>
		  <a href="<?php echo SKT_PRO_THEME_URL; ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/free-vs-pro.png" alt="" /></a>
	</div><!-- .col-left -->
	
	<div class="col-right">			
			<div style="text-align:center; font-weight:bold;">
				<hr />
				<a href="<?php echo SKT_LIVE_DEMO; ?>" target="_blank"><?php esc_attr_e('Live Demo', 'movers-packers'); ?></a> | 
				<a href="<?php echo SKT_PRO_THEME_URL; ?>"><?php esc_attr_e('Buy Pro', 'movers-packers'); ?></a> | 
				<a href="<?php echo SKT_THEME_DOC; ?>" target="_blank"><?php esc_attr_e('Documentation', 'movers-packers'); ?></a>
                <div style="height:5px"></div>
				<hr />                
                <a href="<?php echo SKT_THEME_URL; ?>" target="_blank"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/sktskill.jpg" alt="" /></a>
			</div>		
	</div><!-- .col-right -->
</div><!-- .wrapper-info -->
<?php } ?>