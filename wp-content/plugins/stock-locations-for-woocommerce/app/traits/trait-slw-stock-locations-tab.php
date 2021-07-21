<?php
/**
 * SLW Stock Locations Tab Trait
 *
 * @since 1.0.0
 */

namespace App\Traits;

/**
 * If this file is called directly, abort.
 *
 * @since 1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

use App\SlwAdminNotice;

if(!trait_exists('SlwStockLocationsTab')) {

    trait SlwStockLocationsTab
    {

        // Define properties
        private $tab_stock_locations = SLW_PLUGIN_SLUG . '_tab_stock_locations';

        /**
         * Creates the Stock Locations tab in WC Product.
         *
         * @since 1.0.0
         * @return array
         */
        public function create_custom_stock_locations_tab_wc_product($original_tabs) // Create the custom tabs for this plugin
        {
            // Define custom tabs
            $new_tab[$this->tab_stock_locations] = array(
                'label' 	=> __( 'Stock Locations', 'stock-locations-for-woocommerce' ),
                'target'    => $this->tab_stock_locations,
                'class'     => array( 'show_if_simple', 'show_if_variable' ),
            );

            // Define tab positions
            $insert_at_position = 4;
            $tabs = array_slice( $original_tabs, 0, $insert_at_position, true );
            $tabs = array_merge( $tabs, $new_tab );
            $tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) );

            return $tabs;
        }

        /**
         * Add data to the Stock Locations tab in WC Product.
         *
         * @since 1.0.0
         * @return void
         */
        public function tab_content_stock_locations_wc_product($array): void // Populate the stock locations tab with data
        {
            // Get the product ID
            $product_id = get_the_ID();

            // Get the product object
            $product = wc_get_product( $product_id );

            // if product is variable
            if( $product->is_type('variable') ) {
                // Get product variations
                $product_variations = $product->get_available_variations();
            }

            // Get product location terms
            $product_stock_location_terms = get_the_terms($product_id, SlwProductTaxonomy::get_Tax_Names('singular'));

            // Define $postmeta variable as array type
            $postmeta = array();

            // Define $postmeta_variations variable as array type
            $postmeta_variations = array();

            // Populate the tab content
            echo '<div id="' . $this->tab_stock_locations . '" class="panel woocommerce_options_panel">';
            echo '<div id="' . $this->tab_stock_locations . '_notice">' . __('To be able to manage stock locations, please activate the <b>Stock Management</b> option under the <b>Inventory Tab</b>, and add a location to this product.', 'stock-locations-for-woocommerce') . '</div>';

            // Check if the product has terms
            if($product_stock_location_terms) {

                echo '<div id="' . $this->tab_stock_locations . '_wrapper" style="display:none;">';
                echo '<div id="' . $this->tab_stock_locations . '_title"><h4>' . $product->get_title() . '</h4></div>';

                // Loop throw terms
                foreach($product_stock_location_terms as $term) {

                    $postmeta[] = $this->create_stock_location_input($product_id, $term);

                }

                // Show total stock if '_stock' post meta exists and '_manage_stock' is set to 'yes'
                if( $product->managing_stock() ) {
                    echo '<div id="' . $this->tab_stock_locations . '_total"><u>' . __('Total Stock:', 'stock-locations-for-woocommerce') . ' <b>' . ($product->get_stock_quantity() + 0) . '</b></u></div>';
                    echo '<hr>';
                }

                // Convert $postmeta array values from string to int
                $postmeta_int = array();
                for( $i = 0; $i < count($postmeta); $i++ ) {
                    $postmeta_int[] = intval($postmeta[$i][0]);
                }

                // Check if the total stock matches the sum of the locations stock, if not show warning message
                if( $product->get_stock_quantity() != array_sum($postmeta_int) ) {
                    echo '<div id="' . $this->tab_stock_locations . '_alert" style="display:none;">' . __('The total stock doesn\'t match the sum of the locations stock. Please update this product to fix it.', 'stock-locations-for-woocommerce') . '</div>';
                }

                echo '</div>';

                // If product is variable but no active variations show message
                if( $product->is_type('variable') && empty($product_variations) ) {
                    echo '<div id="' . $this->tab_stock_locations . '_notice_variations">' . __('To be able to manage stock locations for variations please create them and add a price to each one.', 'stock-locations-for-woocommerce') . '</div>';
                }

                // Check if product has variations
                if( isset($product_variations) && ( !empty($product_variations) || ($product_variations !== 0) ) ) {

                    // Interate over variations
                    foreach( $product_variations as $variation ) {

                        $variation_id = $variation['variation_id'];

                        $variation_attributes = implode(",", $variation['attributes']);

                        $variation_manage_stock = get_post_meta($variation_id, '_manage_stock', true);
                        $variation_price = get_post_meta($variation_id, '_price', true);

                        // Check if variation allow manage stock and has price
                        if( ( $variation_manage_stock === 'yes' ) && ( !empty($variation_price) || !isset($variation_price) ) ) {
                            echo '<div id="' . $this->tab_stock_locations . '_wrapper_variations">';
                        } else {
                            echo '<div id="' . $this->tab_stock_locations . '_notice_variations">' . sprintf( __('To be able to manage stock locations in <b>%1$s</b>, please add a <b>price</b> and activate the <b>Stock Management</b> under the variation settings.', 'stock-locations-for-woocommerce'), ucfirst($variation_attributes) ) . '</div>';
                            echo '<div id="' . $this->tab_stock_locations . '_wrapper_variations" style="display:none;">';
                        }

                        echo '<div id="' . $this->tab_stock_locations . '_title"><h4>' . ucfirst($variation_attributes) . '</h4></div>';

                        // Loop throw terms
                        foreach($product_stock_location_terms as $term) {

                            // Create the inputs for the variations
                            $postmeta_variations[] = $this->create_stock_location_input($variation_id, $term);

                        }

                        // Get Variation Object
                        $variation_obj = wc_get_product($variation_id);

                        // Show total stock if '_stock' post meta exists and '_manage_stock' is set to 'yes'
                        if( $variation_obj->managing_stock() ) {
                            echo '<div id="' . $this->tab_stock_locations . '_total"><u>' . __('Total Stock:', 'stock-locations-for-woocommerce') . ' <b>' . ($variation_obj->get_stock_quantity() + 0) . '</b></u></div>';
                            echo '<hr>';
                        }

                        echo '</div>';

                    }

                }

            } else {
                echo '<div id="' . $this->tab_stock_locations . '_alert">' . __('You need to add a stock location to this product.', 'stock-locations-for-woocommerce') . '</div>';
            }

            echo '</div>';

        }

        /**
         * Create Stock Locations inputs in WC Product.
         *
         * @since 1.0.0
         * @return array
         */
        private function create_stock_location_input($id, $term)
        {

            // Create the input
            woocommerce_wp_text_input( array(
                'id'            => '_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id,
                'label'         => $term->name,
                'description'   => __( 'Enter the stock amount for this location.', 'stock-locations-for-woocommerce' ),
                'desc_tip'      => true,
                'class'         => 'woocommerce',
                'type'          => 'number',
                'data_type'     => 'stock',
                'value'         => get_post_meta($id, '_stock_at_' . $term->term_id, true),
            ) );

            // Save postmeta to variable
            $postmeta[] = get_post_meta($id, '_stock_at_' . $term->term_id, true);

            return $postmeta;

        }

        /**
         * Saves data from custom Stock Locations tab upon WC Product save.
         *
         * @since 1.0.0
         * @return int|void
         */
        public function save_tab_data_stock_locations_wc_product_save($post_id, $post, $update) // Save the custom tab data with product
        {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                return $post_id;

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                return $post_id;

            if ( ! current_user_can( 'edit_product', $post_id ) )
                return $post_id;

            // Get product object
            $product = wc_get_product( $post_id );

            // If product is type variable
            if( is_a( $product, 'WC_Product' ) && $product->is_type('variable') ) {
                // Get product variations
                $product_variations = $product->get_available_variations();
            }

            // Product location terms
            $product_stock_location_terms = get_the_terms($post_id, SlwProductTaxonomy::get_Tax_Names('singular'));

            // Count how many terms exist for this product
            if( empty($product_stock_location_terms) ){
                $terms_total = 0;
            } else{
                $terms_total = count($product_stock_location_terms);
            }

            // On product update
            if( $update ){

                // If has terms
                if( $product_stock_location_terms ) {

                    $this->update_product_meta($post_id, $product_stock_location_terms, $terms_total);

                    // Check if product has variations
                    if( isset($product_variations) && ( !empty($product_variations) || ($product_variations !== 0) ) ) {

                        // Interate over variations
                        foreach( $product_variations as $variation ) {

                            $variation_id = $variation['variation_id'];

                            $this->update_product_meta($variation_id, $product_stock_location_terms, $terms_total);

                        }

                    }

                }

            }

        }

        /**
         * Saves data from custom Stock Locations tab upon WC Product save.
         *
         * @since 1.0.0
         * @return void
         */
        public function update_product_meta($id, $product_stock_location_terms, $terms_total): void
        {

            // Grab stock amount from all terms
            $product_terms_stock = array();

            // Grab input amounts
            $input_amounts = array();

            // Define counter
            $counter = 0;

            // Loop through terms
            foreach ( $product_stock_location_terms as $term ) {

                if( isset($_POST['_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id]) ) {

                    // Initiate counter
                    $counter++;

                    // Save input amounts to array
                    $input_amounts[] = sanitize_text_field($_POST['_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id]);

                    // Get post meta
                    $postmeta_stock_at_term = get_post_meta($id, '_stock_at_' . $term->term_id, true);

                    // Pass terms stock to variable
                    if($postmeta_stock_at_term) {
                        $product_terms_stock[] = $postmeta_stock_at_term;
                    }

                    // Check if input is empty
                    if(strlen($_POST['_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id]) === 0) {
                        // Show admin notice
                        SlwAdminNotice::displayError(__('An error occurred. Some field was empty.', 'stock-locations-for-woocommerce'));

                    } else {

                        $stock_location_term_input = sanitize_text_field($_POST['_' . SLW_PLUGIN_SLUG . $id . '_stock_location_' . $term->term_id]);

                        // Check if the $_POST value is the same as the postmeta, if not update the postmeta
                        if( $stock_location_term_input !== $postmeta_stock_at_term ) {

                            // Update the post meta
                            update_post_meta( $id, '_stock_at_' . $term->term_id, $stock_location_term_input );

                        }


                        // Update stock when reach the last term
                        if($counter === $terms_total) {
                            update_post_meta( $id, '_stock', array_sum($input_amounts) );
                        }

                    }

                }

            }

            $product_terms_stock = array_sum($product_terms_stock);

            // Check if stock in terms exist
            if( ( ($product_terms_stock !== NULL) || !empty($product_terms_stock) )  ) {

                // Update stock status if backorders are disabled
                if( isset($_POST['_backorders']) && ( sanitize_text_field($_POST['_backorders']) === 'no' ) ) {
                    if( array_sum($input_amounts) > 0) {
                        update_post_meta($id, '_stock_status', 'instock');
                    } else {
                        update_post_meta($id, '_stock_status', 'outofstock');
                    }
                } else {
                    update_post_meta($id, '_stock_status', 'onbackorder');
                }

            }

        }

    }

}
