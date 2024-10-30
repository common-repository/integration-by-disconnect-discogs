<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisConnect_EnableWooCommerceAPI implements Disconnect_Automation
{
	public function getName()
	{
		return __('Enabling WooCommerce REST API', 'integration-by-disconnect-discogs');
	}

	public function runStep()
	{
		update_option('woocommerce_api_enabled', 'yes');
		return new DisConnect_Result_Object(true);
	}
}
