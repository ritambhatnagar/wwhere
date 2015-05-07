<?php //
require_once(BASEPATH . 'database/DB.php');
$db_instance = &DB();
$SQL_SMTP_DATA = "SELECT `vName`, `vValue` FROM (`setting`) WHERE `vName` IN ('USE_SMTP_SERVERPORT','USE_SMTP_SERVERHOST','USE_SMTP_SERVERUSERNAME','USE_SMTP_SERVERPASS') AND eStatus = 'Active'";
$db_instance->_compile_select();
$DB_SMTP_DATA = $db_instance->select_assoc($SQL_SMTP_DATA, "vName");

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'ssl://'.$DB_SMTP_DATA["USE_SMTP_SERVERHOST"][0]["vValue"];
$config['smtp_port'] = $DB_SMTP_DATA["USE_SMTP_SERVERPORT"][0]["vValue"];
$config['smtp_timeout'] = '7';
$config['smtp_user'] = $DB_SMTP_DATA["USE_SMTP_SERVERUSERNAME"][0]["vValue"];
$config['smtp_pass'] = $DB_SMTP_DATA["USE_SMTP_SERVERPASS"][0]["vValue"];
$config['charset'] = 'utf-8';
$config['newline'] = "\r\n";
$config['mailtype'] = 'html'; // or html
$config['validation'] = TRUE; // bool whether to validate email or not
?>