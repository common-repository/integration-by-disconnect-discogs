jQuery(document).on('submit', '#disconnect-description form', function (e) {
	e.preventDefault();
	jQuery('#disconnect-description').hide();
	jQuery('#disconnect-progress').show();

	disconnectLaunchStep(0);
});

var currentStep;

function disconnectFinishIntegration(result, isError, data) {
	var $result = jQuery('#disconnect-result');

	if (isError) {
		$result.html(result) ;
		$result.addClass('color_error');
		return;
	}

	$result.removeClass('color_error');
	$result.html(result + '<p style="font-size: 15px; color: red;"><b>Now we will open a new tab in 20 seconds where you can create a Disconnect account and your WooCommerce store will be automatically connected after you have created the account. In case if you already have a Disconnect account you can simply login.</b></p> <ul><li><b>Store URL:</b> ' + disconnectStoreUrl + '</li><li><b>API Key:</b> ' + data['consumer_key'] + '</li><li><b>API Secret:</b> ' + data['consumer_secret'] + '</li><li><b>Disconnect Connect URL:</b> <a href="' + data['url'] + '" target="_blank">' + data['url'] + '</a></li></ul>');

	// wait for 20 seconds before open new tab
	setTimeout(function(){
		// try to open the URL in a new tab
		var disconnect_connect_url = window.open(data['url'], '_blank');
		
		if (disconnect_connect_url) {
			// browser has allowed it to be opened
			disconnect_connect_url.focus();
		} else {
			// browser has blocked it, open in the same tab
			window.location = data['url'];
		}
	}, 20000);

}

function disconnectStepResponseHandler(response) {
	var data = response ? JSON.parse(response) : null;

	console.log(data);

	if (!data || !data.success) {
		jQuery('#disconnect-step-' + currentStep).addClass('step_failed');
		disconnectFinishIntegration(!data || !data.message ? defaultIntegrationError : data.message, true);
		return;
	}

	if (currentStep + 1 === integrationStepCount) {
		++currentStep;
		disconnectUpdateIntegrationProgress();
		disconnectFinishIntegration(successfulIntegrationMessage, false, data.data);
		return;
	}

	disconnectLaunchStep(currentStep + 1);
}

function disconnectLaunchStep(step) {
	currentStep = step;
	disconnectUpdateIntegrationProgress();

	jQuery.ajax({
		type: "POST",
		url: disconnectBaseUrl,
		data: {
			action: 'disconnect_integrate',
			action_disconnect_integrate_nonce: f_ajax_object.nonce,
			step: currentStep
		}
	}).always(disconnectStepResponseHandler);
}

function disconnectUpdateIntegrationProgress() {
	for (var i = 0; i < integrationStepCount; ++i) {
		var $step = jQuery('#disconnect-step-' + i);
		$step.removeClass('step_in_progress');
		$step.removeClass('step_complete');
		$step.removeClass('step_failed');

		if (i <= currentStep) {
			$step.addClass((i === currentStep) ? 'step_in_progress' : 'step_complete');
		}
	}
}
