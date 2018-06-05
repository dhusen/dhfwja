<?php
if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }
defined('PHP_MYSQL_CRUD_NATIVE') OR define('PHP_MYSQL_CRUD_NATIVE', TRUE);
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cryptocurrency-includes' . DIRECTORY_SEPARATOR . 'header.php');
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cryptocurrency-includes' . DIRECTORY_SEPARATOR . 'sidebar.php');



$page = (isset($page) ? $page : 'cryptocurrency');
$page = (is_string($page) ? strtolower($page) : 'cryptocurrency');
switch (strtolower($page)) {
	# Cryptocurrency
	case 'currency-market-list':
		$file_included = 'currency/market-list.php';
	break;
	case 'currency-market-add':
		$file_included = 'currency/market-add.php';
	break;
	case 'currency-market-edit':
		$file_included = 'currency/market-edit.php';
	break;
	case 'currency-market-edit-api':
		$file_included = 'currency/market-edit-api.php';
	break;
	//--
	case 'currency-currency-list':
		$file_included = 'currency/currency-list.php';
	break;
	case 'currency-currency-add':
		$file_included = 'currency/currency-add.php';
	break;
	
	
	# Exchange
	case 'cryptocurrency-exchange-list':
		$file_included = 'exchange/exchange-list.php';
	break;
	case 'cryptocurrency-exchange-add':
		$file_included = 'exchange/exchange-add.php';
	break;
	case 'cryptocurrency-exchange-edit':
		$file_included = 'exchange/exchange-edit.php';
	break;
	case 'cryptocurrency-exchange-listcurrency':
		$file_included = 'exchange/currency-list.php';
	break;
	case 'cryptocurrency-exchange-addcurrency':
		$file_included = 'exchange/currency-add.php';
	break;
	case 'cryptocurrency-exchange-editcurrency':
		$file_included = 'exchange/currency-edit.php';
	break;
	# Ticker
	case 'cryptocurrency-ticker-addenabled':
		$file_included = 'ticker/ticker-add-enabled.php';
	break;
	case 'cryptocurrency-ticker-editenabled':
		$file_included = 'ticker/ticker-edit-enabled.php';
	break;
	case 'cryptocurrency-ticker-listenabled':
		$file_included = 'ticker/ticker-list-enabled.php';
	break;
	case 'cryptocurrency-ticker-email-templates':
		$file_included = 'ticker/ticker-email-templates.php';
	break;
	case 'cryptocurrency-ticker-email-address-view':
		$file_included = 'ticker/ticker-email-address-view.php';
	break;
	case 'cryptocurrency-ticker-email-address-edit':
		$file_included = 'ticker/ticker-email-address-edit.php';
	break;
	case 'cryptocurrency-ticker-email-address-insert':
		$file_included = 'ticker/ticker-email-address-insert.php';
	break;
	
	
	//-- Data
	case 'cryptocurrency-ticker-comparison-data':
		$file_included = 'ticker/ticker-comparison-enabled.php';
	break;
	
	
	
	
	case 'cryptocurrency-currency-lists':
	default:
		$file_included = 'currency/lists.php';
	break;
}
include($file_included);
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cryptocurrency-includes' . DIRECTORY_SEPARATOR . 'footer.php');



