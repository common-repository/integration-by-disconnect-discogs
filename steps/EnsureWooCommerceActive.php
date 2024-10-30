<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisConnect_EnsureWooCommerceActive implements Disconnect_Automation
{
	public function getName()
	{
		return __('Checking if the WooCommerce plugin is activated', 'integration-by-disconnect-discogs');
	}

	public function runStep()
	{
		if (is_plugin_active('woocommerce/woocommerce.php')) {
			return new DisConnect_Result_Object(true);
		}

		return new DisConnect_Result_Object(false, __('WooCommerce plugin is not active, Please activate first and try again.', 'integration-by-disconnect-discogs'));
	}
}
