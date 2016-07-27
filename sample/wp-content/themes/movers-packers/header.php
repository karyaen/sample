<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div class="container">
 *
 * @package SKT Movers Packers
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(''); ?>>
<div id="pagefixed">
  <div class="headertop">
    <div class="container" style="display:none">
        <div class="topleft"><?php wp_nav_menu(array('theme_location' => 'topmenu', 'depth' => 1)); ?></div>
        <div class="topright">
           <div class="social-icons">
           <?php if ( '' !== get_theme_mod( 'fb_link' ) ) { ?>
          <a title="facebook" class="fb" target="_blank" href="<?php echo esc_url(get_theme_mod('fb_link','#facebook')); ?>"></a>
          <?php } ?>
          <?php if ( '' !== get_theme_mod( 'twitt_link' ) ) { ?>
          <a title="twitter" class="tw" target="_blank" href="<?php echo esc_url(get_theme_mod('twitt_link','#twitter')); ?>"></a>
          <?php } ?>
          <?php if ( '' !== get_theme_mod('gplus_link') ) { ?>
          <a title="google-plus" class="gp" target="_blank" href="<?php echo esc_url(get_theme_mod('gplus_link','#gplus')); ?>"></a>
          <?php }?>
          <?php if ( '' !== get_theme_mod('linked_link') ) { ?>
          <a title="linkedin" class="in" target="_blank" href="<?php echo esc_url(get_theme_mod('linked_link','#linkedin')); ?>"></a>
          <?php } ?>
               </div>  
        </div>
        <div class="clear"></div>
     </div> 
  </div><!-- end .headertop -->
  <div class="header <?php if ( !is_front_page() && ! is_home() ) { ?>innerheader<?php } ?>">
        <div class="container">
            <div class="logo">
			            <?php movers_packers_the_custom_logo(); ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>"><h1><?php bloginfo('name'); ?></h1></a>
                        <p><?php bloginfo('description'); ?></p>
            </div><!-- logo -->
            <div class="header_right"> 
            
             <?php if ( ! dynamic_sidebar( 'header-info' ) ) : ?>
                 <div class="headerinfo">
                 
                   <div class="headcol-1">
				   <?php if( '' !== get_theme_mod('opning_hours_title')){ ?>
                    <span><?php echo esc_attr(get_theme_mod('opning_hours_title', __('Opening Hours', 'movers-packers'))); ?></span>
                   <?php } ?>                   
                   <?php if( '' !== get_theme_mod('opning_hours')) { ?>
                    <?php echo wp_kses_post(get_theme_mod('opning_hours', __('Mon to Fri - 10.00 AM to 7.00 PM<br /> Sat - 10.00 AM to 4.00 PM', 'movers-packers'))); ?>
                   <?php } ?> 
                   </div>
                   
                   <div class="headcol-2">
                   <?php if( '' !== get_theme_mod('callus_title') ) { ?>
                    <span><?php echo esc_attr(get_theme_mod('callus_title', __('Call Us', 'movers-packers'))); ?></span>
                   <?php } ?>                    
                    <?php if( '' !== get_theme_mod('header_phone') ) { ?>
                    <?php echo esc_attr(get_theme_mod('header_phone', __('+10 2234567890 +10 1123456789', 'movers-packers'))); ?>
                   <?php } ?>
                   </div>
                   
                   <div class="headcol-3">
                    <?php if( '' !== get_theme_mod('emailus_title') ) { ?>
                    <span><?php echo esc_attr(get_theme_mod('emailus_title', __('Email Us', 'movers-packers'))); ?></span>
                    <?php } ?>
                   <?php if( '' !== get_theme_mod('email_address') ) { ?>
                    <?php echo esc_attr(get_theme_mod('email_address', 'support@sitename.com info@sitename.com', 'movers-packers')); ?>
                   <?php } ?>
                   </div>
                   <div class="clear"></div>                  
                       
                 </div>                 
            <?php endif; // end sidebar widget area ?>          
                        
            
            <div class="clear"></div>
          </div><!-- header_right -->
          <div class="clear"></div>
        </div><!-- container -->
  </div><!--.header -->
  <div class="menubar">
     <div class="toggle">
         <a class="toggleMenu" href="<?php echo esc_url('#');?>"><?php _e('Menu','movers-packers'); ?></a>
     </div><!-- toggle --> 
      <div class="sitenav">
          <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
    <div class="getaquote" style="display:none;">
    <ul>
        <li><a href="<?php echo esc_url(get_theme_mod('getquote_link','#')); ?>"><?php echo esc_attr(get_theme_mod('getquote_title', __('Get A Quote', 'movers-packers'))); ?></a></li>
    </ul>
    </div>          
      </div><!-- site-nav -->  
  </div><!--end .menubar -->
  
