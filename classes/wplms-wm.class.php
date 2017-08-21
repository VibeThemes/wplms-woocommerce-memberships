<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if(!class_exists('Wplms_Wm_Class'))
{   

    class Wplms_Wm_Class // We'll use this just to avoid function name conflicts 
    {
        public static $instance;
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new Wplms_Wm_Class();
            return self::$instance;
        }   
        public function __construct(){ 
            //settings added and saved 
            add_action('wplms_course_metabox',array($this,'add_wcm_membership_settings_backend'));

            //Display pricing
            add_filter('wplms_course_credits_array',array($this,'course_wcm_link'),999,2);
            add_filter('wplms_course_product_id',array($this,'course_wcm_link_pid'),10,2);

            //Save primary product id in membership for displaying price in course : No other use
            add_action('wc_membership_plan_options_membership_plan_data_general',array($this,'add_course_options'));
            add_action('save_post_wc_membership_plan',array($this, 'save_wcm_plan'));

            //Select membership levle in pricing front end
            add_filter('wplms_course_creation_tabs',array($this,'add_wcm_membership_settings_frontend'));

            
            //Main Functioning
            add_action('wc_memberships_user_membership_status_changed',array($this,'check_course_accesses'),10,3);
            add_action('wc_memberships_user_membership_saved',array($this,'wplms_course_provide_access'),99999,2);

            
            //FallBack to award Courses when user has membership
            add_action('wplms_the_course_button',array($this,'provide_access_course_button'),10,2);

        } // END public function __construct

        function activate(){
            // ADD Custom Code which you want to run when the plugin is activated
        }
        function deactivate(){
            // ADD Custom Code which you want to run when the plugin is de-activated    
        }

        function provide_access_course_button($course_id,$user_id){
            if(!is_user_logged_in())
                return;
            
            /*if(wplms_user_course_active_check($user_id ,$course_id ))
                return;
                */
            $membership_plans = get_post_meta($course_id,'vibe_wcm_plans',true);

            if(!empty($membership_plans) && is_array($membership_plans)){
                $flag = false;
                foreach($membership_plans as $membership_plan){
                    //Check if user is a member of the membership and it is Active
                    if(wc_memberships_is_user_member($user_id, $membership_plan ) && wc_memberships_is_user_active_member( $user_id, $membership_plan )){
                        $flag=true;
                        break;
                    }else{
                        $flag = false;
                    }
                }

                if($flag){
                    if(function_exists('bp_course_add_user_to_course') && function_exists('wplms_user_course_active_check')){

                        if(!wplms_user_course_active_check($user_id ,$course_id )){

                            $access_length = get_post_meta( $membership_plan, '_access_length', true );
                           $access_length = explode(' ',$access_length);
                           $access_duration = $access_length[0];
                           $access_duration_parameter_string = (isset($access_length[1])?$access_length[1]:$access_length[0]);
                           $access_duration_parameter = $this->access_length_into_sceonds($access_duration_parameter_string);
                           $duration = $access_duration_parameter*$access_duration;
                            bp_course_add_user_to_course($user_id,$course_id ,$duration);
                        }
                    }
                    
                }
            }
        }
        
        function check_course_accesses($user_membership, $old_status, $new_status){
    
            if(in_array($new_status,array('expired','cancelled'))){
                $membership_id = $user_membership->get_plan_id();
                $user_id = $user_membership->user_id;
                //GET all membership levels of user
                $user_memberships = wc_memberships_get_user_memberships( $user_id,array('status'=>'active') );
                $user_membership_ids = array();
                if(!empty($user_memberships)){
                    foreach($user_memberships as $um){
                        if($um->get_plan_id() !=  $membership_id)
                        $user_membership_ids[]=$um->get_plan_id();
                    }
                    
                }

                global $wpdb;
                $courses =$wpdb->get_results($wpdb->prepare("SELECT post_id as course_id  FROM {$wpdb->postmeta} WHERE meta_key ='%s' AND meta_value LIKE '%s'",'vibe_wcm_plans','%"'.$membership_id.'"%'));

                if(!empty($courses)){
                    foreach($courses as $course){
                        $metas = get_post_meta($course->course_id,'vibe_wcm_plans',true);
                        if(!empty($metas)){
                            $result = array_diff($metas, $user_membership_ids);
                            if(count($metas) == count($result)){
                                //unsubscribe from course
                                bp_course_remove_user_from_course($user_id,$course->course_id);
                            }
                        }
                    }
                }
            }
        }

        function wplms_course_provide_access( $membership_plan, $args ){
            global $wpdb;
            $membership_courses=$wpdb->get_results($wpdb->prepare("SELECT post_id as course_id  FROM {$wpdb->postmeta} WHERE meta_key ='%s' AND meta_value LIKE '%s'",'vibe_wcm_plans','%"'.$membership_plan->id.'"%'));
           
            if(empty( $membership_courses) || in_array(get_post_status($args['user_membership_id']),array('wcm-cancelled','wcm-expired','wcm-paused','wcm-pending','wcm-complimentary')))
                return;
            if(!wc_memberships_is_user_active_member($args['user_id'], $membership_plan->id ))
                return;
            $access_length = get_post_meta( $membership_plan->id, '_access_length', true );
            $access_length = explode(' ',$access_length);
            $access_duration = $access_length[0];
            $access_duration_parameter_string = (isset($access_length[1])?$access_length[1]:$access_length[0]);
            $access_duration_parameter = $this->access_length_into_sceonds($access_duration_parameter_string);
           $duration = $access_duration_parameter*$access_duration;
            if(!empty( $membership_courses) && is_array($membership_courses) && wc_memberships_is_user_active_member($args['user_id'], $membership_plan->id )){
                foreach($membership_courses as $course){
                    if(function_exists('bp_course_add_user_to_course') && function_exists('wplms_user_course_active_check')){
                        if(!wplms_user_course_active_check($args['user_id'] ,$course->course_id )){
                            bp_course_add_user_to_course($args['user_id'],$course->course_id ,$duration);
                        }
                    }
                }
           }
        }

        function save_wcm_plan($post_id){

            if(isset($_POST) && isset($_POST['wplms_course_primary_product'])){
                $product_ids = is_array( $_POST['wplms_course_primary_product'] ) ? $_POST['wplms_course_primary_product'] : ( $_POST['wplms_course_primary_product'] ? explode( ',', $_POST['wplms_course_primary_product'] ) : array() );

                // sanitize
                $product_ids = array_map( 'absint', $product_ids );

                update_post_meta( $post_id, 'wplms_course_primary_product', $product_ids );
            }
        }


        function add_course_options(){
            global $post;
            ?>
            <p class="form-field">
                <label for="wplms_course_primary_product"><?php esc_html_e( 'Course primary product:', 'wplms-wcm' ); ?></label>

                <select type="hidden"
                       name="wplms_course_primary_product"
                       id="wplms_course_primary_product"
                       class="js-ajax-select-product"
                       style="width: 50%;"
                       data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wplms-wcm' ); ?>"
                       data-multiple="false"
                       data-selected="<?php
                            $product_id = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, 'wplms_course_primary_product', true ) ) );
                            $json_id    = '';
                                $product = array();
                                if(!empty($product_id[0])){
                                    $product = wc_get_product( $product_id[0] );
                                }
                                if ( is_object( $product ) ) {
                                    $json_id = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
                                }
                            echo $json_id  ;
                             ?>" 
                        >
                       <?php echo (!empty($product_id[0]))?'<option selected="selected" value="'.esc_attr( $product_id[0] ).'">'. $json_id .'</option>':''; ?>">
                       </select>
                       <script>
                       jQuery(".js-ajax-select-product").select2({
                            placeholder: "Search for a product",
                            minimumInputLength: 1,
                           
                            allowClear: true,
                            
                            ajax: {
                                url: wc_memberships_admin.ajax_url,
                                dataType: 'json',
                                data: function (term, page) {
                                    return {
                                    term: term.term, // search term
                                    action:"woocommerce_json_search_products_and_variations",
                                    security:wc_memberships_admin.search_products_nonce,
                                    screen:"wc_membership_plan"
                                };},
                              
                                processResults: function(data, page) {
                                    var d;
                                    return d = [], data && jQuery.each(data, function(a, b) {
                                        return d.push({
                                            id: a,
                                            text: b
                                        })
                                    }), {
                                        results: d
                                    }
                                },
                                cache: true
                            },
                        });
                       </script>
            </p>
            <?php
        }


        function course_wcm_link($credits,$course_id){
            $memberships = get_post_meta($course_id,'vibe_wcm_plans',true);
            if(!empty($memberships) && is_array($memberships)){
                 $link ='';

                foreach($memberships as $membership){
                    $product_id  = get_post_meta($membership,'wplms_course_primary_product',true);
                    if(!empty($product_id)){
                       if(function_exists('icl_object_id'))
                            $product_id = icl_object_id($product_id[0], 'page', true);
                        $link = get_permalink($product_id[0]);
                        
                        $credits[$link] = '<strong>'.get_the_title($membership).'</strong>';  
                    }
                }
            }
            return $credits;
        }

        function add_wcm_membership_settings_frontend($settings){
            if(function_exists('wc_memberships_get_membership_plans')){
                $wcm_plans = wc_memberships_get_membership_plans();
                $palns = array();
                foreach($wcm_plans as $wcm_plan){
                    $plans[] = array('value'=>$wcm_plan->id,'label' => $wcm_plan->name);
                }
                $fields = $settings['course_pricing']['fields'];
                $arr=array(array(
                            'label' => __('Woocommerce memberships','wplms-wcm'), // <label>
                            'desc'  => __('Select woocommerce membership levels here','wplms-wcm'), 
                            'text'=>__('Woocommerce memberships','wplms-wcm' ),// description
                            'id'  => 'vibe_wcm_plans', // field id and name
                            'type'  => 'multiselect', // type of field
                            'options'   => $plans
                            ));

                array_splice($fields, (count($fields)-1), 0,$arr );
                $settings['course_pricing']['fields'] = $fields;  
            }
            return $settings;
        }

        function course_wcm_link_pid($pid,$course_id){
           if(!is_numeric($pid)){
                $memberships = get_post_meta($course_id,'vibe_wcm_plans',true);
                if(!empty($memberships) && is_array($memberships)){
                    foreach($memberships as $membership){
                        $product_id  = get_post_meta($membership,'wplms_course_primary_product',true);
                        if(!empty($product_id)){
                           if(function_exists('icl_object_id'))
                                $product_id = icl_object_id($product_id[0], 'page', true);
                            $pid = get_permalink($product_id[0]);
                            break;
                        }
                    } 
                }
           }
           return $pid;
        }

        function add_wcm_membership_settings_backend($settings){
            if(function_exists('wc_memberships_get_membership_plans'))
            $wcm_plans = wc_memberships_get_membership_plans();
            $palns = array();
            foreach($wcm_plans as $wcm_plan){
                $plans[] = array('value'=>$wcm_plan->id,'label' => $wcm_plan->name);
            }
            $settings['vibe_wcm_plans'] = array(
                'label' => __('Woocommerce memberships','wplms-wcm'), // <label>
                'desc'  => __('Select woocommerce membership levels here','wplms-wcm'), // description
                'id'    => 'vibe_wcm_plans', // field id and name
                'type'  => 'multiselect', // type of field
                'options' => $plans,
            );
            return $settings;
        }

        function access_length_into_sceonds($access_duration_parameter_string){
            switch($access_duration_parameter_string){
                case 'days':
                    $access_duration_parameter = 86400;
                break;
                case 'years':
                    $access_duration_parameter = (365*86400);
                break;
                case 'months':
                    $access_duration_parameter =(30*86400);
                break;
                case 'weeks':
                    $access_duration_parameter = (7*86400);
                break;
                default :
                    $access_duration_parameter = 86400;
                break;
           }
           return $access_duration_parameter;
        }

        

    } // END class Wplms_Wm_Class
} // END if(!class_exists('Wplms_Wm_Class'))

