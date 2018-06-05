<?php 
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
class Model_exchange extends CI_Model {
	private $databases = array();
	private $base_cryptocurrency;
	protected $db_cryptocurrency;
	protected $cryptocurrency_tables = array();
	protected $ModelDateObject;
	function __construct() {
		parent::__construct();
		$this->load->config('cryptocurrency/base_cryptocurrency');
		$this->base_cryptocurrency = $this->config->item('base_cryptocurrency');
		$this->load->library('dashboard/Lib_imzers', $this->base_cryptocurrency, 'imzers');
		$this->db_cryptocurrency = $this->load->database('cryptocurrency', TRUE);
		$this->cryptocurrency_tables = (isset($this->base_cryptocurrency['cryptocurrency_tables']) ? $this->base_cryptocurrency['cryptocurrency_tables'] : array());
		$this->ModelDateObject = new DateTime(date('Y-m-d H:i:s'));
		$this->ModelDateObject->setTimezone(new DateTimeZone(ConstantConfig::$timezone));
		# Load Kraken
		//$this->load->library('cryptocurrency/Lib_Kraken', $this->base_cryptocurrency['market']['kraken'], 'kraken');
		# Load Bitcoin ID
		//$this->load->library('cryptocurrency/Lib_Bitcoin_ID', $this->base_cryptocurrency['market']['bitcoin_id'], 'bitcoin_id');
	}
	function get_dateobject() {
		return $this->ModelDateObject;
	}
	function get_today_exchange() {
		$sql = sprintf("SELECT * FROM %s WHERE (exchange_date = CURDATE() AND exchange_is_active = 'Y') LIMIT 1", $this->cryptocurrency_tables['real_exchange']);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		$row = $sql_query->row();
		if (!isset($row->seq)) {
			$sql = "SELECT * FROM {$this->cryptocurrency_tables['real_exchange']} WHERE exchange_is_active = 'Y' ORDER BY exchange_date DESC LIMIT 1";
			try {
				$sql_query = $this->db_cryptocurrency->query($sql);
			} catch (Exception $ex) {
				throw $ex;
				return FALSE;
			}
			return $sql_query->row();
		} else {
			return $row;
		}
	}
	function get_today_exchange_by_currecency_fromto($from, $to) {
		$from = strtolower($from);
		$to = strtolower($to);
		$sql = sprintf("SELECT * FROM %s WHERE (exchange_date = CURDATE() AND exchange_is_active = 'Y') AND (LOWER(from_to_string) = LOWER(CONCAT('%s', '-', '%s'))) LIMIT 1", 
			$this->cryptocurrency_tables['real_exchange'],
			$this->db_cryptocurrency->escape_str($from),
			$this->db_cryptocurrency->escape_str($to)
		);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		$row = $sql_query->row();
		if (!isset($row->seq)) {
			$sql = sprintf("SELECT * FROM %s WHERE (exchange_is_active = 'Y') AND (LOWER(from_to_string) = LOWER(CONCAT('%s', '-', '%s'))) ORDER BY exchange_date DESC LIMIT 1", 
				$this->cryptocurrency_tables['real_exchange'],
				$this->db_cryptocurrency->escape_str($from),
				$this->db_cryptocurrency->escape_str($to)
			);
			try {
				$sql_query = $this->db_cryptocurrency->query($sql);
			} catch (Exception $ex) {
				throw $ex;
				return FALSE;
			}
			return $sql_query->row();
		} else {
			return $row;
		}
	}
	function get_helper_country() {
		$this->db_cryptocurrency->order_by('name', 'ASC');
		return $this->db_cryptocurrency->get($this->cryptocurrency_tables['helper_country'])->result();
	}
	function get_helper_realcurrency_by($by_type, $by_value, $input_params = array()) {
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
				if (!preg_match('/^[a-z0-9A-Z_\-]*$/', $value)) {
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
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['helper_country']);
		$this->db_cryptocurrency->where('currency_code', $value);
		if (isset($input_params['currency_country_code'])) {
			$input_params['currency_country_code'] = (is_string($input_params['currency_country_code']) ? strtoupper($input_params['currency_country_code']) : '');
			$this->db_cryptocurrency->where('code', $input_params['currency_country_code']);
		}
		
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->row();
	}
	function get_real_currencies() {
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['real_currencies']);
		$this->db_cryptocurrency->where('currency_is_active', 'Y');
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->result();
	}
	function get_real_currencies_by($by_type, $by_value, $is_active = 0) {
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
				if (!preg_match('/^[a-z0-9A-Z_\-]*$/', $value)) {
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
		$sql = sprintf("SELECT * FROM %s WHERE", $this->cryptocurrency_tables['real_currencies']);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" currency_code = '%s'", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		if ((int)$is_active > 0) {
			$sql .= " AND currency_is_active = 'Y'";
		}
		$sql .= " LIMIT 1";
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return false;
		}
		return $sql_query->row();
	}
	function insert_real_currencies($query_params) {
		$this->db_cryptocurrency->insert($this->cryptocurrency_tables['real_currencies'], $query_params);
		return $this->db_cryptocurrency->insert_id();
	}
	function set_real_currencies_by($by_type, $by_value, $input_params = array()) {
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
		try {
			$real_currency_data = $this->get_real_currencies_by($by_type, $by_value);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		if (!isset($real_currency_data->seq)) {
			// Real currency not exists
			return FALSE;
		}
		$query_params = array(
			'currency_code'					=> (isset($input_params['currency_code']) ? $input_params['currency_code'] : ''),
			'currency_name'					=> (isset($input_params['currency_name']) ? $input_params['currency_name'] : ''),
			'currency_country_code'			=> (isset($input_params['currency_country_code']) ? $input_params['currency_country_code'] : ''),
			'currency_country_name'			=> (isset($input_params['currency_country_name']) ? $input_params['currency_country_name'] : ''),
			'currency_datetime_insert'		=> $this->ModelDateObject->format('Y-m-d H:i:s'),
			'currency_datetime_update'		=> $this->ModelDateObject->format('Y-m-d H:i:s'),
			'currency_is_active'			=> (isset($input_params['currency_is_active']) ? $input_params['currency_is_active'] : 'N'),
		);
		$query_params['currency_code'] = base_safe_text($query_params['currency_code'], 3);
		$query_params['currency_country_code'] = base_safe_text($query_params['currency_country_code'], 2);
		$query_params['currency_country_name'] = base_safe_text($query_params['currency_country_name'], 64);
		$query_params['currency_name'] = base_safe_text($query_params['currency_name'], 128);
		$query_params['currency_is_active'] = strtoupper(base_safe_text($query_params['currency_is_active'], 1));
		
		try {
			$this->db_cryptocurrency->trans_start();
			$this->db_cryptocurrency->insert($this->cryptocurrency_tables['real_currencies'], $query_params);
			$new_real_currency_seq = $this->db_cryptocurrency->insert_id();
			$this->db_cryptocurrency->trans_complete();
		} catch (Exception $ex) {
			throw $ex;
			$new_real_currency_seq = 0;
		}
		return $new_real_currency_seq;
	}
	//====================================
	function get_exchange_currency_count_by($by_type, $by_value, $search_text = '', $is_active = TRUE) {
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
				if (!preg_match('/^[a-z0-9A-Z_\-]*$/', $value)) {
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
		$sql = sprintf("SELECT COUNT(exc.seq) AS value FROM %s AS exc WHERE", $this->cryptocurrency_tables['real_exchange']);
		$sql_wheres = "";
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if ($value !== 'all') {
					$sql_wheres .= sprintf(" exc.from_to_string = '%s'", $this->db_cryptocurrency->escape_str($value));
				} else {
					$sql_wheres .= " exc.seq > 0";
				}
			break;
			case 'seq':
			case 'id':
			default:
				$sql_wheres .= sprintf(" exc.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= $sql_wheres;
		// is Enabled?
		if ($is_active) {
			$sql .= " AND exc.exchange_is_active = 'Y'";
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
						$sql_likes .= " AND (CONCAT('', exc.exchange_date, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', exc.exchange_date, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		// Make selected Date
		$selected_date = $this->input->post('exchange_date');
		$selected_date = (is_string($selected_date) || is_numeric($selected_date)) ? $selected_date : '';
		try {
			$selected_date_object = date_create_from_format('Y-m-d', $selected_date);
		} catch (Exception $ex) {
			throw $ex;
			$selected_date_object = FALSE;
		}
		if ($selected_date_object !== FALSE){
			$sql .= sprintf(" AND exc.exchange_date = '%s'", date_format($selected_date_object, 'Y-m-d'));
		}
		$sql_query = $this->db_cryptocurrency->query($sql);
		return $sql_query->row();
	}
	function get_exchange_currency_data_by($by_type, $by_value, $search_text = '', $start = 0, $per_page = 10, $is_active = TRUE) {
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
		$sql = sprintf("SELECT exc.* FROM %s AS exc WHERE", 
			$this->cryptocurrency_tables['real_exchange']
		);
		$sql_wheres = "";
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				if ($value !== 'all') {
					$sql_wheres .= sprintf(" exc.from_to_string = '%s'", $this->db_cryptocurrency->escape_str($value));
				} else {
					$sql_wheres .= " exc.seq > 0";
				}
			break;
			case 'seq':
			case 'id':
			default:
				$sql_wheres .= sprintf(" exc.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= $sql_wheres;
		// is Enabled?
		if ($is_active) {
			$sql .= " AND exc.exchange_is_active = 'Y'";
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
						$sql_likes .= " AND (CONCAT('', exc.exchange_date, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', exc.exchange_date, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		// Make selected Date
		$selected_date = $this->input->post('exchange_date');
		$selected_date = (is_string($selected_date) || is_numeric($selected_date)) ? $selected_date : '';
		try {
			$selected_date_object = date_create_from_format('Y-m-d', $selected_date);
		} catch (Exception $ex) {
			throw $ex;
			$selected_date_object = FALSE;
		}
		if ($selected_date_object !== FALSE){
			$sql .= sprintf(" AND exc.exchange_date = '%s'", date_format($selected_date_object, 'Y-m-d'));
		}
		$sql .= " ORDER BY exc.exchange_date DESC";
		$sql .= sprintf(" LIMIT %d, %d", $start, $per_page);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	//------
	function get_real_currencies_is_active() {
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['real_currencies']);
		$this->db_cryptocurrency->where('currency_is_active', 'Y');
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->result();
	}
	function get_real_currency_count_by($search_text = '', $is_active = TRUE) {
		$sql = sprintf("SELECT COUNT(curr.seq) AS value FROM %s AS curr", $this->cryptocurrency_tables['real_currencies']);
		$sql .= " WHERE 1=1";
		// is Enabled?
		if ($is_active) {
			$sql .= " AND curr.currency_is_active = 'Y'";
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
						$sql_likes .= " AND ((CONCAT('', curr.currency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%') OR (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%'))";
					} else {
						$sql_likes .= " ((CONCAT('', curr.currency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%') OR (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%'))";
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
	function get_real_currency_data_by($search_text = '', $start = 0, $per_page = 10, $is_active = TRUE) {
		$sql = sprintf("SELECT curr.* FROM %s AS curr", $this->cryptocurrency_tables['real_currencies']);
		$sql .= " WHERE 1=1";
		// is Enabled?
		if ($is_active) {
			$sql .= " AND curr.currency_is_active = 'Y'";
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
						$sql_likes .= " AND ((CONCAT('', curr.currency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%') OR (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%'))";
					} else {
						$sql_likes .= " ((CONCAT('', curr.currency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%') OR (CONCAT('', curr.currency_name, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%'))";
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
	private function get_real_currency_exchange_seq_by_date_and_fromto($by_date, $from_seq, $to_seq) {
		if (!strtotime($by_date)) {
			$by_date = $this->ModelDateObject->format('Y-m-d');
		} else {
			$by_date = date('Y-m-d', strtotime($by_date));
		}
		$from_seq = (is_numeric($from_seq) ? $from_seq : 0);
		$to_seq = (is_numeric($to_seq) ? $to_seq : 0);
		$sql = sprintf("SELECT seq AS value FROM %s WHERE exchange_date = '%s' AND (from_seq = '%d' AND to_seq = '%d')",
			$this->cryptocurrency_tables['real_exchange'],
			$this->db_cryptocurrency->escape_str($by_date),
			$this->db_cryptocurrency->escape_str($from_seq),
			$this->db_cryptocurrency->escape_str($to_seq)
		);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		$row = $sql_query->row();
		if (isset($row->seq)) {
			return $row->seq;
		}
		return 0;
	}
	//--
	function get_real_currency_exchange_single_by($by_type, $by_value, $by_date, $input_params = array()) {
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
			case 'fromto':
			case 'date':
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
		$by_date = (is_string($by_date) || is_numeric($by_date)) ? sprintf("%s", $by_date) : date('Y-m-d');
		$query_params = array();
		try {
			$exchange_date = date_create_from_format('Y-m-d', $by_date);
		} catch (Exception $ex) {
			throw $ex;
			$exchange_date = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		if ($exchange_date === FALSE) {
			$exchange_date = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		$exchange_fromto = array();
		if (in_array(strtolower($by_type), array('date', 'fromto'))) {
			if (!isset($input_params['from_to'])) {
				return FALSE;
			} else {
				$exchange_fromto['from'] = (isset($input_params['from_to']['from']) ? ((is_string($input_params['from_to']['from']) || is_numeric($input_params['from_to']['from'])) ? (int)$input_params['from_to']['from'] : 0) : 0);
				$exchange_fromto['to'] = (isset($input_params['from_to']['to']) ? ((is_string($input_params['from_to']['to']) || is_numeric($input_params['from_to']['to'])) ? (int)$input_params['from_to']['to'] : 0) : 0);
			}
		} else {
			$exchange_fromto['from'] = 0;
			$exchange_fromto['to'] = 0;
		}
		// Start SQL
		$sql = sprintf("SELECT e.*, c1.seq AS from_currency_seq, c1.currency_code AS from_currency_code, c1.currency_name AS from_currency_name, c1.currency_is_active AS from_currency_is_active, c2.seq AS to_currency_seq, c2.currency_code AS to_currency_code, c2.currency_name AS to_currency_name, c1.currency_is_active AS to_currency_is_active FROM %s AS e INNER JOIN %s AS c1 ON c1.seq = e.from_seq INNER JOIN %s AS c2 ON c2.seq = e.to_seq",
			$this->cryptocurrency_tables['real_exchange'],
			$this->cryptocurrency_tables['real_currencies'],
			$this->cryptocurrency_tables['real_currencies']
		);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" WHERE (e.exchange_date = '%s' AND LOWER(e.from_to_string) = LOWER('%s'))",
					$this->db_cryptocurrency->escape_str(date_format($exchange_date, 'Y-m-d')),
					$this->db_cryptocurrency->escape_str($value)
				);
			break;
			case 'fromto':
				$sql .= sprintf(" WHERE (e.exchange_date = '%s' AND LOWER(e.from_to_string) = LOWER('%s'))",
					$this->db_cryptocurrency->escape_str(date_format($exchange_date, 'Y-m-d')),
					$this->db_cryptocurrency->escape_str($value)
				);
				$sql .= sprintf(" AND (e.from_seq = '%d' AND to_seq = '%d')",
					$this->db_cryptocurrency->escape_str($exchange_fromto['from']),
					$this->db_cryptocurrency->escape_str($exchange_fromto['to'])
				);
			break;
			case 'date':
				$sql .= sprintf(" WHERE (e.exchange_date = '%s')",
					$this->db_cryptocurrency->escape_str(date_format($exchange_date, 'Y-m-d'))
				);
				$sql .= sprintf(" AND (e.from_seq = '%d' AND to_seq = '%d')",
					$this->db_cryptocurrency->escape_str($exchange_fromto['from']),
					$this->db_cryptocurrency->escape_str($exchange_fromto['to'])
				);
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" WHERE e.seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->row();
	}
	# Insert single real currency
	function insert_real_currency_exchange_by($by_type, $by_value, $input_params = array()) {
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
			case 'fromto':
			case 'date':
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
		$query_params = array(
			'from_seq' => (isset($input_params['from_seq']) ? $input_params['from_seq'] : 0),
			'to_seq' => (isset($input_params['to_seq']) ? $input_params['to_seq'] : 0),
			'from_to_string' => (isset($input_params['from_to_string']) ? $input_params['from_to_string'] : ''),
			'from_to_json' => (isset($input_params['from_to_json']) ? $input_params['from_to_json'] : ''),
			'exchange_date' => (isset($input_params['exchange_date']) ? $input_params['exchange_date'] : $this->ModelDateObject->format('Y-m-d')),
			'exchange_datetime_insert' => $this->ModelDateObject->format('Y-m-d H:i:s'),
			'exchange_datetime_update' => $this->ModelDateObject->format('Y-m-d H:i:s'),
			'exchange_add_by' => (isset($input_params['exchange_add_by']) ? $input_params['exchange_add_by'] : (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0)),
			'exchange_edit_by' => (isset($input_params['exchange_edit_by']) ? $input_params['exchange_edit_by'] : (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0)),
			'exchange_amount_from' => (isset($input_params['exchange_amount_from']) ? $input_params['exchange_amount_from'] : 0),
			'exchange_amount_to' => (isset($input_params['exchange_amount_to']) ? $input_params['exchange_amount_to'] : 0),
			'exchange_is_active' => (isset($input_params['exchange_is_active']) ? $input_params['exchange_is_active'] : 'N'),
		);
		$query_params['from_seq'] = (is_numeric($query_params['from_seq']) ? (int)$query_params['from_seq'] : 0);
		$query_params['to_seq'] = (is_numeric($query_params['to_seq']) ? (int)$query_params['to_seq'] : 0);
		$query_params['from_to_string'] = (is_numeric($query_params['from_to_string']) || is_string($query_params['from_to_string'])) ? strtolower($query_params['from_to_string']) : '';
		if (is_array($query_params['from_to_json']) || is_object($query_params['from_to_json'])) {
			$query_params['from_to_json'] = json_encode($query_params['from_to_json'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		} else {
			$query_params['from_to_json'] = sprintf("%s", $query_params['from_to_json']);
		}
		try {
			$exchange_date = date_create_from_format('Y-m-d', $query_params['exchange_date']);
		} catch (Exception $ex) {
			throw $ex;
			$exchange_date = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		if ($exchange_date === FALSE) {
			$exchange_date = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		$query_params['exchange_date'] = date_format($exchange_date, 'Y-m-d');
		$query_params['exchange_add_by'] = (is_numeric($query_params['exchange_add_by']) ? $query_params['exchange_add_by'] : 0);
		$query_params['exchange_edit_by'] = (is_numeric($query_params['exchange_edit_by']) ? $query_params['exchange_edit_by'] : 0);
		$query_params['exchange_amount_from'] = (is_numeric($query_params['exchange_amount_from']) ? sprintf("%.02f", $query_params['exchange_amount_from']) : 0);
		$query_params['exchange_amount_to'] = (is_numeric($query_params['exchange_amount_to']) ? sprintf("%.02f", $query_params['exchange_amount_to']) : 0);
		if (!in_array($query_params['exchange_is_active'], array('Y', 'N'))) {
			$query_params['exchange_is_active'] = 'N';
		} else {
			$query_params['exchange_is_active'] = strtoupper($query_params['exchange_is_active']);
		}
		// Check if still exists
		try {
			$real_exchange_data = $this->get_real_currency_exchange_single_by('date', '', $query_params['exchange_date'], array('from' => $query_params['from_seq'], 'to' => $query_params['to_seq']));
		} catch (Exception $ex) {
			exit("Cannot check if data already exists or not: {$ex->getMessage()}.");
		}
		if (isset($real_exchange_data->seq)) {
			exit("Data already exists on database, please check ID: {$real_exchange_data->seq}");
		} else {
			// Insert new to database
			//======================
			// Need check date compare to today? Later
			try {
				$this->db_cryptocurrency->trans_start();
				$this->db_cryptocurrency->insert($this->cryptocurrency_tables['real_exchange'], $query_params);
				$new_insert_seq = $this->db_cryptocurrency->insert_id();
				$this->db_cryptocurrency->trans_complete();
			} catch (Exception $ex) {
				throw $ex;
				$new_insert_seq = 0;
			}
			return $new_insert_seq;
		}
		return FALSE;
	}
	# Set single real currency
	function set_real_currency_exchange_by($by_type, $by_value, $input_params = array()) {
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
			case 'fromto':
			case 'date':
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
		$query_params = array(
			'from_seq' => (isset($input_params['from_seq']) ? $input_params['from_seq'] : 0),
			'to_seq' => (isset($input_params['to_seq']) ? $input_params['to_seq'] : 0),
			'from_to_string' => (isset($input_params['from_to_string']) ? $input_params['from_to_string'] : ''),
			'from_to_json' => (isset($input_params['from_to_json']) ? $input_params['from_to_json'] : ''),
			'exchange_date' => (isset($input_params['exchange_date']) ? $input_params['exchange_date'] : $this->ModelDateObject->format('Y-m-d')),
			'exchange_datetime_update' => $this->ModelDateObject->format('Y-m-d H:i:s'),
			'exchange_edit_by' => (isset($input_params['exchange_edit_by']) ? $input_params['exchange_edit_by'] : (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0)),
			'exchange_amount_from' => (isset($input_params['exchange_amount_from']) ? $input_params['exchange_amount_from'] : 0),
			'exchange_amount_to' => (isset($input_params['exchange_amount_to']) ? $input_params['exchange_amount_to'] : 0),
			'exchange_is_active' => (isset($input_params['exchange_is_active']) ? $input_params['exchange_is_active'] : 'N'),
		);
		$query_params['from_seq'] = (is_numeric($query_params['from_seq']) ? (int)$query_params['from_seq'] : 0);
		$query_params['to_seq'] = (is_numeric($query_params['to_seq']) ? (int)$query_params['to_seq'] : 0);
		$query_params['from_to_string'] = (is_numeric($query_params['from_to_string']) || is_string($query_params['from_to_string'])) ? strtolower($query_params['from_to_string']) : '';
		if (is_array($query_params['from_to_json']) || is_object($query_params['from_to_json'])) {
			$query_params['from_to_json'] = json_encode($query_params['from_to_json'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		} else {
			$query_params['from_to_json'] = sprintf("%s", $query_params['from_to_json']);
		}
		try {
			$exchange_date = date_create_from_format('Y-m-d', $query_params['exchange_date']);
		} catch (Exception $ex) {
			throw $ex;
			$exchange_date = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		if ($exchange_date === FALSE) {
			$exchange_date = date_create_from_format('Y-m-d', date('Y-m-d'));
		}
		$query_params['exchange_date'] = date_format($exchange_date, 'Y-m-d');
		$query_params['exchange_edit_by'] = (is_numeric($query_params['exchange_edit_by']) ? $query_params['exchange_edit_by'] : 0);
		$query_params['exchange_amount_from'] = (is_numeric($query_params['exchange_amount_from']) ? sprintf("%.02f", $query_params['exchange_amount_from']) : 0);
		$query_params['exchange_amount_to'] = (is_numeric($query_params['exchange_amount_to']) ? sprintf("%.02f", $query_params['exchange_amount_to']) : 0);
		if (!in_array($query_params['exchange_is_active'], array('Y', 'N'))) {
			$query_params['exchange_is_active'] = 'N';
		} else {
			$query_params['exchange_is_active'] = strtoupper($query_params['exchange_is_active']);
		}
		// Move Old to Trash and Update Exchange
		try {
			$move_to_trash_seq = $this->move_real_currency_exchange_to_trash($value);
		} catch (Exception $ex) {
			throw $ex;
			$move_to_trash_seq = FALSE;
		}
		if ((int)$move_to_trash_seq > 0) {
			// Success move to trash
			switch (strtolower($by_type)) {
				case 'code':
				case 'slug':
				case 'fromto':
				case 'date':
					$this->db_cryptocurrency->where('from_to_string', $value);
					$this->db_cryptocurrency->where('exchange_date', $query_params['exchange_date']);
				break;
				case 'seq':
				case 'id':
				default:
					$this->db_cryptocurrency->where('seq', $value);
				break;
			}
			$this->db_cryptocurrency->update($this->cryptocurrency_tables['real_exchange'], $query_params);
			$affected_rows = $this->db_cryptocurrency->affected_rows();
			return $affected_rows;
		}
		return 0;
	}
	private function move_real_currency_exchange_to_trash($seq = 0) {
		$value = (is_numeric($seq) ? (int)$seq : 0);
		if (!preg_match('/^[1-9][0-9]*$/', $value)) {
			$value = 0;
		} else {
			$value = sprintf('%d', $value);
		}
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['real_exchange'])->where('seq', $value);
		$row = $this->db_cryptocurrency->get()->row();
		if (isset($row->seq)) {
			$trash_params = array(
				'exchange_seq' => $row->seq,
				'exchange_data' => json_encode($row, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
				'trash_create_by_email' => (isset($this->authentication->localdata['account_email']) ? $this->authentication->localdata['account_email'] : 'system@root'),
				'trash_create_by_seq' => (isset($this->authentication->localdata['seq']) ? $this->authentication->localdata['seq'] : 0),
				'trash_create_datetime' => $this->ModelDateObject->format('Y-m-d H:i:s'),
			);
			$trash_expired_datetime = new DateTime($this->ModelDateObject->format('Y-m-d H:i:s'));
			$trash_expired_datetime->add(new DateInterval('P30D'));
			$trash_params['trash_expired_datetime'] = $trash_expired_datetime->format('Y-m-d H:i:s');
			// Move query
			$this->db_cryptocurrency->trans_start();
			$this->db_cryptocurrency->insert($this->cryptocurrency_tables['real_exchange_trash'], $trash_params);
			$new_exchange_trash_seq = $this->db_cryptocurrency->insert_id();
			$this->db_cryptocurrency->trans_complete();
			return $new_exchange_trash_seq;
		}
		return FALSE;
	}
	
	
	
}









