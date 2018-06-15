<?php
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Cryptocurrency_ticker_data_update.php');


$Cryptocurrency_ticker_data_update = new Cryptocurrency_ticker_data_update();
$Cryptocurrency_ticker_data_update->delete_data_scheduler();

$Comparison_enabled = $Cryptocurrency_ticker_data_update->get_enabled_comparison();
if (is_array($Comparison_enabled) && (count($Comparison_enabled) > 0)) {
	foreach ($Comparison_enabled as $val) {
		$Cryptocurrency_ticker_data_update->update_enabled_data($val->seq);
		//print_r($val);
	}
}
