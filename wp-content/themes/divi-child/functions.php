<?php
function my_theme_enqueue_styles() { 
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

/**
 * Custom tabs to Product page
 */

//Map location
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab_map' );
function woo_new_product_tab_map( $tabs ) {
	
	// Adds the new tab
	if ( !function_exists( 'have_rows' ) )
    return;
    
  	$tabs['map_tab'] = array(
		'title' 	=> __( 'Locations', 'woocommerce' ),
		'priority' 	=> 50,
		'callback' 	=> 'woo_new_product_tab_content_map'
	);
  
	return $tabs;

}
function woo_new_product_tab_content_map() {

	// The new tab content

	echo '<h2>Find Location Near You</h2>';
	echo do_shortcode('[wpsl]');
}



//Warranty
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab_warranty' );
function woo_new_product_tab_warranty( $tabs ) {
	
	// Adds the new tab
	if ( !function_exists( 'have_rows' ) )
    return;
    
  if ( get_field('warranty') ) {
	$tabs['warranty_tab'] = array(
		'title' 	=> __( 'Warranty', 'woocommerce' ),
		'priority' 	=> 50,
		'callback' 	=> 'woo_new_product_tab_content_warranty'
	);
  }
	return $tabs;

}
function woo_new_product_tab_content_warranty() {

	// The new tab content

	echo '<h2>Warranty</h2>';
	echo the_field('warranty');
}

//Warnings
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab_warning' );
function woo_new_product_tab_warning( $tabs ) {
	
	// Adds the new tab	
	if ( !function_exists( 'have_rows' ) )
    return;
    
  if ( get_field('warnings') ) {
	$tabs['warning_tab'] = array(
		'title' 	=> __( 'Warning', 'woocommerce' ),
		'priority' 	=> 50,
		'callback' 	=> 'woo_new_product_tab_warning_content'
	);
  }
	return $tabs;

}
function woo_new_product_tab_warning_content() {

	// The new tab content

	echo '<h2>Warning</h2>';
	echo the_field('warnings');
}




/* **************************************** /
/* **************************************** /
/* *************CUSTOM MODULES************* /
/* **************************************** /
/* *************************************** */

//customized blog module
function divi_custom_blog_module() {
get_template_part( '/custom-modules/Blog' );
$myblog = new custom_ET_Builder_Module_Blog();
remove_shortcode( 'et_pb_blog' );
add_shortcode( 'et_pb_blog', array( $myblog, '_render' ) );
}
add_action( 'et_builder_ready', 'divi_custom_blog_module' );