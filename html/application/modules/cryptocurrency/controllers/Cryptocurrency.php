<?php
// Concept: https://docs.google.com/spreadsheets/d/13SxAErn4fCy1ktEmyUqLVRsgStytFaiAfCHThrE70pk/edit#gid=0
if (!defined('BASEPATH')) { exit('No direct script access allowed: Kraken'); }
class Cryptocurrency extends MY_Controller {
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
		# Load Model Currency
		$this->load->model('cryptocurrency/Model_currencies', 'mod_currency');
		
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
	//=================================
	function index() {
		$this->listmarket();
	}
	function market($page = 'add') {
		$collectData = array(
			'page'					=> 'currency-market-list',
			'title'					=> 'Add Cryptocurrency Marketplace',
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
			header("Location: " . base_url("{$this->imzers->base_path}/account/login"));
			exit;
		}
		if (!$this->is_editor) {
			$this->error = true;
			$this->error_msg[] = "You are prohibited to access page, need editor privileges.";
			$this->accessDenied($collectData);
		}
		//=================================
		if (!$this->error) {
			$collectData['page'] = 'currency-market-add';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			print_r($this->error_msg);
		}
	}
	function editapi($market_code = 'kraken') {
		$collectData = array(
			'page'					=> 'currency-market-edit-api',
			'title'					=> 'Add Cryptocurrency Marketplace',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'market_code'			=> (is_string($market_code) ? strtolower($market_code) : 'kraken'),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) || is_numeric($collectData['search_text'])) ? sprintf("%s", $collectData['search_text']) : '';
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
		if (!$this->error) {
			try {
				$collectData['collect']['market_data'] = $this->mod_currency->get_marketplace_data_by('code', $collectData['market_code']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get market-data by code: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['market_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Market data not exists on database";
			} else {
				if (strtoupper($collectData['collect']['market_data']->market_is_enabled) !== 'Y') {
					$this->error = true;
					$this->error_msg[] = "Market data not enabled or inactive.";
				} else {
					$collectData['collect']['market_data']->market_api_keys = array();
					try {
						$collectData['collect']['market_data']->market_api_keys = $this->mod_currency->get_marketplace_api_keys_by_marketseq($collectData['collect']['market_data']->seq);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception while get api-keys of marketplace.";
					}
				}
			}
		}
		if (!$this->error) {
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			$error_message = "";
			$this->session->set_flashdata('error', TRUE);
			foreach ($this->error_msg as $msg) {
				$error_message .= "{$msg}<br/>";
			}
			$this->session->set_flashdata('action_message', $error_message);
			redirect(base_url("{$collectData['base_path']}/cryptocurrency/listmarket"));
			exit;
		}
	}
	function editapiaction($market_seq = 0) {
		$collectData = array(
			'page'					=> 'currency-market-edit-api',
			'title'					=> 'Add Cryptocurrency Marketplace',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'market_seq'			=> (is_numeric($market_seq) ? strtolower($market_seq) : 0),
		);
		$collectData['search_text'] = (isset($this->imzcustom->php_input_request['body']['search_text']) ? $this->imzcustom->php_input_request['body']['search_text'] : '');
		$collectData['search_text'] = (is_string($collectData['search_text']) || is_numeric($collectData['search_text'])) ? sprintf("%s", $collectData['search_text']) : '';
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
		if (!$this->error) {
			try {
				$collectData['collect']['market_data'] = $this->mod_currency->get_marketplace_data_by('seq', $collectData['market_seq']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Error exception while get market-data by code: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['market_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Market data not exists on database";
			} else {
				if (strtoupper($collectData['collect']['market_data']->market_is_enabled) !== 'Y') {
					$this->error = true;
					$this->error_msg[] = "Market data not enabled or inactive.";
				} else {
					$collectData['collect']['market_data']->market_api_keys = array();
					try {
						$collectData['collect']['market_data']->market_api_keys = $this->mod_currency->get_marketplace_api_keys_by_marketseq($collectData['collect']['market_data']->seq);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Error exception while get api-keys of marketplace.";
					}
				}
			}
		}
		if (!$this->error) {
			$this->form_validation->set_rules('api_key', 'Market API Key', 'required|trim|max_length[64]');
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = "Form validation return error.";
				$this->session->set_flashdata('error', TRUE);
				$this->session->set_flashdata('action_message', validation_errors('<div>', '</div>'));
				redirect(base_url("{$collectData['base_path']}/cryptocurrency/listmarket"));
				exit;
			} else {
				$collectData['query_params'] = array(
					'api_code'			=> $this->input->post('api_key'),
				);
				if (is_string($collectData['query_params']['api_code'])) {
					$collectData['query_params']['api_code'] = strtolower($collectData['query_params']['api_code']);
					try {
						$collectData['market_api_code_data'] = $this->mod_currency->get_marketplace_api_keys_by_marketseq_and_apikeycode($collectData['collect']['market_data']->seq, $collectData['query_params']['api_code']);
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Exception error while get market-api-key-code by market-seq and api-key-code: {$ex->getMessage()}.";
					}
				} else {
					$this->error = true;
					$this->error_msg[] = "Api key code should be in string format.";
				}
			}
		}
		if (!$this->error) {
			if (!isset($collectData['market_api_code_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Market data api key code not exist as listed on database.";
			} else {
				try {
					$collectData['update_to_marketplace_data'] = $this->mod_currency->set_marketplace_price_index($collectData['collect']['market_data']->seq, $collectData['market_api_code_data']->market_api_key);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot update api key code to marketplace data.";
				}
			}
		}
		
		
		if (!$this->error) {
			$this->session->set_flashdata('error', FALSE);
			$this->session->set_flashdata('action_message', 'Success editing marketplace api price key.');
		} else {
			$error_message = "";
			$this->session->set_flashdata('error', TRUE);
			foreach ($this->error_msg as $msg) {
				$error_message .= "{$msg}<br/>";
			}
			$this->session->set_flashdata('action_message', $error_message);
		}
		redirect(base_url("{$collectData['base_path']}/cryptocurrency/listmarket"));
	}
	function listmarket() {
		$collectData = array(
			'page'					=> 'currency-market-list',
			'title'					=> 'Cryptocurrency Marketplace',
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
			$collectData['collect']['marketplace'] = $this->mod_currency->get_marketplace();
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot retrieve marketplace data with exception: {$ex->getMessage()}";
		}
		if (!$this->error) {
			if (is_array($collectData['collect']['marketplace']) && (count($collectData['collect']['marketplace']) > 0)) {
				foreach ($collectData['collect']['marketplace'] as &$marketplace) {
					$marketplace->market_api_keys = $this->mod_currency->get_marketplace_api_keys_by_marketseq($marketplace->seq);
				}
			}
		}
		
		
		if (!$this->error) {
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			print_r($this->error_msg);
		}
	}
	//--
	function currency($page = 'add') {
		$collectData = array(
			'page'					=> 'currency-currency-add',
			'title'					=> 'Add Currency',
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
		if (!$this->error) {
			$collectData['page'] = 'currency-currency-add';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			print_r($this->error_msg);
		}
	}
	function listcurrency($market_code = 'kraken', $pgnumber = 0) {
		$collectData = array(
			'page'					=> 'currency-currency-list',
			'title'					=> 'Cryptocurrency Currencies',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> 'dashboard',
			'collect'				=> array(),
			'market_code'			=> (is_numeric($market_code) || is_string($market_code)) ? sprintf("%s", $market_code) : 'kraken',
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
		$collectData['market_code'] = strtolower($collectData['market_code']);
		try {
			$collectData['market_data'] = $this->mod_currency->get_marketplace_data_by('code', $collectData['market_code'], 1);
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot get retrieve market-data by code with exception: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			// Set is Enabled Only?
			$collectData['is_enabled'] = FALSE;
			$collectData['collect']['currencies'] = array();
			if (!isset($collectData['market_data']->seq)) {
				$this->error = true;
				$this->error_msg[] = "Market data not exists on database.";
				// Should be redirect to default page
			} else {
				try {
					$collectData['collect']['currencies']['count'] = $this->mod_currency->get_marketplace_currency_count_by('seq', $collectData['market_data']->seq, $collectData['search_text'], $collectData['is_enabled']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot count currencies on marketplace with exceptions: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			$collectData['collect']['marketplace'] = $this->mod_currency->get_marketplace();
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
					$collectData['collect']['currencies']['data'] = $this->mod_currency->get_marketplace_currency_data_by('seq', $collectData['market_data']->seq, $collectData['search_text'], $collectData['pagination']['start'], base_config('rows_per_page'), $collectData['is_enabled']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Error while get currency data by market-seq with exception: {$ex->getMessage()}";
				}
			} else {
				$this->error = true;
				$this->error_msg[] = "Should have value as total rows.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pagination'] = $this->imzcustom->generate_pagination(base_url("{$collectData['base_path']}/cryptocurrency/listcurrency/{$collectData['market_data']->market_code}/%d"), $collectData['pagination']['page'], base_config('rows_per_page'), $collectData['collect']['currencies']['count']->value, $collectData['pagination']['start']);
			
			// == Count ticker data
			if (is_array($collectData['collect']['currencies']['data']) && (count($collectData['collect']['currencies']['data']) > 0)) {
				foreach ($collectData['collect']['currencies']['data'] as $dataVal) {
					
				}
			}
		}
		//=================================
		if (!$this->error) {
			$collectData['page'] = 'currency-currency-list';
			$this->load->view($this->base_cryptocurrency['base_path'] . '/cryptocurrency.php', $collectData);
		} else {
			print_r($this->error_msg);
		}
	}
	
	
	
	
	//=================================
		
	
	
	
	
	
	
	
	
	
	
	
	public function addemail() {
		$collectData = array(
			'page'					=> 'cryptocurrency-add-email',
			'title'					=> 'Cryptocurrency Bank',
			'base_path'				=> $this->base_cryptocurrency['base_path'],
			'base_dashboard_path'	=> $this->base_dashboard['base_path'],
			'collect'				=> array(),
		);
		//================================================================
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
		
		
		
		
	}
}












