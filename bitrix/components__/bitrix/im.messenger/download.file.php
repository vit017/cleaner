<?
define("IM_AJAX_INIT", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!\Bitrix\Main\Loader::includeModule('disk'))
{
	die;
}

if($_GET['action'] == 'showFile')
{
	if ($_GET['preview'] == 'Y')
	{
		$_GET['width'] = 204;
		$_GET['height'] = 119;
		$_GET['signature'] = \Bitrix\Disk\Security\ParameterSigner::getImageSignature($_GET['fileId'], $_GET['width'], $_GET['height']);
	}
	else
	{
		unset($_GET['width']);
		unset($_GET['height']);
	}
	unset($_GET['exact']);
}
else
{
	$_GET['action'] = 'downloadFile';
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	LocalRedirect(\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestUri());
}

$controller = new \Bitrix\Disk\DownloadController();
$controller->setActionName($_GET['action'])->exec();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>