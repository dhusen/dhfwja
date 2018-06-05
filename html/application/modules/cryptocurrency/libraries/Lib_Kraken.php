<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
# Use Imzers Lib
use Imzers\Utils\Utils;
use Imzers\Utils\Curl;
class Lib_Kraken {
	protected $CI;
	protected $base_kraken;
	protected $kraken;
	function __construct($base_kraken = NULL) {
		if (!isset($base_kraken)) {
			$this->CI = &get_instance();
			$this->CI->load->config('cryptocurrency/base_cryptocurrency');
			$base_cryptocurrency = $this->CI->config->item('base_cryptocurrency');
			$this->base_kraken = (isset($base_cryptocurrency['market']['kraken']) ? $base_cryptocurrency['market']['kraken'] : FALSE);
		} else {
			$this->base_kraken = $base_kraken;
		}
		if (isset($this->base_kraken['server'])) {
			$this->set_api_endpoint($this->base_kraken['server']);
		}
		$this->kraken = new KrakenAPI($this->base_kraken['client']['api'], $this->api_endpoint, $this->base_kraken['client']['ssl']);
	}
	private function set_api_endpoint($api_env) {
		if (isset($this->base_kraken['url'][$api_env])) {
			$this->api_endpoint = $this->base_kraken['url'][$api_env];
		} else {
			$this->api_endpoint = 'https://api.beta.kraken.com';
		}
		return $this;
	}
	
	function get_kraken() {
		return $this->api_endpoint;
	}
	public function get_marketplace_assets() {
		try {
			$marketplace_assets = $this->kraken->QueryPublic('Assets');
		} catch (Exception $ex) {
			throw $ex;
			return array(
				'error'		=> TRUE,
				'result'	=> NULL,
			);
		}
		return $marketplace_assets;
	}
	public function get_marketplace_ticker($ticker_currency = NULL) {
		if (!isset($ticker_currency)) {
			$ticker_currency = 'XXRPZUSD';
		}
		try {
			$marketplace_ticker = $this->kraken->QueryPublic('Ticker', array('pair' => $ticker_currency));
		} catch (Exception $ex) {
			throw $ex;
			return array(
				'error'		=> TRUE,
				'result'	=> NULL,
			);
		}
		return $marketplace_ticker;
	}
	
	
	
}






