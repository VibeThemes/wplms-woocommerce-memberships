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
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

wplms_customizer program is free software; you can redistribute it and/or modify
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
/*add_filter('bp_activity_time_since', 'bp_activity_time_since_newformat', 10, 2);

function bp_activity_time_since_newformat( $time_since, &$actvitiy ) {

 

// you can change the date format to "Y-m-d H:i:s"

$time_since = '<span class="time-since">' .  date_i18n("d-m-Y H:i:s A", strtotime( $actvitiy->date_recorded ) ) . '</span>';

return $time_since;

}*/

include_once 'classes/wplms-wm.class.php';



if(class_exists('Wplms_Wm_Class'))
{	
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('Wplms_Wm_Class', 'activate'));
    register_deactivation_hook(__FILE__, array('Wplms_Wm_Class', 'deactivate'));

    // instantiate the plugin class
    $wplms_customizer = new Wplms_Wm_Class();
}

function wplms_wplms_wm_scripts(){
    wp_enqueue_style( 'wplms-customizer-css', plugins_url( 'css/custom.css' , __FILE__ ));
    wp_enqueue_script( 'wplms-customizer-js', plugins_url( 'js/custom.js' , __FILE__ ));
}

add_action('wp_head','wplms_wplms_wm_scripts');


