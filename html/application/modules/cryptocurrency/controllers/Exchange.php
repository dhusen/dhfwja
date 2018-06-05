<?php
// Concept: https://docs.google.com/spreadsheets/d/13SxAErn4fCy1ktEmyUqLVRsgStytFaiAfCHThrE70pk/edit#gid=0
if (!defined('BASEPATH')) { exit('No direct script access allowed: Kraken'); }
class Exchange extends MY_Controller {
	public $is_editor = FALSE;
	public $error = FALSE, $error_msg = array();
	protected $DateObject;
	protected $email_vendor;
	protected $base_dashboard, $base_cryptocurrency = array();
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
		# Load Model Exchange
		$this->load->model('cryptocurrency/Model_exchange', 'mod_exchange');
		
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
	function index() {
		$this->listexchange('usd-idr', 0);
	}
	function listexchange($exchange = 'all', $pgnumber = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-list',
			'title'					=> 'Real Currency Exchange',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'exchange'				=> (is_numeric($exchange) || is_string($exchange)) ? sprintf("%s", $exchange) : 'usd-idr',
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
		//=================================
		$collectData['exchange'] = strtolower($collectData['exchange']);
		$collectData['exchange'] = str_replace("_", "-", $collectData['exchange']);
		$collectData['exchange'] = base_safe_text($collectData['exchange'], 32);
		$collectData['collect']['currencies'] = array();
		$collectData['exchange_date'] = (isset($this->imzcustom->php_input_request['body']['exchange_date']) ? $this->imzcustom->php_input_request['body']['exchange_date'] : '');
		$collectData['exchange_date'] = (is_string($collectData['exchange_date']) ? strtolower($collectData['exchange_date']) : '');
		// Set is Active Only?
		$collectData['is_active'] = FALSE;
		try {
			$collectData['collect']['currencies']['count'] = $this->mod_exchange->get_exchange_currency_count_by('code', $collectData['exchange'], $collectData['search_text'], $collectData['is_active']);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot count currencies on real-currencies with exceptions: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			if (isset($collectData['collect']['currencies']['count']->value)) {
				if ((int)$collectData['collect']['currencies']['count']->value > 0) {
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
					$collectData['pagination']['start'] = $this->imzcustom->get_pagination_start($collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['currencies']['count']->value);
				} else {
					$collectData['pagination'] = array(
						'page'		=> 1,
						'start'		=> 0,
					);
				}
				try {
					$collectData['collect']['currencies']['data'] = $this->mod_exchange->get_exchange_currency_data_by('code', $collectData['exchange'], $collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'), $collectData['is_active']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get currency data by from-to-string with exception: {$ex->getMessage()}";
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "Should have value as total rows.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/exchange/listexchange/{$collectData['exchange']}/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['currencies']['count']->value, $collectData['pagination']['start']);
			
		}
		//=================================
		if (!$this->error) {
			$collectData['page'] = 'cryptocurrency-exchange-list';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			print_r($this->error_msg);
		}
	}
	function listcurrency($pgnumber = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-listcurrency',
			'title'					=> 'Real Currencies',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
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
		//=================================
		$collectData['collect']['currencies'] = array();
		// Set is Active Only?
		$collectData['is_active'] = FALSE;
		try {
			$collectData['collect']['currencies']['count'] = $this->mod_exchange->get_real_currency_count_by($collectData['search_text'], $collectData['is_active']);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot count real-currencies with exceptions: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			if (isset($collectData['collect']['currencies']['count']->value)) {
				if ((int)$collectData['collect']['currencies']['count']->value > 0) {
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
					$collectData['pagination']['start'] = $this->imzcustom->get_pagination_start($collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['currencies']['count']->value);
				} else {
					$collectData['pagination'] = array(
						'page'		=> 1,
						'start'		=> 0,
					);
				}
				try {
					$collectData['collect']['currencies']['data'] = $this->mod_exchange->get_real_currency_data_by($collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'), $collectData['is_active']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get real-currencies with exception: {$ex->getMessage()}";
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "Should have value as total rows.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/exchange/listcurrency/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['currencies']['count']->value, $collectData['pagination']['start']);
			$collectData['page'] = 'cryptocurrency-exchange-listcurrency';
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
		//=================================
	}
	function addcurrency() {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-addcurrency',
			'title'					=> 'Add Currency Exchange',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
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
		if (!$this->error) {
			$collectData['collect']['countries'] = $this->mod_exchange->get_helper_country();
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
			redirect(base_url($collectData['base_path'] . '/exchange/listcurrency'));
		}
	}
	function addcurrencyaction() {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-addcurrency',
			'title'					=> 'Add Currency Exchange',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
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
		if (!$this->error) {
			$this->form_validation->set_rules('currency_code', 'Currency Code', 'required|min_length[3]|max_length[3]|xss_clean');
			$this->form_validation->set_rules('currency_is_active', 'Enabled Real Currency is Active', 'max_length[1]|trim|xss_clean');
			$this->form_validation->set_rules('currency_country_code', 'Country Code', 'required|min_length[2]|max_length[2]|trim|xss_clean');
			//================================================================
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = validation_errors('<div class="alert alert-sm alert-warning">', '</div>');
			} else {
				$collectData['input_params'] = array(
					'currency_code'				=> $this->input->post('currency_code'),
					'currency_is_active'		=> $this->input->post('currency_is_active'),
					'currency_country_code'		=> $this->input->post('currency_country_code'),
				);
				if ($collectData['input_params']['currency_code'] != FALSE) {
					$collectData['input_params']['currency_code'] = (is_string($collectData['input_params']['currency_code']) || is_numeric($collectData['input_params']['currency_code'])) ? sprintf("%s", $collectData['input_params']['currency_code']) : "";
					$collectData['input_params']['currency_code'] = strtoupper($collectData['input_params']['currency_code']);
					if (strlen($collectData['input_params']['currency_code']) === 0) {
						$this->error = true;
						$this->error = "Currency code cannot be empty.";
					} else {
						try {
							$collectData['collect']['real_currency_data'] = $this->mod_exchange->get_real_currencies_by('code', $collectData['input_params']['currency_code'], 0);
						} catch (Exception $ex) {
							$this->error = true;
							$this->error_msg[] = "Error exception while check if currency code already exists or not: {$ex->getMessage()}.";
						}
					}
				} else {
					$this->error = true;
					$this->error_msg[] = "Currency code cannot be false.";
				}
				$collectData['input_params']['currency_country_code'] = (is_string($collectData['input_params']['currency_country_code']) || is_numeric($collectData['input_params']['currency_country_code'])) ? sprintf("%s", $collectData['input_params']['currency_country_code']) : '';
				if ($collectData['input_params']['currency_is_active'] != FALSE) {
					$collectData['input_params']['currency_is_active'] = 'Y';
				} else {
					$collectData['input_params']['currency_is_active'] = 'N';
				}
			}
		}
		if (!$this->error) {
			if (isset($collectData['collect']['real_currency_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Real currency data already exists, please edit it at <a href='" . base_url($collectData['base_path'] . '/exchange/editcurrency/' . $collectData['collect']['real_currency_data']->seq) . "'>here</a>";
			} else {
				try {
					$collectData['country_currency'] = $this->mod_exchange->get_helper_realcurrency_by('code', $collectData['input_params']['currency_code'], $collectData['input_params']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Exception error while check currency from helper country: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			if (!isset($collectData['country_currency']->ID)) {
				$this->error = true;
				$this->error_msg[] = "Real currency is not valid currency-code or country-code.";
			} else {
				$collectData['query_params'] = array(
					'currency_code'					=> (isset($collectData['country_currency']->currency_code) ? $collectData['country_currency']->currency_code : ''),
					'currency_name'					=> (isset($collectData['country_currency']->currency_name) ? $collectData['country_currency']->currency_name : ''),
					'currency_country_code'			=> (isset($collectData['country_currency']->code) ? $collectData['country_currency']->code : ''),
					'currency_country_name'			=> (isset($collectData['country_currency']->name) ? $collectData['country_currency']->name : ''),
					'currency_datetime_insert'		=> $this->DateObject->format('Y-m-d H:i:s'),
					'currency_datetime_update'		=> $this->DateObject->format('Y-m-d H:i:s'),
					'currency_is_active'			=> strtoupper($collectData['input_params']['currency_is_active']),
				);
				try {
					$collectData['insert_new_realcurrency_seq'] = $this->mod_exchange->insert_real_currencies($collectData['query_params']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while insert new real currency: {$ex->getMessage()}.";
				}
			}
		}
		
		if (!$this->error) {
			$this->session->set_flashdata('error', FALSE);
			$this->session->set_flashdata('action_message', "Success add new real currency.");
			redirect(base_url($collectData['base_path'] . '/exchange/listcurrency'));
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $msg) {
				$action_message_string .= "{$msg}<br/>";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/exchange/listcurrency'));
		}
	}
	function editcurrency($currency_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-editcurrency',
			'title'					=> 'Edit Real Currency',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'currency_seq'			=>(is_numeric($currency_seq) ? (int)$currency_seq : 0),
		);
		//================================================================
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
		$collectData['collect']['countries'] = $this->mod_exchange->get_helper_country();
		try {
			$collectData['collect']['currency_data'] = $this->mod_exchange->get_real_currencies_by('seq', $collectData['currency_seq'], 0);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Error exception while get real-currency data: {$ex->getMessage()}";
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['currency_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Real currency-data not exists on database.";
			}
		}
		
		
		
		
		
		
		if (!$this->error) {
			$collectData['page'] = 'cryptocurrency-exchange-editcurrency';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/exchange/listcurrency'));
		}
	}
	//-------------------------------------------------
	function addexchange() {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-add',
			'title'					=> 'Add Exchange Currency',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) || is_numeric($collectData['search_text'])) ? sprintf("%s", $collectData['search_text']) : '';
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
			$collectData['collect']['currencies'] = $this->mod_exchange->get_real_currencies_is_active();
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Error exception while get all available currencies.";
		}
		if (!$this->error) {
			$collectData['page'] = 'cryptocurrency-exchange-add';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			print_r($this->error_msg);
		}
	}
	function addexchangeaction() {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-add',
			'title'					=> 'Add Exchange Currency',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
		);
		//================================================================
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
		$this->form_validation->set_rules('exchange_date', 'Exchange Date', 'required|max_length[10]|xss_clean');
		$this->form_validation->set_rules('exchange_is_active', 'Exchange is Active', 'max_length[1]|trim|xss_clean');
		$this->form_validation->set_rules('exchange_from', 'Exchange From', 'required|numeric|max_length[2]');
		$this->form_validation->set_rules('exchange_to', 'Exchange To', 'required|numeric|max_length[2]');
		$this->form_validation->set_rules('exchange_amount', 'Exchange Amount', 'required|numeric|trim|max_length[6]');
		if ($this->form_validation->run() == FALSE) {
			$this->error = true;
			$this->error_msg[] = "Form validation return error.";
			$collectData['collect']['form_validation'] = validation_errors('<div>', '</div>');
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
			
			
			redirect(base_url("{$collectData['base_path']}/exchange/addexchange"));
			exit;
		}
		if (!$this->error) {
			$collectData['query_params'] = array();
			$collectData['input_params'] = array(
				'exchange_date' => $this->input->post('exchange_date'),
				'exchange_is_active' => $this->input->post('exchange_is_active'),
				'exchange_from' => $this->input->post('exchange_from'),
				'exchange_to' => $this->input->post('exchange_to'),
				'exchange_amount' => $this->input->post('exchange_amount'),
			);
			if (isset($collectData['input_params']['exchange_is_active'])) {
				if (!in_array($collectData['input_params']['exchange_is_active'], array('Y', 'N'))) {
					$collectData['query_params']['exchange_is_active'] = 'N';
				} else {
					$collectData['query_params']['exchange_is_active'] = strtoupper($collectData['input_params']['exchange_is_active']);
				}
			} else {
				$collectData['query_params']['exchange_is_active'] = 'N';
			}
			try {
				$exchange_date = $this->authentication->create_dateobject(ConstantConfig::$timezone, 'Y-m-d', $collectData['input_params']['exchange_date']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Date format is not valid: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if ($exchange_date === FALSE) {
				$this->error = true;
				$this->error_msg[] = "Date format cannot create dateobject.";
			} else {
				$collectData['query_params']['exchange_date'] = $exchange_date->format('Y-m-d');
				$collectData['from_to'] = array(
					'from'			=> (int)$collectData['input_params']['exchange_from'],
					'to'			=> (int)$collectData['input_params']['exchange_to'],
				);
				try {
					$collectData['exchange_date_data'] = $this->mod_exchange->get_real_currency_exchange_single_by('date', '', $collectData['query_params']['exchange_date'], array('from_to' => $collectData['from_to']));
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while check if date alredy exists or not with exception: {$ex->getMessage()}";
				}
			}
		}
		if (!$this->error) {
			if (isset($collectData['exchange_date_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Exchange date already exists, please edit existing exchange-seq: <a href='" . base_url($collectData['base_path'] . '/exchange/editexchange/' . $collectData['exchange_date_data']->seq) . "'>" . base_url($collectData['base_path'] . '/exchange/editexchange/' . $collectData['exchange_date_data']->seq) . "</a>";
			} else {
				try {
					$collectData['exchange_fromto_data'] = array(
						'from_data'			=> $this->mod_exchange->get_real_currencies_by('seq', $collectData['from_to']['from'], 1),
						'to_data'			=> $this->mod_exchange->get_real_currencies_by('seq', $collectData['from_to']['to'], 1),
					);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get data of currency both from and to with exception: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			if (!isset($collectData['exchange_fromto_data']['from_data']->seq) || !isset($collectData['exchange_fromto_data']['to_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "From-Currency or To-Currency is not exists or inactive.";
			} else {
				$collectData['exchange_fromto_data']['from_to_code'] = array(
					'from'		=> $collectData['exchange_fromto_data']['from_data']->currency_code,
					'to'		=> $collectData['exchange_fromto_data']['to_data']->currency_code,
				);
				// Set Query-params
				$collectData['query_params']['from_seq'] = $collectData['exchange_fromto_data']['from_data']->seq;
				$collectData['query_params']['to_seq'] = $collectData['exchange_fromto_data']['to_data']->seq;
				$collectData['query_params']['from_to_string'] = implode("-", $collectData['exchange_fromto_data']['from_to_code']);
				$collectData['query_params']['from_to_string'] = strtolower($collectData['query_params']['from_to_string']);
				$collectData['query_params']['from_to_json'] = json_encode($collectData['exchange_fromto_data']['from_to_code'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
				$collectData['query_params']['exchange_add_by'] = (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0);
				$collectData['query_params']['exchange_edit_by'] = (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0);
				$collectData['query_params']['exchange_amount_from'] = 1; // Default is 1.00
				$collectData['query_params']['exchange_amount_to'] = round($collectData['input_params']['exchange_amount'], 2, PHP_ROUND_HALF_ODD);
				$collectData['query_params']['exchange_amount_to'] = sprintf("%.02f", $collectData['query_params']['exchange_amount_to']);
				try {
					$collectData['new_insert_exchange_seq'] = $this->mod_exchange->insert_real_currency_exchange_by('code', $collectData['query_params']['from_to_string'], $collectData['query_params']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while insert new exchange data: {$ex->getMessage()}";
				}
			}
		}
		
		if (!$this->error) {
			if ((int)$collectData['new_insert_exchange_seq'] > 0) {
				// Success Insert
				$collectData['redirect_url'] = base_url($collectData['base_path'] . '/exchange/listexchange');
			} else {
				$this->error = true;
				$this->error_msg[] = "Error while insert new exchange data, return zero value.";
			}
		}
		if (!$this->error) {
			redirect($collectData['redirect_url']);
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/exchange/listexchange'));
		}
	}
	function editexchange($exchange_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-edit',
			'title'					=> 'Edit Exchange Currency',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'exchange_seq'			=>(is_numeric($exchange_seq) ? (int)$exchange_seq : 0),
		);
		//================================================================
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
			$collectData['collect']['exchange_data'] = $this->mod_exchange->get_real_currency_exchange_single_by('seq', $collectData['exchange_seq'], $this->DateObject->format('Y-m-d'));
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Error exception while retrieve exchange data by seq: {$ex->getMessage()}";
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['exchange_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Exchange data not exists on database";
			} else {
				try {
					$collectData['collect']['currencies'] = $this->mod_exchange->get_real_currencies_is_active();
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while get all available currencies.";
				}
			}
		}
		//===================
		if (!$this->error) {
			
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			$error_message_string = "";
			foreach ($this->error_msg as $error_msg) {
				$error_message_string .= $error_msg . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $error_message_string);
			redirect(base_url($collectData['base_path'] . '/exchange/listexchange'));
		}
	}
	function editexchangeaction($exchange_seq = 0) {
		$collectData = array(
			'page'					=> 'cryptocurrency-exchange-edit',
			'title'					=> 'Edit Exchange Currency',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'exchange_seq'			=>(is_numeric($exchange_seq) ? (int)$exchange_seq : 0),
		);
		//================================================================
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
		if ($this->authentication->localdata) {
			$collectData['collect']['ggdata'] = $this->authentication->userdata;
			$collectData['collect']['userdata'] = $this->authentication->localdata;
			$collectData['collect']['roles'] = $this->mod_account->get_dashboard_roles();
			$collectData['collect']['match'] = $this->authentication->get_altorouter_match();
		} else {
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		try {
			$collectData['collect']['exchange_data'] = $this->mod_exchange->get_real_currency_exchange_single_by('seq', $collectData['exchange_seq'], $this->DateObject->format('Y-m-d'));
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Error exception while retrieve exchange data by seq: {$ex->getMessage()}";
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['exchange_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Exchange data not exists on database.";
				$this->session->set_flashdata('error', TRUE);
				$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
				redirect(base_url("{$collectData['base_path']}/exchange/listexchange"));
				exit;
			} else {
				$this->form_validation->set_rules('exchange_is_active', 'Exchange is Active', 'max_length[1]|trim|xss_clean');
				$this->form_validation->set_rules('exchange_from', 'Exchange From', 'required|numeric|max_length[2]');
				$this->form_validation->set_rules('exchange_to', 'Exchange To', 'required|numeric|max_length[2]');
				$this->form_validation->set_rules('exchange_amount', 'Exchange Amount', 'required|numeric|trim|max_length[12]');
			}
		}
		if (!$this->error) {
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = "Form validation return error.";
				$collectData['collect']['form_validation'] = validation_errors('<div>', '</div>');
				$this->session->set_flashdata('error', TRUE);
				$this->session->set_flashdata('action_message', $collectData['collect']['form_validation']);
				
				
				redirect(base_url("{$collectData['base_path']}/exchange/editexchange/{$collectData['collect']['exchange_data']->seq}"));
				exit;
			} else {
				$collectData['query_params'] = array();
				$collectData['input_params'] = array(
					'exchange_is_active' => $this->input->post('exchange_is_active'),
					'exchange_from' => $this->input->post('exchange_from'),
					'exchange_to' => $this->input->post('exchange_to'),
					'exchange_amount' => $this->input->post('exchange_amount'),
				);
				if (isset($collectData['input_params']['exchange_is_active'])) {
					if (!in_array($collectData['input_params']['exchange_is_active'], array('Y', 'N'))) {
						$collectData['query_params']['exchange_is_active'] = 'N';
					} else {
						$collectData['query_params']['exchange_is_active'] = strtoupper($collectData['input_params']['exchange_is_active']);
					}
				} else {
					$collectData['query_params']['exchange_is_active'] = 'N';
				}
				$collectData['from_to'] = array(
					'from'			=> (int)$collectData['input_params']['exchange_from'],
					'to'			=> (int)$collectData['input_params']['exchange_to'],
				);
				try {
					$collectData['exchange_fromto_data'] = array(
						'from_data'			=> $this->mod_exchange->get_real_currencies_by('seq', $collectData['from_to']['from'], 1),
						'to_data'			=> $this->mod_exchange->get_real_currencies_by('seq', $collectData['from_to']['to'], 1),
					);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get data of currency both from and to with exception: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			if (!isset($collectData['exchange_fromto_data']['from_data']->seq) || !isset($collectData['exchange_fromto_data']['to_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "From-Currency or To-Currency is not exists or inactive.";
			} else {
				$collectData['exchange_fromto_data']['from_to_code'] = array(
					'from'		=> $collectData['exchange_fromto_data']['from_data']->currency_code,
					'to'		=> $collectData['exchange_fromto_data']['to_data']->currency_code,
				);
				// Set Query-params
				$collectData['query_params']['exchange_date'] = $collectData['collect']['exchange_data']->exchange_date;
				$collectData['query_params']['from_seq'] = $collectData['exchange_fromto_data']['from_data']->seq;
				$collectData['query_params']['to_seq'] = $collectData['exchange_fromto_data']['to_data']->seq;
				$collectData['query_params']['from_to_string'] = implode("-", $collectData['exchange_fromto_data']['from_to_code']);
				$collectData['query_params']['from_to_string'] = strtolower($collectData['query_params']['from_to_string']);
				$collectData['query_params']['from_to_json'] = json_encode($collectData['exchange_fromto_data']['from_to_code'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
				$collectData['query_params']['exchange_add_by'] = (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0);
				$collectData['query_params']['exchange_edit_by'] = (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0);
				$collectData['query_params']['exchange_amount_from'] = 1; // Default is 1.00
				$collectData['query_params']['exchange_amount_to'] = round($collectData['input_params']['exchange_amount'], 2, PHP_ROUND_HALF_ODD);
				$collectData['query_params']['exchange_amount_to'] = sprintf("%.02f", $collectData['query_params']['exchange_amount_to']);
				// DOING UPDATE DATE
				try {
					$collectData['edited_exchange_seq'] = $this->mod_exchange->set_real_currency_exchange_by('seq', $collectData['collect']['exchange_data']->seq, $collectData['query_params']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error exception while edit exchange data: {$ex->getMessage()}";
				}
			}
		}
		if (!$this->error) {
			if ((int)$collectData['edited_exchange_seq'] > 0) {
				// Success Insert
				$collectData['redirect_url'] = base_url($collectData['base_path'] . '/exchange/listexchange');
			} else {
				$this->error = true;
				$this->error_msg[] = "Error while edit exchange data, return zero affected rows.";
			}
		}
		if (!$this->error) {
			redirect($collectData['redirect_url']);
		} else {
			$action_message_string = "";
			foreach ($this->error_msg as $errorVal) {
				$action_message_string .= $errorVal . "<br/>\n";
			}
			$this->session->set_flashdata('error', TRUE);
			$this->session->set_flashdata('action_message', $action_message_string);
			redirect(base_url($collectData['base_path'] . '/exchange/editexchange/' . base_safe_text($collectData['exchange_seq'], 12)));
		}
	}
	
	
	function objdate() {
		echo "<pre>";
		$dateobj = $this->mod_exchange->get_dateobject();
		print_r($dateobj);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
















