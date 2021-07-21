<?php
$pro_plugins = array(
	0 => array(
		'title' => 'Advanced Shipment Tracking Pro',
		'description' => 'Advanced Shipment Tracking comes with powerful features to help you manage & automate the WooCommerce fulfillment workflow.',
		'url' => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=ast-pro&utm_campaign=add-ons',
		'image' => 'ast-icon.png',
		'width' => '140px',
		'file' => 'ast-pro/ast-pro.php'
	),
	1 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/?utm_source=wp-admin&utm_medium=SMSWOO&utm_campaign=add-ons',
		'image' => 'smswoo-icon.png',
		'width' => '90px',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	2 => array(
		'title' => 'Advanced Order Status Manager',
		'description' => 'The Advanced Order Status Manager allows store owners to manage the WooCommerce orders statuses, create, edit, and delete custom Custom Order Statuses and integrate them into the WooCommerce orders flow.',
		'url' => 'https://www.zorem.com/products/advanced-order-status-manager/?utm_source=wp-admin&utm_medium=OSM&utm_campaign=add-ons',
		'image' => 'osm-icon.png',
		'width' => '60px',
		'file' => 'advanced-order-status-manager/advanced-order-status-manager.php'
	),
	3 => array(
		'title' => 'Sales Report Email Pro',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.',
		'url' => 'https://www.zorem.com/products/sales-report-email-for-woocommerce/?utm_source=wp-admin&utm_medium=SRE&utm_campaign=add-ons',
		'image' => 'sre-icon.png',
		'width' => '60px',
		'file' => 'sales-report-email-pro-addon/sales-report-email-pro-addon.php'
	),		
	4 => array(
		'title' => 'Country Based Restrictions Pro',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'url' => 'https://www.zorem.com/products/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=CBR&utm_campaign=add-ons',
		'image' => 'cbr-icon.png',
		'width' => '70px',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),
	5 => array(
		'title' => 'Customer Email Verification',
		'description' => 'The verify customers email address when they register an account or checkout on your store and filter out customers that try to create an account on your store with fake email addresses.',
		'url' => 'https://www.zorem.com/product/customer-verification-for-woocommerce/?utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'cev-icon.png',
		'width' => '60px',
		'file' => 'customer-email-verification-pro/customer-email-verification-pro.php'
	),				
);
?>
<section id="wclp_content4" class="wclp_tab_section">
	<div class="d_table addons_page_dtable" style="">
		<section id="content_tab_addons" class="">			
			
        	<div class="section-content">
				<div class="alp_row">
               	 <div class="alp_addon_section">
					<div class="alp_col_inner">
						<div class="plugin_details">
							<div class="plugin_logo">
								<img src="<?php echo  wc_local_pickup()->plugin_dir_url()?>assets/images/alp-pro-icon.png" width="85px">						
							</div>
							<div class="plugin_description">
								<h3 class="plugin_title">Advanced Local Pickup Pro</h3>
								<p>The Advanced Local Pickup Pro extends the free version and lets you set up multiple pickup locations, apply discounts for local pickup, display local pickup messages, split the business hours, allow customers to choose pickup location per product during checkout and more..</p>
								<?php 
								if ( is_plugin_active( 'advanced-local-pickup-pro/advanced-local-pickup-pro.php' ) ) { ?>
									<button type="button" class="button button-disabled" disabled="disabled">Installed</button>
								<?php } else{ ?>
									<a href="https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/?utm_source=wp-admin&utm_medium=ALPPRO&utm_campaign=add-ons" class="install-now button-primary" target="blank">more info</a>
								<?php } ?>					
							</div>			
						</div>
					</div>
                    </div>
				</div>
			</div>
			<div class="section-content wclp_tab_inner_container">
				<div class="plugins_section free_plugin_section">
					<?php foreach($pro_plugins as $plugin){ ?>
						<div class="single_plugin">
							<div class="free_plugin_inner">
								<div class="plugin_image">
									<img src="<?php echo  wc_local_pickup()->plugin_dir_url()?>assets/images/<?php echo $plugin['image']; ?>" width="<?php echo $plugin['width']; ?>">
								</div>
								<div class="plugin_description">
									<h3 class="plugin_title"><?php echo $plugin['title']; ?></h3>
									<p><?php echo $plugin['description']; ?></p>
									<?php 
									if ( is_plugin_active( $plugin['file'] ) ) { ?>
										<button type="button" class="button button-disabled" disabled="disabled">Installed</button>
									<?php } else{ ?>
										<a href="<?php echo $plugin['url']; ?>" class="install-now button-primary" target="blank">more info</a>
									<?php } ?>								
								</div>
							</div>	
						</div>	
					<?php } ?>						
				</div>
			</div>	
		</section>		
	</div>
</section>