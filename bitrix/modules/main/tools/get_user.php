<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2016 Bitrix             #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$ID = intval($_REQUEST["ID"]);

$auth = false;
if($USER->IsAuthorized())
{
	$auth = ($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users'));
	if(!$auth)
	{
		if(IsModuleInstalled("intranet") && CModule::IncludeModule("socialnetwork"))
		{
			$auth = CSocNetUser::CanProfileView($USER->GetID(), $ID);
		}
	}
}

$res = '';

if($auth)
{
	$rsUser = CUser::GetByID($ID);
	if($arUser = $rsUser->Fetch())
	{
		$res = '[<a title="'.GetMessage("MAIN_EDIT_USER_PROFILE").'" class="tablebodylink" href="/bitrix/admin/user_edit.php?ID='.$arUser["ID"].'&lang='.LANG.'">'.$arUser["ID"].'</a>] ('.htmlspecialcharsbx($arUser["LOGIN"]).') '.htmlspecialcharsbx($arUser["NAME"]).' '.htmlspecialcharsbx($arUser["LAST_NAME"]);
	}
}
$strName = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST["strName"]);
?>
<script type="text/javascript">
if(window.parent.document.getElementById("div_<?=$strName?>"))
{
	window.parent.document.getElementById("div_<?=$strName?>").innerHTML = '<?=CUtil::JSEscape($res)?>';
}
</script>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>