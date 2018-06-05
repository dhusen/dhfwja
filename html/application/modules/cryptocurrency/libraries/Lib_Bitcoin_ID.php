<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed.'); }
// Dokumentasi: https://vip.bitcoin.co.id/downloads/BITCOINCOID-API-DOCUMENTATION.pdf
# Use Imzers Lib
use Imzers\Utils\Utils;
use Imzers\Utils\Curl;
class Lib_Bitcoin_ID {
	protected $CI;
	protected $bitcoin_id;
	protected $utils, $curl;
	function __construct($bitcoin_id = NULL) {
		if (!isset($bitcoin_id)) {
			$this->CI = &get_instance();
			$this->CI->load->config('cryptocurrency/base_cryptocurrency');
			$base_cryptocurrency = $this->CI->config->item('base_cryptocurrency');
			$this->bitcoin_id = (isset($base_cryptocurrency['market']['bitcoin_id']) ? $base_cryptocurrency['market']['bitcoin_id'] : FALSE);
		} else {
			$this->bitcoin_id = $bitcoin_id;
		}
		$bitcoind_id_server = (isset($this->bitcoin_id['server']) ? $this->bitcoin_id['server'] : 'sandbox');
		if (isset($this->bitcoin_id['url'][$bitcoind_id_server])) {
			$this->set_api_endpoint($this->bitcoin_id['url'][$bitcoind_id_server]);
		} else {
			$this->set_api_endpoint('');
		}
		
		// Set Curl from Imzers Lib
		$this->curl = new Curl();
		$this->curl->set_endpoint($this->api_endpoint);
	}
	
	private function set_api_endpoint($api_endpoint) {
		$this->api_endpoint = $api_endpoint;
		return $this;
	}
	//===========================
	private function get_curl($url) {
		$headers = $this->curl->generate_curl_headers();
		try {
			$get_curl = $this->curl->create_curl_request('GET', $url, $this->curl->UA, $headers, NULL);
		} catch (Exception $ex) {
			throw $ex;
			$get_curl = false;
		}
		return $get_curl;
	}
	//===========================
	
	
	function get_marketplace_ticker($ticker_currency = NULL) {
		if (!isset($ticker_currency)) {
			$ticker_currency = 'xrp_idr';
		}
		$ticker_currency = strtolower($ticker_currency);
		$api_url = "{$this->curl->endpoint}/{$ticker_currency}/ticker";
		try {
			$marketplace_ticker = $this->get_curl($api_url);
		} catch (Exception $ex) {
			throw $ex;
			return array(
				'error'		=> TRUE,
				'result'	=> NULL,
			);
		}
		if (isset($marketplace_ticker['response']['body'])) {
			try {
				$json_response = json_decode($marketplace_ticker['response']['body'], true);
			} catch (Exception $ex) {
				throw $ex;
				return array(
					'error'		=> TRUE,
					'result'	=> NULL,
				);
			}
			
			
			return $json_response;
		}
	}
	
	
}











