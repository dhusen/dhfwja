<?php 
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
class Cli_cryptocurrency_update_scheduler extends CI_Model {
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
	
	function get_enabled_ticker_comparison($is_enabled = 0) {
		$is_enabled = (int)$is_enabled;
		if ($is_enabled > 0) {
			$this->db_cryptocurrency->where('cryptocurrency_is_enabled', 'Y');
		}
		$this->db_cryptocurrency->order_by('cryptocurrency_code', 'ASC');
		$sql_query = $this->db_cryptocurrency->get($this->cryptocurrency_tables['ticker_enabled']);
		return $sql_query->result();
	}
	function get_notification_emails($is_enabled = 0) {
		$is_enabled = (int)$is_enabled;
		if ($is_enabled > 0) {
			$this->db_cryptocurrency->where('email_is_enabled', 'Y');
		}
		$sql_query = $this->db_cryptocurrency->get($this->cryptocurrency_tables['ticker_email']);
		return $sql_query->result();
	}
	
	
	
	//------------
	function usp_delete_ticker_data30days() {
		$this->db_cryptocurrency->query('CALL usp_delete_ticker_data30days()');
	}
	function usp_delete_ticker_logs_everyday() {
		$this->db_cryptocurrency->query('CALL usp_delete_ticker_logs_everyday()');
	}
}



