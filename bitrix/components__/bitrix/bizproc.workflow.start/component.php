<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule('bizproc')):
	return false;
endif;

if (!function_exists("BPWSInitParam"))
{
	function BPWSInitParam(&$arParams, $name)
	{
		$arParams[$name] = trim($arParams[$name]);
		if ($arParams[$name] <= 0)
			$arParams[$name] = trim($_REQUEST[$name]);
		if ($arParams[$name] <= 0)
			$arParams[$name] = trim($_REQUEST[strtolower($name)]);
	}
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["MODULE_ID"] = trim(empty($arParams["MODULE_ID"]) ? $_REQUEST["module_id"] : $arParams["MODULE_ID"]);
	$arParams["ENTITY"] = trim(empty($arParams["ENTITY"]) ? $_REQUEST["entity"] : $arParams["ENTITY"]);
	$arParams["DOCUMENT_TYPE"] = trim(empty($arParams["DOCUMENT_TYPE"]) ? $_REQUEST["document_type"] : $arParams["DOCUMENT_TYPE"]);
	$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] : $arParams["DOCUMENT_ID"]);
	$arParams["TEMPLATE_ID"] = intval($_REQUEST["workflow_template_id"]);
//***************** URL ********************************************/
	$arResult["back_url"] = trim($_REQUEST["back_url"]);
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Main data
********************************************************************/
$arError = array();
if (strlen($arParams["MODULE_ID"]) <= 0)
	$arError[] = array(
		"id" => "empty_module_id",
		"text" => GetMessage("BPATT_NO_MODULE_ID"));
if (strlen($arParams["ENTITY"]) <= 0)
	$arError[] = array(
		"id" => "empty_entity",
		"text" => GetMessage("BPABS_EMPTY_ENTITY"));
if (strlen($arParams["DOCUMENT_TYPE"]) <= 0)
	$arError[] = array(
		"id" => "empty_document_type",
		"text" => GetMessage("BPABS_EMPTY_DOC_TYPE"));
if (strlen($arParams["DOCUMENT_ID"]) <= 0)
	$arError[] = array(
		"id" => "empty_document_id",
		"text" => GetMessage("BPABS_EMPTY_DOC_ID"));


$arParams["DOCUMENT_TYPE"] = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_TYPE"]);
$arParams["DOCUMENT_ID"] = array($arParams["MODULE_ID"], $arParams["ENTITY"], $arParams["DOCUMENT_ID"]);
$arParams["USER_GROUPS"] = $GLOBALS["USER"]->GetUserGroupArray();

if (method_exists($arParams["DOCUMENT_TYPE"][1], "GetUserGroups"))
{
	$arParams["USER_GROUPS"] = call_user_func_array(
		array($arParams["DOCUMENT_TYPE"][1], "GetUserGroups"),
		array($arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"], $GLOBALS["USER"]->GetID()));
}

if (empty($arError))
{
	$arDocumentStates = CBPDocument::GetDocumentStates($arParams["DOCUMENT_TYPE"], $arParams["DOCUMENT_ID"]);

	if (!CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::StartWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		array(
			"DocumentStates" => $arDocumentStates,
			"UserGroups" => $arParams["USER_GROUPS"]))):
		$arError[] = array(
			"id" => "access_denied",
			"text" => GetMessage("BPABS_NO_PERMS"));
	endif;
}
if (!empty($arError))
{
	$e = new CAdminException($arError);
	ShowError($e->GetString());
	return false;
}
elseif (!empty($_REQUEST["cancel"]) && !empty($_REQUEST["back_url"]))
{
	LocalRedirect(str_replace("#WF#", "", $_REQUEST["back_url"]));
}
/********************************************************************
				/Main data
********************************************************************/

$arResult["SHOW_MODE"] = "SelectWorkflow";
$arResult["TEMPLATES"] = array();
$arResult["PARAMETERS_VALUES"] = array();
$arResult["ERROR_MESSAGE"] = "";


/********************************************************************
				Data
********************************************************************/
$dbWorkflowTemplate = CBPWorkflowTemplateLoader::GetList(
	array(),
	array("DOCUMENT_TYPE" => $arParams["DOCUMENT_TYPE"], "ACTIVE" => "Y"),
	false,
	false,
	array("ID", "NAME", "DESCRIPTION", "MODIFIED", "USER_ID", "PARAMETERS")
);
while ($arWorkflowTemplate = $dbWorkflowTemplate->GetNext())
{
	if (!CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::StartWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arParams["DOCUMENT_ID"],
		array(
			"UserGroups" => $arParams["USER_GROUPS"],
			"DocumentStates" => $arDocumentStates,
			"WorkflowTemplateId" => $arWorkflowTemplate["ID"]))):
		continue;
	endif;
	$arResult["TEMPLATES"][$arWorkflowTemplate["ID"]] = $arWorkflowTemplate;
	$arResult["TEMPLATES"][$arWorkflowTemplate["ID"]]["URL"] =
		htmlspecialcharsex($APPLICATION->GetCurPageParam(
			"workflow_template_id=".$arWorkflowTemplate["ID"].'&'.bitrix_sessid_get(),
			Array("workflow_template_id", "sessid")));
}

