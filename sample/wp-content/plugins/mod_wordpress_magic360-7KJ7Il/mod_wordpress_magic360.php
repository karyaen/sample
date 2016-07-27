<?php
/*

Copyright 2008 MagicToolbox (email : support@magictoolbox.com)
Plugin Name: Magic 360
Plugin URI: http://www.magictoolbox.com/magic360/?utm_source=TrialVersion&utm_medium=WordPress&utm_content=plugins-page-plugin-url-link&utm_campaign=Magic360
Description: Spin products round in 360 degrees to show them from every angle. Activate plugin then <a href="https://www.magictoolbox.com/magic360/modules/wordpress/#installation">Get Started</a>.
Version: 6.1.31
Author: Magic Toolbox
Author URI: http://www.magictoolbox.com/?utm_source=TrialVersion&utm_medium=WordPress&utm_content=plugins-page-author-url-link&utm_campaign=Magic360


*/

/*
    WARNING: DO NOT MODIFY THIS FILE!

    NOTE: If you want change Magic 360 settings
            please go to plugin page
            and click 'Magic 360 Configuration' link in top navigation sub-menu.
*/

if(!function_exists('magictoolbox_WordPress_Magic360_init')) {
    /* Include MagicToolbox plugins core funtions */
    require_once(dirname(__FILE__)."/magic360/plugin.php");
}

//MagicToolboxPluginInit_WordPress_Magic360 ();
register_activation_hook( __FILE__, 'WordPress_Magic360_activate');

register_deactivation_hook( __FILE__, 'WordPress_Magic360_deactivate');

register_uninstall_hook(__FILE__, 'WordPress_Magic360_uninstall');

magictoolbox_WordPress_Magic360_init();
?>