<?php
if (!defined('BASEPATH')) {
	exit("Cannot load script directly.");
}
// -------------------------------------------------------
class Cryptocurrency_ticker_data_update extends MY_Controller {
	public $is_editor = FALSE;
	public $error = FALSE, $error_msg = array();
	protected $DateObject;
	protected $email_vendor;
	protected $base_dashboard, $base_cryptocurrency = array();
	//protected $CI;
	protected $insert_to_enabled_data_params = array();
	function __construct() {
		parent::__construct();
		//$this->CI = &get_instance();
		$this->load->helper('dashboard/dashboard_functions');
		$this->load->config('dashboard/base_dashboard');
		$this->base_dashboard = $this->config->item('base_dashboard');
		$this->email_vendor = (isset($this->base_dashboard['email_vendor']) ? $this->base_dashboard['email_vendor'] : '');
		$this->load->library('dashboard/Lib_authentication', $this->base_dashboard, 'authentication');
		$this->load->model('dashboard/Model_account', 'mod_account');
		$this->DateObject = $this->authentication->create_dateobject(ConstantConfig::$timezone, 'Y-m-d H:i:s', date('Y-m-d H:i:s'));
		if (($this->authentication->localdata != FALSE)) {
			if (in_array((int)$this->authentication->localdata['account_role'], base_config('editor_role'))) {
				$this->is_editor = TRUE;
			}
		}
		# Load cryptocurrency config
		$this->load->config('cryptocurrency/base_cryptocurrency');
		$this->base_cryptocurrency = $this->config->item('base_cryptocurrency');
		# Load Models
		$this->load->model('cryptocurrency/Model_currencies', 'mod_currency');
		$this->load->model('cryptocurrency/Model_exchange', 'mod_exchange');
		$this->load->model('cryptocurrency/Model_ticker', 'mod_ticker');
		$this->load->model('cryptocurrency/Cli_cryptocurrency_update_scheduler', 'mod_cli');
	}
	
	
	//======================================================================
	// Cryptocurrency Actions
	function update_cryptocurrency_ticker($market_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-add-email',
			'title'					=> 'Cryptocurrency Bank',
			'collect'				=> array(),
			'market_seq'			=> (is_numeric($market_seq) ? (int)$market_seq : 0),
		);
		//================================================================
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		}
		/*
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		*/
		//=================================
		if (!$this->error) {
			try {
				$collectData['market_data'] = $this->mod_currency->get_marketplace_data_by('market', $collectData['market_seq'], 1);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get marketplace data of market-seq.";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['market_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Market data return empty data...";
				$this->error_msg[] = $collectData;
			} else {
				if (strtoupper($collectData['market_data']->market_is_enabled) !== 'Y') {
					$this->error = true;
					$this->error_msg[] = "Market data is not enabled (not active).";
				} else {
					try {
						$collectData['tickers'] = $this->mod_currency->get_marketplace_tickers($collectData['market_data']);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Cannot get all tickers on marketplace: {$ex->getMessage()}";
					}
				}
			}
		}
		if (!$this->error) {
			$collectData['collect']['tickers'] = array();
			if (is_array($collectData['tickers']) && (count($collectData['tickers']) > 0)) {
				foreach ($collectData['tickers'] as $keval) {
					$currency = array(
						'from'		=> $keval->ticker_currency_from,
						'to'		=> $keval->ticker_currency_to,
					);
					try {
						$collectData['collect']['tickers'][$keval->seq] = $this->mod_currency->get_marketplace_ticker_data($keval->market_seq, $currency);
						if (!isset($collectData['collect']['tickers'][$keval->seq]['data'])) {
							$collectData['collect']['tickers'][$keval->seq]['data'] = $keval;
						}
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception get ticker data market from and to: {$ex->getMessage()}.";
					}
				}
			}
		}
		if (!$this->error) {
			$collectData['ticker_imploded_array'] = array();
			if (count($collectData['collect']['tickers']) > 0) {
				foreach ($collectData['collect']['tickers'] as $tickerKey => $tickerVal) {
					switch (strtolower($collectData['market_data']->market_api_string)) {
						case 'uppercase':
							$collectData['ticker_imploded_array'] = array(
								strtoupper($tickerVal['from']->currency_market_code),
								strtoupper($tickerVal['to']->currency_market_code),
							);
						break;
						case 'lowercase':
						default:
							$collectData['ticker_imploded_array'] = array(
								strtolower($tickerVal['from']->currency_market_code),
								strtolower($tickerVal['to']->currency_market_code),
							);
						break;
					}
					$collectData['collect']['tickers'][$tickerKey]['ticker_imploded'] = implode($collectData['market_data']->market_api_implode, $collectData['ticker_imploded_array']);
					$collectData['collect']['tickers'][$tickerKey]['api_response'] = $this->mod_currency->get_ticker_from_marketplace_api($collectData['market_data']->seq, $collectData['collect']['tickers'][$tickerKey]['ticker_imploded']);
				}
			}
		}
		if (!$this->error) {
			$collectData['ticker_api_amount'] = '0';
			switch (strtolower($collectData['market_data']->market_code)) {
				case 'bitcoin_id':
					$market_price_index = $collectData['market_data']->market_price_index;
					if (count($collectData['collect']['tickers']) > 0) {
						foreach ($collectData['collect']['tickers'] as $tickerKey => $tickerVal) {
							if (isset($tickerVal['api_response']['ticker'][$market_price_index])) {
								$collectData['ticker_api_amount'] = sprintf('%s', $tickerVal['api_response']['ticker'][$market_price_index]);
							}
							// === Insert to Database
							$affected_seq_insert_ticker_data = $this->mod_currency->insert_ticker_amount_by_tickerseq($tickerVal['data']->seq, $collectData['ticker_api_amount']);
						}
					}
				break;
				case 'kraken':
				default:
					$market_price_index = $collectData['market_data']->market_price_index;
					if (count($collectData['collect']['tickers']) > 0) {
						foreach ($collectData['collect']['tickers'] as $tickerKey => $tickerVal) {
							$ticker_imploded = (isset($tickerVal['ticker_imploded']) ? $tickerVal['ticker_imploded'] : '');
							if (isset($tickerVal['api_response']['result'][$ticker_imploded][$market_price_index][0])) {
								$collectData['ticker_api_amount'] = sprintf('%s', $tickerVal['api_response']['result'][$ticker_imploded][$market_price_index][0]);
							}
							// === Insert to Database
							$affected_seq_insert_ticker_data = $this->mod_currency->insert_ticker_amount_by_tickerseq($tickerVal['data']->seq, $collectData['ticker_api_amount']);
						}
					}
				break;
			}
			
		}
		
		if (!$this->error) {
			//print_r($collectData);
			echo "DONE\r\n";
		} else {
			print_r($this->error_msg);
		}
	}
	
	//============================
	function update_enabled_data($ticker_seq = 1) {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-insert-compared',
			'title'					=> 'Real Currency Exchange',
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'ticker_seq'			=> (is_numeric($ticker_seq) ? $ticker_seq : 0),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) || is_numeric($collectData['search_text'])) ? sprintf("%s", $collectData['search_text']) : '';
		$collectData['search_text'] = base_safe_text($collectData['search_text'], 128);
		//==========================================================================================
		$collectData['insert_to_enabled_data_params'] = array();
		$collectData['input_date'] = (isset($this->imzcustom->php_input_request['body']['input_date']) ? $this->imzcustom->php_input_request['body']['input_date'] : '');
		$collectData['input_date'] = (is_string($collectData['input_date']) ? strtolower($collectData['input_date']) : '');
		try {
			$collectData['input_date_object'] = date_create_from_format('Y-m-d', $collectData['input_date']);
		} catch (Exception $ex) {
			throw $ex;
			$collectData['input_date_object'] = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		if ($collectData['input_date_object'] !== FALSE) {
			$collectData['tickerdata_date'] = date_format($collectData['input_date_object'], 'Y-m-d');
		} else {
			$collectData['tickerdata_date'] = $this->DateObject->format('Y-m-d');
		}
		$collectData['marketplace'] = $this->mod_currency->get_marketplace();
		if (!$this->error) {
			try {
				$collectData['collect']['enabled_data'] = $this->mod_ticker->get_enabled_data_single_by('seq', $collectData['ticker_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get data of enabled to view data.";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['enabled_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Enabled ticker comparison data not exists on database.";
			} else {
				try {
					$collectData['ticker_data'] = array(
						'from'		=> $this->mod_ticker->get_ticker_data_by('seq', $collectData['collect']['enabled_data']->cryptocurrency_compare_ticker_seq_from),
						'to'		=> $this->mod_ticker->get_ticker_data_by('seq', $collectData['collect']['enabled_data']->cryptocurrency_compare_ticker_seq_to),
					);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot get ticker_data (from) and ticker_data (to) by seq: {$ex->getMessage()}";
				}
			}
		}
		if (!$this->error) {
			if (!isset($collectData['ticker_data']['from']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Ticker data (from) sequence not exists on database.";
			}
			if (!isset($collectData['ticker_data']['to']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Ticker data (to) sequence not exists on database.";
			}
			try {
				$collectData['all_tickers'] = $this->mod_currency->get_allmarketplace_tickers();
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get all marketplace tickers with exception: {$ex->getMessage()}";
			}
		}
		
		if (!$this->error) {
			//$collectData['today_exchange'] = $this->mod_exchange->get_today_exchange();
			$collectData['today_exchange'] = $this->mod_exchange->get_today_exchange_by_currecency_fromto($collectData['ticker_data']['from']->ticker_currency_to, $collectData['ticker_data']['to']->ticker_currency_to);
			if (!isset($collectData['today_exchange']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Not get today exchange amount.";
			}
		}
		if (!$this->error) {
			// SET Ticker Data Collections
			//---- (From)
			$collectData['collect']['ticker_data_collection'] = array();
			try {
				$collectData['collect']['ticker_data_collection']['from'] = $this->mod_ticker->get_ticker_data_collection_by($collectData['collect']['enabled_data']->cryptocurrency_compare_ticker_seq_from, $collectData['tickerdata_date'], $collectData['collect']['enabled_data']->cryptocurrency_compare_unit, $collectData['collect']['enabled_data']->cryptocurrency_compare_amount);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get ticker data collection (from) with exception: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			if (count($collectData['collect']['ticker_data_collection']['from']) > 0) {
				foreach ($collectData['collect']['ticker_data_collection']['from'] as $FromKey => &$FromCollectVal) {
					$FromCollectVal['exchange'] = array(
						'min_amount'			=> round(sprintf("%.08f", ($FromCollectVal['result']->min_amount * $collectData['today_exchange']->exchange_amount_to)), 2, PHP_ROUND_HALF_ODD),
						'max_amount'			=> round(sprintf("%.08f", ($FromCollectVal['result']->max_amount * $collectData['today_exchange']->exchange_amount_to)), 2, PHP_ROUND_HALF_ODD),
						'avg_amount'			=> round(sprintf("%.08f", ($FromCollectVal['result']->avg_amount * $collectData['today_exchange']->exchange_amount_to)), 2, PHP_ROUND_HALF_ODD),
						'last_amount'			=> round(sprintf("%.08f", ($FromCollectVal['result']->last_amount * $collectData['today_exchange']->exchange_amount_to)), 2, PHP_ROUND_HALF_ODD),
					);
					// Create Dateobject of Starting
					$from_starting_dateobject = new DateTime($FromCollectVal['starting']);
					if ($from_starting_dateobject !== FALSE) {
						if ($from_starting_dateobject->format('Y-m-d H:i') < $this->DateObject->format('Y-m-d H:i')) {
							$from_stopping_dateobject = new DateTime($FromCollectVal['stopping']);
							$collectData['insert_to_enabled_data_params'][$FromKey] = array(
								'enabled_seq'						=> $collectData['collect']['enabled_data']->seq,
								'comparison_date'					=> $from_starting_dateobject->format('Y-m-d'),
								'comparison_datetime_starting'		=> $from_starting_dateobject->format('Y-m-d H:i:s'),
								'comparison_datetime_stopping'		=> $from_stopping_dateobject->format('Y-m-d H:i:s'),
								'comparison_every_amount'			=> sprintf("%d", $collectData['collect']['enabled_data']->cryptocurrency_compare_amount),
								'comparison_every_unit'				=> sprintf("%s", $collectData['collect']['enabled_data']->cryptocurrency_compare_unit),
								'exchange_from_raw'					=> json_encode($FromCollectVal['result'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
								'exchange_from_max'					=> $FromCollectVal['exchange']['max_amount'],
								'exchange_from_last'				=> $FromCollectVal['exchange']['last_amount'],
								'today_comparison_currency'			=> $collectData['today_exchange']->exchange_amount_to,
								'today_comparison_limit'			=> $collectData['collect']['enabled_data']->cryptocurrency_premium_limit,
								'today_comparison_limit_min'		=> $collectData['collect']['enabled_data']->cryptocurrency_premium_limit_min,
								'today_comparison_limit_max'		=> $collectData['collect']['enabled_data']->cryptocurrency_premium_limit_max,
							);
						}
					}
				}
			}
			//---- (To)
			try {
				$collectData['collect']['ticker_data_collection']['to'] = $this->mod_ticker->get_ticker_data_collection_by($collectData['collect']['enabled_data']->cryptocurrency_compare_ticker_seq_to, $collectData['tickerdata_date'], $collectData['collect']['enabled_data']->cryptocurrency_compare_unit, $collectData['collect']['enabled_data']->cryptocurrency_compare_amount);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get ticker data collection (to) with exception: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			if (count($collectData['collect']['ticker_data_collection']['to']) > 0) {
				foreach ($collectData['collect']['ticker_data_collection']['to'] as $ToKey => &$ToCollectVal) {
					$ToCollectVal['exchange'] = array(
						'min_amount'			=> round(sprintf("%.08f", ($ToCollectVal['result']->min_amount * 1)), 2, PHP_ROUND_HALF_ODD),
						'max_amount'			=> round(sprintf("%.08f", ($ToCollectVal['result']->max_amount * 1)), 2, PHP_ROUND_HALF_ODD),
						'avg_amount'			=> round(sprintf("%.08f", ($ToCollectVal['result']->avg_amount * 1)), 2, PHP_ROUND_HALF_ODD),
						'last_amount'			=> round(sprintf("%.08f", ($ToCollectVal['result']->last_amount * 1)), 2, PHP_ROUND_HALF_ODD),
					);
					// Create Dateobject of Starting
					$to_starting_dateobject = new DateTime($ToCollectVal['starting']);
					if ($to_starting_dateobject !== FALSE) {
						if ($to_starting_dateobject->format('Y-m-d H:i') < $this->DateObject->format('Y-m-d H:i')) {
							$to_stopping_dateobject = new DateTime($ToCollectVal['stopping']);
							$collectData['insert_to_enabled_data_params'][$ToKey]['exchange_to_raw'] = json_encode($ToCollectVal['result'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
							$collectData['insert_to_enabled_data_params'][$ToKey]['exchange_to_max'] = $ToCollectVal['exchange']['max_amount'];
							$collectData['insert_to_enabled_data_params'][$ToKey]['exchange_to_last'] = $ToCollectVal['exchange']['last_amount'];
						}
					}
				}
			}
		}
		if (!$this->error) {
			$collectData['insert_enabled_data_result'] = array();
			if (is_array($collectData['insert_to_enabled_data_params'])) {
				if (count($collectData['insert_to_enabled_data_params']) > 0) {
					foreach ($collectData['insert_to_enabled_data_params'] as &$paramsVal) {
						$paramsVal['comparison_after_exchange_result'] = $this->calculte_and_insert_to_enabled_data($paramsVal);
						if ($paramsVal['comparison_after_exchange_result'] !== 0) {
							$paramsVal['comparison_after_exchange_persen'] = ($paramsVal['comparison_after_exchange_result'] * 100);
						} else {
							$paramsVal['comparison_after_exchange_persen'] = 0;
						}
					}
				}
			}
			if (count($collectData['insert_to_enabled_data_params']) > 0) {
				foreach ($collectData['insert_to_enabled_data_params'] as $insertKey => $insertVal) {
					$comparison_date = array(
						'starting' => $insertVal['comparison_datetime_starting'], 
						'stopping' => $insertVal['comparison_datetime_stopping'],
					);
					if ($to_stopping_dateobject->format('Y-m-d H:i:s') > $this->DateObject->format('Y-m-d H:i:s')) {
						$collectData['insert_enabled_data_result'][] = array(
							'insert_id'		=> $this->mod_ticker->insert_ticker_data_collection_to_enabled_data($collectData['collect']['enabled_data']->seq, $comparison_date, $insertVal),
							'insert_params'	=> $insertVal,
						);
						// Send email if comparison_after_exchange_persen limit more than cryptocurrency_premium_limit
						$notification_datetime_marker = new DateTime($insertVal['comparison_datetime_stopping']);
						if ($notification_datetime_marker > $this->DateObject) {
							/*
							if ($insertVal['comparison_after_exchange_persen'] > $insertVal['today_comparison_limit']) {
								$this->running_send_email_of_premium_limit($insertVal, $collectData['collect']['enabled_data'], $comparison_date);
							}
							*/
							if (($insertVal['comparison_after_exchange_persen'] < $insertVal['today_comparison_limit_min']) || ($insertVal['comparison_after_exchange_persen'] > $insertVal['today_comparison_limit_max'])) {
								$this->running_send_email_of_premium_limit($insertVal, $collectData['collect']['enabled_data'], $comparison_date);
							}
						}
					} else {
						$collectData['insert_enabled_data_result'][] = array(
							'insert_id'		=> -1001,
							'insert_params'	=> $insertVal,
						);
					}
				}
			}
		}
		if (!$this->error) {
			//print_r($collectData);
			//echo "\r\n -- DONE -- \r\n";
			
			//print_r($collectData['insert_enabled_data_result']);
			print_r($collectData['insert_enabled_data_result']);
		} else {
			print_r($this->error_msg);
		}
	}
	private function calculte_and_insert_to_enabled_data($input_params = array()) {
		$return_int = 0;
		if (count($input_params) === 0) {
			return FALSE;
		}
		if (isset($input_params['exchange_from_max']) && isset($input_params['exchange_to_max'])) {
			$input_params['exchange_from_max'] = sprintf("%.02f", $input_params['exchange_from_max']);
			$input_params['exchange_to_max'] = sprintf("%.02f", $input_params['exchange_to_max']);
			$input_params['exchange_from_last'] = sprintf("%.02f", $input_params['exchange_from_last']);
			$input_params['exchange_to_last'] = sprintf("%.02f", $input_params['exchange_to_last']);
			/*
			if (($input_params['exchange_from_max'] > 0) && ($input_params['exchange_to_max'] > 0)) {
				$return_int = ($input_params['exchange_from_max'] / $input_params['exchange_to_max']);
				$return_int = round($return_int, 4, PHP_ROUND_HALF_ODD);
				
				//$return_int = sprintf("%.02f", $return_int);
			}
			*/
			if (($input_params['exchange_from_last'] > 0) && ($input_params['exchange_to_last'] > 0)) {
				$return_int = ($input_params['exchange_from_last'] / $input_params['exchange_to_last']);
				$return_int = round($return_int, 4, PHP_ROUND_HALF_ODD);
			}
		}
		if ($return_int > 0) {
			$return_int = (1 - $return_int);
		}
		return $return_int;
	}
	function get_enabled_comparison() {
		return $this->mod_cli->get_enabled_ticker_comparison(1);
	}
	
	private function running_send_email_of_premium_limit($result_params, $enabled_data, $comparison_date) {
		$is_send_email = FALSE;
		if (($comparison_date['starting'] < $this->DateObject->format('Y-m-d H:i:s')) && ($comparison_date['stopping'] > $this->DateObject->format('Y-m-d H:i:s'))) {
			if ($result_params['comparison_after_exchange_persen'] > $enabled_data->cryptocurrency_premium_limit) {
				$notification_emails = $this->mod_cli->get_notification_emails(1);
				$email_templates = $this->mod_ticker->get_email_templates_by('code', 'all');
				if (is_array($notification_emails) && (count($notification_emails) > 0)) {
					foreach ($notification_emails as $email) {
						$query_params = array(
							'account_email'					=> $email->email_address,
							'account_name'					=> $email->email_name,
							'account_action_subject'		=> "{$enabled_data->ticker_data->cryptocurrency_code} limit is {$result_params['comparison_after_exchange_persen']}",
							'account_action_body'			=> (isset($email_templates->email_status_description) ? $email_templates->email_status_description : ''),
						);
						$query_params['account_action_body'] = str_replace('[tag_cryptocurrency_code]', $enabled_data->cryptocurrency_code, $query_params['account_action_body']);
						
						$query_params['account_action_body'] = str_replace('[tag_from_market_name]', $enabled_data->ticker_data->from_market_name, $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_to_market_name]', $enabled_data->ticker_data->to_market_name, $query_params['account_action_body']);
						
						$query_params['account_action_body'] = str_replace('[tag_result]', $result_params['comparison_after_exchange_persen'], $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_limit_premium]', $result_params['today_comparison_limit'], $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_limit_premium_min]', $result_params['today_comparison_limit_min'], $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_limit_premium_max]', $result_params['today_comparison_limit_max'], $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_currency_code]', strtoupper($enabled_data->ticker_data->cryptocurrency_from_realcurrency), $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_currency_exchange]', $result_params['today_comparison_currency'], $query_params['account_action_body']);
						
						$query_params['account_action_body'] = str_replace('[tag_datetime_starting]', $result_params['comparison_datetime_starting'], $query_params['account_action_body']);
						$query_params['account_action_body'] = str_replace('[tag_datetime_stopping]', $result_params['comparison_datetime_stopping'], $query_params['account_action_body']);
						try {
							$send_email = $this->authentication->send_email($this->email_vendor, $query_params);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Cannot send email for ticker notification.";
						}
					}
				}
			}
		}
	}
	
	
	//=================================
	// Running this instance\
	function running_cryptocurrency_cli() {
		try {
			$Comparison_enabled = $this->get_enabled_comparison();
		} catch (Exception $ex) {
			throw $ex;
			return false;
		}
		// Kraken
		$this->update_cryptocurrency_ticker(1);
		// Indodax (Former: Bitcoin Indonesia)
		$this->update_cryptocurrency_ticker(2);
		if (is_array($Comparison_enabled) && (count($Comparison_enabled) > 0)) {
			foreach ($Comparison_enabled as $val) {
				$this->update_enabled_data($val->seq);
			}
		}
	}
	
}


















