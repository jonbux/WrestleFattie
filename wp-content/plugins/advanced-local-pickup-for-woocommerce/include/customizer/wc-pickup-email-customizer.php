<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wclp_pickup_customizer_email {
	// Get our default values	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wclp_generate_defaults();
						
		// Register our sample default controls
		add_action( 'customize_register', array( $this, 'wclp_register_sample_default_controls' ) );
		
		// Only proceed if this is own request.		
		if ( ! wclp_pickup_customizer_email::is_own_customizer_request() && ! wclp_pickup_customizer_email::is_own_preview_request() ) {
			return;
		}	
			
		add_action( 'customize_register', array( wclp_customizer(), 'wclp_add_customizer_panels' ) );
		// Register our sections
		add_action( 'customize_register', array( wclp_customizer(), 'wclp_add_customizer_sections' ) );	
		
		// Remove unrelated components.
		add_filter( 'customize_loaded_components', array( wclp_customizer(), 'remove_unrelated_components' ), 99, 2 );

		// Remove unrelated sections.
		add_filter( 'customize_section_active', array( wclp_customizer(), 'remove_unrelated_sections' ), 10, 2 );	
		
		// Unhook divi front end.
		add_action( 'woomail_footer', array( wclp_customizer(), 'unhook_divi' ), 10 );

		// Unhook Flatsome js
		add_action( 'customize_preview_init', array( wclp_customizer(), 'unhook_flatsome' ), 50  );
		
		add_filter( 'customize_controls_enqueue_scripts', array( wclp_customizer(), 'enqueue_customizer_scripts' ) );				
		
		add_action( 'parse_request', array( $this, 'set_up_preview' ) );	
		
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );					
	}
	
	public function enqueue_preview_scripts() {		 
		wp_enqueue_script('wclp-email-preview-scripts', wc_local_pickup()->plugin_dir_url() . 'assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_local_pickup()->version, true);
		wp_enqueue_style('wclp-preview-styles', wc_local_pickup()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_local_pickup()->version  );
				// Send variables to Javascript
		$preview_id     = get_theme_mod('wclp_pickup_preview_order_id');
		wp_localize_script('wclp-email-preview-scripts', 'wclp_preview', array(
			'site_title'   => $this->get_blogname(),
			'order_number' => $preview_id,			
		));
	}
	
	/**
	* Get blog name formatted for emails.
	*
	* @return string
	*/
	public function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	
	/**
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_preview_request() {
		return isset( $_REQUEST['wclp-pickup-email-customizer-preview'] ) && '1' === $_REQUEST['wclp-pickup-email-customizer-preview'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'customer_email_notifications';
	}

	/**
	 * Get Customizer URL
	 *
	 */
	public static function get_customizer_url($email) {		
		$customizer_url = add_query_arg( array(
			'wclp-customizer' => '1',
			'email' => $email,
			'order_status' => 'picked_up',
			'url'                  => urlencode( add_query_arg( array( 'wclp-pickup-email-customizer-preview' => '1' ), home_url( '/' ) ) ),
			'return'               => urlencode( wclp_pickup_customizer_email::get_email_settings_page_url() ),
		), admin_url( 'customize.php' ) );		

	return $customizer_url;
	}		
	
	/**
	 * Get WooCommerce email settings page URL
	 *
	 * @access public
	 * @return string
	 */
	public static function get_email_settings_page_url() {
		return admin_url( 'admin.php?page=local_pickup' );
	}
	
	/**
	 * code for initialize default value for customizer
	*/
	public function wclp_generate_defaults() {
		$customizer_defaults = array(
			'wclp_pickup_email_subject' => __( 'Your order from {site_title} was picked up', 'advanced-local-pickup-pro' ),
			'wclp_pickup_email_heading' => __( "You've Got it!", 'woocommerce' ),
			'wclp_pickup_email_content' => __( "Hi {customer_first_name}. Thank you for picking up your {site_title} order #{order_number}. We hope you enjoyed your shopping experience.", 'advanced-local-pickup-pro' ), 
			'wclp_enable_pickup_email'  => 'no',
		);

		return apply_filters( 'skyrocket_customizer_defaults', $customizer_defaults );
	}

	/**
	 * Register our sample default controls
	 */
	public function wclp_register_sample_default_controls( $wp_customize ) {		
		/**
		* Load all our Customizer Custom Controls
		*/
		require_once trailingslashit( dirname(__FILE__) ) . 'custom-controls.php';
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'customizer_pickup_order_settings_enabled',
			array(
				'default' => $this->defaults['wclp_enable_pickup_email'],
				'transport' => 'refresh',
				'type'      => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'customizer_pickup_order_settings_enabled',
			array(
				'label' => __( 'Enable Picked up order status email', 'advanced-local-pickup-pro' ),
				'description' => esc_html__( '', 'advanced-local-pickup-pro' ),
				'section' => 'customer_email_notifications',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);							
		
		// Header Text		
		$wp_customize->add_setting( 'woocommerce_customer_pickup_order_settings[subject]',
			array(
				'default' => $this->defaults['wclp_pickup_email_subject'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'woocommerce_customer_pickup_order_settings[subject]',
			array(
				'label' => __( 'Subject', 'woocommerce' ),
				'description' => esc_html__( 'Available variables:', 'advanced-local-pickup-pro' ).' {site_title}, {order_number}',
				'section' => 'customer_email_notifications',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( $this->defaults['wclp_pickup_email_subject'], 'advanced-local-pickup-pro' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Header Text		
		$wp_customize->add_setting( 'woocommerce_customer_pickup_order_settings[heading]',
			array(
				'default' => $this->defaults['wclp_pickup_email_heading'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'woocommerce_customer_pickup_order_settings[heading]',
			array(
				'label' => __( 'Email heading', 'woocommerce' ),
				'description' => esc_html__( 'Available variables:', 'advanced-local-pickup-pro' ).' {site_title}, {order_number}',
				'section' => 'customer_email_notifications',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( $this->defaults['wclp_pickup_email_heading'], 'advanced-local-pickup-pro' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Test of TinyMCE control
		$wp_customize->add_setting( 'woocommerce_customer_pickup_order_settings[additional_content]',
			array(
				'default' => $this->defaults['wclp_pickup_email_content'],
				'transport' => 'refresh',				
				'type'  => 'option',
			)
		);
		$wp_customize->add_control( new WPLP_Skyrocket_TinyMCE_Custom_control( $wp_customize, 'woocommerce_customer_pickup_order_settings[additional_content]',
			array(
				'label' => __( 'Email content', 'advanced-local-pickup-pro' ),
				'description' => '',
				'section' => 'customer_email_notifications',
				'input_attrs' => array(
					'toolbar1' => 'bold italic bullist numlist alignleft aligncenter alignright link',
					'mediaButtons' => true,
					'placeholder' => __( $this->defaults['wclp_pickup_email_content'], 'advanced-local-pickup-pro' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		) );
		
		$wp_customize->add_setting( 'pickup_email_code_block',
			array(
				'default' => '',
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		
		$wp_customize->add_control( new WPLP_Customize_codeinfoblock_Control( $wp_customize, 'pickup_email_code_block',
			array(
				'label' => __( 'Available variables:', 'advanced-local-pickup-pro' ),
				'description' => '<code>{site_title}<br>{customer_email}<br>{customer_first_name}<br>{customer_last_name}<br>{customer_username}<br>{order_number}</code>',
				'section' => 'customer_email_notifications',
				'active_callback' => array( $this, 'active_callback' ),				
			)
		) );
			
	}
	
	public function active_callback(){
		if ( wclp_pickup_customizer_email::is_own_preview_request() ) {
			return true;
		} else{
			return false;
		}
	}
		
	/**
	 * Set up preview
	 *
	 * @access public
	 * @return void
	 */
	public function set_up_preview() {
		
		// Make sure this is own preview request.
		if ( ! wclp_pickup_customizer_email::is_own_preview_request() ) {
			return;
		}
		include wc_local_pickup()->get_plugin_path() . '/include/customizer/preview/pickup_preview.php';		
		exit;			
	}
	
	/**
	 * code for preview of delivered order status email
	*/
	public function preview_pickup_email(){
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();		
		$preview_id     = get_theme_mod('wclp_pickup_preview_order_id');
		
		/*if($preview_id == '' || $preview_id == 'mockup') {
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'advanced-local-pickup-for-woocommerce' ) . '</div>';							
			echo $content;
			return;
		}		
		
		$order = wc_get_order( $preview_id );
		
		if(!$order){
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'advanced-local-pickup-for-woocommerce' ) . '</div>';							
			echo $content;
			return;
		}*/
		
		$email_type = 'WC_Email_Customer_Pickup_Order';
		
		if ( false === $email_type ) {
			return false;
		}		 				
		
		// Reference email.
		if ( isset( $emails[ $email_type ] ) && is_object( $emails[ $email_type ] ) ) {
			$email = $emails[ $email_type ];
		}
		
		$order_status = 'ready-pickup';
		// Get an order
		$order = self::get_wc_order_for_preview( $order_status, $preview_id );		
		
		// Make sure gateways are running in case the email needs to input content from them.
		WC()->payment_gateways();
		// Make sure shipping is running in case the email needs to input content from it.
		WC()->shipping();
		
		$email->object               = $order;
		
		$woocommerce_customer_pickup_order_settings = get_option('woocommerce_customer_pickup_order_settings');
		$email->settings['additional_content'] =  $woocommerce_customer_pickup_order_settings['additional_content'];
		
		$email->find['customer-first-name']   = '{customer_first_name}';
		$email->find['customer-last-name']   = '{customer_last_name}';
		$email->find['customer-email']   = '{customer_email}';
		$email->find['order-date']   = '{order_date}';
		$email->find['order-number'] = '{order_number}';
		$email->find['customer-username'] = '{customer_username}';
		if ( is_object( $order ) ) {
			$email->replace['customer-first-name'] = $email->object->get_billing_first_name();
			$email->replace['customer-last-name'] = $email->object->get_billing_last_name();
			$email->replace['customer-email'] = $email->object->get_billing_email();
			$email->replace['order-date']   = wc_format_datetime( $email->object->get_date_created() );
			$email->replace['order-number'] = $email->object->get_order_number();
			$customer = new WC_Customer( $email->object->get_customer_id() );
			$email->replace['customer-username'] = $customer->get_username();
			// Other properties
			$email->recipient = $email->object->get_billing_email();
		}
		
		// Get email content and apply styles.
		$content = $email->get_content();		
		$content = $email->style_inline( $content );
		$content = apply_filters( 'woocommerce_mail_content', $content );		
		echo $content;		
	}
	
	/**
	 * Get WooCommerce order for preview
	 *
	 * @access public
	 * @param string $order_status
	 * @return object
	 */
	public static function get_wc_order_for_preview( $order_status = null, $order_id = null ) {
		if ( ! empty( $order_id ) && 'mockup' != $order_id ) {
			return wc_get_order( $order_id );
		} else {
			// Use mockup order

			// Instantiate order object
			$order = new WC_Order();

			// Other order properties
			$order->set_props( array(
				'id'                 => 1,
				'status'             => ( null === $order_status ? 'processing' : $order_status ),
				'billing_first_name' => 'Sherlock',
				'billing_last_name'  => 'Holmes',
				'billing_company'    => 'Detectives Ltd.',
				'billing_address_1'  => '221B Baker Street',
				'billing_city'       => 'London',
				'billing_postcode'   => 'NW1 6XE',
				'billing_country'    => 'GB',
				'billing_email'      => 'sherlock@holmes.co.uk',
				'billing_phone'      => '02079304832',
				'date_created'       => date( 'Y-m-d H:i:s' ),
				'total'              => 24.90,
			) );

			// Item #1
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'A Study in Scarlet',
				'subtotal' => '9.95',
				'sku'      => 'kwd_ex_1',
			) );
			$order->add_item( $order_item );

			// Item #2
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'The Hound of the Baskervilles',
				'subtotal' => '14.95',
				'sku'      => 'kwd_ex_2',
			) );
			$order->add_item( $order_item );

			// Return mockup order
			return $order;
		}

	}	
}
/**
 * Initialise our Customizer settings
 */

$wclp_pickup_customizer_settings = new wclp_pickup_customizer_email();
add_action( 'customize_save_customizer_pickup_order_settings_enabled', 'woocommerce_customer_pickup_order_settings_fun', 100, 1 ); 

/**
 * Update Delivered order email enable/disable
 *
 */
function woocommerce_customer_pickup_order_settings_fun($data){
	$post_values = json_decode( wp_unslash( $_POST['customized'] ), true );
	$pickup_order_settings = get_option( 'woocommerce_customer_pickup_order_settings');
	
	if(isset($post_values['customizer_pickup_order_settings_enabled']) && ($post_values['customizer_pickup_order_settings_enabled'] == 1)){
		$pickup_order_settings['enabled'] = 'yes';
	} else{
		$pickup_order_settings['enabled'] = 'no';
	}		
	update_option( 'woocommerce_customer_pickup_order_settings', wc_clean( $pickup_order_settings) );	
}