<?php

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LANG", "s1");
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
	$_SERVER['DOCUMENT_ROOT'] = '/home/u429586/cleanandaway.ru/www/';
}
$_SERVER['DOCUMENT_ROOT'] = '/home/u429586/cleanandaway.ru/www/';
file_put_contents($_SERVER["DOCUMENT_ROOT"].'/logs/test1.txt', serialize($_GET)."\n", FILE_APPEND);
die;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$smpphost = 'smpp04.a1smsmarket.ru';
$smppport = 5000;
$systemid = 'sm627155010';
$password = "gXSv6WSr";
// $systemid = 'novativegar';
// $password = "begibyfa";
$from = "GetTidy";
declare(ticks = 1);
$tx = new SMPP($smpphost,$smppport);
$tx->system_type="";
$tx->addr_npi = 0;

$tx->bindTransceiver($systemid,$password);
$tx->sendSMS($from, $phone_formated, $string);