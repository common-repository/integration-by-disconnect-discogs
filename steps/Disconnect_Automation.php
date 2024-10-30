<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

interface Disconnect_Automation
{
	public function getName();

	public function runStep();
}
