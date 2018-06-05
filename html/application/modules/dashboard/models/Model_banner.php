<?php 
if ( ! defined('BASEPATH')) { exit('No direct script access allowed: ' . (__FILE__)); }
class Model_banner extends CI_Model {
	private $databases = array();
	protected $db_web;
	protected $web_tables = array();
	function __construct() {
		parent::__construct();
		$this->load->config('dashboard/base_dashboard');
		$this->base_dashboard = $this->config->item('base_dashboard');
		$this->load->library('dashboard/Lib_authentication', $this->base_dashboard, 'authentication');
		$this->load->library('dashboard/Lib_imzers', $this->base_dashboard, 'imzers');
		$this->db_web = $this->load->database('web', TRUE);
		$this->web_tables = (isset($this->base_dashboard['web_tables']) ? $this->base_dashboard['web_tables'] : array());
	}
	
	function get_banner_location() {
		return $this->db_web->get($this->web_tables['banner_location'])->result();
	}
	function get_banner_location_by($by_type, $by_value) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'location');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'location':
			default:
				if (!preg_match('/^[a-z0-9]*$/', $by_value)) {
					$value = 'home';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
		}
		$sql = sprintf("SELECT * FROM %s WHERE", $this->web_tables['banner_location']);
		switch (strtolower($by_type)) {
			case 'location':
			default:
				$sql .= sprintf(" location = '%s'", $this->imzers->sql_addslashes($value));
			break;
		}
		return $this->db_web->query($sql)->row();
	}
	//=========================================================
	function get_banner_slider_count_by($by_type, $by_value, $search_text = '')  {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'location':
			default:
				if (!preg_match('/^[a-z0-9]*$/', $by_value)) {
					$value = 'home';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
		}
		$this->db_web->select("COUNT(seq) AS value")->from($this->web_tables['banner_slider']);
		switch (strtolower($by_type)) {
			case 'location':
			default:
				$this->db_web->where('banner_location', $this->db_web->escape_str($value));
			break;
		}
		$search_text = (is_string($search_text) ? $search_text : '');
		if (!empty($search_text) && (strlen($search_text) > 0)) {
			$search_array = base_permalink($search_text);
			$search_array = explode("-", $search_array);
			if (count($search_array) > 0) {
				$for_i = 0;
				foreach ($search_array as $val) {
					$this->db_web->like('banner_title', $this->db_web->escape_str($val), 'both');
					$for_i++;
				}
			}
		}
		return $this->db_web->get()->row();
    }
	function get_banner_slider_data_by($by_type, $by_value, $search_text = '', $start = 0, $per_page = 10) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value) || is_string($value)) {
				$value = sprintf("%s", $by_value);
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'location':
			default:
				if (!preg_match('/^[a-z0-9]*$/', $by_value)) {
					$value = 'home';
				} else {
					$value = sprintf('%s', $value);
				}
			break;
		}
		$sql = sprintf("SELECT b.*, l.location FROM %s AS b LEFT JOIN %s AS l ON l.location = b.banner_location WHERE",
			$this->web_tables['banner_slider'],
			$this->web_tables['banner_location']
		);
		switch (strtolower($by_type)) {
			case 'location':
			default:
				$sql_wheres = sprintf(" b.banner_location = '%s'", $this->db_web->escape_str($value));
			break;
		}
		$sql .= $sql_wheres;
		$search_text = (is_string($search_text) ? $search_text : '');
		if (!empty($search_text) && (strlen($search_text) > 0)) {
			$sql .= " AND (";
			$sql_likes = "";
			$search_array = base_permalink($search_text);
			$search_array = explode("-", $search_array);
			if (count($search_array) > 0) {
				$for_i = 0;
				foreach ($search_array as $val) {
					if ($for_i > 0) {
						$sql_likes .= " AND (CONCAT('', b.banner_title, '') LIKE '%{$this->db_web->escape_str($value)}%')";
					} else {
						$sql_likes .= " (CONCAT('', b.banner_title, '') LIKE '%{$this->db_web->escape_str($value)}%')";
					}
					$for_i++;
				}
			}
			$sql .= $sql_likes;
			$sql .= ")";
		}
		$sql .= " ORDER BY b.banner_order ASC";
		$sql .= sprintf(" LIMIT %d, %d", $start, $per_page);
		$sql_query = $this->db_web->query($sql);
		return $sql_query->result();
	}
	//=========================================================
	function get_banner_slider_by($by_type, $by_value, $input_params = array()) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value)) {
				$value = (int)$by_value;
			} else if (is_string($by_value)) {
				$value = sprintf("%s", $by_value);
			} else {
				$value = "";
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'location':
			case 'code':
			case 'slug':
				$value = sprintf("%s", $value);
			break;
			case 'seq':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $by_value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT b.*, l.location FROM %s AS b LEFT JOIN %s AS l ON l.location = b.banner_location WHERE", 
			$this->web_tables['banner_slider'],
			$this->web_tables['banner_location']
		);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$sql .= sprintf(" b.banner_slug = '%s'", $this->db_web->escape_str($value));
			break;
			case 'location':
				$sql .= sprintf(" b.banner_location = '%s'", $this->db_web->escape_str($value));
				if (is_array($input_params)) {
					if (count($input_params) > 0) {
						$for_i = 0;
						$sql .= " AND (";
						foreach ($input_params as $key => $val) {
							if ($for_i > 0) {
								$sql .= sprintf(" AND b.%s = '%s'", $this->db_web->escape_str($key), $this->db_web->escape_str($val));
							} else {
								$sql .= sprintf("b.%s = '%s'", $this->db_web->escape_str($key), $this->db_web->escape_str($val));
							}
							$for_i++;
						}
						$sql .= ")";
					}
				}
			break;
			case 'seq':
			default:
				$sql .= sprintf(" b.seq = '%d'", $this->db_web->escape_str($value));
			break;
		}
		$sql .= " ORDER BY b.banner_order ASC";
		$sql_query = $this->db_web->query($sql);
		return $sql_query->result();
	}
	function get_banner_slider_single_by($by_type, $by_value) {
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value)) {
				$value = (int)$by_value;
			} else if (is_string($by_value)) {
				$value = sprintf("%s", $by_value);
			} else {
				$value = "";
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$value = sprintf("%s", $value);
			break;
			case 'seq':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $by_value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$this->db_web->select('*')->from($this->web_tables['banner_slider']);
		switch (strtolower($by_type)) {
			case 'code':
			case 'slug':
				$this->db_web->where('banner_slug', $this->db_web->escape_str($value));
			break;
			case 'seq':
			default:
				$this->db_web->where('seq', $this->db_web->escape_str($value));
			break;
		}
		$this->db_web->limit(1);
		return $this->db_web->get()->row();
	}
	function get_banner_slider_single_with_location($location, $by_type, $by_value, $is_item_seq = 0) {
		$location = (is_string($location) ? strtolower($location) : 'home');
		$by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
		$value = "";
		if (isset($by_value)) {
			if (is_numeric($by_value)) {
				$value = (int)$by_value;
			} else if (is_string($by_value)) {
				$value = sprintf("%s", $by_value);
			} else {
				$value = "";
			}
		}
		$value = ((is_string($value) || is_numeric($value)) ? $value : '');
		switch (strtolower($by_type)) {
			case 'title':
			case 'code':
			case 'slug':
				$value = sprintf("%s", $value);
			break;
			case 'seq':
			default:
				if (!preg_match('/^[1-9][0-9]*$/', $by_value)) {
					$value = 0;
				} else {
					$value = sprintf('%d', $value);
				}
			break;
		}
		$sql = sprintf("SELECT seq AS value FROM %s WHERE (", $this->web_tables['banner_slider']);
		$sql .= sprintf("banner_location = '%s'", $this->db_web->escape_str($location));
		switch (strtolower($by_type)) {
			case 'title':
				$sql .= sprintf(" AND banner_title = '%s'", $this->db_web->escape_str($value));
			break;
			case 'code':
			case 'slug':
				$sql .= sprintf(" AND banner_slug = '%s'", $this->db_web->escape_str($value));
			break;
			case 'seq':
			default:
				$sql .= sprintf(" AND seq = '%d'", $this->db_web->escape_str($value));
			break;
		}
		$sql .= ")";
		if ($is_item_seq > 0) {
			if ($by_type !== 'seq') {
				$sql .= sprintf(" AND (seq != '%d')", $is_item_seq);
			}
		}
		$sql_query = $this->db_web->query($sql);
		while ($row = $sql_query->result()) {
			return $row;
		}
		return false;
	}
}


















