<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisConnect_SendWooCommerceKeysStep implements Disconnect_Automation
{
	const APP_URL = 'https://disconnect.constacloud.com';
	public function getName()
	{
		return __('Sending WooCommerce API keys to Disconnect', 'integration-by-disconnect-discogs');
	}

	public function runStep()
	{
		$consumerKey    = get_option('disconnect_woocommerce_consumer_key');
		$consumerSecret = get_option('disconnect_woocommerce_consumer_secret');

		if (empty($consumerKey) || empty($consumerSecret)) {
			return new DisConnect_Result_Object(false, 'Could not find WooCommerce API key. Please try again.');
		}

		return new DisConnect_Result_Object(true, 'Redirecting to Disconnect...', ['consumer_key' => $consumerKey, 'consumer_secret' => $consumerSecret, 'url' => $this->getAppRedirectUrl($consumerKey, $consumerSecret)]);
	}

	function getAppRedirectUrl( $consumerKey, $consumerSecret ) {

		$url   = self::APP_URL . '/user/connect-channel/woocommerce?consumer_key=' . $consumerKey;
		$url  .= '&consumer_secret=' . $consumerSecret;
		$url  .= '&channel_url=' . urlencode(site_url());
		if(@$_GET['reconnect'] == 1){
			$url .= '&reconnect=1';
		}
		return $url;
	}
}