<?php if ( is_front_page() && is_home() ) { ?>
<!-- Slider Section -->
<?php for($sld=7; $sld<10; $sld++) { ?>
<?php if( get_theme_mod('page-setting'.$sld)) { ?>
<?php $slidequery = new WP_query('page_id='.get_theme_mod('page-setting'.$sld,true)); ?>
<?php while( $slidequery->have_posts() ) : $slidequery->the_post();
$image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
$img_arr[] = $image;
$id_arr[] = $post->ID;
endwhile;
}
}
?>
<?php if(!empty($id_arr)){ ?>
<section id="home_slider" <?php if( get_theme_mod( 'hide_slides' ) != '') { ?> class="none"<?php } ?>>
<div class="slider-wrapper theme-default">
<div id="slider" class="nivoSlider">
	<?php 
	$i=1;
	foreach($img_arr as $url){ ?>
    <img src="<?php echo esc_url($url); ?>" title="#slidecaption<?php echo $i; ?>" />
    <?php $i++; }  ?>
</div>   
<?php 
$i=1;
foreach($id_arr as $id){ 
$title = get_the_title( $id ); 
$post = get_post($id); 
$content = apply_filters('the_content', substr(strip_tags($post->post_content), 0, 150)); 
?>                 
<div id="slidecaption<?php echo $i; ?>" class="nivo-html-caption">
<div class="slide_info">
<h2><?php echo $title; ?></h2>
<?php echo $content; ?>
<div class="clear"></div>
<div class="slide_more"><a href="<?php the_permalink(); ?>"><?php esc_attr_e('Read More', 'movers-packers');?></a></div>
</div>
</div>      
<?php $i++; } ?>       
 </div>
<div class="clear"></div>        
</section>
<?php } else { ?>
<section id="home_slider" <?php if( get_theme_mod( 'hide_slides' ) != '') { ?> class="none"<?php } ?>>
<div class="slider-wrapper theme-default">
    <div id="slider" class="nivoSlider">
        <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider1.jpg" alt="" title="#slidecaption1" />
        <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider2.jpg" alt="" title="#slidecaption2" />
        <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider3.jpg" alt="" title="#slidecaption3" />
    </div>                    
      <div id="slidecaption1" class="nivo-html-caption">
        <div class="slide_info">
                <h2><?php esc_attr_e('We are moving experts','movers-packers'); ?></h2>
                <p><?php esc_attr_e('This is an example page. Its different from a blog post because it will stay in one place and will show up in your site navigation','movers-packers'); ?></p>
        </div>
        </div>
        
        <div id="slidecaption2" class="nivo-html-caption">
            <div class="slide_info">
                    <h2><?php esc_attr_e('We are moving experts','movers-packers'); ?></h2>
                        <p><?php esc_attr_e('This is an example page. Its different from a blog post because it will stay in one place and will show up in your site navigation','movers-packers'); ?></p
            ></div>
        </div>
        
        <div id="slidecaption3" class="nivo-html-caption">
            <div class="slide_info">
                    <h2><?php esc_attr_e('We are moving experts','movers-packers'); ?></h2>
                        <p><?php esc_attr_e('This is an example page. Its different from a blog post because it will stay in one place and will show up in your site navigation','movers-packers'); ?></p
            ></div>
        </div>
</div>
<div class="clear"></div>
</section>
<?php } ?>
<!-- Slider Section -->
<?php if( get_theme_mod( 'hide_choose' ) == '') { ?>	
  <section id="wrapfirst">
            	<div class="container">
                    <div class="welcomewrap">
					<?php if( get_theme_mod('page-setting1')) { ?>
                    <?php $queryvar = new WP_query('page_id='.get_theme_mod('page-setting1' ,true)); ?>
                    <?php while( $queryvar->have_posts() ) : $queryvar->the_post();?> 		
                     <?php the_post_thumbnail( array(570,380, true));?>
                     <h1><?php the_title(); ?></h1>         
                     <?php the_content(); ?>
                     <div class="clear"></div>
                    <?php endwhile; } else { ?>                    
                    <h2><?php esc_attr_e('Why Choose Us','movers-packers'); ?></h2>
                    <p><?php esc_attr_e('Vivamus non elementum lacus. Nam ac molestie tortor. Ut sit amet lobortis magna, ut ornare ante. Curabitur lobortis urna non ligula porta
sodales. Pellentesque at efficitur risus, at dignissim libero. Etiam lacinia lorem sit amet arcu vulputate varius. Suspendisse sodales quam eu
egestas consequat. Donec vestibulum elementum libero eget fringilla. ','movers-packers'); ?></p>                   
                    <?php } ?>
                      
               </div><!-- welcomewrap-->
              <div class="clear"></div>
            </div><!-- container -->
</section>
<?php } ?>
<?php if( get_theme_mod( 'hide_column' ) == '') { ?>        
<div id="pagearea">
    <div class="container">         
            <?php for($fx=1; $fx<4; $fx++) { ?>
        	<?php if( get_theme_mod('page-column'.$fx,false) ) { ?>
        	<?php $queryvar = new wp_query('page_id='.get_theme_mod('page-column'.$fx,true));				
			while( $queryvar->have_posts() ) : $queryvar->the_post(); ?> 
        	    <div class="threebox <?php if($fx % 3 == 0) { echo "last_column"; } ?>">
				 <a href="<?php the_permalink(); ?>">				 
                  <?php if ( has_post_thumbnail() ) { ?>
                        <?php the_post_thumbnail( array(65,65,true));?>                        
                   <?php } else { ?>
                       <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/img_404.png" width="65" alt=""/>
                   <?php } ?>
                  <h3><?php the_title(); ?></h3>
                 </a>
                 <?php the_excerpt(); ?>
                 
        	   </div>
             <?php endwhile;
						wp_reset_query(); ?>
        <?php } else { ?>
        <div class="threebox <?php if($fx % 3 == 0) { echo "last_column"; } ?>">
             <a href="<?php echo esc_url('#')?>">
             <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/services-icon<?php echo $fx; ?>.png" alt="" />
             <h3><?php esc_attr_e('Largest Warehousing', 'movers-packers'); ?>&nbsp;<?php echo $fx; ?></h3>
             </a>
             <p><?php esc_attr_e('Praesent eget malesuada massa. Curabitur pretium, est eu iaculis maximus, sem arcu rhoncus arcu, sit amet consequat justo sem eget sapien. Nulla egestas non ex ultricies semper. Aliquam vitae orci rutrum','movers-packers');?></p>
         </div>
		<?php }} ?>      
    <div class="clear"></div>
    </div><!-- .container -->
 </div><?php }?><!-- #pagearea -->  

<?php
}
elseif ( is_front_page() ) { 
?>
<!-- Slider Section -->
<?php for($sld=7; $sld<10; $sld++) { ?>
<?php if( get_theme_mod('page-setting'.$sld)) { ?>
<?php $slidequery = new WP_query('page_id='.get_theme_mod('page-setting'.$sld,true)); ?>
<?php while( $slidequery->have_posts() ) : $slidequery->the_post();
$image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
$img_arr[] = $image;
$id_arr[] = $post->ID;
endwhile;
}
}
?>
<?php if(!empty($id_arr)){ ?>
<section id="home_slider" <?php if( get_theme_mod( 'hide_slides' ) != '') { ?> class="none"<?php } ?>>
<div class="slider-wrapper theme-default">
<div id="slider" class="nivoSlider">
	<?php 
	$i=1;
	foreach($img_arr as $url){ ?>
    <img src="<?php echo esc_url($url); ?>" title="#slidecaption<?php echo $i; ?>" />
    <?php $i++; }  ?>
</div>   
<?php 
$i=1;
foreach($id_arr as $id){ 
$title = get_the_title( $id ); 
$post = get_post($id); 
$content = apply_filters('the_content', substr(strip_tags($post->post_content), 0, 150)); 
?>                 
<div id="slidecaption<?php echo $i; ?>" class="nivo-html-caption">
<div class="slide_info">
<h2><?php echo $title; ?></h2>
<?php echo $content; ?>
<div class="clear"></div>
<div class="slide_more"><a href="<?php the_permalink(); ?>"><?php esc_attr_e('Read More', 'movers-packers');?></a></div>
</div>
</div>      
<?php $i++; } ?>       
 </div>
<div class="clear"></div>        
</section>
<?php } else { ?>
<section id="home_slider" <?php if( get_theme_mod( 'hide_slides' ) != '') { ?> class="none"<?php } ?>>
<div class="slider-wrapper theme-default">
    <div id="slider" class="nivoSlider">
        <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider1.jpg" alt="" title="#slidecaption1" />
        <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider2.jpg" alt="" title="#slidecaption2" />
        <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/slides/slider3.jpg" alt="" title="#slidecaption3" />
    </div>                    
      <div id="slidecaption1" class="nivo-html-caption">
        <div class="slide_info">
                <h2><?php esc_attr_e('We are moving experts','movers-packers'); ?></h2>
                <p><?php esc_attr_e('This is an example page. Its different from a blog post because it will stay in one place and will show up in your site navigation','movers-packers'); ?></p>
        </div>
        </div>
        
        <div id="slidecaption2" class="nivo-html-caption">
            <div class="slide_info">
                    <h2><?php esc_attr_e('We are moving experts','movers-packers'); ?></h2>
                        <p><?php esc_attr_e('This is an example page. Its different from a blog post because it will stay in one place and will show up in your site navigation','movers-packers'); ?></p
            ></div>
        </div>
        
        <div id="slidecaption3" class="nivo-html-caption">
            <div class="slide_info">
                    <h2><?php esc_attr_e('We are moving experts','movers-packers'); ?></h2>
                        <p><?php esc_attr_e('This is an example page. Its different from a blog post because it will stay in one place and will show up in your site navigation','movers-packers'); ?></p
            ></div>
        </div>
</div>
<div class="clear"></div>
</section>
<?php } ?>
<!-- Slider Section -->
<?php if( get_theme_mod( 'hide_choose' ) == '') { ?>	
  <section id="wrapfirst">
            	<div class="container">
                    <div class="welcomewrap">
					<?php if( get_theme_mod('page-setting1')) { ?>
                    <?php $queryvar = new WP_query('page_id='.get_theme_mod('page-setting1' ,true)); ?>
                    <?php while( $queryvar->have_posts() ) : $queryvar->the_post();?> 		
                     <?php the_post_thumbnail( array(570,380, true));?>
                     <h1><?php the_title(); ?></h1>         
                     <?php the_content(); ?>
                     <div class="clear"></div>
                    <?php endwhile; } else { ?>                    
                    <h2><?php esc_attr_e('Why Choose Us','movers-packers'); ?></h2>
                    <p><?php esc_attr_e('Vivamus non elementum lacus. Nam ac molestie tortor. Ut sit amet lobortis magna, ut ornare ante. Curabitur lobortis urna non ligula porta
sodales. Pellentesque at efficitur risus, at dignissim libero. Etiam lacinia lorem sit amet arcu vulputate varius. Suspendisse sodales quam eu
egestas consequat. Donec vestibulum elementum libero eget fringilla. ','movers-packers'); ?></p>                   
                    <?php } ?>
                      
               </div><!-- welcomewrap-->
              <div class="clear"></div>
            </div><!-- container -->
</section>
<?php } ?>
<?php if( get_theme_mod( 'hide_column' ) == '') { ?>        
<div id="pagearea">
    <div class="container">         
            <?php for($fx=1; $fx<4; $fx++) { ?>
        	<?php if( get_theme_mod('page-column'.$fx,false) ) { ?>
        	<?php $queryvar = new wp_query('page_id='.get_theme_mod('page-column'.$fx,true));				
			while( $queryvar->have_posts() ) : $queryvar->the_post(); ?> 
        	    <div class="threebox <?php if($fx % 3 == 0) { echo "last_column"; } ?>">
				 <a href="<?php the_permalink(); ?>">				 
                  <?php if ( has_post_thumbnail() ) { ?>
                        <?php the_post_thumbnail( array(65,65,true));?>                        
                   <?php } else { ?>
                       <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/img_404.png" width="65" alt=""/>
                   <?php } ?>
                  <h3><?php the_title(); ?></h3>
                 </a>
                 <?php the_excerpt(); ?>
                 
        	   </div>
             <?php endwhile;
						wp_reset_query(); ?>
        <?php } else { ?>
        <div class="threebox <?php if($fx % 3 == 0) { echo "last_column"; } ?>">
             <a href="<?php echo esc_url('#')?>">
             <img src="<?php echo esc_url( get_template_directory_uri() ) ; ?>/images/services-icon<?php echo $fx; ?>.png" alt="" />
             <h3><?php esc_attr_e('Largest Warehousing', 'movers-packers'); ?>&nbsp;<?php echo $fx; ?></h3>
             </a>
             <p><?php esc_attr_e('Praesent eget malesuada massa. Curabitur pretium, est eu iaculis maximus, sem arcu rhoncus arcu, sit amet consequat justo sem eget sapien. Nulla egestas non ex ultricies semper. Aliquam vitae orci rutrum','movers-packers');?></p>
         </div>
		<?php }} ?>      
    <div class="clear"></div>
    </div><!-- .container -->
 </div><?php } ?><!-- #pagearea --> 
<?php
}
elseif ( is_home() ) {
?>
<section id="home_slider" style="display:none;"></section>
<section id="wrapfirst" style="display:none;"></section>
<section id="pagearea" style="display:none;"></section>
<?php
}
?>  