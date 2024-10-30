<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DisConnect_PermalinkSettings implements Disconnect_Automation
{
	public function getName()
	{
		return __('Making sure proper permalink structure settings', 'integration-by-disconnect-discogs');
	}

	public function runStep()
	{
		$currentStructure = get_option('permalink_structure');

		if (!empty($currentStructure)) {
			return new DisConnect_Result_Object(true);
		}

		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure('/%postname%/');

		return new DisConnect_Result_Object(true);
	}
}
