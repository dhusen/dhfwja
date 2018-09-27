<?php
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Cryptocurrency_ticker_data_update.php');


$Cryptocurrency_ticker_data_update = new Cryptocurrency_ticker_data_update();

# Make it every 30 seconds to collect data
# (5 * 60) / 30 = 10
for ($i = 0; $i < 10; $i++) {
	$Cryptocurrency_ticker_data_update->update_cryptocurrency_ticker(1); // Kraken
	sleep(5);
	$Cryptocurrency_ticker_data_update->update_cryptocurrency_ticker(2); // Bitcoin Indonesia
	sleep(5);
}