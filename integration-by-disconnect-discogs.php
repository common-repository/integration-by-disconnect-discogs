<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: Integration by Disconnect for Discogs
Description: Integration by Disconnect for Discogs helps you easily integrate your WooCommerce store with Discogs and manage inventory across Discogs to Woocommerce.
Version: 1.0
Author: Integration by Disconnect for Discogs
Author URI: https://constacloud.com/disconnect
License: GPL2
Text Domain: integration-by-disconnect-discogs
Requires Plugins: woocommerce
*/

class DisConnectPlugin
{	

	/** @var Disconnect_Automation[] */
	public $steps = [];

	public function registerPluginHooks()
	{
		add_menu_page('Integration by Disconnect for Discogs', 'Integration by Disconnect for Discogs', 'manage_options', 'integration-by-disconnect-discogs', [$this, 'renderPage']);
		add_action('admin_action_disconnect_integrate', [$this, 'integrate']);
		add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
	}

	function integrate()
	{
		if(current_user_can('install_plugins') && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['action_disconnect_integrate_nonce'])), 'action_disconnect_integrate')){
			$stepIndex = isset($_POST['step']) ? intval(sanitize_text_field(wp_unslash($_POST['step']))) : -1;
			$result    = $this->runStep($stepIndex);

			echo wp_json_encode($result);
		}
		exit();
	}

	/**
	 * @param int $stepIndex
	 *
	 * @return DisConnect_Result_Object
	 */
	function runStep($stepIndex)
	{
		if ($stepIndex < 0 || $stepIndex >= count($this->steps)) {
			return new DisConnect_Result_Object(
				false,
				__('Invalid integration step received. Please contact us for support.', 'integration-by-disconnect-discogs')
			);
		}

		return $this->steps[$stepIndex]->runStep();
	}

	function enqueueScripts()
	{
		wp_enqueue_script(
			'integration-by-disconnect-discogs-js',
			plugin_dir_url(__FILE__).'js/integration-by-disconnect-discogs.js?v=1',
			array('jquery'),
			'0.1'
		);

		wp_localize_script('integration-by-disconnect-discogs-js', 'f_ajax_object', array(
	         'nonce' => wp_create_nonce('action_disconnect_integrate')
	    ));

		wp_enqueue_style(
			'integration-by-disconnect-discogs-css',
			plugin_dir_url(__FILE__).'css/integration-by-disconnect-discogs.css',
			array(),
			'0.1'
		);
	}

	function renderPage()
	{
		echo '<h1>Integration by Disconnect for Discogs</h1>';
		$is_reconnect = @$_GET['reconnect'] == 1;

		if (!empty(get_option('disconnect_woocommerce_consumer_key'))) {
			$is_connected = true;
			if($is_reconnect) {

				$buttonLabel = __('Re-connect to Integration by Disconnect for Discogs', 'integration-by-disconnect-discogs');
			}
			else {
				$buttonLabel = __('Open Integration by Disconnect for Discogs', 'integration-by-disconnect-discogs');

			}
		}
		else {
			$is_connected = false;
			$buttonLabel = __('Click here to connect your WooCommerce website with Discogs by Disconnect', 'integration-by-disconnect-discogs');
		}

		?>

		<?php if(!$is_connected || $is_reconnect){ ?>
	        <script>
	            var disconnectBaseUrl = <?php echo wp_json_encode(admin_url('admin.php')); ?>;
	            var disconnectStoreUrl = <?php echo wp_json_encode(home_url()); ?>;
	            var integrationStepCount = <?php echo wp_json_encode(count($this->steps)); ?>;
	            var defaultIntegrationError = <?php echo wp_json_encode(__('Could not connect to the website to complete the integration step. Please, try again.', 'integration-by-disconnect-discogs')) ?>;
	            var successfulIntegrationMessage = <?php echo wp_json_encode(__('Successfully prepared to integrate with Integration by Disconnect for Discogs!', 'integration-by-disconnect-discogs')) ?>;
	            
	        </script>
	        <div id="disconnect-description">
	            <p style="font-size: 15px;">Easily activate Discogs Integration with WooCommerce by Disconnect. Connect Discogs and WooCommerce on your website with a single click of the button below and manage inventory across Discogs to WooCommerce.</p>
	            <p style="font-size: 15px;">By clicking the button below, you are acknowledging that Disconnect can make the following changes:</p>
	            <ul style="list-style: circle inside;">
					<?php foreach ($this->steps as $index => $step) { ?>
	                    <li><?php echo esc_html($step->getName()); ?></li>
					<?php } ?>
	            </ul>
	            <form method="post" id="loader-xyz-form-id" action="<?php echo esc_url(admin_url('admin.php')); ?>" novalidate="novalidate">
	                <p class="submit">
	                	<?php
	                	wp_nonce_field('action_disconnect_integrate', 'action_disconnect_integrate_nonce');
	                	?>

	                    <input type="hidden" name="action" value="disconnect_integrate"/>
	                    <input type="hidden" name="step" value="0"/>
	                    <input type="submit" value="<?php echo esc_attr($buttonLabel); ?>" class="button button-primary" id="submit-btn-id">
	                </p>
	            </form>
	        </div>
	        <div id="disconnect-progress" style="display: none">
	            <p style="font-size: 15px;">Integration progress:</p>
	            <ol>
					<?php foreach ($this->steps as $index => $step) { ?>
	                    <li id="disconnect-step-<?php echo esc_attr($index); ?>" style="font-size: 15px;">
							<?php echo esc_html($step->getName()); ?>
	                    </li>
					<?php } ?>
	            </ol>
	            <p id="disconnect-result" style="font-size: 15px;">
	            </p>
	        </div>
			
			<?php if(@$_GET['reconnect'] == 1) {?>
	            <script>
	                var link = document.getElementById('submit-btn-id');
					link.click();
	            </script>
			<?php } ?>
		
		<?php } else { ?>
	        <a type="submit" href="https://disconnect.constacloud.com/user/dashboard" target="_blank" class="button button-primary" id="submit-btn-id"><?php echo esc_attr($buttonLabel); ?></a>
			<?php
			$url = site_url() .'/wp-admin/admin.php?page=integration-by-disconnect-discogs&reconnect=1'
			?>
	        <p style="font-size: 18px;">To manage the integration configurations <a target="_blank" href="https://disconnect.constacloud.com/user/dashboard">login</a> to your Disconnect account.</p>

	        <p style="font-size: 18px;">If your WooCommerce store is not yet connected with Disconnect, please <a href="<?php echo esc_url($url); ?>">click here</a> to reconnect.</p>
		<?php } ?>
	    
	    	<p style="font-size: 18px;"> In case if you use Cloudflare service to protect your server, please whitelist our IP <code>54.205.36.83</code> in your Cloudflare configurations for smoother operations. If you have any question contact us at <a href="mailto:help@disconnect-app.com">help@disconnect-app.com</a> OR you can check our <a href="https://help.constacloud.com/category/disconnect/discogs-woocommerce/" target="_blank">help article</a>.</p>

		<?php
	}
}

include_once('DisConnectResultObject.php');
include_once('steps/Disconnect_Automation.php');
include_once('steps/EnsureWooCommercePlugin.php');
include_once('steps/EnsureWooCommerceActive.php');
include_once('steps/EnableWooCommerceAPI.php');
include_once('steps/PermalinkSettings.php');
include_once('steps/GenerateWooCommerceKeys.php');
include_once('steps/SendWooCommerceKeys.php');

$disconnectPlugin          = new DisConnectPlugin();
$disconnectPlugin->steps[] = new DisConnect_EnsureWooCommercePlugin();
$disconnectPlugin->steps[] = new DisConnect_EnsureWooCommerceActive();
$disconnectPlugin->steps[] = new DisConnect_EnableWooCommerceAPI();
$disconnectPlugin->steps[] = new DisConnect_PermalinkSettings();
$disconnectPlugin->steps[] = new DisConnect_GenerateWooCommerceKeys();
$disconnectPlugin->steps[] = new DisConnect_SendWooCommerceKeysStep();

add_action('admin_menu', [$disconnectPlugin, 'registerPluginHooks']);

?>