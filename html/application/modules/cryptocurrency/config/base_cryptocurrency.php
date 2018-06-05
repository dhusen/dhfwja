<?php
if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }
$config['base_cryptocurrency'] = array(
	'site-name'				=> 'Cryptocurrency Ticker Collector',
	'site-version'			=> 'v.1.01',
	'site-copyright'		=> '2018 - imzers@gmail.com',
	'base_path'				=> 'cryptocurrency',
	'base_password_forget'	=> 5,
	'email_vendor'			=> 'localsmtp',
	'email_template'		=> (FCPATH . 'media' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'email-template.html'),
	'super_admin_role'		=> 4,
	'admin_role'			=> array(4, 6),
	'editor_role'			=> array(3, 4, 5, 6),
	'merchant_role'			=> array(3, 5),
	'rows_per_page'			=> 10,
);

//================================
// Cryptocurrency Marketplace API
//================================
$config['base_cryptocurrency']['market'] = array(
	'kraken'		=> array(),
	'bitcoin_id'	=> array(),
);
# Kraken
$config['base_cryptocurrency']['market']['kraken']['server'] = 'live';
$config['base_cryptocurrency']['market']['kraken']['client'] = array(
	'ssl'		=> array(
		'peer'			=> FALSE,
		'host'			=> FALSE,
	),
	'api'		=> array(
		'version'		=> '0',
		'key'			=> '',
		'secret'		=> '',
	),
);
$config['base_cryptocurrency']['market']['kraken']['url'] = array(
	'live'		=> 'https://api.kraken.com',
	'sandbox'	=> 'https://api.beta.kraken.com',
);
# Bitcoin ID
$config['base_cryptocurrency']['market']['bitcoin_id']['server'] = 'live';
$config['base_cryptocurrency']['market']['bitcoin_id']['client'] = array(
	'ssl'		=> array(
		'peer'			=> FALSE,
		'host'			=> FALSE,
	),
	'api'		=> array(
		'version'		=> '0',
		'key'			=> '',
		'secret'		=> '',
	),
);
$config['base_cryptocurrency']['market']['bitcoin_id']['url'] = array(
	'live'		=> 'https://indodax.com/api',
	'sandbox'	=> 'https://vip.bitcoin.co.id/api',
);
//----------------------------------------------------------------------------------




//============
// From native
//============
try {
	$base_database = ConstantConfig::get_dashboard_database('cryptocurrency', ConstantConfig::THIS_SERVER_MODE);
} catch (Exception $ex){
	throw $ex;
	$base_database = FALSE;
}
$config['base_cryptocurrency']['get_database'] = array();
if ($base_database) {
	$config['base_cryptocurrency']['get_database']['db_type'] = 'mysql';
	$config['base_cryptocurrency']['get_database']['db_host'] = (isset($base_database['hostname']) ? $base_database['hostname'] : 'localhost');
	$config['base_cryptocurrency']['get_database']['db_port'] = (isset($base_database['dbport']) ? (((int)$base_database['dbport'] > 0) ? $base_database['dbport'] : 3306) : 3306);
	$config['base_cryptocurrency']['get_database']['db_user'] = (isset($base_database['username']) ? $base_database['username'] : '');
	$config['base_cryptocurrency']['get_database']['db_pass'] = (isset($base_database['password']) ? $base_database['password'] : '');
	$config['base_cryptocurrency']['get_database']['db_name'] = (isset($base_database['database']) ? $base_database['database'] : '');
	$config['base_cryptocurrency']['get_database']['db_table'] = (isset($base_database['dbprefix']) ? $base_database['dbprefix'] : '');
	$config['base_cryptocurrency']['get_database']['db_session_max'] = 3600;
}
//================
// Cryptocurrency Database Tables
$config['base_cryptocurrency']['cryptocurrency_tables'] = array(
	'marketplace'					=> 'cryptocurrency_marketplace',
	'marketplace_api'				=> 'cryptocurrency_marketplace_api_key',
	'currencies'					=> 'cryptocurrency_currencies',
	'tickers'						=> 'cryptocurrency_tickers',
	'ticker_data'					=> 'cryptocurrency_tickers_data',
	'ticker_config'					=> 'cryptocurrency_tickers_config',
	'ticker_enabled'				=> 'cryptocurrency_tickers_enabled',
	'ticker_enabled_data'			=> 'cryptocurrency_tickers_enabled_data',
	'ticker_enabled_data_email'		=> 'cryptocurrency_tickers_enabled_data_email',
	'ticker_compared'				=> 'cryptocurrency_tickers_compared',
	'ticker_email'					=> 'cryptocurrency_tickers_emails',
	'ticker_email_templates'		=> 'cryptocurrency_tickers_emails_templates',
	// Exchange Tables
	'real_currencies'				=> 'realcurrency_currencies',
	'real_exchange'					=> 'realcurrency_currencies_exchange',
	// Trash
	'real_exchange_trash'			=> 'realcurrency_currencies_exchange_trash',
	
	// Helpers
	'helper_country'				=> 'helper_country',
	
);





