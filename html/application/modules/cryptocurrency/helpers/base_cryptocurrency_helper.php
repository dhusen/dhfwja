<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

if (!function_exists('base_cryptocurrency')) {
	function base_cryptocurrency($config_name = '') {
		$CI = &get_instance();
		$CI->load->config('base_cryptocurrency');
		$base_cryptocurrency = $CI->config->item('base_cryptocurrency');
		if (isset($base_cryptocurrency[$config_name])) {
			return $base_cryptocurrency[$config_name];
		}
		return "";
	}
}