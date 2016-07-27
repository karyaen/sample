<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package SKT Movers Packers
 */
?>
<div id="footer-wrapper">
  <div class="container footerfix">
    <div class="fixed3">
      <div class="addressbx">
        <?php if( '' !== get_theme_mod('contact_title') ) { ?>
        <span><?php echo esc_html(get_theme_mod('contact_title', __('Address:-', 'movers-packers'))); ?></span>
        <?php } ?>
        <?php if( '' !== get_theme_mod('contact_add') ) {
		echo esc_html( get_theme_mod('contact_add', __('Mover & Packers 10 Down Street, Hunterdon, United States', 'movers-packers'))); ?>
        <?php } ?>
      </div>
    </div>
    <div class="fixed3">
      <div class="phonebx">
        <?php if( '' !== get_theme_mod('callus_title') ) { ?>
        <span><?php echo esc_html(get_theme_mod('callus_title', __('Call Us:-', 'movers-packers'))); ?></span>
        <?php } ?>
        <?php if( '' !== get_theme_mod('header_phone') ) { ?>
        <?php echo esc_html(get_theme_mod('header_phone', __('+10 2234567890 +10 1123456789', 'movers-packers'))); ?>
        <?php } ?>
      </div>
    </div>
    <div class="fixed3 last_column">
      <div class="emailbx">
        <?php if( '' !== get_theme_mod('emailus_title') ) { ?>
        <span><?php echo esc_html(get_theme_mod('emailus_title', __('Email Us:-', 'movers-packers'))); ?></span>
        <?php } ?>
        <?php if( '' !== get_theme_mod('email_address') ) { ?>
        <?php echo esc_html(get_theme_mod('email_address', __('support@sitename.com info@sitename.com', 'movers-packers'))); ?>
        <?php } ?>
      </div>
    </div>
    <div class="clear" ></div>
  </div>
  <!--end .container-->
  
  <div class="container" style="display:none">
    <div class="cols-3 widget-column-1">
      <h5><?php echo esc_html(get_theme_mod('about_title',__('About Us','movers-packers'))); ?></h5>
      <p><?php echo wp_kses_post(get_theme_mod('about_description',__('Donec ut ex ac nulla pellentesque mollis in a enim. Praesent placerat sapien mauris, vitae sodales tellus venenatis ac. Suspendisse suscipit velit id ultricies auctor. Duis turpis arcu, aliquet sed sollicitudin sed, porta quis urna. Quisque velit nibh, egestas et erat a, vehicula interdum augue. <br /> </br > Donec ut ex ac nulla pellentesque mollis in a enim. Praesent placerat sapien mauris, vitae sodales tellus venenatis ac suspendisse suscipit velit. ','movers-packers'))); ?></p>
    </div>
    <!--end .widget-column-1-->
    <div class="cols-3 widget-column-2">
      <h5><?php echo esc_html(get_theme_mod('services_title',__('Our Services','movers-packers'))); ?></h5>
      <div class="menu">
        <?php wp_nav_menu(array('theme_location' => 'footer', 'depth' => 1)); ?>
      </div>
    </div>
    <!--end .widget-column-2-->
    
    <div class="cols-3 widget-column-3">
      <h5><?php echo esc_attr(get_theme_mod('latestpost_title',__('Latest Posts','movers-packers'))); ?></h5>
      <?php $args = array( 'posts_per_page' => 2, 'post__not_in' => get_option('sticky_posts'), 'orderby' => 'date', 'order' => 'desc' ); $the_query = new WP_Query( $args ); ?>
      <?php while ( $the_query->have_posts() ) :  $the_query->the_post(); ?>
      <div class="recent-post"> <a href="<?php the_permalink(); ?>">
        <?php if ( has_post_thumbnail() ) { $src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );   $thumbnailSrc = $src[0]; ?>
        <img src="<?php echo esc_url($thumbnailSrc); ?>" alt="" width="60" height="auto" >
        <?php } 
                    else { ?>
        <img src="<?php echo esc_url( get_template_directory_uri()); ?>/images/img_404.png" width="60"  />
        <?php } ?>
        </a> <?php the_excerpt(); ?> <a href="<?php the_permalink(); ?>"><span>
        <?php esc_attr_e('Read more','movers-packers'); ?>
        </span></a> </div>
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    </div>
    <!--end .widget-column-3-->
    
    <div class="clear"></div>
  </div>
  <!--end .container-->
  <div class="copyright-wrapper">
    <div class="container">
      <div class="copyright-txt"><?php esc_attr_e('&copy; 2016','movers-packers');?> <?php bloginfo('name'); ?>. <?php esc_attr_e('All Rights Reserved', 'movers-packers');?></div>
                
    </div>
    <div class="clear"></div>
  </div>
</div>
</div>
<!--end #pagefixed-->
<?php wp_footer(); ?>
</body></html>