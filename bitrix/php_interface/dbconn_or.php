<?
define("DBPersistent", false);
$DBType = "mysql";
$DBDebug = true;
foreach ($argv as $arg) {
	$e=explode("=",$arg);
	if (count($e)==2)
		$_GET[$e[0]]=$e[1];
	else
		$_GET[$e[0]]=0;
}
if ($_GET['SERVER_NAME']){
	$_SERVER['SERVER_NAME'] = $_GET['SERVER_NAME'];
}
if ( !isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == '' ){
	$_SERVER['DOCUMENT_ROOT'] = '/var/www/gettidy/';
}


    $DBHost = '127.0.0.1';
    $DBLogin = 'gett';
    $DBPassword = 'aT41H32SQMaL';
    $DBName = 'gett';

$DBDebugToFile = false;

define("DELAY_DB_CONNECT", true);
define("CACHED_b_file", 3600);
define("CACHED_b_file_bucket_size", 10);
define("CACHED_b_lang", 3600);
define("CACHED_b_option", 3600);
define("CACHED_b_lang_domain", 3600);
define("CACHED_b_site_template", 3600);
define("CACHED_b_event", 3600);
define("CACHED_b_agent", 3660);
define("CACHED_menu", 3600);

define("BX_UTF", true);
define("BX_FILE_PERMISSIONS", 0655);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~BX_DIR_PERMISSIONS);
define("BX_DISABLE_INDEX_PAGE", true);

define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);
if(!(defined("CHK_EVENT") && CHK_EVENT===true))
	define("BX_CRONTAB_SUPPORT", true);
?>
