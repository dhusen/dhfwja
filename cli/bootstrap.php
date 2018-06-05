<?php
// -------------------------------------------------------
// Important thing for allow load Codeigniter from outside
// -------------------------------------------------------
define('CLI_BASEPATH', __DIR__);
$HTML_BASEPATH = (dirname(CLI_BASEPATH) . DIRECTORY_SEPARATOR . 'html');
$cli_system_path = ($HTML_BASEPATH . DIRECTORY_SEPARATOR . 'system');
$cli_application_folder = ($HTML_BASEPATH . DIRECTORY_SEPARATOR . 'application');
// -------------------------------------------------------
ob_start();
define("REQUEST", "external");
require($HTML_BASEPATH . DIRECTORY_SEPARATOR . 'index.php');
ob_end_clean();

