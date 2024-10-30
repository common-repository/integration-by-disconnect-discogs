<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisConnect_GenerateWooCommerceKeys implements Disconnect_Automation
{
	public function getName()
	{
		return __('Creating WooCommerce API keys for the Disconnect user account', 'integration-by-disconnect-discogs');
	}

	public function runStep()
	{
		disConnect_WC_Auth();

		if (!class_exists('Disconnect_WC_Auth')) {
			return new DisConnect_Result_Object(false, 'Could not find WooCommerce plugin. Please try again.');
		}

		$user = wp_get_current_user();

		if (!$user) {
			return new DisConnect_Result_Object(false, 'Integration by Disconnect for Discogs user account not found. Please try again.');
		}

		$apiKey = (new DisConnect_WC_Auth())->createAPIKey($user->ID);

		// store the key and secret
		if (!empty($apiKey['consumer_key'])) {
			update_option('disconnect_woocommerce_consumer_key', $apiKey['consumer_key']);
		}
		if (!empty($apiKey['consumer_secret'])) {
			update_option('disconnect_woocommerce_consumer_secret', $apiKey['consumer_secret']);
		}

		return new DisConnect_Result_Object(true, null, $apiKey);
	}
}

function disConnect_WC_Auth()
{
	if (class_exists('WC_Auth')) {
		class DisConnect_WC_Auth extends WC_Auth
		{
			public function createAPIKey($userId)
			{
				return $this->create_keys(
					'Integration by Disconnect for Discogs',
					$userId,
					'read_write'
				);
			}
		}
	}
}
