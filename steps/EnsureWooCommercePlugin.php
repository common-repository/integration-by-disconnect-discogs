<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisConnect_EnsureWooCommercePlugin implements Disconnect_Automation
{
	public function getName()
	{
		return __('Checking if a WooCommerce version is supported', 'integration-by-disconnect-discogs');
	}

	public function runStep()
	{
		$version = $this->ensureWooCommercePlugin();

		if ($version === false) {
			return new DisConnect_Result_Object(false, __('WooCommerce currently not installed and failed to install during the automated process.', 'integration-by-disconnect-discogs'));
		}

		return new DisConnect_Result_Object(true);
	}

	function ensureWooCommercePlugin()
	{
		$pluginInstalled = $this->isPluginInstalled('woocommerce/woocommerce.php');

		if ($pluginInstalled !== false) {
			return $pluginInstalled['Version'];
		}

		$this->installWooCommerce();
		$pluginInstalled = $this->isPluginInstalled('woocommerce/woocommerce.php')('woocommerce/woocommerce.php');

		if ($pluginInstalled !== false) {
			return $pluginInstalled['Version'];
		}

		return false;
	}

	function isPluginInstalled($slug)
	{
		if (!function_exists('get_plugins')) {
			require_once ABSPATH.'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		if (!empty($all_plugins[$slug])) {
			return $all_plugins[$slug];
		} else {
			return false;
		}
	}

	function installWooCommerce()
	{
		include_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();

		ob_start();

		$upgrade = new WP_Upgrader(new Automatic_Upgrader_Skin());
		$upgrade->init();

		$upgrade->run(
			array(
				'package'           => 'https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip',
				'destination'       => WP_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working'     => true,
				'hook_extra'        => array(),
			)
		);

		ob_end_clean();
	}
}