if ($arParams["TEMPLATE_ID"] > 0 && strlen($_POST["CancelStartParamWorkflow"]) <= 0
	&& array_key_exists($arParams["TEMPLATE_ID"], $arResult["TEMPLATES"]))
{
	$arWorkflowTemplate = $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]];

	$arWorkflowParameters = array();
	$bCanStartWorkflow = false;

	if (count($arWorkflowTemplate["PARAMETERS"]) <= 0)
	{
		$bCanStartWorkflow = true;
	}
	elseif ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["DoStartParamWorkflow"]) > 0)
	{
		$arErrorsTmp = array();

		$arWorkflowParameters = CBPWorkflowTemplateLoader::CheckWorkflowParameters(
			$arWorkflowTemplate["PARAMETERS"],
			$_REQUEST,
			$arParams["DOCUMENT_TYPE"],
			$arErrorsTmp
		);

		if (count($arErrorsTmp) > 0)
		{
			$bCanStartWorkflow = false;

			foreach ($arErrorsTmp as $e)
				$arError[] = array(
					"id" => "CheckWorkflowParameters",
					"text" => $e["message"]);
		}
		else
		{
			$bCanStartWorkflow = true;
		}
	}

	if ($bCanStartWorkflow)
	{
		$arErrorsTmp = array();

		$wfId = CBPDocument::StartWorkflow(
			$arParams["TEMPLATE_ID"],
			$arParams["DOCUMENT_ID"],
			$arWorkflowParameters,
			$arErrorsTmp
		);

		if (count($arErrorsTmp) > 0)
		{
			$arResult["SHOW_MODE"] = "StartWorkflowError";
			foreach ($arErrorsTmp as $e)
				$arError[] = array(
					"id" => "StartWorkflowError",
					"text" => "[".$e["code"]."] ".$e["message"]);
		}
		else
		{
			$arResult["SHOW_MODE"] = "StartWorkflowSuccess";
			if (strlen($arResult["back_url"]) > 0):
				LocalRedirect(str_replace("#WF#", $wfId, $_REQUEST["back_url"]));
				die();
			endif;
		}
	}
	else
	{
		$p = ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["DoStartParamWorkflow"]) > 0);
		$keys = array_keys($arWorkflowTemplate["PARAMETERS"]);
		foreach ($keys as $key)
		{
			$v = ($p ? $_REQUEST[$key] : $arWorkflowTemplate["PARAMETERS"][$key]["Default"]);
			if (!is_array($v))
			{
				$arResult["PARAMETERS_VALUES"][$key] = htmlspecialchars($v);
			}
			else
			{
				$keys1 = array_keys($v);
				foreach ($keys1 as $key1)
					$arResult["PARAMETERS_VALUES"][$key][$key1] = htmlspecialchars($v[$key1]);
			}
		}

		$arResult["SHOW_MODE"] = "WorkflowParameters";
	}
	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	}
}
else
{
	$arResult["SHOW_MODE"] = "SelectWorkflow";
}

/********************************************************************
				/Data
********************************************************************/

$this->IncludeComponentTemplate();

/********************************************************************
				Standart operations
********************************************************************/
if($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(GetMessage("BPABS_TITLE"));
}
/********************************************************************
				/Standart operations
********************************************************************/
?>