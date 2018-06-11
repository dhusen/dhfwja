<?php
error_reporting(E_ALL ^ E_DEPRECATED);
class ConstantConfig {
	private static $instance = NULL;
	public static $timezone = 'Asia/Bangkok';
	const THIS_SERVER_NAME 				= 'dhfwja.com'; // Change domain of live or sandbox
	const THIS_SERVER_MODE 				= 'live'; // 'sandbox' || 'live'
	const THIS_SERVER_PROTOCOL			= 'https'; // 'http' || 'https'
	const THIS_SERVER_LOGPATH			= (__DIR__ . '/logs'); // Server Logs path
	## return to api caller
	const PUBLIC_URL_PROTOCOL			= 'https';
	const PUBLIC_URL_ADDRESS			= 'dhfwja.com';
	const PUBLIC_URL_PORT				= '80';
	const PUBLIC_URL_PATH				= '/home';
	## DB For Service Control Check
	const CONTROL_SERVICE_ENABLED		= 'Y'; // Y = Enabled, N = Disabled
	## Another constant you can put below:
	public static $THIS_SERVER_VHOST	= NULL;
	static protected $_root				= null;
	static protected $_hostname			= null;
	static protected $_logpath			= null;
	public static $databases = array(
		'log',
		'dashboard',
		'api',
		'core',
		'web',
		'default',
		// Mutasi Rekening
		'mutasi',
		// Cryptocurrency
		'cryptocurrency',
	);
	function __construct() {
		self::$THIS_SERVER_VHOST = self::root();
	}
	public static function get_instance() {
		if (!self::$instance) {
            self::$instance = new ConstantConfig();
        }
		return self::$instance;
	}
	static public function root() {
		if (is_null(self::$_root)) {
			self::$_root = dirname(__DIR__);
		}
		return self::$_root;
	}
	static public function hostname() {
		if (is_null(self::$_hostname)) {
			self::$_hostname = ConstantConfig::PUBLIC_URL_ADDRESS;
			if (!in_array(ConstantConfig::PUBLIC_URL_PORT, array('80', '443'))) {
				self::$_hostname .= ":" . ConstantConfig::PUBLIC_URL_PORT;
			}
		}
		return self::$_hostname;
	}
	static public function logpath() {
		if (is_null(self::$_logpath)) {
			self::$_logpath = dirname(dirname(__DIR__));
		}
		return self::$_logpath;
	}
	static public function get_database_config() {
		$instance = self::get_instance();
		$enabled_pg_code = array(
			'all',
		);
		$database_config = array(
			'sandbox'		=> array(
				'mysql'				=> array(),
				'mssql'				=> array(),
				'postsql'			=> array(),
				'orasql'			=> array(),
			),
			'live'			=> array(
				'mysql'				=> array(),
				'mssql'				=> array(),
				'postsql'			=> array(),
				'orasql'			=> array(),
			)
		);
		foreach ($enabled_pg_code as $val) {
			$database_config['sandbox']['mysql'][$val] = array(
				'db_host' => $instance->set_dashboard_params('hostname', 'core', 'sandbox'),
				'db_port' => $instance->set_dashboard_params('dbport', 'core', 'sandbox'),
				'db_user' => $instance->set_dashboard_params('username', 'core', 'sandbox'),
				'db_pass' => $instance->set_dashboard_params('password', 'core', 'sandbox'),
				'db_name' => $instance->set_dashboard_params('database', 'core', 'sandbox'),
			);
			$database_config['live']['mysql'][$val] = array(
				'db_host' => $instance->set_dashboard_params('hostname', 'core', 'live'),
				'db_port' => $instance->set_dashboard_params('dbport', 'core', 'live'),
				'db_user' => $instance->set_dashboard_params('username', 'core', 'live'),
				'db_pass' => $instance->set_dashboard_params('password', 'core', 'live'),
				'db_name' => $instance->set_dashboard_params('database', 'core', 'live'),
			);
		}
		return $database_config;
	}
	public static function get_dashboard_database($name, $mode = 'sandbox') {
		$name = (is_string($name) ? strtolower($name) : 'log');
		$mode = (is_string($mode) ? strtolower($mode) : 'sandbox');
		$instance = self::get_instance();
		return array(
			'dsn'				=> '',
			'hostname' 			=> $instance->set_dashboard_params('hostname', $name, $mode),
			'dbport'			=> $instance->set_dashboard_params('dbport', $name, $mode),
			'username' 			=> $instance->set_dashboard_params('username', $name, $mode),
			'password' 			=> $instance->set_dashboard_params('password', $name, $mode),
			'database' 			=> $instance->set_dashboard_params('database', $name, $mode),
			'dbdriver' 			=> 'mysqli',
			'dbprefix' 			=> '',
			'pconnect' 			=> FALSE,
			'db_debug' 			=> (ENVIRONMENT !== 'production'),
			'cache_on' 			=> FALSE,
			'cachedir' 			=> '',
			'char_set' 			=> 'utf8',
			'dbcollat' 			=> 'utf8_general_ci',
			'swap_pre' 			=> '',
			'encrypt' 			=> FALSE,
			'compress' 			=> FALSE,
			'stricton' 			=> FALSE,
			'failover' 			=> array(),
			'save_queries' 		=> TRUE
		);
	}
	private function set_dashboard_params($params_name, $name, $mode = 'dev') {
		$mode = (is_string($mode) ? strtolower($mode) : 'dev');
		$name = (is_string($name) ? strtolower($name) : 'dashboard');
		$params_name = (is_string($params_name) ? $params_name : '');
		$db_params = array();
		switch (strtolower($mode)) {
			case 'live':
				switch (strtolower($name)) {
					case 'api':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'USERNAME';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_api';
					break;
					case 'dashboard':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'USERNAME';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_dashboard';
					break;
					case 'log':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'USERNAME';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_logs';
					break;
					case 'core':
					case 'default':
					case 'web':
					default:
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'USERNAME';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_core';
					break;
					case 'cryptocurrency':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'USERNAME';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_core';
					break;
				}
			break;
			case 'sandbox':
				switch (strtolower($name)) {
					case 'api':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'project.true';
						$db_params['database'] = 'dhfwjaco_api';
					break;
					case 'dashboard':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_dashboard';
					break;
					case 'log':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_logs';
					break;
					case 'core':
					case 'default':
					case 'web':
					default:
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_core';
					break;
					case 'mutasi':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_mutasi_core';
					break;
					case 'cryptocurrency':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'PASSWORD';
						$db_params['database'] = 'dhfwjaco_cryptocurrency';
					break;
				}
			break;
			case 'dev':
			default:
				switch (strtolower($name)) {
					case 'api':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'project.true';
						$db_params['database'] = 'tdpid_api';
					break;
					case 'dashboard':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'project.true';
						$db_params['database'] = 'tdpid_dashboard';
					break;
					case 'log':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'project.true';
						$db_params['database'] = 'tdpid_logs';
					break;
					case 'core':
					case 'default':
					case 'web':
					default:
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'project.true';
						$db_params['database'] = 'tdpid_cryptocurrency';
					break;
					case 'cryptocurrency':
						$db_params['hostname'] = 'localhost';
						$db_params['dbport'] = 3306;
						$db_params['username'] = 'project';
						$db_params['password'] = 'project.true';
						$db_params['database'] = 'tdpid_cryptocurrency';
					break;
				}
			break;
		}
		if (isset($db_params[$params_name])) {
			return $db_params[$params_name];
		}
		return FALSE;
	}
	
	
}
$config['constant_config'] = new ConstantConfig();









