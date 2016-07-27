<?php
/**
 * SKT Movers Packers Theme Customizer
 *
 * @package SKT Movers Packers
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function movers_packers_customize_register( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->remove_control('header_textcolor');
	$wp_customize->remove_control('display_header_text');
	
	$wp_customize->add_setting('color_scheme',array(
			'default'	=> '#1874c1',
			'sanitize_callback'	=> 'sanitize_hex_color'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'color_scheme',array(
			'label' => __('Color Scheme','movers-packers'),			
			 'description' => __( 'More color options in PRO Version.', 'movers-packers' ),			
			'section' => 'colors',
			'settings' => 'color_scheme'
		))
	);
	
	$wp_customize->add_setting('color_option',array(
			'default'	=> '#ffb400',
			'sanitize_callback'	=> 'sanitize_hex_color'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'color_option',array(
			'label' => __('Other Color Option','movers-packers'),			
			 'description' => __( 'More color options in PRO Version.', 'movers-packers' ),			
			'section' => 'colors',
			'settings' => 'color_option'
		))
	);	
	
	// Slider Section		
	$wp_customize->add_section(
        'slider_section',
        array(
            'title' => __('Slider Settings', 'movers-packers'),
            'priority' => null,
            'description' => __( 'Featured Image Size Should be ( 1209x494 ) More slider settings available in PRO Version.', 'movers-packers' ),			
        )
    );
	
	
	$wp_customize->add_setting('page-setting7',array(
			'default' => '0',
			'capability' => 'edit_theme_options',
			'sanitize_callback'	=> 'movers_packers_sanitize_integer'
	));
	
	$wp_customize->add_control('page-setting7',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide one:','movers-packers'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting8',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'movers_packers_sanitize_integer'
	));
	
	$wp_customize->add_control('page-setting8',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide two:','movers-packers'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting9',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'movers_packers_sanitize_integer'
	));
	
	$wp_customize->add_control('page-setting9',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide three:','movers-packers'),
			'section'	=> 'slider_section'
	));	// Slider Section
	
	$wp_customize->add_setting('hide_slides',array(
			'sanitize_callback' => 'sanitize_text_field',
	));	 

	$wp_customize->add_control( 'hide_slides', array(
    	   'section'   => 'slider_section',
    	   'label'     => 'Hide Slider',
    	   'type'      => 'checkbox'
     ));	
	
	
	// Home Welcome Section 	
	$wp_customize->add_section('section_first',array(
		'title'	=> __('Homepage  Why Choose Us','movers-packers'),
		'description'	=> __('Select Page from the dropdown for first section','movers-packers'),
		'priority'	=> null
	));
	
	$wp_customize->add_setting('page-setting1',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'movers_packers_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-setting1',array('type' => 'dropdown-pages',
			'label' => __('','movers-packers'),
			'section' => 'section_first',
	));
	
	$wp_customize->add_setting('hide_choose',array(
			'sanitize_callback' => 'sanitize_text_field',
	));	 

	$wp_customize->add_control( 'hide_choose', array(
    	   'section'   => 'section_first',
    	   'label'     => 'Hide This Section',
    	   'type'      => 'checkbox'
     ));
	
	// Home Why Choose Us Section 	
	$wp_customize->add_section('section_second', array(
		'title'	=> __('Homepage  Three Box Services','movers-packers'),
		'description'	=> __('Select Pages from the dropdown for homepage three column box section','movers-packers'),
		'priority'	=> null
	));	
	
	
	$wp_customize->add_setting('page-column1',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'movers_packers_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-column1',array('type' => 'dropdown-pages',
			'label' => __('','movers-packers'),
			'section' => 'section_second',
	));	
	
	
	$wp_customize->add_setting('page-column2',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'movers_packers_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-column2',array('type' => 'dropdown-pages',
			'label' => __('','movers-packers'),
			'section' => 'section_second',
	));
	
	$wp_customize->add_setting('page-column3',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'movers_packers_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-column3',array('type' => 'dropdown-pages',
			'label' => __('','movers-packers'),
			'section' => 'section_second',
	));	//end three column part
	
	$wp_customize->add_setting('hide_column',array(
			'sanitize_callback' => 'sanitize_text_field',
	));	 

	$wp_customize->add_control( 'hide_column', array(
    	   'section'   => 'section_second',
    	   'label'     => 'Hide This Section',
    	   'type'      => 'checkbox'
     ));	
	
	
	$wp_customize->add_section('social_sec',array(
			'title'	=> __('Social Settings','movers-packers'),				
			'description' => __( 'More social icon available in PRO Version.', 'movers-packers' ),			
			'priority'		=> null
	));
	
	$wp_customize->add_setting('fb_link',array(
			'default'	=> '#facebook',
			'sanitize_callback'	=> 'esc_url_raw'	
	));
	
	$wp_customize->add_control('fb_link',array(
			'label'	=> __('Add facebook link here','movers-packers'),
			'section'	=> 'social_sec',
			'setting'	=> 'fb_link'
	));	
	$wp_customize->add_setting('twitt_link',array(
			'default'	=> '#twitter',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('twitt_link',array(
			'label'	=> __('Add twitter link here','movers-packers'),
			'section'	=> 'social_sec',
			'setting'	=> 'twitt_link'
	));
	$wp_customize->add_setting('gplus_link',array(
			'default'	=> '#gplus',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	$wp_customize->add_control('gplus_link',array(
			'label'	=> __('Add google plus link here','movers-packers'),
			'section'	=> 'social_sec',
			'setting'	=> 'gplus_link'
	));
	$wp_customize->add_setting('linked_link',array(
			'default'	=> '#linkedin',
			'sanitize_callback'	=> 'esc_url_raw'
	));
	$wp_customize->add_control('linked_link',array(
			'label'	=> __('Add linkedin link here','movers-packers'),
			'section'	=> 'social_sec',
			'setting'	=> 'linked_link'
	));
	
	$wp_customize->add_section('footer_area',array(
			'title'	=> __('Footer Area','movers-packers'),
			'priority'	=> null,
			'description'	=> __('Add footer copyright text','movers-packers')
	));
	
	$wp_customize->add_setting('about_title',array(
			'default'	=> __('About Us','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('about_title',array(
			'label'	=> __('Add title for our philosophy','movers-packers'),
			'section'	=> 'footer_area',
			'setting'	=> 'about_title'
	));
	
	$wp_customize->add_setting('about_description',array(
			'default'	=> __('Donec ut ex ac nulla pellentesque mollis in a enim. Praesent placerat sapien mauris, vitae sodales tellus venenatis ac. Suspendisse suscipit velit id ultricies auctor. Duis turpis arcu, aliquet sed sollicitudin sed, porta quis urna. Quisque velit nibh, egestas et erat a, vehicula interdum augue. <br /> </br > Donec ut ex ac nulla pellentesque mollis in a enim. Praesent placerat sapien mauris, vitae sodales tellus venenatis ac suspendisse suscipit velit. ','movers-packers'),
			'sanitize_callback'	=> 'wp_kses_post'
	));
	
	$wp_customize->add_control('about_description', array(	
			'label'	=> __('Add description for about us','movers-packers'),
			'section'	=> 'footer_area',
			'setting'	=> 'about_description',
			'type' => 'textarea',
	));
	
	$wp_customize->add_setting('services_title',array(
			'default'	=> __('Our Services','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('services_title',array(
			'label'	=> __('Add title for our services','movers-packers'),
			'section'	=> 'footer_area',
			'setting'	=> 'services_title'
	));	
	
	$wp_customize->add_setting('latestpost_title',array(
			'default'	=> __('Latest Posts','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('latestpost_title',array(
			'label'	=> __('Add title for office hours','movers-packers'),
			'section'	=> 'footer_area',
			'setting'	=> 'latestpost_title'
	));	
	
	
	$wp_customize->add_setting('contact_title',array(
			'default'	=> __('Address','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('contact_title',array(
			'label'	=> __('Add title for contact address','movers-packers'),
			'section'	=> 'footer_area',
			'setting'	=> 'contact_title'
	));
	
	
	$wp_customize->add_setting('contact_add',array(
			'default'	=> __('Mover & Packers 10 Down Street, Hunterdon, United States','movers-packers'),
			'sanitize_callback'	=> 'wp_kses_post'
	));
	
	$wp_customize->add_control('contact_add', array(
				'label'	=> __('Add contact address here','movers-packers'),
				'section'	=> 'footer_area',
				'setting'	=> 'contact_add',
				'type' => 'textarea',
	));
	
	$wp_customize->add_setting('footer_copyright',array(
			'default'	=> __('Copyright @ 2016 SKT Movers Packers. All Rights Reserved','movers-packers'),
			'sanitize_callback'	=> 'wp_kses_post'
	));
	
	$wp_customize->add_control('footer_copyright', array(
				'label'	=> __('Add copyright text for footer','movers-packers'),
				'section'	=> 'footer_area',
				'setting'	=> 'footer_copyright',
				'type' => 'textarea',
	));	
	
	$wp_customize->add_section('header_info',array(
			'title'	=> __('Header And Footer Info','movers-packers'),
			'description'	=> __('','movers-packers'),
			'priority'	=> null
	));
	
	$wp_customize->add_setting('opning_hours_title',array(
			'default'	=> __('Opening Hours','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('opning_hours_title',array(
			'label'	=> __('Add title for opening hour','movers-packers'),
			'section'	=> 'header_info',
			'setting'	=> 'opning_hours_title'
	));		
	
	$wp_customize->add_setting('opning_hours',array(
			'default'	=> __('Mon to Fri - 10.00 AM to 7.00 PM Sat - 10.00 AM to 4.00 PM','movers-packers'),
			'sanitize_callback'	=> 'wp_kses_post'
	));
	
	$wp_customize->add_control('opning_hours', array(
				'label'	=> __('Add opning hours for header','movers-packers'),
				'section'	=> 'header_info',
				'setting'	=> 'opning_hours',
				'type' => 'textarea',
	));
	
	$wp_customize->add_setting('callus_title',array(
			'default'	=> __('Call Us','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('callus_title',array(
			'label'	=> __('Add title for call us','movers-packers'),
			'section'	=> 'header_info',
			'setting'	=> 'callus_title'
	));	
	
	
	$wp_customize->add_setting('header_phone',array(
			'default'	=> __(' +10 2234567890 +10 1123456789','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('header_phone',array(
			'label'	=> __('Add contact phone number for header','movers-packers'),
			'section'	=> 'header_info',
			'setting'	=> 'header_phone'
	));	
	
	$wp_customize->add_setting('emailus_title',array(
			'default'	=> __('Email Us','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('emailus_title',array(
			'label'	=> __('Add title for email us','movers-packers'),
			'section'	=> 'header_info',
			'setting'	=> 'emailus_title'
	));	
	
	$wp_customize->add_setting('email_address',array(
			'default'	=> __('support@sitename.com info@sitename.com','movers-packers'),
			'sanitize_callback'	=> 'wp_kses_post'
	));
	
	$wp_customize->add_control('email_address', array(
				'label'	=> __('Add email address for header','movers-packers'),
				'section'	=> 'header_info',
				'setting'	=> 'email_address',
				'type' => 'textarea',
	));
	
	$wp_customize->add_setting('getquote_title',array(
			'default'	=> __('Get A Quote','movers-packers'),
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('getquote_title',array(
			'label'	=> __('Add title for get a quote','movers-packers'),
			'section'	=> 'header_info',
			'setting'	=> 'getquote_title'
	));	
	
	$wp_customize->add_setting('getquote_link',array(
			'default'	=> __('#','movers-packers'),
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('getquote_link',array(
			'label'	=> __('Add link for get a quote','movers-packers'),
			'section'	=> 'header_info',
			'setting'	=> 'getquote_link'
	));	
}
add_action( 'customize_register', 'movers_packers_customize_register' );

//Integer
function movers_packers_sanitize_integer( $input ) {
    if( is_numeric( $input ) ) {
        return intval( $input );
    }
}

function movers_packers_custom_css_styles() {
        wp_enqueue_style( 'movers-packers-custom-styles', get_template_directory_uri() . '/css/custom-style.css' ); 
        $color = esc_attr(get_theme_mod('color_scheme','#1874c1')); //E.g. #1874c1
		$othercolor = esc_attr(get_theme_mod('color_option','#ffb400')); //E.g. #ffb400
        $custom_css = "a, .blog_lists h2 a:hover, .logo h1 span{color: {$color};}.menubar, .sub-menu, .pagination ul li a:hover, #commentform input#submit:hover, .nivo-controlNav a.active, h3.widget-title, .wpcf7 input[type=submit], #pagearea .threebox:hover, .current, input.search-submit, .post-password-form input[type=submit], .wpcf7-form input[type=submit]{background-color: {$color} !important;}.headerxxx{border-color: {$color};}.sitenav ul li a:hover, .sitenav ul li.current_page_item a, #sidebar ul li a:hover, .cols-3 ul li a:hover, .cols-3 ul li.current_page_item a, div.recent-post a:hover, .design-by a:hover, .container a:hover, .topleft ul li a:hover{color: {$othercolor};}.getaquote ul li a{background-color: {$othercolor};}";
        wp_add_inline_style( 'movers-packers-custom-styles', $custom_css ); // Custom CSS after style sheet
}
add_action( 'wp_enqueue_scripts', 'movers_packers_custom_css_styles' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function movers_packers_customize_preview_js() {
	wp_enqueue_script( 'movers_packers_customizer', get_template_directory_uri() . '/js/customize-preview.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'movers_packers_customize_preview_js' );


function movers_packers_custom_customize_enqueue() {
	wp_enqueue_script( 'movers-packers-custom-customize', get_template_directory_uri() . '/js/custom.customize.js', array( 'jquery', 'customize-controls' ), false, true );
}
add_action( 'customize_controls_enqueue_scripts', 'movers_packers_custom_customize_enqueue' );