<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Order;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$lang = ($request->get('lang') !== null) ? trim($request->get('lang')) : "ru";
\Bitrix\Main\Context::getCurrent()->setLanguage($lang);

Loc::loadMessages(__FILE__);

$arResult = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Error! Can't include module \"Sale\"";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/internals/input.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($arResult["ERROR"] === '' && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$action = ($request->get('action') !== null) ? trim($request->get('action')): '';

	switch ($action)
	{
		case "get_restriction_params_html":
			/** @var Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$cashboxId = ($request->get('cashboxId') !== null) ? intval($request->get('cashboxId')) : 0;
			$sort = ($request->get('sort') !== null) ? intval($request->get('sort')) : 100;

			if(!$className)
				throw new \Bitrix\Main\ArgumentNullException("className");

			Cashbox\Restrictions\Manager::getClassesList();
			$paramsStructure = $className::getParamsStructure($cashboxId);
			$params = $className::prepareParamsValues($params, $cashboxId);

			$paramsField = "<table>";

			foreach ($paramsStructure as $name => $param)
			{
				$paramsField .= "<tr>".
					"<td>".(strlen($param["LABEL"]) > 0 ? $param["LABEL"].": " : "")."</td>".
					"<td>".\Bitrix\Sale\Internals\Input\Manager::getEditHtml("RESTRICTION[".$name."]", $param, (isset($params[$name]) ? $params[$name] : null))."</td>".
					"</tr>";
			}

			$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_CASHBOX_RESTRICTION_SORT").': </td>'.
				'<td><input type="text" name="SORT" value="'.$sort.'"></td>'.
				'</tr>';

			$arResult["RESTRICTION_HTML"] = $paramsField."</table>";
			break;

		case "save_restriction":
			Cashbox\Restrictions\Manager::getClassesList();

			/** @var Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$sort = ($request->get('sort') !== null) ? (int)$request->get('sort') : 100;
			$cashboxId = ($request->get('cashboxId') !== null) ? (int)$request->get('cashboxId') : 0;
			$restrictionId = ($request->get('restrictionId') !== null) ? (int)$request->get('restrictionId') : 0;

			if(!class_exists($className))
				throw new \Bitrix\Main\ArgumentNullException("className");

			if(!$cashboxId)
				throw new \Bitrix\Main\ArgumentNullException("cashboxId");

			foreach ($className::getParamsStructure() as $key => $rParams)
			{
				$errors = \Bitrix\Sale\Internals\Input\Manager::getError($rParams, $params[$key]);
				if (!empty($errors))
					$arResult["ERROR"] .= Loc::getMessage('SALE_CASHBOX_ERROR_FIELD').': "'.$rParams["LABEL"].'" '.implode("\n", $errors)."\n";
			}

			if (!$params)
				$arResult["ERROR"] = Loc::getMessage('SALE_CASHBOX_ERROR_PARAMS');

			if ($arResult["ERROR"] == '')
			{
				$fields = array(
					"SERVICE_ID" => $cashboxId,
					"SERVICE_TYPE" => Cashbox\Restrictions\Manager::SERVICE_TYPE_CASHBOX,
					"SORT" => $sort,
					"PARAMS" => $params
				);

				/** @var \Bitrix\Sale\Result $res */
				$res = $className::save($fields, $restrictionId);

				if (!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
				$arResult["HTML"] = getRestrictionHtml($cashboxId);
			}

			break;

		case "delete_restriction":
			Cashbox\Restrictions\Manager::getClassesList();
			$restrictionId = ($request->get('restrictionId') !== null) ? (int)$request->get('restrictionId') : 0;
			$cashboxId = ($request->get('cashboxId') !== null) ? (int)$request->get('cashboxId') : 0;

			if(!$restrictionId)
				throw new \Bitrix\Main\ArgumentNullException('restrictionId');

			$dbRes =  Cashbox\Restrictions\Manager::getById($restrictionId);

			if($fields = $dbRes->fetch())
			{
				/** @var \Bitrix\Sale\Result $res */
				$res = $fields["CLASS_NAME"]::delete($restrictionId, $cashboxId);

				if(!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
			}
			else
			{
				$arResult["ERROR"] .= "Can't find restriction with id: ".$restrictionId;
			}

			$arResult["HTML"] = getRestrictionHtml($cashboxId);

			break;
		case "generate_link":
			$arResult["LINK"] = Cashbox\Manager::getConnectionLink();
			break;		
		case "reload_settings":
			
			$cashbox = array('HANDLER' => $request->get('handler'), 'KKM_ID' => (int)$request->get('kkmId'));
			$handler = $cashbox['HANDLER'];
			if (class_exists($handler))
			{
				ob_start();
				require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/sale/admin/cashbox_settings.php");
				$arResult["HTML"] = ob_get_contents();
				ob_end_clean();
			}

			break;
		default:
			$arResult["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if ($request->get('mode') == 'settings')
		getRestrictionHtml($request->get('ID'));
	elseif(strlen($arResult["ERROR"]) <= 0)
		$arResult["ERROR"] = "Error! Access denied";
}

if(strlen($arResult["ERROR"]) > 0)
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));

function getRestrictionHtml($cashboxId)
{
	if(intval($cashboxId) <= 0)
		throw new \Bitrix\Main\ArgumentNullException("cashboxId");

	$_REQUEST['table_id'] = 'table_cashbox_restrictions';
	$_REQUEST['admin_history'] = 'Y';
	$_GET['ID'] = $cashboxId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/cashbox_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();

	return $restrictionsHtml;
}