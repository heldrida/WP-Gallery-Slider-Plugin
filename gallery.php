<?php
/*
Plugin Name: Gallery
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: The Plugin's Version Number, e.g.: 1.0
Author: Name Of The Plugin Author
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

define('WEBSITE_URL', site_url().'/');
define('WEBSITE_ADMIN_URL', admin_url());

define('GAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('GAL_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

register_activation_hook( __FILE__, 'gal_installation_dependencies' );
register_deactivation_hook( __FILE__, 'gal_deactivation_dependencies' );

add_action('admin_init', 'gal_administration_scripts_styles_loader');
add_action('admin_init', 'gal_cpt_meta_field_setter');
add_action( 'init', 'gal_constructor' );
add_action( 'init', 'gal_ajax_request_processor');
add_action( 'init', 'gal_request_router' );

add_action("manage_cpt-gallery_posts_custom_column", "gal_cpt_column_action");

add_filter("manage_edit-cpt-gallery_columns", "gal_cpt_gallery_display_columns");

add_action('do_meta_boxes', 'cpt_image_box');
//add_action( 'admin_init', 'cpt_image_box', 1);

if(! function_exists('cpt_image_box') ){
	function cpt_image_box() {
		remove_meta_box( 'postimagediv', 'cpt-gallery', 'side' );
		add_meta_box('cpt-galler-meta-box', __('Gallery image'), 'post_thumbnail_meta_box', 'cpt-gallery', 'normal', 'high');		
	};
};
if(! function_exists('gal_request_router')){
	function gal_request_router(){
		
		if( ! isset($_GET['action']) ){
			return;
		};

		switch($_GET['action']){
			
			case 'delete_gallery_item':

				wp_delete_post( $_GET['post'] );
				wp_redirect( WEBSITE_ADMIN_URL.'edit.php?post_type=cpt-gallery' );
				exit;

			break;

			case 're_order':
				
				gal_re_order_slide();

			break;

		};

	}
};
if(! function_exists('gal_ajax_request_processor') ){
	function gal_ajax_request_processor(){

		if( ! isset($_GET['action']) || $_GET['action'] != 'gallery-ajax' ){
			return;
		};
		
		global $wpdb;
		
		$results =  $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_title LIKE post_type = 'post' AND post_status = 'publish' " ) );
		
		
		foreach ($results as $key=>$v) {

				if (strpos(strtolower($v), $_GET[q]) !== false) {
					echo "$v\n";
				}

		}
		
		exit();
	}
};
if(! function_exists('gal_installation_dependencies')){
	/*
	 *	Actions on plugin activation goes here
	 */
	function gal_installation_dependencies(){
		
		return;
		
	};
};
if(! function_exists('gal_deactivation_dependencies')){
	/*
	 *	Action on plugin deactivation goes here
	 */
	function gal_deactivation_dependencies(){
		
		return;
		
	};
};
if(! function_exists('gal_constructor')){		
	/*
	 * Constructor
	 */
	function gal_constructor(){		
		/**
		 *	Grids-event custom post type
		 */
		register_post_type( 'cpt-gallery',
			array(
				'labels' => array(
					'menu_name' => 'Slides Gallery',
					'name' => __( 'Galleries' ),
					'singular_name' => __( 'Gallery' )
				),
			'public' => true,
			'has_archive' => true,
			'supports' => array('title', 'thumbnail')
			)
		);		
		register_taxonomy('Categories', array('cpt-gallery'), array('hierarchical' => true, 'label' => 'Categories', 'singular_label' => 'Category', 'rewrite' => true));	
		
	};
};
if(! function_exists('gal_administration_scripts_styles_loader')){
	/*
	 *	Scripts that are loaded in administration painel header tags, go here.
	 */
	function gal_administration_scripts_styles_loader(){

		wp_enqueue_script('suggest');
		wp_register_script( 'js-gallery', GAL_PLUGIN_URL.'/public/js/gallery.js');
		wp_enqueue_script( 'js-gallery');
		
		wp_register_style('GalleryStyleSheet', GAL_PLUGIN_URL.'/public/css/gallery.css');
		wp_enqueue_style( 'GalleryStyleSheet');

		wp_enqueue_script('jquery-ui-sortable');


	};
};
if(! function_exists('gal_cpt_gallery_display_columns')){
	/*
	 *	Set the columns in table
	 */
	function gal_cpt_gallery_display_columns($cols){
	
		unset($cols);
		
		return array(
					'order' => __('Order'),
					/* 'ID' => __('Nrº'), */
					'thumb' => __('Preview'),
					'links_to' => __('Linking to Post'),
					'post_author' => __('Author'),
					'options' => __('Options'),
					'delete' => __('Delete')
				);
	
	};
};
if(! function_exists('gal_cpt_column_action')){
	/*
	 * Adds actions to each column
	 */
	function gal_cpt_column_action($column){
		
		global $post;
		
		$usr = get_userdata($post->post_author);
		$meta = get_post_meta($post->ID,'cpt_meta',TRUE);
		
		
		switch ( $column ) {
		
				case 'ID':
					
					echo $post->ID;
				
				break;
				
				case 'thumb':
					the_post_thumbnail('thumbnail');
					//echo '<img src="http://angelleadesigns.com/wp-content/uploads/2011/09/lion-thumbnail.jpg" alt="" style="width:50px; height:50px;" />';
					
				break;
				
				case 'links_to':
					
					echo '<a href="'.(WEBSITE_URL.'?post_type=cpt-gallery&p='.$post->ID).'" title="" >'.($post->post_title).'</a>';
				
				break;
				
				case 'options':
				
					echo '<a href="'.(WEBSITE_ADMIN_URL.'post.php?post='.$post->ID.'&action=edit').'" >'.( __('Edit') ).'</a>';
				
				break;
				
				case 'post_author':
					
					echo $usr->user_login;
				
				break;
				
				case 'delete':
					
					//http://localhost/wordpress/wp-admin/post.php?post=633&action=trash&_wpnonce=095c4c60e2
					echo '<a href="'.( WEBSITE_ADMIN_URL.'post.php?post='.$post->ID.'&action=delete_gallery_item' ).'">'.( __('Delete') ).'</a>';
					
				break;
				
				case 'order':
					
					gal_order_handlers();

				break;

				default:
				
					echo 'Under development';
		};
	};
};
if(! function_exists('gal_cpt_meta_field_setter')){
	/*
	 *	Set meta fields on Give Costum Post Type
	 */
	function gal_cpt_meta_field_setter(){
		
		$type = 'cpt-gallery';
		
	    add_meta_box('cpt-gallery-config-parameters', 'Gallery options', 'gal_meta_fields_nodes', $type, 'normal', 'high');
			
	    add_action('save_post','gal_meta_save');
	
	};
};
if(! function_exists('gal_meta_fields_nodes')){
	/*
	 * Html nodes to use for Custom Post Type
	 */
	function gal_meta_fields_nodes(){
	  
		global $post;
	 
		// Use underscore to prevent showing up in Custom Field Section
	    $meta = get_post_meta($post->ID,'_cpt_meta_gallery', TRUE);
		
		echo '<p><strong>'.(__('Post title')).':</strong></p>';
		
		echo '<input id="post-title-type" type="text" name="post-title-type" value="'.(isset($meta['link-to-post-title']) ? $meta['link-to-post-title']  : null).'">';
		echo '<input id="link-to-post-title" type="hidden" name="_cpt_meta_gallery[link-to-post-title]" value="">';
		
		echo '<p><strong>'.(__('Order')).':</strong></p>';
		echo '<input type="text" value="'.(isset($meta['cpt-order']) ? $meta['cpt-order']  : null).'" name="cpt-order" />';
	};	
};
if(! function_exists('gal_meta_save')){
	/*
	* Meta data saver
	*/
	function gal_meta_save(){
		
		if( ! isset( $_POST['post-title-type'] ) ){
			return;
		}
		
		global $post;
		
		$data = array(
			'link-to-post-title' => $_POST['post-title-type'],
			'cpt-order' => $_POST['cpt-order']
		);
		update_post_meta($post->ID,'_cpt_meta_gallery', $data );
		
		$d = get_post_by_title( $data['link-to-post-title'] );
		
		$new_data = array(
			'link-to-post-title' => $data['link-to-post-title'],
			'post_id' => $d->ID,
			'cpt-order' => $data['cpt-order']
		);
		update_post_meta($post->ID,'_cpt_meta_gallery',$new_data);
		
	};
};
if(! function_exists('get_post_by_title') ){
	function get_post_by_title($page_title, $output = OBJECT) {
		global $wpdb;
			$post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s ", $page_title ));
			if ( $post )
				return get_post($post, $output);

		return null;
	};
};
//http://wordpress.org/support/topic/how-to-move-featured-image-box-from-side-to-main-column
if(! function_exists('gal_cmp')){
	function gal_cmp($a, $b){

	    if ($a['meta']['cpt-order'] == $b['meta']['cpt-order']) return 0;
	    return ($a['meta']['cpt-order'] < $b['meta']['cpt-order']) ? -1 : 1;
	}	
};
if(! function_exists('getSlideGalleryData')){
	function getSlideGalleryData(){

		$args = array(
			'post_type'       => 'cpt-gallery',
			'post_status'     => 'publish'
		); 
		
		$posts = get_posts( $args );

		foreach ($posts as $k) {
			$meta = get_post_meta( $k->ID, '_cpt_meta_gallery', TRUE );
			$permalink = get_permalink( $m['post_id'] );									
			$image = wp_get_attachment_url( get_post_thumbnail_id( $k->ID ) );
			
			$arr[] = array(
				'post_title' =>  $k->post_title,
				'guid' => $k->guid,
				'meta' => $meta,
				'permalink' => $permalink,
				'image' => $image
			);
		};
		usort($arr, 'gal_cmp');
		
		return ! empty( $arr ) && is_array($arr) ? $arr : FALSE;
				
	};
};
if(! function_exists('gal_order_handlers')){
	function gal_order_handlers(){
		
		global $post;
		
		$meta = get_post_meta( $post->ID, '_cpt_meta_gallery', TRUE );

		/*
		echo '<a class="o-mv-up" href="'.(WEBSITE_ADMIN_URL.'edit.php?post_type=cpt-gallery&action=move_up&post_id='.$post->ID).'" title="Move up" style="background: url('.GAL_PLUGIN_URL.'public/images/arrow_up.png) no-repeat 0 0 transparent;">MoveUp</a>';
		echo '<a class="o-mv-up" href="'.(WEBSITE_ADMIN_URL.'edit.php?post_type=cpt-gallery&action=move_down&post_id='.$post->ID).'" title="Move down" style="background: url('.GAL_PLUGIN_URL.'public/images/arrow_down.png) no-repeat 0 0 transparent;">MoveDown</a>';
		*/
		echo '<input class="cpt-o-pos" type="" name="item_order_'.($post->ID).'" value="'.($meta['cpt-order']).'" readonly="readonly">';

	};
};
if(! function_exists('gal_re_order_slide')){
	function gal_re_order_slide(){

		$meta = get_post_meta( $_GET['post_id'], '_cpt_meta_gallery', TRUE );

		$data = array(
			'link-to-post-title' => $meta['link-to-post-title'],
			'post_id' => $_GET['post_id'],
			'cpt-order' => $_GET['order_pos']		
		);
		
		update_post_meta($_GET['post_id'],'_cpt_meta_gallery', $data );		
		
		exit;

	};
};