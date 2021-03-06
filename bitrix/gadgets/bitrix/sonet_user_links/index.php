<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;

/*
if (!array_key_exists("SHOW_FEATURES", $arGadgetParams) || strlen($arGadgetParams["SHOW_FEATURES"]) <= 0 || $arGadgetParams["SHOW_FEATURES"] != "Y")
	$arGadgetParams["SHOW_FEATURES"] = "N";
*/
$arGadgetParams["SHOW_FEATURES"] = "Y";
	
?>
<?=htmlspecialcharsback($arGadgetParams["IMAGE"])?><br />
<?
if ($arGadgetParams['IS_ONLINE'] || $arGadgetParams['IS_BIRTHDAY'] || $arGadgetParams['IS_ABSENT'] || $arGadgetParams["IS_HONOURED"]):
	?><div class="bx-user-control">
	<ul>
		<?if ($arGadgetParams['IS_ONLINE']):?><li class="bx-icon bx-icon-online"><?= GetMessage("GD_SONET_USER_LINKS_ONLINE") ?></li><?endif;?>
		<?if ($arGadgetParams['IS_BIRTHDAY']):?><li class="bx-icon bx-icon-birth"><?= GetMessage("GD_SONET_USER_LINKS_BIRTHDAY") ?></li><?endif;?>
		<?if ($arGadgetParams["IS_HONOURED"]):?><li class="bx-icon bx-icon-featured"><?= GetMessage("GD_SONET_USER_LINKS_HONOURED") ?></li><?endif;?>
		<?if ($arGadgetParams['IS_ABSENT']):?><li class="bx-icon bx-icon-away"><?= GetMessage("GD_SONET_USER_LINKS_ABSENT") ?></li><?endif;?>
	</ul>
	</div><?
endif;

if ($GLOBALS["USER"]->IsAuthorized()):
	if (!$arGadgetParams["IS_CURRENT_USER"]):
		?><div class="bx-user-control">
		<ul><?
			if ($arGadgetParams["CAN_MESSAGE"]):
				?><li class="bx-icon-action bx-icon-message"><a href="<?= $arGadgetParams["URL_MESSAGE_CHAT"] ?>" onclick="window.open('<?= $arGadgetParams["URL_MESSAGE_CHAT"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false;"><?= GetMessage("GD_SONET_USER_LINKS_SEND_MESSAGE") ?></a></li><?
			endif;
			if ($arGadgetParams["CAN_VIDEOCALL"]):
				?><li class="bx-icon-action bx-icon-video-call"><a href="<?= $arGadgetParams["URL_VIDEOCALL"] ?>" onclick="window.open('<?= $arGadgetParams["URL_VIDEOCALL"]?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5)); return false;"><?= GetMessage("GD_SONET_USER_VIDEOCALL") ?></a></li><?
			endif;
			?><li class="bx-icon-action bx-icon-history"><a href="<?= $arGadgetParams["URL_USER_MESSAGES"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_SHOW_MESSAGES") ?></a></li><?
			if (CSocNetUser::IsFriendsAllowed() && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite())):
				if ($arGadgetParams["RELATION"] == SONET_RELATIONS_FRIEND):
					?><li class="bx-icon-action bx-icon-friend-remove"><a href="<?= $arGadgetParams["URL_FRIENDS_DELETE"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_FR_DEL") ?></a></li><?
				elseif (!$arGadgetParams["RELATION"]):
					?><li class="bx-icon-action bx-icon-friend-add"><a href="<?= $arGadgetParams["URL_FRIENDS_ADD"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_FR_ADD") ?></a></li><?
				endif;
			endif;
			if ($arGadgetParams["CAN_INVITE_GROUP"]):
				?><li class="bx-icon-action bx-icon-group-add"><a href="<?= $arGadgetParams["URL_REQUEST_GROUP"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_INV_GROUP") ?></a></li><?
			endif;
			if ($arGadgetParams["CAN_VIEW_PROFILE"]):
				?><li class="bx-icon-action bx-icon-subscribe"><a href="<?= $arGadgetParams["URL_SUBSCRIBE"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_SUBSCR") ?></a></li><?
			endif;
		?></ul>
		</div><?
	endif;

	if ($arGadgetParams["CAN_MODIFY_USER"]):
		?><div class="bx-user-control">
		<ul><?
			if ($arGadgetParams["CAN_MODIFY_USER_MAIN"]):						
				?><li class="bx-icon-action bx-icon-profile"><a href="<?= $arGadgetParams["URL_EDIT"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_PROFILE") ?></a></li><?
			endif;
			if (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()):
				?><li class="bx-icon-action bx-icon-privacy"><a href="<?= $arGadgetParams["URL_SETTINGS"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_SETTINGS") ?></a></li><?
			endif;
			if ($arGadgetParams["SHOW_FEATURES"] == "Y"):
				?><li class="bx-icon-action bx-icon-settings"><a href="<?= $arGadgetParams["URL_FEATURES"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_EDIT_FEATURES") ?></a></li><?
			endif;
			if ($arGadgetParams["IS_CURRENT_USER"]):
				?><li class="bx-icon-action bx-icon-subscribe"><a href="<?= $arGadgetParams["URL_SUBSCRIBE_LIST"] ?>"><?= GetMessage("GD_SONET_USER_LINKS_SUBSCR1") ?></a></li><?
			endif;
		?></ul>
		</div><?
	endif;
endif;
?>