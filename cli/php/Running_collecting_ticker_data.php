<?php
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Cryptocurrency_ticker_data_update.php');


$Cryptocurrency_ticker_data_update = new Cryptocurrency_ticker_data_update();

$Cryptocurrency_ticker_data_update->update_cryptocurrency_ticker(1); // Kraken
$Cryptocurrency_ticker_data_update->update_cryptocurrency_ticker(2); // Bitcoin Indonesia