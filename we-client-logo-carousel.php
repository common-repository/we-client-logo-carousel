<?php
/**
 * Plugin Name: We - Client Logo Carousel
 * Plugin URI: 
 * Description: Display client's logo, partner's logo, sponsor's logos or images using We-client-logo-carousel plugin in beautiful and responsive manner.
 * Version: 1.4
 * Author: wordpresteem
 * Author URI: http://www.wordpresteem.com/
 * Author Email: wordpresteem@gmail.com
 * Tested up to: 6.2.2
 * Requires PHP: 5.6.3
 * Text Domain: we-client-logo-carousel
 * License: GPL v2 or later
 */
 
//We Client Logos post type to add images	
if ( ! defined( 'ABSPATH' ) ) exit;
add_action('init', 'we_client_logo_register');
function we_client_logo_register() {

	$labels = array(
		'name' => _x('WE Client Logo', 'post type general name'),
		'singular_name' => _x('Client Logo', 'post type singular name'),
		'add_new' => _x('Add New Client Logo', 'WE Client Logo'),
		'add_new_item' => __('Add New Client Logo'),
		'edit_item' => __('Edit Client Logo'),
		'new_item' => __('New Client Logo'),
		'view_item' => __('View Client Logo'),
		'search_items' => __('Search Client Logo'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => 'dashicons-images-alt2',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'thumbnail' )
	  ); 

	register_post_type( 'we-client-logo' , $args );
} 

//add logo carousel category
function weclient_carousel_taxonomy() {
	register_taxonomy(
		'carousel_cat',  
		'we-client-logo',                  
		array(
			'hierarchical'          => true,
			'label'                         => 'Carousel Category',  
			'query_var'             => true,
			'show_admin_column'			=> true,
			'rewrite'                       => array(
				'slug'                  => 'carousel-category', 
				'with_front'    => true 
				)
			)
	);
}
add_action( 'init', 'weclient_carousel_taxonomy'); 


// Add the posts and pages columns filter.
add_filter('manage_posts_columns', 'weclient_add_post_thumbnail_column', 2);
	
// Add the column
function weclient_add_post_thumbnail_column($cols){
  	
	global $post;
	$pst_type=$post->post_type;
		if( $pst_type == 'we-client-logo'){ 
		$cols['weclient_logo_thumb'] = __('Logo Image');
		$cols['weclient_client_url'] = __('url');
		}
	return $cols;
}

// Hook into the posts an pages column managing.
add_action('manage_posts_custom_column', 'weclient_display_post_thumbnail_column', 5, 2);
	
// Grab featured-thumbnail size post thumbnail and display it.
function weclient_display_post_thumbnail_column($col, $id){
  switch($col){
	case 'weclient_logo_thumb':
	  if( function_exists('the_post_thumbnail') ){
	
		$post_thumbnail_id = get_post_thumbnail_id($id);
		$post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');
		$post_thumbnail_img= $post_thumbnail_img[0];
		if($post_thumbnail_img !='')
		  echo '<img width="120" height="120" src="' . $post_thumbnail_img . '" />';
		else {?>
	 <img width="120" height="120" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/placeholder.png'; ?>"> 
		<?php } 
		
		}
	  
	case 'weclient_client_url':
		if($col == 'weclient_client_url'){
			echo get_post_meta( $id, 'weclient_clientlogo_meta_url', true );;
		} 		   
	  break;
 
  }
}

// We Client Logo Meta Box
function weclient_clientlogo_add_meta_box(){
// add meta Box
 remove_meta_box( 'postimagediv', 'we-client-logo', 'side' );
 add_meta_box('postimagediv', __('WE Client Logo'), 'post_thumbnail_meta_box', 'we-client-logo', 'normal', 'high');
 add_meta_box('weclient_clientlogo_meta_id', __('site url'), 'weclient_meta_callback', 'we-client-logo', 'normal', 'high');
}
add_action('add_meta_boxes' , 'weclient_clientlogo_add_meta_box');

