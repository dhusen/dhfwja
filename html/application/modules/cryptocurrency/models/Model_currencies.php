<?php 
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
class Model_currencies extends CI_Model {
	private $databases = array();
	private $base_cryptocurrency;
	protected $db_cryptocurrency;
	protected $cryptocurrency_tables = array();
	protected $DateObject;
	function __construct() {
		parent::__construct();
		$this->load->config('cryptocurrency/base_cryptocurrency');
		$this->base_cryptocurrency = $this->config->item('base_cryptocurrency');
		$this->load->library('dashboard/Lib_imzers', $this->base_cryptocurrency, 'imzers');
		$this->db_cryptocurrency = $this->load->database('cryptocurrency', TRUE);
		$this->cryptocurrency_tables = (isset($this->base_cryptocurrency['cryptocurrency_tables']) ? $this->base_cryptocurrency['cryptocurrency_tables'] : array());
		$this->DateObject = new DateTime(date('Y-m-d H:i:s'));
		$this->DateObject->setTimezone(new DateTimeZone(ConstantConfig::$timezone));
		# Load Kraken
		$this->load->library('cryptocurrency/Lib_Kraken', $this->base_cryptocurrency['market']['kraken'], 'kraken');
		# Load Bitcoin ID
		$this->load->library('cryptocurrency/Lib_Bitcoin_ID', $this->base_cryptocurrency['market']['bitcoin_id'], 'bitcoin_id');
	}
	
	
	function get_marketplace() {
		return $this->db_cryptocurrency->get($this->cryptocurrency_tables['marketplace'])->result();
	}
	function get_marketplace_data_by($by_type, $by_value, $is_enabled = 0) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'market');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if (!preg_match('/^[a-z0-9A-Z_\-]*$/', $value)) {
					$value = '';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
			case 'market':
			case 'seq':
			case 'id':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT * FROM %s WHERE", $this->cryptocurrency_tables['marketplace']);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" LOWER(market_code) = LOWER('%s')", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'market':
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		if ((int)$is_enabled > 0) {
			$sql .= " AND market_is_enabled = 'Y'";
		}
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			return FALSE;
		}
		return $sql_query->row();
	}
	//------
	function get_marketplace_api_keys_by_marketseq($market_seq = 0) {
		$this->db_cryptocurrency->where('market_seq', $market_seq);
		return $this->db_cryptocurrency->get($this->cryptocurrency_tables['marketplace_api'])->result();
	}
	function get_marketplace_api_keys_by_marketseq_and_apikeycode($market_seq, $apikeycode) {
		$market_seq = (is_numeric($market_seq) ? (int)$market_seq : 0);
		$apikeycode = (is_string($apikeycode) ? strtolower($apikeycode) : '');
		$this->db_cryptocurrency->where('market_seq', $market_seq);
		$this->db_cryptocurrency->where('api_code', $apikeycode);
		return $this->db_cryptocurrency->get($this->cryptocurrency_tables['marketplace_api'])->row();
	}
	function set_marketplace_price_index($market_seq, $apikeycode) {
		$market_seq = (is_numeric($market_seq) ? (int)$market_seq : 0);
		$apikeycode = (is_string($apikeycode) ? strtolower($apikeycode) : '');
		$this->db_cryptocurrency->where('seq', $market_seq);
		$this->db_cryptocurrency->update($this->cryptocurrency_tables['marketplace'], array('market_price_index' => $apikeycode));
		return $this->db_cryptocurrency->affected_rows();
	}
	//------
	function insert_cryptocurrency_by($by_type, $by_value, $input_params = array()) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'market');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if (!preg_match('/^[a-z0-9_\-]*$/', $value)) {
					$value = '';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
			case 'market':
			case 'seq':
			case 'id':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		try {
			$market_data = $this->get_marketplace_data_by($by_type, $by_value, 1);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		if (!isset($market_data->seq)) {
			// Market is not active or not exists
			return FALSE;
		}
		if (count($input_params) > 0) {
			$query_params = array(
				'market_seq'						=> $market_data->seq,
				'currency_code'						=> (isset($input_params['currency_code']) ? $input_params['currency_code'] : 'currency_code'),
				'currency_name'						=> (isset($input_params['currency_name']) ? $input_params['currency_name'] : ''),
				'currency_description'				=> (isset($input_params['currency_description']) ? $input_params['currency_description'] : ''),
				'currency_is_enabled'				=> (isset($input_params['currency_is_enabled']) ? $input_params['currency_is_enabled'] : ''),
				'currency_market_code'				=> (isset($input_params['currency_market_code']) ? $input_params['currency_market_code'] : ''),
				'currency_market_name'				=> (isset($input_params['currency_market_name']) ? $input_params['currency_market_name'] : ''),
				'currency_market_class'				=> (isset($input_params['currency_market_class']) ? $input_params['currency_market_class'] : ''),
				'currency_market_altname'			=> (isset($input_params['currency_market_altname']) ? $input_params['currency_market_altname'] : ''),
				'currency_market_decimals'			=> (isset($input_params['currency_market_decimals']) ? $input_params['currency_market_decimals'] : ''),
				'currency_market_decimals_display'	=> (isset($input_params['currency_market_decimals_display']) ? $input_params['currency_market_decimals_display'] : ''),
				'currency_datetime_insert'			=> (isset($input_params['currency_datetime_insert']) ? $input_params['currency_datetime_insert'] : $this->DateObject->format('Y-m-d H:i:s')),
				'currency_datetime_update'			=> (isset($input_params['currency_datetime_update']) ? $input_params['currency_datetime_update'] : $this->DateObject->format('Y-m-d H:i:s')),
			);
			$query_params['currency_market_decimals'] = (is_numeric($query_params['currency_market_decimals']) ? $query_params['currency_market_decimals'] : 0);
			$query_params['currency_market_decimals_display'] = (is_numeric($query_params['currency_market_decimals_display']) ? $query_params['currency_market_decimals_display'] : 0);
		} else {
			return FALSE;
		}
		$this->db_cryptocurrency->trans_start();
		$this->db_cryptocurrency->insert($this->cryptocurrency_tables['currencies'], $query_params);
		$new_currency_seq = $this->db_cryptocurrency->insert_id();
		$this->db_cryptocurrency->trans_complete();
		
		return $new_currency_seq;
	}
	//====================================
	function get_marketplace_currency_count_by($by_type, $by_value, $search_text = '', $is_enabled = TRUE) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if (!preg_match('/^[a-z0-9_\-]*$/', $value)) {
					$value = '';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
			case 'seq':
			case 'id':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT COUNT(curr.seq) AS value FROM %s AS curr LEFT JOIN %s AS m ON m.seq = curr.market_seq WHERE",
			$this->cryptocurrency_tables['currencies'],
			$this->cryptocurrency_tables['marketplace']
		);
		$sql_wheres = "";
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql_wheres .= sprintf(" m.market_code = '%s'", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql_wheres .= sprintf(" m.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= $sql_wheres;
		// is Enabled?
		if ($is_enabled) {
			$sql .= " AND curr.currency_is_enabled = 'Y'";
		}
		// Search Text
		$search_text = (is_string($search_text) ? $search_text : '');
		if (!empty($search_text) && (strlen($search_text) > 0)) {
			$sql_likes = " AND (";
			$search_array = base_permalink($search_text);
			$search_array = explode("-", $search_array);
			if (count($search_array) > 0) {
				$for_i = 0;
				foreach ($search_array as $val) {
					if ($for_i > 0) {
						$sql_likes .= " AND (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%' OR CONCAT('', curr.currency_market_altname, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%' OR CONCAT('', curr.currency_market_altname, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		$sql_query = $this->db_cryptocurrency->query($sql);
		return $sql_query->row();
	}
	function get_marketplace_currency_data_by($by_type, $by_value, $search_text = '', $start = 0, $per_page = 10, $is_enabled = TRUE) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if (!preg_match('/^[a-z0-9_\-]*$/', $value)) {
					$value = '';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
			case 'seq':
			case 'id':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT curr.*, m.seq AS m_market_seq, m.market_code, m.market_name, m.market_address, m.market_is_enabled, m.market_description, m.market_api_implode FROM %s AS curr LEFT JOIN %s AS m ON m.seq = curr.market_seq WHERE",
			$this->cryptocurrency_tables['currencies'],
			$this->cryptocurrency_tables['marketplace']
		);
		$sql_wheres = "";
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql_wheres .= sprintf(" m.market_code = '%s'", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql_wheres .= sprintf(" m.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= $sql_wheres;
		// is Enabled?
		if ($is_enabled) {
			$sql .= " AND curr.currency_is_enabled = 'Y'";
		}
		// Search Text
		$search_text = (is_string($search_text) ? $search_text : '');
		if (!empty($search_text) && (strlen($search_text) > 0)) {
			$sql_likes = " AND (";
			$search_array = base_permalink($search_text);
			$search_array = explode("-", $search_array);
			if (count($search_array) > 0) {
				$for_i = 0;
				foreach ($search_array as $val) {
					if ($for_i > 0) {
						$sql_likes .= " AND (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%' OR CONCAT('', curr.currency_market_altname, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%' OR CONCAT('', curr.currency_market_altname, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		$sql .= " ORDER BY curr.currency_code ASC";
		$sql .= sprintf(" LIMIT %d, %d", $start, $per_page);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	//====================================
	function get_currency_data_by($market_seq, $by_type, $by_value, $is_limit = 0) {
		$market_seq = ((is_string($market_seq) || is_numeric($market_seq)) ? $market_seq : 0);
		$market_seq = sprintf("%d", $market_seq);
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if (!preg_match('/^[a-z0-9_\-]*$/', $value)) {
					$value = '';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
			case 'seq':
			case 'id':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT c.*, m.seq AS m_market_seq, m.market_code, m.market_name, m.market_is_enabled, m.market_api_implode, m.market_api_string FROM %s AS c LEFT JOIN %s AS m ON m.seq = c.market_seq",
			$this->cryptocurrency_tables['currencies'],
			$this->cryptocurrency_tables['marketplace']
		);
		$sql .= sprintf(" WHERE m.seq = '%d'", $this->db_cryptocurrency->escape_str($market_seq));
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" AND LOWER(c.currency_code) = LOWER('%s')", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" AND c.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= " ORDER BY c.currency_datetime_update DESC";
		if ((int)$is_limit > 0) {
			$sql .= sprintf(" LIMIT %d", (int)$is_limit);
		} else {
			$sql .= " LIMIT 1";
		}
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		if ((int)$is_limit > 0) {
			return $sql_query->result();
		} else {
			return $sql_query->row();
		}
	}
	function get_allmarketplace_tickers() {
		$sql = sprintf("SELECT t.*, m.market_code, m.market_name, m.market_address, m.market_is_enabled FROM %s AS t LEFT JOIN %s AS m ON m.seq = t.market_seq",
			$this->cryptocurrency_tables['tickers'],
			$this->cryptocurrency_tables['marketplace']
		);
		$sql .= " ORDER BY CONCAT(m.market_name, '-', t.ticker_currency_from) ASC";
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	function get_marketplace_tickers($market_data = null) {
		if (!isset($market_data)) {
			return FALSE;
		}
		if (!isset($market_data->seq)) {
			return FALSE;
		}
		$this->db_cryptocurrency->where('market_seq', $market_data->seq);
		$sql_query = $this->db_cryptocurrency->get($this->cryptocurrency_tables['tickers']);
		return $sql_query->result();
	}
	function get_marketplace_tickers_by($by_type, $by_value) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
			case 'ticker_currency':
				if (!preg_match('/^[a-z0-9_A-Z\-]*$/', $value)) {
					$value = '';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
			case 'market_seq':
			case 'seq':
			case 'id':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT t.*, m.market_code, m.market_name, m.market_address, m.market_is_enabled FROM %s AS t LEFT JOIN %s AS m ON m.seq = t.market_seq",
			$this->cryptocurrency_tables['tickers'],
			$this->cryptocurrency_tables['marketplace']
		);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" WHERE LOWER(t.ticker_currency_from) = LOWER('%s')", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'ticker_currency':
				$sql .= sprintf(" WHERE t.ticker_currency_from = '%s'", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'market_seq':
				$sql .= sprintf(" WHERE t.market_seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" WHERE t.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= " ORDER BY CONCAT(m.market_name, '-', t.ticker_currency_from) ASC";
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	// ==========================
	// Market Place Library API
	function get_marketplace_ticker_data($market_seq, $currency = array()) {
		$market_seq = ((is_string($market_seq) || is_numeric($market_seq)) ? $market_seq : 0);
		$market_seq = sprintf("%d", $market_seq);
		try {
			$market_data = $this->get_marketplace_data_by('market', $market_seq, 1);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		if (!isset($market_data->seq)) {
			return FALSE;
			// Market data not active or exists
		}
		$params_currency = array(
			'from'			=> (isset($currency['from']) ? $currency['from'] : ''),
			'to'			=> (isset($currency['to']) ? $currency['to'] : ''),
		);
		$params_currency['from'] = (is_string($params_currency['from']) ? strtolower($params_currency['from']) : '');
		$params_currency['to'] = (is_string($params_currency['to']) ? strtolower($params_currency['to']) : '');
		$currency_data = array(
			'from'			=> $this->get_currency_data_by($market_data->seq, 'code', $params_currency['from'], 0),
			'to'			=> $this->get_currency_data_by($market_data->seq, 'code', $params_currency['to'], 0),
		);
		return $currency_data;
	}
	function get_ticker_from_marketplace_api($market_seq, $by_value = '') {
		$market_seq = ((is_string($market_seq) || is_numeric($market_seq)) ? $market_seq : 0);
		$market_seq = sprintf("%d", $market_seq);
		try {
			$market_data = $this->get_marketplace_data_by('market', $market_seq, 1);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		if (!isset($market_data->seq)) {
			return FALSE;
			// Market data not active or exists
		}
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($market_data->market_code)) {
			case 'bitcoin_id':
				$api_response = $this->bitcoin_id->get_marketplace_ticker($value);
			break;
			case 'kraken':
			case 'default':
			default:
				$api_response = $this->kraken->get_marketplace_ticker($value);
			break;
		}
		return $api_response;
	}
	//------------------
	// Insert ticker data
	function insert_ticker_amount_by_tickerseq($ticker_seq, $ticker_amount = '', $raw = array()) {
		$ticker_seq = (is_numeric($ticker_seq) ? (int)$ticker_seq : 0);
		if (is_string($ticker_amount) || is_numeric($ticker_amount)) {
			$ticker_amount = sprintf("%s", $ticker_amount);
		}
		$sql = sprintf("INSERT INTO %s(ticker_seq, item_date, item_datetime, item_amount) VALUES('%d', CURDATE(), NOW(), '%s')",
			$this->cryptocurrency_tables['ticker_data'],
			$this->db_cryptocurrency->escape_str($ticker_seq),
			$this->db_cryptocurrency->escape_str($ticker_amount)
		);
		$this->db_cryptocurrency->query($sql);
		$new_insert_seq = $this->db_cryptocurrency->insert_id();
		if ((int)$new_insert_seq > 0) {
			$ticker_raw_json = '';
			if (is_array($raw) || is_object($raw)) {
				$ticker_raw_json = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
			} else if (is_string($raw)) {
				$ticker_raw_json = sprintf("%s", $raw);
			} else if (is_numeric($raw)) {
				$ticker_raw_json = sprintf("%s", $raw);
			} else {
				$ticker_raw_json = date('Y-m-d H:i:s');
			}
			$raw_params = array(
				'data_seq'			=> $new_insert_seq,
				'data_logtime'		=> $this->DateObject->format('Y-m-d H:i:s'),
				'data_raw'			=> $ticker_raw_json
			);
			try {
				$sql_query = $this->db_cryptocurrency->insert('cryptocurrency_tickers_data_logs', $raw_params);
			} catch (Exception $ex) {
				exit("Cannot insert log ticker data seq: " . $ex->getMessage());
			}
		}
		
		
		
		
		return $new_insert_seq;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}











