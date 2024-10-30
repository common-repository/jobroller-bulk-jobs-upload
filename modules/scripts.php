<?php 

add_action('wp_print_scripts', 'ji_add_script_fn');
function ji_add_script_fn(){
wp_enqueue_style('at_buttons_js', plugins_url('/css/buttons.css', __FILE__ ) ) ;
   if(is_admin()){
	wp_enqueue_script('at_admin_js', plugins_url('/js/admin.js', __FILE__ ), array('jquery'), '1.0' ) ;
	wp_enqueue_style('ji_admin_css', plugins_url('/css/admin.css', __FILE__ ) ) ;	
  }else{

	wp_enqueue_style('at_qtip_css', plugins_url('/css/qtip.css', __FILE__ ) ) ;
  wp_enqueue_script('at_qtip_js', plugins_url('/js/qtip.js', __FILE__ ), array('jquery'), '1.0' ) ;
	wp_enqueue_script('at_front_js', plugins_url('/js/front.js', __FILE__ ), array('jquery'), '1.0' ) ;
	wp_enqueue_style('at_front_css', plugins_url('/css/front.css', __FILE__ ) ) ;
	
  }
}
?>