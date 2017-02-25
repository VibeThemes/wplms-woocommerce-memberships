<?php
/*
Plugin Name: WPLMS woocommerce membership addon
Plugin URI: http://www.Vibethemes.com
Description: woocommerce memberships addon plugin
Version: 1.0
Author: VibeThemes,alexhal
Author URI: http://www.vibethemes.com
License: GPL2
*/
/*
Copyright 2017  VibeThemes  (email : vibethemes@gmail.com)

WPLMS woocommerce membership addon program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

wplms_customizer program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with wplms_customizer program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( in_array( 'vibe-customtypes/vibe-customtypes.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( 'vibe-course-module/loader.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( 'woocommerce-memberships/woocommerce-memberships.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {
	include_once 'classes/wplms-wm.class.php';
    if(class_exists('Wplms_Wm_Class')){   
        // Installation and uninstallation hooks
        $wplms_wm = Wplms_Wm_Class::init();
        register_activation_hook(__FILE__, array($wplms_wm, 'activate'));
        register_deactivation_hook(__FILE__, array($wplms_wm, 'deactivate'));

    }
}
add_action('plugins_loaded','wplms_wm_translations');
function wplms_wm_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-wcm');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-wcm', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-wcm', $mofile_global );
    } else {
        load_textdomain( 'wplms-wcm', $mofile_local );
    }  
}
//for future 
//add_action('wp_head','wplms_wm_scripts');
function wplms_wm_scripts(){
    wp_enqueue_style( 'wplms-wm-css', plugins_url( 'css/custom.css' , __FILE__ ));
    wp_enqueue_script( 'wplms-wm-js', plugins_url( 'js/custom.js' , __FILE__ ));
}