// We Client Logo Meta Box Call Back Funtion
function weclient_meta_callback($post){

    wp_nonce_field( basename( __FILE__ ), 'aft_nonce' );
    $aft_stored_meta = get_post_meta( $post->ID );
    ?>

    <p>
        <label for="weclient_clientlogo_meta_url" class="weclient_clientlogo_meta_url"><?php _e( 'site url', '' )?></label>
        <input class="widefat" type="text" name="weclient_clientlogo_meta_url" id="weclient_clientlogo_meta_url" value="<?php if ( isset ( $aft_stored_meta['weclient_clientlogo_meta_url'] ) ) echo $aft_stored_meta['weclient_clientlogo_meta_url'][0]; ?>" /> <br>
		<em>(eg: http://www.google.com)</em>
    </p>

<?php

}

//We Client Logo Save Meta Box 
function weclient_clientlogo_meta_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'weclient_nonce' ] ) && wp_verify_nonce( $_POST[ 'weclient_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'weclient_clientlogo_meta_url' ] ) ) {
        update_post_meta( $post_id, 'weclient_clientlogo_meta_url', sanitize_text_field( $_POST[ 'weclient_clientlogo_meta_url' ] ) );
    }

}
add_action( 'save_post', 'weclient_clientlogo_meta_save' );



// Initialize the plugin options on first run
function weclient_initialize(){
	$options_not_set = get_option('weclient_post_type_settings');
	if( $options_not_set ) return;
	
	$we_slider_logo_settings = array(
	'items' => 3,
	'slide_speed' => 500,
	'auto_play' => true,
	'stop_on_hover' => true, 
	'navigation' => false, 
	'pagination' => true,
	);
	update_option('weclient_slider_settings', $we_slider_logo_settings);
}
register_activation_hook(__FILE__, 'weclient_initialize');

// Delete the plugin options on uninstall
function weclient_remove_options(){
	delete_option('weclient_slider_settings');
}
register_uninstall_hook(__FILE__, 'weclient_remove_options');

// Setup the shortcode
function weclient_logo_slider_callback( $atts ) {
	
	//include css and js start
	wp_enqueue_script( 'we-client-logo-slick', plugins_url('js/slick.min.js', __FILE__ ), array('jquery'),'1.1', true);
	wp_enqueue_script( 'we-client-logo-slick-script', plugins_url('js/slick-script.js', __FILE__ ), array('jquery'),'1.1', true);
	wp_enqueue_style( 'we-client-logo-slick', plugins_url('css/jquerysctipttop.css', __FILE__), array(), '1.1', 'all' );
	
	$we_slider_logo_settings = get_option('weclient_slider_settings');
	
	wp_localize_script( 'we-client-logo-slick', 'weclient', $we_slider_logo_settings);
		ob_start();
	
	$order_by='date';//default value
	if($we_slider_logo_settings['slide_orderby']!=''){
     $order_by = $we_slider_logo_settings['slide_orderby']; 
	}
	$order= 'DESC';
	if($order_by == 'title'){	
		$order= 'ASC';
	}
	
	$category='default'; // default category
	$add_id='default';
	if( isset($atts['category']) and $atts['category'] !=''){
		$add_id=$atts['category']; //additional id 
	}
	
    extract( shortcode_atts( array (
        'type' => 'we-client-logo',
        'category' => '',
        'order' => $order,
        'orderby' => $order_by,
        'posts' => -1,
    
    ), $atts ) );
	
    $options = array(
        'post_type' => $type,
        'order' => $order,
        'orderby' => $orderby,
        'posts_per_page' => $posts,
		'carousel_cat' => $category
  		
    );
    $query = new WP_Query( $options );?>
    <?php if ( $query->have_posts() ) { ?>
	<div class="container">
   <section class="customer-logos slider"><?php while ( $query->have_posts() ) : $query->the_post(); ?>	
	   <div class="slide"><?php if(get_post_meta(get_the_ID(),'weclient_clientlogo_meta_url',true) != ''){?><a target="_blank" href="<?php echo get_post_meta(get_the_ID(),'weclient_clientlogo_meta_url',true);?>"><?php the_post_thumbnail('full'); ?></a>
    <?php }else{ the_post_thumbnail('full'); }?></div>
	<?php endwhile;
      wp_reset_postdata(); ?>
	  </section>
	  </div>
	<?php 	
	}else{		
		echo "No Image is added.";
	}	
return ob_get_clean();
}
add_shortcode( 'weclient_logo_slider', 'weclient_logo_slider_callback' );

//include setting page
include('includes/we-client-logo-carousel-settings.php');
?>