<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = $GLOBALS["USER"]->GetID();

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
	$arParams["NAME_TEMPLATE"] = '#NOBR##NAME# #LAST_NAME##/NOBR#';
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
			
$arResult["FatalError"] = "";

if (!$GLOBALS["USER"]->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	if ($arParams["USER_ID"] <= 0)
		$arResult["FatalError"] = GetMessage("SONET_C40_NO_USER_ID").".";

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		$dbUser = CUser::GetByID($arParams["USER_ID"]);
		$arResult["User"] = $dbUser->GetNext();

		if (is_array($arResult["User"]))
		{
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

			if ($arResult["CurrentUserPerms"]["Operations"]["modifyuser"])
			{
				$arResult["Features"] = array();

				global $arSocNetUserOperations;
				foreach ($arSocNetUserOperations as $feature => $perm)
					$arResult["Features"][$feature] = CSocNetUserPerms::GetOperationPerms($arResult["User"]["ID"], $feature);
			}
			else
			{
				$arResult["FatalError"] = GetMessage("SONET_C40_NO_PERMS").".";
			}
		}
		else
		{
			$arResult["FatalError"] = GetMessage("SONET_C40_NO_USER").".";
		}
	}

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));


		$arTmpUser = array(
				'NAME' => $arResult["User"]["~NAME"],
				'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
				'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
				'LOGIN' => $arResult["User"]["~LOGIN"],
			);
	
		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);	
		
		if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
		{
			$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
				array("#NOBR#", "#/NOBR#"), 
				array("", ""), 
				$arParams["NAME_TEMPLATE"]
			);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);	
		}		
		
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C40_PAGE_TITLE"));

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["User"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C40_PAGE_TITLE"));
		}

		$arResult["ShowForm"] = "Input";

		if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"]) > 0 && check_bitrix_sessid())
		{
			$errorMessage = "";

			foreach ($arResult["Features"] as $feature => $perm)
			{
				$idTmp = CSocNetUserPerms::SetPerm(
					$arResult["User"]["ID"],
					$feature,
					$_REQUEST[$feature."_perm"]
				);
				if (!$idTmp)
				{
					if ($e = $APPLICATION->GetException())
						$errorMessage .= $e->GetString();
				}
			}

			if (strlen($errorMessage) > 0)
			{
				$arResult["ErrorMessage"] = $errorMessage;
			}
			else
			{
				$arResult["ShowForm"] = "Confirm";
			}
		}

		if ($arResult["ShowForm"] == "Input")
		{
			if (CSocNetUser::IsFriendsAllowed())
			{
				$arResult["PermsVar"] = array(
					SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_C40_NOBODY"),
					SONET_RELATIONS_TYPE_FRIENDS => GetMessage("SONET_C40_ONLY_FRIENDS"),
					SONET_RELATIONS_TYPE_FRIENDS2 => GetMessage("SONET_C40_FRIENDS2"),
					SONET_RELATIONS_TYPE_AUTHORIZED => GetMessage("SONET_C40_AUTHORIZED"),
					SONET_RELATIONS_TYPE_ALL => GetMessage("SONET_C40_ALL"),
				);
			}
			else
			{
				$arResult["PermsVar"] = array(
					SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_C40_NOBODY"),
					SONET_RELATIONS_TYPE_AUTHORIZED => GetMessage("SONET_C40_AUTHORIZED"),					
					SONET_RELATIONS_TYPE_ALL => GetMessage("SONET_C40_ALL"),
				);
			}
		}
	}
}
$this->IncludeComponentTemplate();
?>