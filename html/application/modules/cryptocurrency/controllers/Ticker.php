<?php
// Concept: https://docs.google.com/spreadsheets/d/13SxAErn4fCy1ktEmyUqLVRsgStytFaiAfCHThrE70pk/edit#gid=0
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
class Ticker extends MY_Controller {
	public $is_editor = FALSE;
	public $error = FALSE, $error_msg = array();
	protected $DateObject;
	protected $email_vendor;
	protected $base_dashboard, $base_cryptocurrency = array();
	protected $insert_to_enabled_data_params = array();
	function __construct() {
		parent::__construct();
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
		## Load base_cryptocurrency helper
		$this->load->helper('cryptocurrency/base_cryptocurrency');
		# Load Model Currency
		$this->load->model('cryptocurrency/Model_currencies', 'mod_currency');
		# Load Model Exchange
		$this->load->model('cryptocurrency/Model_exchange', 'mod_exchange');
		# Load Model Ticker
		$this->load->model('cryptocurrency/Model_ticker', 'mod_ticker');
		
		# Load Codeigniter helpers
		$this->load->helper('security');
		$this->load->helper('form');
		$this->load->library('form_validation');
	}
	private function accessDenied($collectData = null) {
		if (!isset($collectData)) {
			exit("This page is available if have collectData object.");
		}
		$collectData['page'] = 'error-access-denied';
		
		echo "<h1>Access Denied</h1>";
	}
	//========================================================
	private function calculte_and_insert_to_enabled_data($input_params = array()) {
		$return_int = 0;
		if (count($input_params) === 0) {
			return FALSE;
		}
		if (isset($input_params['exchange_from_max']) && isset($input_params['exchange_to_max'])) {
			$input_params['exchange_from_max'] = sprintf("%.02f", $input_params['exchange_from_max']);
			$input_params['exchange_to_max'] = sprintf("%.02f", $input_params['exchange_to_max']);
			if (($input_params['exchange_from_max'] > 0) && ($input_params['exchange_to_max'] > 0)) {
				$return_int = ($input_params['exchange_from_max'] / $input_params['exchange_to_max']);
				$return_int = round($return_int, 4, PHP_ROUND_HALF_ODD);
				
				//$return_int = sprintf("%.02f", $return_int);
			}
		}
		if ($return_int > 0) {
			$return_int = (1 - $return_int);
		}
		return $return_int;
	}
	//-------------------------------------------
	function data($ticker_seq = 0, $pgnumber = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-insert-compared',
			'title'					=> 'Real Currency Exchange',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'ticker_seq'			=> (is_numeric($ticker_seq) ? $ticker_seq : 0),
			'pgnumber'				=> (is_numeric($pgnumber) ? $pgnumber : 0),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) || is_numeric($collectData['search_text'])) ? sprintf("%s", $collectData['search_text']) : '';
		$collectData['search_text'] = base_safe_text($collectData['search_text'], 128);
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_dashboard_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		$collectData['comparison_limit'] = (isset($this->imzcustom->php_input_request['body']['comparison_limit']) ? $this->imzcustom->php_input_request['body']['comparison_limit'] : '');
		$collectData['comparison_limit'] = (is_numeric($collectData['comparison_limit']) || is_string($collectData['comparison_limit'])) ? sprintf("%s", $collectData['comparison_limit']) : '';
		$collectData['comparison_limit_min'] = (isset($this->imzcustom->php_input_request['body']['comparison_limit_min']) ? $this->imzcustom->php_input_request['body']['comparison_limit_min'] : '0');
		$collectData['comparison_limit_max'] = (isset($this->imzcustom->php_input_request['body']['comparison_limit_max']) ? $this->imzcustom->php_input_request['body']['comparison_limit_max'] : '0');
		//=================================
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
			$collectData['today_exchange'] = $this->mod_exchange->get_today_exchange();
			if (!isset($collectData['today_exchange']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Not get today exchange amount.";
			}
		}
		if (!$this->error) {
			$collectData['unit_comparison_every'] = array(
				'unit'		=> $collectData['collect']['enabled_data']->cryptocurrency_compare_unit,
				'amount'	=> $collectData['collect']['enabled_data']->cryptocurrency_compare_amount,
				'limit'		=> $collectData['comparison_limit'],
				'limit_min'	=> $collectData['comparison_limit_min'],
				'limit_max'	=> $collectData['comparison_limit_max'],
			);
			try {
				$collectData['collect']['ticker_data_enabled']['count'] = $this->mod_ticker->get_ticker_enabled_comparison_count_by('seq', $collectData['collect']['enabled_data']->seq, $collectData['tickerdata_date'], $collectData['unit_comparison_every'], $collectData['search_text']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot count ticker_data_enabled on with exceptions: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if (isset($collectData['collect']['ticker_data_enabled']['count']->value)) {
				if ((int)$collectData['collect']['ticker_data_enabled']['count']->value > 0) {
					$collectData['pagination'] = array(
						'page'		=> (isset($collectData['pgnumber']) ? $collectData['pgnumber'] : 1),
						'start'		=> 0,
					);
					$collectData['pagination']['page'] = (is_numeric($collectData['pagination']['page']) ? sprintf("%d", $collectData['pagination']['page']) : 1);
					if ($collectData['pagination']['page'] > 0) {
						$collectData['pagination']['page'] = (int)$collectData['pagination']['page'];
					} else {
						$collectData['pagination']['page'] = 1;
					}
					$collectData['pagination']['start'] = $this->imzcustom->get_pagination_start($collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['ticker_data_enabled']['count']->value);
				} else {
					$collectData['pagination'] = array(
						'page'		=> 1,
						'start'		=> 0,
					);
				}
				try {
					$collectData['collect']['ticker_data_enabled']['data'] = $this->mod_ticker->get_ticker_enabled_comparison_data_by('seq', $collectData['collect']['enabled_data']->seq, $collectData['tickerdata_date'], $collectData['unit_comparison_every'], $collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'));
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get ticker_data_enabled with exception: {$ex->getMessage()}";
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "Should have value as total rows.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/ticker/data/{$collectData['collect']['enabled_data']->seq}/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['ticker_data_enabled']['count']->value, $collectData['pagination']['start']);
			try {
				$collectData['collect']['grouped_limit_values'] = $this->mod_ticker->get_ticker_enabled_comparison_grouped_limit($collectData['collect']['enabled_data']->seq, $collectData['tickerdata_date']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get grouped-limit for enabled-data seq: {$ex->getMessage()}.";
			}
		}
		
		
		
		
		//=========================
		/*
		echo "<pre>";
		if (!$this->error) {
			print_r($collectData);
		} else {
			print_r($this->error_msg);
		}
		exit;
		*/
		
		if (!$this->error) {
			$collectData['page'] = 'cryptocurrency-ticker-comparison-data';
			$collectData['title'] = "Comparison Data of {$collectData['collect']['enabled_data']->cryptocurrency_code}";
			
			$this->load->view($collectData['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/ticker/listenabled'));
		}
	}
	//--------------------------------------------------------------------------------------
	function listenabled($pgnumber = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-listenabled',
			'title'					=> 'Show Ticker Comparison',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'pgnumber'				=> (is_numeric($pgnumber) ? (int)$pgnumber : 0),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) || is_numeric($collectData['search_text'])) ? sprintf("%s", $collectData['search_text']) : '';
		$collectData['search_text'] = base_safe_text($collectData['search_text'], 128);
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_dashboard_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		// Set is Enabled Only?
		$collectData['is_enabled'] = FALSE;
		$collectData['collect']['ticker_comparison'] = array();
		try {
			$collectData['collect']['ticker_comparison']['count'] = $this->mod_ticker->get_ticker_comparison_count_by($collectData['search_text'], $collectData['is_enabled']);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot count ticker-comparison with exceptions: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			if (isset($collectData['collect']['ticker_comparison']['count']->value)) {
				if ((int)$collectData['collect']['ticker_comparison']['count']->value > 0) {
					$collectData['pagination'] = array(
						'page'		=> (isset($collectData['pgnumber']) ? $collectData['pgnumber'] : 1),
						'start'		=> 0,
					);
					$collectData['pagination']['page'] = (is_numeric($collectData['pagination']['page']) ? sprintf("%d", $collectData['pagination']['page']) : 1);
					if ($collectData['pagination']['page'] > 0) {
						$collectData['pagination']['page'] = (int)$collectData['pagination']['page'];
					} else {
						$collectData['pagination']['page'] = 1;
					}
					$collectData['pagination']['start'] = $this->imzcustom->get_pagination_start($collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['ticker_comparison']['count']->value);
				} else {
					$collectData['pagination'] = array(
						'page'		=> 1,
						'start'		=> 0,
					);
				}
				try {
					$collectData['collect']['ticker_comparison']['data'] = $this->mod_ticker->get_ticker_comparison_data_by($collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'), $collectData['is_enabled']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get ticker-compariosn with exception: {$ex->getMessage()}";
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "Should have value as total rows.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/ticker/listenabled/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['ticker_comparison']['count']->value, $collectData['pagination']['start']);
			$collectData['page'] = 'cryptocurrency-ticker-listenabled';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/exchange'));
		}
	}
	function addenabled($this_method = 'insert') {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-addenabled',
			'title'					=> 'Add Ticker Comparison',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'this_method'			=> (is_string($this_method) ? strtolower($this_method) : 'insert'),
		);
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_dashboard_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		try {
			$collectData['collect']['currencies'] = $this->mod_ticker->get_all_currencies('currencies', TRUE);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Error exception while get all available crypto-currencies.";
		}
		if (!$this->error) {
			$collectData['collect']['units'] = $this->mod_ticker->get_enabled_units();
			try {
				$collectData['collect']['all_tickers'] = $this->mod_currency->get_allmarketplace_tickers();
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get all marketplace tickers with exception: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			if (isset($collectData['collect']['currencies'][0]->currency_code)) {
				try {
					$collectData['collect']['selected_tickers'] = $this->mod_currency->get_marketplace_tickers_by('ticker_currency', strtolower($collectData['collect']['currencies'][0]->currency_code));
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot get selected tickers from first units row with exception: {$ex->getMessage()}";
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "First units row not exists.";
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['market_real_currencies'] = array(
					'from'			=> $this->mod_ticker->get_all_currencies('real', TRUE),
					'to'			=> $this->mod_ticker->get_all_currencies('real', TRUE),
				);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get real currencies enabled on marketplace with exception: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			switch (strtolower($collectData['this_method'])) {
				case 'action':
					$this->form_validation->set_rules('enabled_crypto_code', 'Ticker Cryptocurrency Code', 'required|max_length[4]|trim|xss_clean');
					$this->form_validation->set_rules('enabled_from_realcurrency', 'Real Currency Code', 'required|max_length[3]|trim|xss_clean');
					$this->form_validation->set_rules('enabled_is_active', 'Ticker Comparison is Active', 'max_length[1]|trim|xss_clean');
					$this->form_validation->set_rules('enabled_unit_name', 'Ticker Comparison Unit Name', 'required|max_length[16]');
					$this->form_validation->set_rules('enabled_unit_amount', 'Ticker Comparison Unit Amount', 'required|numeric|trim|max_length[3]');
					$this->form_validation->set_rules('enabled_comparison_limit_min', 'Ticker Comparison Premium Limit Minimum', 'required|numeric|trim|max_length[6]');
					$this->form_validation->set_rules('enabled_comparison_limit_max', 'Ticker Comparison Premium Limit Maximum', 'required|numeric|trim|max_length[6]');
					$this->form_validation->set_rules('ticker_comparison_from', 'Comparion From Marketplace', 'required|numeric|trim|max_length[2]');
					$this->form_validation->set_rules('ticker_comparison_to', 'Comparion To Marketplace', 'required|numeric|trim|max_length[2]');
					if ($this->form_validation->run() == FALSE) {
						$this->error = true;
						$this->error_msg[] = "Form validation return error.";
						$collectData['collect']['form_validation'] = validation_errors('<div>', '</div>');
						$this->session->set_flashdata('error', TRUE);
						$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
						
						
						redirect(base_url("{$collectData['base_path']}/ticker/listenabled"));
						exit;
					} else {
						$collectData['query_params'] = array();
						$collectData['input_params'] = array(
							'enabled_crypto_code' => $this->input->post('enabled_crypto_code'),
							'enabled_from_realcurrency' => $this->input->post('enabled_from_realcurrency'),
							'enabled_is_active' => $this->input->post('enabled_is_active'),
							'enabled_unit_name' => $this->input->post('enabled_unit_name'),
							'enabled_unit_amount' => $this->input->post('enabled_unit_amount'),
							'enabled_comparison_limit_min' => $this->input->post('enabled_comparison_limit_min'),
							'enabled_comparison_limit_max' => $this->input->post('enabled_comparison_limit_max'),
							'ticker_comparison_from' => $this->input->post('ticker_comparison_from'),
							'ticker_comparison_to' => $this->input->post('ticker_comparison_to'),
						);
						//----
						$collectData['query_params']['cryptocurrency_code'] = (is_string($collectData['input_params']['enabled_crypto_code']) ? strtoupper($collectData['input_params']['enabled_crypto_code']) : '');
						$collectData['query_params']['cryptocurrency_from_realcurrency'] = (is_string($collectData['input_params']['enabled_from_realcurrency']) ? strtoupper($collectData['input_params']['enabled_from_realcurrency']) : '');
						$collectData['input_params']['enabled_unit_name'] = (is_string($collectData['input_params']['enabled_unit_name']) || is_numeric($collectData['input_params']['enabled_unit_name'])) ? strtolower($collectData['input_params']['enabled_unit_name']) : 'hour';
						if (isset($collectData['input_params']['enabled_is_active'])) {
							if (!in_array($collectData['input_params']['enabled_is_active'], array('Y', 'N'))) {
								$collectData['query_params']['cryptocurrency_is_enabled'] = 'N';
							} else {
								$collectData['query_params']['cryptocurrency_is_enabled'] = strtoupper($collectData['input_params']['enabled_is_active']);
							}
						} else {
							$collectData['query_params']['cryptocurrency_is_enabled'] = 'N';
						}
						$collectData['unit_codes'] = array();
						if (is_array($collectData['collect']['units']) && (count($collectData['collect']['units']) > 0)) {
							foreach ($collectData['collect']['units'] as $unitval) {
								$collectData['unit_codes'][] = $unitval['code'];
							}
						}
						if (!in_array($collectData['input_params']['enabled_unit_name'], $collectData['unit_codes'])) {
							$collectData['query_params']['cryptocurrency_compare_unit'] = 'hour';
						} else {
							$collectData['query_params']['cryptocurrency_compare_unit'] = strtolower($collectData['input_params']['enabled_unit_name']);
						}
						if (is_numeric($collectData['input_params']['enabled_unit_amount'])) {
							$collectData['query_params']['cryptocurrency_compare_amount'] = sprintf("%d", $collectData['input_params']['enabled_unit_amount']);
						} else {
							$collectData['query_params']['cryptocurrency_compare_amount'] = 0;
						}
						if (is_numeric($collectData['input_params']['enabled_comparison_limit_min'])) {
							$collectData['query_params']['cryptocurrency_premium_limit_min'] = sprintf("%.02f", $collectData['input_params']['enabled_comparison_limit_min']);
						} else {
							$collectData['query_params']['cryptocurrency_premium_limit_min'] = 0;
						}
						if (is_numeric($collectData['input_params']['enabled_comparison_limit_max'])) {
							$collectData['query_params']['cryptocurrency_premium_limit_max'] = sprintf("%.02f", $collectData['input_params']['enabled_comparison_limit_max']);
						} else {
							$collectData['query_params']['cryptocurrency_premium_limit_max'] = 0;
						}
						if (is_numeric($collectData['input_params']['ticker_comparison_from']) && is_numeric($collectData['input_params']['ticker_comparison_to'])) {
							$collectData['query_params']['cryptocurrency_compare_ticker_seq_from'] = sprintf("%d", $collectData['input_params']['ticker_comparison_from']);
							$collectData['query_params']['cryptocurrency_compare_ticker_seq_to'] = sprintf("%d", $collectData['input_params']['ticker_comparison_to']);
						} else {
							$collectData['query_params']['cryptocurrency_compare_ticker_seq_from'] = 0;
							$collectData['query_params']['cryptocurrency_compare_ticker_seq_to'] = 0;
						}
						//----
						if (strlen($collectData['query_params']['cryptocurrency_code']) > 0) {
							try {
								$collectData['collect']['enabled_data'] = $this->mod_ticker->get_enabled_data_single_by('code', $collectData['query_params']['cryptocurrency_code']);
							} catch (Exception $ex) {
								$this->error = true;
								$this->error_msg[] = "Error exception while get data of enabled data to checking before insert: {$ex->getMessage()}";
							}
						} else {
							$this->error = true;
							$this->error_msg[] = "Empty cryptocurrency code inputted.";
						}
					}
				break;
				case 'ajaxrequest':
					$this->form_validation->set_rules('enabled_crypto_code', 'Ticker Cryptocurrency Code', 'required|max_length[6]|trim|xss_clean');
					if ($this->form_validation->run() == FALSE) {
						$this->error = true;
						$this->error_msg[] = "Form validation return error.";
						//$this->session->set_flashdata('error', TRUE);
						//$this->session->set_flashdata('action_message', validation_errors('<div>', '</div>'));
					} else {
						$collectData['ticker_currency'] = $this->input->post('enabled_crypto_code');
						$collectData['ticker_currency'] = (is_string($collectData['ticker_currency']) || is_numeric($collectData['ticker_currency'])) ? sprintf("%s", $collectData['ticker_currency']) : '';
						$collectData['ticker_currency'] = strtolower($collectData['ticker_currency']);
						if (strlen($collectData['ticker_currency']) > 0) {
							try {
								$collectData['collect']['selected_tickers'] = $this->mod_currency->get_marketplace_tickers_by('ticker_currency', $collectData['ticker_currency']);
							} catch (Exception $ex) {
								$this->error = true;
								$this->error_msg[] = "Cannot get selected tickers with exception: {$ex->getMessage()}";
							}
						} else {
							$collectData['collect']['selected_tickers'] = FALSE;
						}
					}
				break;
			}
		}
		if (!$this->error) {
			switch (strtolower($collectData['this_method'])) {
				case 'action':
					if (!isset($collectData['collect']['enabled_data']->seq)) {
						//=========================
						// Insert Ticker Comparison
						try {
							$collectData['insert_ticker_comparison_seq'] = $this->mod_ticker->insert_enabled_data_single_by($collectData['query_params']);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Error exception while insert enabled data: {$ex->getMessage()}";
						}
					} else {
						$this->error = true;
						$this->error_msg[] = "Enabled data of cryptocurrency code already exists or enabled.";
					}
				break;
				case 'ajaxrequest':
					if ($collectData['collect']['selected_tickers'] != FALSE) {
						if (is_array($collectData['collect']['selected_tickers']) && (count($collectData['collect']['selected_tickers']) > 0)) {
							foreach ($collectData['collect']['selected_tickers'] as $alltickerval) {
								?>
								<option value="<?=$alltickerval->seq;?>"><?= sprintf("%s - %s - [%s]", $alltickerval->market_name, strtoupper($alltickerval->ticker_currency_from), strtoupper($alltickerval->ticker_currency_to));?></option>
								<?php
							}
						}
					} else {
						?>
						<option value="0"> -- No ticker available --</option>
						<?php
					}
					exit;
				break;
			}
		}
		if (!$this->error) {
			switch (strtolower($collectData['this_method'])) {
				case 'action':
					$this->session->set_flashdata('error', FALSE);
					$this->session->set_flashdata('action_message', 'Success add new comparison tickers.');
					redirect(base_url($collectData['base_path'] . '/ticker/listenabled'));
					exit;
				break;
				case 'insert':
				default:
					$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
				break;
			}
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/ticker/listenabled'));
			exit;
		}
	}
	function editenabled($enable_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-editenabled',
			'title'					=> 'Edit Enabled Ticker Comparison',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'enable_seq'			=> (is_numeric($enable_seq) ? $enable_seq : 0),
		);
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_dashboard_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		try {
			$collectData['collect']['currencies'] = $this->mod_ticker->get_all_currencies('currencies', TRUE);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Error exception while get all available crypto-currencies.";
		}
		if (!$this->error) {
			try {
				$collectData['collect']['enabled_data'] = $this->mod_ticker->get_enabled_data_single_by('seq', $collectData['enable_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get data of enabled to editing.";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['enabled_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Enabled data not exists or enabled.";
			} else {
				$collectData['collect']['units'] = $this->mod_ticker->get_enabled_units();
				try {
					$collectData['collect']['all_tickers'] = $this->mod_currency->get_allmarketplace_tickers();
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot get all marketplace tickers with exception: {$ex->getMessage()}";
				}
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['market_real_currencies'] = array(
					'from'			=> $this->mod_ticker->get_all_currencies('real', TRUE, $collectData['collect']['enabled_data']->ticker_data->from_market_seq),
					'to'			=> $this->mod_ticker->get_all_currencies('real', TRUE, $collectData['collect']['enabled_data']->ticker_data->to_market_seq),
				);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get real currencies enabled on marketplace with exception: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['selected_tickers'] = $this->mod_currency->get_marketplace_tickers_by('ticker_currency', strtolower($collectData['collect']['enabled_data']->cryptocurrency_code));
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get selected tickers with exception: {$ex->getMessage()}";
			}
		}
		
		
		if (!$this->error) {
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/ticker/listenabled'));
		}
	}
	function editenabledaction($enable_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-addenabled',
			'title'					=> 'Edit Enabled Ticker Comparison',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'enable_seq'			=> (is_numeric($enable_seq) ? $enable_seq : 0),
		);
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_dashboard_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================	
		if (!$this->error) {
			try {
				$collectData['collect']['enabled_data'] = $this->mod_ticker->get_enabled_data_single_by('seq', $collectData['enable_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get data of enabled to editing: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['enabled_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Enabled data not exists or enabled.";
			} else {
				$this->form_validation->set_rules('enabled_is_active', 'Ticker Comparison is Active', 'max_length[1]|trim|xss_clean');
				$this->form_validation->set_rules('enabled_unit_name', 'Ticker Comparison Unit Name', 'required|max_length[16]');
				$this->form_validation->set_rules('enabled_unit_amount', 'Ticker Comparison Unit Amount', 'required|numeric|trim|max_length[3]');
				$this->form_validation->set_rules('enabled_comparison_limit_min', 'Ticker Comparison Premium Limit Minimum', 'required|numeric|trim|max_length[6]');
				$this->form_validation->set_rules('enabled_comparison_limit_max', 'Ticker Comparison Premium Limit Maximum', 'required|numeric|trim|max_length[6]');
				$this->form_validation->set_rules('ticker_comparison_from', 'Comparion From Marketplace', 'required|numeric|trim|max_length[2]');
				$this->form_validation->set_rules('ticker_comparison_to', 'Comparion To Marketplace', 'required|numeric|trim|max_length[2]');
				if ($this->form_validation->run() == FALSE) {
					$this->error = true;
					$this->error_msg[] = "Form validation return error.";
					$collectData['collect']['form_validation'] = validation_errors('<div>', '</div>');
					$this->session->set_flashdata('error', TRUE);
					$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
					
					
					redirect(base_url("{$collectData['base_path']}/ticker/editenabled/{$collectData['collect']['enabled_data']->seq}"));
					exit;
				}
			}
		}
		if (!$this->error) {
			$collectData['query_params'] = array();
			$collectData['input_params'] = array(
				'enabled_is_active' => $this->input->post('enabled_is_active'),
				'enabled_unit_name' => $this->input->post('enabled_unit_name'),
				'enabled_unit_amount' => $this->input->post('enabled_unit_amount'),
				'enabled_comparison_limit_min' => $this->input->post('enabled_comparison_limit_min'),
				'enabled_comparison_limit_max' => $this->input->post('enabled_comparison_limit_max'),
				'ticker_comparison_from' => $this->input->post('ticker_comparison_from'),
				'ticker_comparison_to' => $this->input->post('ticker_comparison_to'),
			);
			$collectData['input_params']['enabled_unit_name'] = (is_string($collectData['input_params']['enabled_unit_name']) || is_numeric($collectData['input_params']['enabled_unit_name'])) ? strtolower($collectData['input_params']['enabled_unit_name']) : 'hour';
			if (isset($collectData['input_params']['enabled_is_active'])) {
				if (!in_array($collectData['input_params']['enabled_is_active'], array('Y', 'N'))) {
					$collectData['query_params']['cryptocurrency_is_enabled'] = 'N';
				} else {
					$collectData['query_params']['cryptocurrency_is_enabled'] = strtoupper($collectData['input_params']['enabled_is_active']);
				}
			} else {
				$collectData['query_params']['cryptocurrency_is_enabled'] = 'N';
			}
			$collectData['unit_codes'] = array();
			$collectData['collect']['units'] = $this->mod_ticker->get_enabled_units();
			if (is_array($collectData['collect']['units']) && (count($collectData['collect']['units']) > 0)) {
				foreach ($collectData['collect']['units'] as $unitval) {
					$collectData['unit_codes'][] = $unitval['code'];
				}
			}
			if (!in_array($collectData['input_params']['enabled_unit_name'], $collectData['unit_codes'])) {
				$collectData['query_params']['cryptocurrency_compare_unit'] = 'hour';
			} else {
				$collectData['query_params']['cryptocurrency_compare_unit'] = strtolower($collectData['input_params']['enabled_unit_name']);
			}
			if (is_numeric($collectData['input_params']['enabled_unit_amount'])) {
				$collectData['query_params']['cryptocurrency_compare_amount'] = sprintf("%d", $collectData['input_params']['enabled_unit_amount']);
			} else {
				$collectData['query_params']['cryptocurrency_compare_amount'] = 0;
			}
			if (is_numeric($collectData['input_params']['enabled_comparison_limit_min'])) {
				$collectData['query_params']['cryptocurrency_premium_limit_min'] = sprintf("%.02f", $collectData['input_params']['enabled_comparison_limit_min']);
			} else {
				$collectData['query_params']['cryptocurrency_premium_limit_min'] = 0;
			}
			if (is_numeric($collectData['input_params']['enabled_comparison_limit_max'])) {
				$collectData['query_params']['cryptocurrency_premium_limit_max'] = sprintf("%.02f", $collectData['input_params']['enabled_comparison_limit_max']);
			} else {
				$collectData['query_params']['cryptocurrency_premium_limit_max'] = 0;
			}
			if (is_numeric($collectData['input_params']['ticker_comparison_from']) && is_numeric($collectData['input_params']['ticker_comparison_to'])) {
				$collectData['query_params']['cryptocurrency_compare_ticker_seq_from'] = sprintf("%d", $collectData['input_params']['ticker_comparison_from']);
				$collectData['query_params']['cryptocurrency_compare_ticker_seq_to'] = sprintf("%d", $collectData['input_params']['ticker_comparison_to']);
			} else {
				$collectData['query_params']['cryptocurrency_compare_ticker_seq_from'] = 0;
				$collectData['query_params']['cryptocurrency_compare_ticker_seq_to'] = 0;
			}
			//=========================
			// Update Ticker Comparison
			try {
				$collectData['affected_updated_rows'] = $this->mod_ticker->set_enabled_data_single_by('seq', $collectData['collect']['enabled_data']->seq, $collectData['query_params']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while set enabled data: {$ex->getMessage()}";
			}
		}
		if (!$this->error) {
			if ((int)$collectData['affected_updated_rows'] > 0) {
				// Redirect to list of enabled ticker comparison
				$this->session->set_flashdata('error', FALSE);
				$this->session->set_flashdata('action_message', 'Success editing enabled ticker comparison');
				redirect(base_url($collectData['base_path'] . '/ticker/listenabled'));
			} else {
				$this->error = true;
				$this->error_msg[] = "No affected rows while update.";
			}
		}
		if ($this->error) {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/ticker/listenabled'));
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	//============================
	function listemail() {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-addenabled',
			'title'					=> 'Edit Enabled Ticker Comparison',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
		);
		
		
		
		echo "Enabled Email";
		
	}
	//------------------------------------------------------------------------------------------------------
	function emailaddress($this_method = 'view', $pgnumber = 0) {
		$collectData = array(
			'this_method'			=> (is_string($this_method) ? strtolower($this_method) : 'view'),
			'page'					=> 'cryptocurrency-ticker-email-address',
			'title'					=> 'Email Address',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'pgnumber'				=> (is_numeric($pgnumber) ? $pgnumber : 0),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) ? $collectData['search_text'] : '');
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_dashboard_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		if (!$this->error) {
			switch ($collectData['this_method']) {
				case 'edit':
				case 'delete':
				case 'editaction':
				case 'deleteaction':
					$collectData['page'] = 'cryptocurrency-ticker-email-address-edit';
				break;
				case 'insert':
				case 'insertaction':
					$collectData['page'] = 'cryptocurrency-ticker-email-address-insert';
				break;
				case 'view':
				default:
					$collectData['page'] = 'cryptocurrency-ticker-email-address-view';
				break;
			}
		}
		if (!$this->error) {
			switch ($collectData['this_method']) {
				case 'edit':
				case 'delete':
				case 'editaction':
				case 'deleteaction':
					try {
						$collectData['collect']['email_single_data'] = $this->mod_ticker->get_email_address_single_by('seq', $collectData['pgnumber']);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception while get email single data: {$ex->getMessage()}.";
					}
				break;
				case 'insert':
				case 'insertaction':
					// Nothing to do
				break;
				case 'view':
				default:
					try {
						$collectData['collect']['email_data'] = array(
							'count'			=> $this->mod_ticker->get_email_address_count(0, $collectData['search_text']),
						);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception while get count email address: {$ex->getMessage()}.";
					}
				break;
			}
		}
		if (!$this->error) {
			switch ($collectData['this_method']) {
				case 'edit':
				case 'delete':
					if (!isset($collectData['collect']['email_single_data']->seq)) {
						$this->error = true;
						$this->error_msg[] = "Email address single data not exists.";
					}
				break;
				case 'editaction':
					if (!isset($collectData['collect']['email_single_data']->seq)) {
						$this->error = true;
						$this->error_msg[] = "Email address single data not exists.";
					} else {
						$this->form_validation->set_rules('email_name', 'Email Name', 'required|max_length[64]');
						$this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|max_length[128]|valid_email|xss_clean');
						$this->form_validation->set_rules('email_is_enabled', 'Email is Enabled', 'max_length[1]|trim|xss_clean');
						if ($this->form_validation->run() == FALSE) {
							$this->error = true;
							$this->error_msg[] = "Form validation return error.";
							$this->session->set_flashdata('error', TRUE);
							$this->session->set_flashdata('action_message', validation_errors('<div class="btn btn-warning btn-sm">', '</div>'));
							redirect(base_url($collectData['base_path'] . '/ticker/emailaddress/view'));
							exit;
						} else {
							$collectData['query_params'] = array(
								'email_name'		=> $this->input->post('email_name'),
								'email_address'		=> $this->input->post('email_address'),
								'email_is_enabled'	=> $this->input->post('email_is_enabled'),
							);
							$collectData['query_params']['email_name'] = (is_string($collectData['query_params']['email_name']) || is_numeric($collectData['query_params']['email_name'])) ? sprintf("%s", $collectData['query_params']['email_name']) : '';
							$collectData['query_params']['email_address'] = (is_string($collectData['query_params']['email_address']) || is_numeric($collectData['query_params']['email_address'])) ? sprintf("%s", $collectData['query_params']['email_address']) : '';
							if (is_string($collectData['query_params']['email_is_enabled']) || is_numeric($collectData['query_params']['email_is_enabled'])) {
								$collectData['query_params']['email_is_enabled'] = strtoupper($collectData['query_params']['email_is_enabled']);
								if (!in_array($collectData['query_params']['email_is_enabled'], array('Y', 'N'))) {
									$collectData['query_params']['email_is_enabled'] = 'N';
								}
							}
						}
					}
				break;
				case 'deleteaction':
					if (!isset($collectData['collect']['email_single_data']->seq)) {
						$this->error = true;
						$this->error_msg[] = "Email address single data not exists.";
					} else {
						$this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|max_length[128]|valid_email|xss_clean');
						if ($this->form_validation->run() == FALSE) {
							$this->error = true;
							$this->error_msg[] = "Form validation return error.";
							$this->session->set_flashdata('error', TRUE);
							$this->session->set_flashdata('action_message', validation_errors('<div class="btn btn-warning btn-sm">', '</div>'));
							redirect(base_url($collectData['base_path'] . '/ticker/emailaddress/view'));
							exit;
						} else {
							$collectData['query_params'] = array(
								'email_address'		=> $this->input->post('email_address'),
							);
							$collectData['query_params']['email_address'] = (is_string($collectData['query_params']['email_address']) || is_numeric($collectData['query_params']['email_address'])) ? sprintf("%s", $collectData['query_params']['email_address']) : '';
						}
					}
				break;
				case 'insert':
					// Nothing to do
				break;
				case 'insertaction':
					$this->form_validation->set_rules('email_name', 'Email Name', 'required|max_length[64]');
					$this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|max_length[128]|valid_email|xss_clean');
					$this->form_validation->set_rules('email_is_active', 'Email is Enabled', 'max_length[1]|trim|xss_clean');
					if ($this->form_validation->run() == FALSE) {
						$this->error = true;
						$this->error_msg[] = "Form validation return error.";
						$this->session->set_flashdata('error', TRUE);
						$this->session->set_flashdata('action_message', validation_errors('<div class="btn btn-warning btn-sm">', '</div>'));
						redirect(base_url($collectData['base_path'] . '/ticker/emailaddress/view'));
						exit;
					} else {
						$collectData['query_params'] = array(
							'email_name'		=> $this->input->post('email_name'),
							'email_address'		=> $this->input->post('email_address'),
							'email_is_enabled'	=> $this->input->post('email_is_enabled'),
						);
						$collectData['query_params']['email_insert'] = $this->DateObject->format('Y-m-d H:i:s');
						$collectData['query_params']['email_update'] = $this->DateObject->format('Y-m-d H:i:s');
						$collectData['query_params']['email_name'] = (is_string($collectData['query_params']['email_name']) || is_numeric($collectData['query_params']['email_name'])) ? sprintf("%s", $collectData['query_params']['email_name']) : '';
						$collectData['query_params']['email_address'] = (is_string($collectData['query_params']['email_address']) || is_numeric($collectData['query_params']['email_address'])) ? sprintf("%s", $collectData['query_params']['email_address']) : '';
						if (is_string($collectData['query_params']['email_is_enabled']) || is_numeric($collectData['query_params']['email_is_enabled'])) {
							$collectData['query_params']['email_is_enabled'] = strtoupper($collectData['query_params']['email_is_enabled']);
							if (!in_array($collectData['query_params']['email_is_enabled'], array('Y', 'N'))) {
								$collectData['query_params']['email_is_enabled'] = 'N';
							}
						}
					}
				break;
				case 'view':
				default:
					if (isset($collectData['collect']['email_data']['count']->value)) {
						if ((int)$collectData['collect']['email_data']['count']->value > 0) {
							$collectData['pagination'] = array(
								'page'		=> (isset($collectData['pgnumber']) ? $collectData['pgnumber'] : 1),
								'start'		=> 0,
							);
							$collectData['pagination']['page'] = (is_numeric($collectData['pagination']['page']) ? sprintf("%d", $collectData['pagination']['page']) : 1);
							if ($collectData['pagination']['page'] > 0) {
								$collectData['pagination']['page'] = (int)$collectData['pagination']['page'];
							} else {
								$collectData['pagination']['page'] = 1;
							}
							$collectData['pagination']['start'] = $this->imzcustom->get_pagination_start($collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['email_data']['count']->value);
						} else {
							$collectData['pagination'] = array(
								'page'		=> 1,
								'start'		=> 0,
							);
						}
					} else {
						$this->error = true;
						$this->error_msg[] = "Should have value as total rows.";
					}
				break;
			}
		}
		if (!$this->error) {
			switch ($collectData['this_method']) {
				case 'insert':
					$this->load->view($collectData['base_path'] . '/cryptocurrency.php', $collectData);
				break;
				case 'edit':
					$this->load->view($collectData['base_path'] . '/ticker/ticker-email-address-modal-edit.php', $collectData);
				break;
				case 'delete':
					$this->load->view($collectData['base_path'] . '/ticker/ticker-email-address-modal-delete.php', $collectData);
				break;
				case 'deleteaction':
					if (strtolower($collectData['query_params']['email_address']) !== $collectData['collect']['email_single_data']->email_address) {
						$this->error = true;
						$this->error_msg[] = "Input email address not same with deleted email address data.";
					} else {
						try {
							$collectData['email_address_seq'] = $this->mod_ticker->delete_email_address_single_by('seq', $collectData['collect']['email_single_data']->seq);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Exception error while deleting email address single data: {$ex->getMessage()}.";
						}
					}
				break;
				case 'editaction':
					try {
						$collectData['email_address_seq'] = $this->mod_ticker->set_email_address_single_by($collectData['collect']['email_single_data']->seq, $collectData['query_params']);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Exception error while editing email address single data: {$ex->getMessage()}.";
					}
				break;
				case 'insertaction':
					try {
						$collectData['email_address_single_exists'] = $this->mod_ticker->get_email_address_single_by('address', $collectData['query_params']['email_address']);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Cannot check if email-address already exists or not with exception: {$ex->getMessage()}.";
					}
				break;
				case 'view':
				default:
					$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/ticker/emailaddress/{$collectData['this_method']}/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['email_data']['count']->value, $collectData['pagination']['start']);
					try {
						$collectData['collect']['email_data']['data'] = $this->mod_ticker->get_email_address_data(0, $collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'));
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error while get all email address with exception: {$ex->getMessage()}";
					}
				break;
			}
		}
		if (!$this->error) {
			switch (strtolower($collectData['this_method'])) {
				case 'deleteaction':
					if ($collectData['email_address_seq'] > 0) {
						$this->session->set_flashdata('error', FALSE);
						$this->session->set_flashdata('action_message', 'Success delete email address.');
					} else {
						$this->session->set_flashdata('error', TRUE);
						$this->session->set_flashdata('action_message', 'Failed to delete email address');
					}
					redirect(base_url($collectData['base_path'] . '/ticker/emailaddress/view'));
					exit;
				break;
				case 'editaction':
					if ($collectData['email_address_seq'] > 0) {
						$this->session->set_flashdata('error', FALSE);
						$this->session->set_flashdata('action_message', 'Success edit email address.');
					} else {
						$this->session->set_flashdata('error', TRUE);
						$this->session->set_flashdata('action_message', 'Failed to edit email address');
					}
					redirect(base_url($collectData['base_path'] . '/ticker/emailaddress/view'));
					exit;
				break;
				case 'insertaction':
					if (!isset($collectData['email_address_single_exists']->seq)) {
						try {
							$collectData['email_address_seq'] = $this->mod_ticker->insert_email_address_single_by($collectData['query_params']);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Exception error while inserting email address single data: {$ex->getMessage()}.";
						}
					} else {
						$this->error = true;
						$this->error_msg[] = "Email address input already exists on database at <a class='btn-modal-view-item' href='" . base_url($collectData['base_path'] . '/ticker/emailaddress/edit/' . $collectData['email_address_single_exists']->seq) . "'>here</a>";
					}
				break;
			}
		}
		if (!$this->error) {
			if (strtolower($collectData['this_method']) === 'insertaction') {
				if ($collectData['email_address_seq'] > 0) {
					$this->session->set_flashdata('error', FALSE);
					$this->session->set_flashdata('action_message', 'Success insert email address.');
				} else {
					$this->session->set_flashdata('error', TRUE);
					$this->session->set_flashdata('action_message', 'Failed to insert email address');
				}
				redirect(base_url($collectData['base_path'] . '/ticker/emailaddress/view'));
				exit;
			} else if (strtolower($collectData['this_method']) === 'view') {
				$this->load->view($collectData['base_path'] . '/cryptocurrency.php', $collectData);
			} else {
				// Nothing to do
			}
		} else {
			//=== Error persist
			$error_message = "";
			foreach ($this->error_msg as $msg) {
				$error_message .= $msg . "<br/>";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $error_message);
			redirect(base_url("{$collectData['base_path']}/ticker/emailaddress/view"));
			exit;
		}
		
		
		
		
		
		
		
		
		
		
		
		
	}
	function emailtemplates($detail_code = 'all') {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-email-templates',
			'title'					=> 'Email Templates',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'detail_code'			=> (is_string($detail_code) ? strtolower($detail_code) : 'all'),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) ? $collectData['search_text'] : '');
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		$collectData['allowed_codes'] = array('waiting', 'approved', 'canceled', 'deleted', 'already', 'failed');
		if (!in_array($collectData['detail_code'], $collectData['allowed_codes'])) {
			$collectData['detail_code'] = 'all';
		}
		$collectData['collect']['auto_approve_description'] = $this->mod_ticker->get_email_templates_by('code', $collectData['detail_code']);
		
		if (!$this->error) {
			$this->load->view("{$this->base_cryptocurrency['base_path']}/cryptocurrency.php", $collectData);
		} else {
			$this->session->set_flashdata('error', TRUE);
			$action_message = "";
			foreach ($this->error_msg as $error_msg) {
				$action_message .= "- {$error_msg}<br/>";
			}
			$this->session->set_flashdata('action_message', $action_message);
			redirect(base_url("{$collectData['base_path']}/ticker/emailtemplates/all"));
			exit;
		}
	}
	function emailtemplatesaction($detail_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-ticker-email-templates',
			'title'					=> 'Email Templates',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'detail_seq'			=> (is_numeric($detail_seq) ? (int)$detail_seq : 0),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) ? $collectData['search_text'] : '');
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$collectData['base_path']}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		if (!$this->error) {
			$collectData['collect']['auto_approve_description'] = $this->mod_ticker->get_email_templates_by('seq', $collectData['detail_seq']);
			if (!isset($collectData['collect']['auto_approve_description']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Auto approve description data not exists on database.";
			}
		}
		if (!$this->error) {
			$this->form_validation->set_rules('email_status_description', 'Deposit description details', 'max_length[20480]');
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = "Form validation return error.";
				$this->session->set_flashdata('error', TRUE);
				$this->session->set_flashdata('action_message', validation_errors('<div class="btn btn-warning btn-sm">', '</div>'));
				redirect(base_url("{$collectData['base_path']}/ticker/emailtemplates/all"));
				exit;
			} else {
				$query_params = array(
					'email_status_description'		=> $this->input->post('email_status_description'),
				);
				try {
					$collectData['update_status_description'] = $this->mod_ticker->set_email_templates($collectData['collect']['auto_approve_description']->seq, $query_params);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while update deposit description: {$ex->getMessage()}";
				}
			}
		}
		if (!$this->error) {
			redirect(base_url($collectData['base_path'] . '/ticker/emailtemplates/' . $collectData['collect']['auto_approve_description']->email_status));
			exit;
		} else {
			$this->error_msg[] = "Form validation return error.";
			$this->session->set_flashdata('error', TRUE);
			$action_message = "";
			foreach ($this->error_msg as $error_msg) {
				$action_message .= "- {$error_msg}<br/>";
			}
			$this->session->set_flashdata('action_message', $action_message);
			redirect(base_url("{$collectData['base_path']}/ticker/emailtemplates/all"));
			exit;
		}
	}
}




















