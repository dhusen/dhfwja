<?php 
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
class Model_ticker extends CI_Model {
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
	}
	//========
	function get_enabled_units() {
		return array(
			array('code' => 'minute', 'name' => 'Minute'),
			array('code' => 'hour', 'name' => 'Hour'),
			array('code' => 'day', 'name' => 'Day'),
		);
	}
	//==========================
	function get_ticker_comparison_count_by($search_text = '', $is_active = TRUE) {
		$sql = sprintf("SELECT COUNT(c.seq) AS value FROM %s AS c", $this->cryptocurrency_tables['ticker_enabled']);
		$sql .= " WHERE 1=1";
		// is Enabled?
		if ($is_active) {
			$sql .= " AND c.cryptocurrency_is_enabled = 'Y'";
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
						$sql_likes .= " AND (CONCAT('', c.cryptocurrency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', c.cryptocurrency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
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
	function get_ticker_comparison_data_by($search_text = '', $start = 0, $per_page = 10, $is_active = TRUE) {
		$sql = sprintf("SELECT c.*, t1.seq AS from_ticker_seq, m1.seq AS from_market_seq, m1.market_code AS from_market_code, m1.market_name AS from_market_name, m1.market_address AS from_market_address, t2.seq AS to_ticker_seq, m2.seq AS to_market_seq, m2.market_code AS to_market_code, m2.market_name AS to_market_name, m2.market_address AS to_market_address FROM ((%s AS c LEFT JOIN %s AS t1 ON t1.seq = c.cryptocurrency_compare_ticker_seq_from INNER JOIN %s AS m1 ON m1.seq = t1.market_seq) LEFT JOIN %s AS t2 ON t2.seq = c.cryptocurrency_compare_ticker_seq_to INNER JOIN %s AS m2 ON m2.seq = t2.market_seq)", 
			$this->cryptocurrency_tables['ticker_enabled'],
			$this->cryptocurrency_tables['tickers'],
			$this->cryptocurrency_tables['marketplace'],
			$this->cryptocurrency_tables['tickers'],
			$this->cryptocurrency_tables['marketplace']
			);
		$sql .= " WHERE 1=1";
		// is Enabled?
		if ($is_active) {
			$sql .= " AND c.cryptocurrency_is_enabled = 'Y'";
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
						$sql_likes .= " AND (CONCAT('', c.cryptocurrency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', c.cryptocurrency_code, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		$sql .= " ORDER BY c.cryptocurrency_code ASC";
		$sql .= sprintf(" LIMIT %d, %d", $start, $per_page);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	//---------------------------
	//=== INSERT
	function insert_ticker_data_collection_to_enabled_data($enabled_seq, $comparison_date = array(), $input_params = array()) {
		$enabled_seq = (is_numeric($enabled_seq) ? (int)$enabled_seq : 0);
		if (!isset($comparison_date['starting']) || !isset($comparison_date['stopping'])) {
			return -90;
		}
		try {
			$comparison_date_object_starting = new DateTime($comparison_date['starting']);
			$comparison_date_object_stopping = new DateTime($comparison_date['stopping']);
		} catch (Exception $ex) {
			throw $ex;
			return -91;
		}
		if ($comparison_date_object_starting !== FALSE) {
			$comparison_every = array(
				'amount' => (isset($input_params['comparison_every_amount']) ? $input_params['comparison_every_amount'] : 1),
				'unit' => (isset($input_params['comparison_every_unit']) ? $input_params['comparison_every_unit'] : 'minute'),
				'limit' => (isset($input_params['today_comparison_limit']) ? sprintf("%s", $input_params['today_comparison_limit']) : '0'),
				'limit_min' => (isset($input_params['today_comparison_limit_min']) ? sprintf("%s", $input_params['today_comparison_limit_min']) : '0'),
				'limit_max' => (isset($input_params['today_comparison_limit_max']) ? sprintf("%s", $input_params['today_comparison_limit_max']) : '0'),
			);
			$check_params = array(
				'enabled_seq'			=> $enabled_seq,
				'comparison_date'		=> $comparison_date_object_starting->format('Y-m-d'),
				'comparison_datetime'	=> array(
					'starting'					=> "{$comparison_date_object_starting->format('Y-m-d H:i')}:00",
					'stopping'					=> "{$comparison_date_object_stopping->format('Y-m-d H:i')}:00",
				),
				'comparison_every'		=> array(
					'amount'					=> $comparison_every['amount'],
					'unit'						=> $comparison_every['unit'],
					'limit'						=> $comparison_every['limit'],
					'limit_min'					=> $comparison_every['limit_min'],
					'limit_max'					=> $comparison_every['limit_max'],
				),
			);
			$check_is_inserted = $this->chek_is_inserted_enabled_data($check_params['enabled_seq'], $check_params['comparison_date'], $check_params['comparison_datetime'], $check_params['comparison_every']);
			
			//return $check_is_inserted;
			
			if ($check_is_inserted === 0) {
				#### Insert New
				$this->db_cryptocurrency->trans_start();
				/*
				$sql = sprintf("INSERT INTO %s(", $this->cryptocurrency_tables['ticker_enabled_data']);
				if (count($input_params) > 0) {
					foreach ($input_params as $inputKey => $inputVal) {
						if ($for_i > 0) {
							$sql .= sprintf(", %s", $inputKey);
						} else {
							$sql .= sprintf("%s", $inputKey);
						}
					}
				}
				$sql .= ")";
				*/
				$this->db_cryptocurrency->set('log_datetime', 'NOW()', FALSE);
				$this->db_cryptocurrency->insert($this->cryptocurrency_tables['ticker_enabled_data'], $input_params);
				$new_insert_id = $this->db_cryptocurrency->insert_id();
				$this->db_cryptocurrency->trans_complete();
			} else {
				$new_insert_id = (int)$check_is_inserted;
				$this->db_cryptocurrency->where('seq', $new_insert_id);
				$this->db_cryptocurrency->set('log_datetime', 'NOW()', FALSE);
				$this->db_cryptocurrency->update($this->cryptocurrency_tables['ticker_enabled_data'], $input_params);
			}
			return $new_insert_id;
		} else {
			// Invalid Date Format
			return -99;
		}
	}
	private function chek_is_inserted_enabled_data($enabled_seq, $comparison_date, $comparison_datetime, $comparison_every) {
		$sql = sprintf("SELECT seq AS value FROM %s WHERE (enabled_seq = '%d' AND comparison_date = '%s') AND (DATE_FORMAT(comparison_datetime_starting, '%%Y-%%m-%%d %%H:%%i:%%s') = '%s' AND DATE_FORMAT(comparison_datetime_stopping, '%%Y-%%m-%%d %%H:%%i:%%s') = '%s') AND (comparison_every_amount = '%d' AND comparison_every_unit = '%s') AND (today_comparison_limit_min = '%s' AND today_comparison_limit_max = '%s')",
			$this->cryptocurrency_tables['ticker_enabled_data'],
			$this->db_cryptocurrency->escape_str($enabled_seq),
			$this->db_cryptocurrency->escape_str($comparison_date),
			$this->db_cryptocurrency->escape_str($comparison_datetime['starting']),
			$this->db_cryptocurrency->escape_str($comparison_datetime['stopping']),
			$this->db_cryptocurrency->escape_str($comparison_every['amount']),
			$this->db_cryptocurrency->escape_str($comparison_every['unit']),
			$this->db_cryptocurrency->escape_str($comparison_every['limit_min']),
			$this->db_cryptocurrency->escape_str($comparison_every['limit_max'])
		);
		
		
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return 0;
		}
		$row = $sql_query->row();
		if (isset($row->value)) {
			return (int)$row->value;
		} else {
			return 0;
		}
	}
	//=== GET
	function get_ticker_data_collection_by($ticker_seq, $date, $by_type, $by_value) {
		$ticker_seq = (is_numeric($ticker_seq) ? (int)$ticker_seq : 0);
		try {
			$selected_date_object = date_create_from_format('Y-m-d', $date);
		} catch (Exception $ex) {
			throw $ex;
			$selected_date_object = FALSE;
		}
		if ($selected_date_object !== FALSE) {
			$query_date = date_format($selected_date_object, 'Y-m-d');
		} else {
			$query_date = $this->ModelDateObject->format('Y-m-d');
		}
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'minute');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'minute':
			case 'hour':
			case 'day':
			default:
				$value = sprintf('%d', $value);
			break;
		}
		try {
			$DateObject = $this->authentication->create_dateobject(ConstantConfig::$timezone, 'Y-m-d', $query_date);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		switch (strtolower($by_type)) {
			case 'minute':
			default:
				$timeline_params = $this->generate_datetime_interval_collections($DateObject->format('Y-m-d'), 'minute', $value);
			break;
			case 'hour':
				$timeline_params = $this->generate_datetime_interval_collections($DateObject->format('Y-m-d'), 'hour', $value);
			break;
			case 'day':
				$timeline_params = $this->generate_datetime_interval_collections($DateObject->format('Y-m-d'), 'day', $value);
			break;
		}
		if (count($timeline_params) > 0) {
			foreach ($timeline_params as &$tlVal) {
				$tlVal['sql'] = sprintf("SELECT MIN(CAST(t.item_amount AS DECIMAL(20,8))) AS min_amount, MAX(CAST(t.item_amount AS DECIMAL(20,8))) AS max_amount, AVG(CAST(t.item_amount AS DECIMAL(20,8))) AS avg_amount, MAX(t.seq) AS last_seq FROM %s AS t WHERE (t.ticker_seq = '%d' AND t.item_date = '%s') AND (DATE_FORMAT(t.item_datetime, '%%Y-%%m-%%d %%H:%%i') BETWEEN '%s' AND '%s')",
					$this->cryptocurrency_tables['ticker_data'],
					$this->db_cryptocurrency->escape_str($ticker_seq),
					$this->db_cryptocurrency->escape_str($date),
					$this->db_cryptocurrency->escape_str($tlVal['starting']),
					$this->db_cryptocurrency->escape_str($tlVal['stopping'])
				);
				try {
					$sql_query = $this->db_cryptocurrency->query($tlVal['sql']);
				} catch (Exception $ex) {
					throw $ex;
					$tlVal['result'] = FALSE;
				}
				$tlVal['result'] = $sql_query->row();
				$tlVal['result']->last_amount = 0;
				if (isset($tlVal['result']->last_seq)) {
					$sql = sprintf("SELECT CAST(t.item_amount AS DECIMAL(20,8)) AS last_amount FROM %s WHERE (t.ticker_seq = '%d' AND t.item_date = '%s') AND (DATE_FORMAT(t.item_datetime, '%%Y-%%m-%%d %%H:%%i') BETWEEN '%s' AND '%s') ORDER BY t.item_datetime DESC LIMIT 1",
						$this->cryptocurrency_tables['ticker_data'],
						$this->db_cryptocurrency->escape_str($ticker_seq),
						$this->db_cryptocurrency->escape_str($date),
						$this->db_cryptocurrency->escape_str($tlVal['starting']),
						$this->db_cryptocurrency->escape_str($tlVal['stopping'])
					);
					try {
						$sql_query = $this->db_cryptocurrency->query($sql);
					} catch (Exception $ex) {
						exit("Cannot query for get last-amount of ticker.");
					}
					$tmp_last_amount = $sql_query->row()->last_amount;
										
					
					$last_amount = $this->db_cryptocurrency->select('CAST(item_amount AS DECIMAL(20,8)) AS last_item_amount')->from($this->cryptocurrency_tables['ticker_data'])->where('seq', $tlVal['result']->last_seq)->get()->row();
					if (isset($last_amount->last_item_amount)) {
						$tlVal['result']->last_amount = $last_amount->last_item_amount;
					}
					if ((int)$tlVal['result']->last_amount == 0) {
						$tlVal['result']->last_amount = sprintf("%d", $tmp_last_amount);
					}
				}
			}
		}
		return $timeline_params;
	}
	private function generate_datetime_interval_collections($date, $unit, $amount) {
		$time_interval = array(
			'starting'		=> new \DateTime("{$date} 00:00"),
			'stopping'		=> new \DateTime("{$date} 23:59"),
		);
		$time_array = array();
		$while_i = 0;
		$amount = (is_numeric($amount) ? (int)$amount : 1);
		if ($amount < 1) {
			$amount = 1;
		}
		switch (strtolower($unit)) {
			case 'day':
				while ($time_interval['starting'] <= $time_interval['stopping']) {
					$time_array[$while_i] = array(
						'starting'		=> $time_interval['starting']->format('Y-m-d H:i'),
					);
					$time_interval['starting']->add(new \DateInterval("P{$amount}D"));
					$time_array[$while_i]['stopping'] = $time_interval['starting']->format('Y-m-d H:i');
					$while_i += 1;
				}
			break;
			case 'hour':
				while ($time_interval['starting'] <= $time_interval['stopping']) {
					$time_array[$while_i] = array(
						'starting'		=> $time_interval['starting']->format('Y-m-d H:i'),
					);
					$time_interval['starting']->add(new \DateInterval("PT{$amount}H"));
					$time_array[$while_i]['stopping'] = $time_interval['starting']->format('Y-m-d H:i');
					$while_i += 1;
				}
			break;
			case 'minute':
			default:
				while ($time_interval['starting'] <= $time_interval['stopping']) {
					$time_array[$while_i] = array(
						'starting'		=> $time_interval['starting']->format('Y-m-d H:i'),
					);
					$time_interval['starting']->add(new \DateInterval("PT{$amount}M"));
					$time_array[$while_i]['stopping'] = $time_interval['starting']->format('Y-m-d H:i');
					$while_i += 1;
				}
			break;
		}
		return $time_array;
	}
	//---------------------------------------------------
	// Ticker Enabled Comparison List Data
	//---------------------------------------------------
	function get_ticker_enabled_comparison_count_by($by_type, $by_value, $tickerdata_date, $comparison_every, $search_text = '') {
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
		$unit_comparison_every = array(
			'unit'		=> (isset($comparison_every['unit']) ? $comparison_every['unit'] : 'hour'),
			'amount'	=> (isset($comparison_every['amount']) ? $comparison_every['amount'] : 1),
			'limit'		=> (isset($comparison_every['limit']) ? $comparison_every['limit'] : '0'),
			'limit_min'	=> (isset($comparison_every['limit_min']) ? $comparison_every['limit_min'] : '0'),
			'limit_max'	=> (isset($comparison_every['limit_max']) ? $comparison_every['limit_max'] : '0'),
		);
		$unit_comparison_every['unit'] = (is_string($unit_comparison_every['unit']) ? strtolower($unit_comparison_every['unit']) : 'hour');
		$unit_comparison_every['amount'] = (is_numeric($unit_comparison_every['amount']) ? (int)$unit_comparison_every['amount'] : 1);
		$unit_comparison_every['limit'] = (is_numeric($unit_comparison_every['limit']) || is_string($unit_comparison_every['limit'])) ? sprintf("%s", $unit_comparison_every['limit']) : '0';
		$unit_comparison_every['limit_min'] = (is_numeric($unit_comparison_every['limit_min']) || is_string($unit_comparison_every['limit_min'])) ? sprintf("%s", $unit_comparison_every['limit_min']) : '0';
		$unit_comparison_every['limit_max'] = (is_numeric($unit_comparison_every['limit_max']) || is_string($unit_comparison_every['limit_max'])) ? sprintf("%s", $unit_comparison_every['limit_max']) : '0';
		$sql = sprintf("SELECT COUNT(d.seq) AS value FROM %s AS d LEFT JOIN %s AS e ON e.seq = d.enabled_seq",
			$this->cryptocurrency_tables['ticker_enabled_data'],
			$this->cryptocurrency_tables['ticker_enabled']
		);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" WHERE LOWER(e.cryptocurrency_code) = LOWER('%s')", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" WHERE d.enabled_seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= sprintf(" AND (d.comparison_date = '%s')", $this->db_cryptocurrency->escape_str($tickerdata_date));
		if (sprintf("%.02f", $unit_comparison_every['limit_min']) != 0.00) {
			$sql .= sprintf(" AND (d.today_comparison_limit_min = '%s')", $this->db_cryptocurrency->escape_str($unit_comparison_every['limit_min']));
		}
		if (sprintf("%.02f", $unit_comparison_every['limit_max']) != 0.00) {
			$sql .= sprintf(" AND (d.today_comparison_limit_max = '%s')", $this->db_cryptocurrency->escape_str($unit_comparison_every['limit_max']));
		}
		/*
		$sql .= sprintf(" AND (d.comparison_every_unit = '%s' AND d.comparison_every_amount = '%d')",
			$this->db_cryptocurrency->escape_str($unit_comparison_every['unit']),
			$this->db_cryptocurrency->escape_str($unit_comparison_every['amount'])
		);
		*/
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
						$sql_likes .= " AND (CONCAT('', d.comparison_after_exchange_persen, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', d.comparison_after_exchange_persen, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->row();
	}
	function get_ticker_enabled_comparison_data_by($by_type, $by_value, $tickerdata_date, $comparison_every, $search_text = '', $start = 0, $per_page = 10) {
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
		$unit_comparison_every = array(
			'unit'		=> (isset($comparison_every['unit']) ? $comparison_every['unit'] : 'hour'),
			'amount'	=> (isset($comparison_every['amount']) ? $comparison_every['amount'] : 1),
			'limit'		=> (isset($comparison_every['limit']) ? $comparison_every['limit'] : 0),
			'limit_min'	=> (isset($comparison_every['limit_min']) ? $comparison_every['limit_min'] : 0),
			'limit_max'	=> (isset($comparison_every['limit_max']) ? $comparison_every['limit_max'] : 0),
		);
		$unit_comparison_every['unit'] = (is_string($unit_comparison_every['unit']) ? strtolower($unit_comparison_every['unit']) : 'hour');
		$unit_comparison_every['amount'] = (is_numeric($unit_comparison_every['amount']) ? (int)$unit_comparison_every['amount'] : 1);
		$unit_comparison_every['limit'] = (is_numeric($unit_comparison_every['limit']) || is_string($unit_comparison_every['limit'])) ? sprintf("%s", $unit_comparison_every['limit']) : '0';
		$unit_comparison_every['limit_min'] = (is_numeric($unit_comparison_every['limit_min']) || is_string($unit_comparison_every['limit_min'])) ? sprintf("%s", $unit_comparison_every['limit_min']) : '0';
		$unit_comparison_every['limit_max'] = (is_numeric($unit_comparison_every['limit_max']) || is_string($unit_comparison_every['limit_max'])) ? sprintf("%s", $unit_comparison_every['limit_max']) : '0';
		$sql = sprintf("SELECT d.*, DATE_FORMAT(d.comparison_datetime_starting, '%%H:%%i:%%s') AS comparison_datetime_starting_time, DATE_FORMAT(d.comparison_datetime_stopping, '%%H:%%i:%%s') AS comparison_datetime_stopping_time, e.cryptocurrency_code, e.cryptocurrency_premium_limit_min, e.cryptocurrency_premium_limit_max, e.cryptocurrency_is_enabled, e.cryptocurrency_compare_amount, e.cryptocurrency_compare_unit, e.cryptocurrency_compare_ticker_seq_from, e.cryptocurrency_compare_ticker_seq_to FROM %s AS d LEFT JOIN %s AS e ON e.seq = d.enabled_seq",
			$this->cryptocurrency_tables['ticker_enabled_data'],
			$this->cryptocurrency_tables['ticker_enabled']
		);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" WHERE LOWER(e.cryptocurrency_code) = LOWER('%s')", $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" WHERE d.enabled_seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql .= sprintf(" AND (d.comparison_date = '%s')", $this->db_cryptocurrency->escape_str($tickerdata_date));
		if (sprintf("%.02f", $unit_comparison_every['limit_min']) != 0.00) {
			$sql .= sprintf(" AND (d.today_comparison_limit_min = '%s')", $this->db_cryptocurrency->escape_str($unit_comparison_every['limit_min']));
		}
		if (sprintf("%.02f", $unit_comparison_every['limit_max']) != 0.00) {
			$sql .= sprintf(" AND (d.today_comparison_limit_max = '%s')", $this->db_cryptocurrency->escape_str($unit_comparison_every['limit_max']));
		}
		/*
		$sql .= sprintf(" AND (d.comparison_every_unit = '%s' AND d.comparison_every_amount = '%d')",
			$this->db_cryptocurrency->escape_str($unit_comparison_every['unit']),
			$this->db_cryptocurrency->escape_str($unit_comparison_every['amount'])
		);
		*/
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
						$sql_likes .= " AND (CONCAT('', d.comparison_after_exchange_persen, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					} else {
						$sql_likes .= " (CONCAT('', d.comparison_after_exchange_persen, '') LIKE '%{$this->db_cryptocurrency->escape_str($val)}%')";
					}
					$for_i++;
				}	
			} else {
				$sql_likes .= " 1=1";
			}
			$sql_likes .= ")";
			$sql .= $sql_likes;
		}
		$sql .= " ORDER BY d.comparison_datetime_starting DESC";
		$sql .= sprintf(" LIMIT %d, %d", $start, $per_page);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	function get_ticker_enabled_comparison_grouped_limit($enabled_seq = 0, $tickerdata_date = null) {
		$enabled_seq = (is_numeric($enabled_seq) ? (int)$enabled_seq : 0);
		if (!isset($tickerdata_date)) {
			$tickerdata_date = date('Y-m-d');
		}
		$sql = sprintf("SELECT DISTINCT today_comparison_limit_min, today_comparison_limit_max FROM %s WHERE enabled_seq = '%d' AND DATE(comparison_date) = '%s' ORDER BY today_comparison_limit_min ASC",
			$this->cryptocurrency_tables['ticker_enabled_data'],
			$this->db_cryptocurrency->escape_str($enabled_seq),
			$this->db_cryptocurrency->escape_str($tickerdata_date)
		);
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	//---------------------------------------------------
	
	
	
	
	
	
	
	function get_ticker_data_by($by_type, $by_value) {
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
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['tickers']);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$this->db_cryptocurrency->where('ticker_exchange', $this->db_cryptocurrency->escape_str($value));
			break;
			case 'seq':
			case 'id':
			default:
				$this->db_cryptocurrency->where('seq', $this->db_cryptocurrency->escape_str($value));
			break;
		}
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->row();
	}
	
	
	
	//=========
	function get_enabled_data_single_by($by_type, $by_value) {
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
		$sql = sprintf("SELECT * FROM %s", $this->cryptocurrency_tables['ticker_enabled']);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" WHERE cryptocurrency_code = '%s'", $this->db_cryptocurrency->escape_str(strtoupper($value)));
			break;
			case 'seq':
			case 'id':
			default:
				$sql .= sprintf(" WHERE seq = '%d'", $this->db_cryptocurrency->escape_str($value));
			break;
		}
		try {
			$sql_query = $this->db_cryptocurrency->query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		$row = $sql_query->row();
		if (isset($row->cryptocurrency_compare_ticker_seq_from)) {
			$sql = sprintf("SELECT e.*, t1.seq AS from_ticker_seq, t1.market_seq AS from_market_seq, t2.seq AS to_ticker_seq, t2.market_seq AS to_market_seq, m1.market_code AS from_market_code, m1.market_name AS from_market_name, m1.market_address AS from_market_address, m1.market_is_enabled AS from_market_is_enabled, m2.market_code AS to_market_code, m2.market_name AS to_market_name, m2.market_address AS to_market_address, m2.market_is_enabled AS to_market_is_enabled FROM %s AS e LEFT JOIN %s AS t1 ON t1.seq = e.cryptocurrency_compare_ticker_seq_from LEFT JOIN %s AS t2 ON t2.seq = e.cryptocurrency_compare_ticker_seq_to INNER JOIN %s AS m1 ON m1.seq = t1.market_seq INNER JOIN %s AS m2 ON m2.seq = t2.market_seq WHERE e.seq = '%d'",
				$this->cryptocurrency_tables['ticker_enabled'],
				$this->cryptocurrency_tables['tickers'],
				$this->cryptocurrency_tables['tickers'],
				$this->cryptocurrency_tables['marketplace'],
				$this->cryptocurrency_tables['marketplace'],
				$this->db_cryptocurrency->escape_str($row->seq)
			);
			$sql_query = $this->db_cryptocurrency->query($sql);
			$row->ticker_data = $sql_query->row();
		}
		return $row;
	}
	function set_enabled_data_single_by($by_type, $by_value, $input_params = array()) {
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
		$enabled_data = $this->get_enabled_data_single_by('seq', $value);
		if (!isset($enabled_data->seq)) {
			return FALSE;
			// Data not exists
		}
		$query_params = array(
			'cryptocurrency_premium_limit_min' => (isset($input_params['cryptocurrency_premium_limit_min']) ? $input_params['cryptocurrency_premium_limit_min'] : 0),
			'cryptocurrency_premium_limit_max' => (isset($input_params['cryptocurrency_premium_limit_max']) ? $input_params['cryptocurrency_premium_limit_max'] : 0),
			'cryptocurrency_compare_amount' => (isset($input_params['cryptocurrency_compare_amount']) ? $input_params['cryptocurrency_compare_amount'] : 0),
			'cryptocurrency_is_enabled' => (isset($input_params['cryptocurrency_is_enabled']) ? $input_params['cryptocurrency_is_enabled'] : 'N'),
			'cryptocurrency_compare_unit' => (isset($input_params['cryptocurrency_compare_unit']) ? $input_params['cryptocurrency_compare_unit'] : 'hour'),
			'cryptocurrency_compare_ticker_seq_from' => (isset($input_params['cryptocurrency_compare_ticker_seq_from']) ? $input_params['cryptocurrency_compare_ticker_seq_from'] : 0),
			'cryptocurrency_compare_ticker_seq_to' => (isset($input_params['cryptocurrency_compare_ticker_seq_to']) ? $input_params['cryptocurrency_compare_ticker_seq_to'] : 0),
		);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$this->db_cryptocurrency->where('cryptocurrency_code', $value);
			break;
			case 'seq':
			case 'id':
			default:
				$this->db_cryptocurrency->where('seq', $value);
			break;
		}
		$this->db_cryptocurrency->update($this->cryptocurrency_tables['ticker_enabled'], $query_params);
		return $this->db_cryptocurrency->affected_rows();
	}
	function insert_enabled_data_single_by($input_params = array()) {
		$query_params = array(
			'cryptocurrency_code' => (isset($input_params['cryptocurrency_code']) ? $input_params['cryptocurrency_code'] : ''),
			'cryptocurrency_from_realcurrency' => (isset($input_params['cryptocurrency_from_realcurrency']) ? $input_params['cryptocurrency_from_realcurrency'] : ''),
			'cryptocurrency_premium_limit_min' => (isset($input_params['cryptocurrency_premium_limit_min']) ? $input_params['cryptocurrency_premium_limit_min'] : 0),
			'cryptocurrency_premium_limit_max' => (isset($input_params['cryptocurrency_premium_limit_max']) ? $input_params['cryptocurrency_premium_limit_max'] : 0),
			'cryptocurrency_compare_amount' => (isset($input_params['cryptocurrency_compare_amount']) ? $input_params['cryptocurrency_compare_amount'] : 0),
			'cryptocurrency_is_enabled' => (isset($input_params['cryptocurrency_is_enabled']) ? $input_params['cryptocurrency_is_enabled'] : 'N'),
			'cryptocurrency_compare_unit' => (isset($input_params['cryptocurrency_compare_unit']) ? $input_params['cryptocurrency_compare_unit'] : 'hour'),
			'cryptocurrency_compare_ticker_seq_from' => (isset($input_params['cryptocurrency_compare_ticker_seq_from']) ? $input_params['cryptocurrency_compare_ticker_seq_from'] : 0),
			'cryptocurrency_compare_ticker_seq_to' => (isset($input_params['cryptocurrency_compare_ticker_seq_to']) ? $input_params['cryptocurrency_compare_ticker_seq_to'] : 0),
		);
		$this->db_cryptocurrency->trans_start();
		$this->db_cryptocurrency->insert($this->cryptocurrency_tables['ticker_enabled'], $query_params);
		$insert_seq = $this->db_cryptocurrency->insert_id();
		$this->db_cryptocurrency->trans_complete();
		return $insert_seq;
	}
	//===============================================
	function get_all_currencies($by_type, $is_active = FALSE, $market_seq = 0) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'currencies');
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['currencies']);
		switch (strtolower($by_type)) {
			case 'real':
				$this->db_cryptocurrency->where('currency_market_class', 'real');
			break;
			case 'currencies':
			default:
				$this->db_cryptocurrency->where('currency_market_class', 'currency');
			break;
		}
		if ($is_active === TRUE) {
			$this->db_cryptocurrency->where('currency_is_enabled', 'Y');
		}
		$market_seq = (is_numeric($market_seq) ? (int)$market_seq : 0);
		if ($market_seq > 0) {
			$this->db_cryptocurrency->where('market_seq', $market_seq);
		}
		$this->db_cryptocurrency->group_by('currency_code');
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->result();
	}
	//------------------------------------------------------------------------------------------------------
	function get_email_templates_by($by_type, $by_value) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (is_numeric($by_value) || is_string($by_value)) {
			$value = sprintf("%s", $by_value);
		} else {
			$value = "";
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		$value = strtolower($value);
		switch (strtolower($by_type)) {
			case 'status':
			case 'slug':
			case 'code':
				$value = sprintf("%s", $value);
			break;
			case 'id':
			case 'seq':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $by_value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['ticker_email_templates']);
		switch (strtolower($by_type)) {
			case 'status':
			case 'slug':
			case 'code':
				$this->db_cryptocurrency->where('email_status', $value);
			break;
			case 'id':
			case 'seq':
			default:
				$this->db_cryptocurrency->where('seq', $value);
			break;
		}
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->row();
	}
	function set_email_templates($seq = 0, $input_params) {
		$seq = (int)$seq;
		$this->db_cryptocurrency->where('seq', $seq);
		$this->db_cryptocurrency->set('description_update', 'NOW()', FALSE);
		$this->db_cryptocurrency->update($this->cryptocurrency_tables['ticker_email_templates'], $input_params);
		$affected_rows = $this->db_cryptocurrency->affected_rows();
		return $affected_rows;
	}
	function get_email_address_single_by($by_type, $by_value, $is_active = 0) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (is_numeric($by_value) || is_string($by_value)) {
			$value = sprintf("%s", $by_value);
		} else {
			$value = "";
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		$value = strtolower($value);
		$is_active = (is_numeric($is_active) ? (int)$is_active : 0);
		switch (strtolower($by_type)) {
			case 'address':
			case 'slug':
			case 'code':
				$value = sprintf("%s", $value);
			break;
			case 'id':
			case 'seq':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $by_value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$this->db_cryptocurrency->select('*')->from($this->cryptocurrency_tables['ticker_email']);
		switch (strtolower($by_type)) {
			case 'address':
			case 'slug':
			case 'code':
				$this->db_cryptocurrency->where('email_address', $value);
			break;
			case 'id':
			case 'seq':
			default:
				$this->db_cryptocurrency->where('seq', $value);
			break;
		}
		if ($is_active > 0) {
			$this->db_cryptocurrency->where('email_is_enabled', 'Y');
		}
		$this->db_cryptocurrency->limit(1);
		$this->db_cryptocurrency->order_by('seq', 'ASC');
		$sql_query = $this->db_cryptocurrency->get();
		return $sql_query->row();
	}
	function set_email_address_single_by($email_seq, $input_params) {
		$email_seq = (int)$email_seq;
		$this->db_cryptocurrency->where('seq', $email_seq);
		$this->db_cryptocurrency->set('email_update', 'NOW()', FALSE);
		$this->db_cryptocurrency->update($this->cryptocurrency_tables['ticker_email'], $input_params);
		$affected_rows = $this->db_cryptocurrency->affected_rows();
		return $affected_rows;
	}
	function insert_email_address_single_by($input_params) {
		$this->db_cryptocurrency->trans_start();
		$this->db_cryptocurrency->insert($this->cryptocurrency_tables['ticker_email'], $input_params);
		$email_address_seq = $this->db_cryptocurrency->insert_id();
		$this->db_cryptocurrency->trans_complete();
		return $email_address_seq;
	}
	function delete_email_address_single_by($by_type, $by_value) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (is_numeric($by_value) || is_string($by_value)) {
			$value = sprintf("%s", $by_value);
		} else {
			$value = "";
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		$value = strtolower($value);
		switch (strtolower($by_type)) {
			case 'address':
			case 'slug':
			case 'code':
				$value = sprintf("%s", $value);
				$this->db_cryptocurrency->where('email_address', $value);
			break;
			case 'id':
			case 'seq':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $by_value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
				$this->db_cryptocurrency->where('seq', $value);
			break;
		}
		$this->db_cryptocurrency->delete($this->cryptocurrency_tables['ticker_email']);
		return $this->db_cryptocurrency->affected_rows();
	}
	//====
	function get_email_address_count($is_active = 0, $search_text = '') {
		$is_active = (is_numeric($is_active) ? (int)$is_active : 0);
		$this->db_cryptocurrency->select("COUNT(seq) AS value")->from($this->cryptocurrency_tables['ticker_email']);
		if ($is_active > 0) {
			$this->db_cryptocurrency->where('email_is_enabled', 'Y');
		}
		$search_text = (is_string($search_text) ? $search_text : '');
		if (!empty($search_text) && (strlen($search_text) > 0)) {
			$sql_likes = "";
			$search_array = base_permalink($search_text);
			$search_array = explode("-", $search_array);
			if (count($search_array) > 0) {
				$for_i = 0;
				foreach ($search_array as $val) {
					if ($for_i > 0) {
						$sql_likes .= sprintf(" AND (CONCAT('', email_address, '') LIKE '%%%s%%' OR CONCAT('', email_name, '') LIKE '%%%s%%')", 
							$this->db_cryptocurrency->escape_str($val),
							$this->db_cryptocurrency->escape_str($val)
						);
					} else {
						$sql_likes .= sprintf("(CONCAT('', email_address, '') LIKE '%%%s%%' OR CONCAT('', email_name, '') LIKE '%%%s%%')",
							$this->db_cryptocurrency->escape_str($val),
							$this->db_cryptocurrency->escape_str($val)
						);
					}
					$for_i++;
				}
				$this->db_cryptocurrency->where($sql_likes, NULL, FALSE);
			}
		}
		return $this->db_cryptocurrency->get()->row();
	}
	function get_email_address_data($is_active = 0, $search_text = '', $start = 0, $per_page = 10) {
		$is_active = (is_numeric($is_active) ? (int)$is_active : 0);
		$this->db_cryptocurrency->select("*")->from($this->cryptocurrency_tables['ticker_email']);
		if ($is_active > 0) {
			$this->db_cryptocurrency->where('email_is_enabled', 'Y');
		}
		$search_text = (is_string($search_text) ? $search_text : '');
		if (!empty($search_text) && (strlen($search_text) > 0)) {
			$sql_likes = "";
			$search_array = base_permalink($search_text);
			$search_array = explode("-", $search_array);
			if (count($search_array) > 0) {
				$for_i = 0;
				foreach ($search_array as $val) {
					if ($for_i > 0) {
						$sql_likes .= sprintf(" AND (CONCAT('', email_address, '') LIKE '%%%s%%' OR CONCAT('', email_name, '') LIKE '%%%s%%')", 
							$this->db_cryptocurrency->escape_str($val),
							$this->db_cryptocurrency->escape_str($val)
						);
					} else {
						$sql_likes .= sprintf("(CONCAT('', email_address, '') LIKE '%%%s%%' OR CONCAT('', email_name, '') LIKE '%%%s%%')",
							$this->db_cryptocurrency->escape_str($val),
							$this->db_cryptocurrency->escape_str($val)
						);
					}
					$for_i++;
				}
				$this->db_cryptocurrency->where($sql_likes, NULL, FALSE);
			}
		}
		$this->db_cryptocurrency->order_by('email_address', 'ASC');
		$this->db_cryptocurrency->limit($per_page, $start);
		try {
			$sql_query = $this->db_cryptocurrency->get();
		} catch (Exception $ex) {
			throw $ex;
			return FALSE;
		}
		return $sql_query->result();
	}
	//------------------------------------------------------------------------------------------------------
}




















