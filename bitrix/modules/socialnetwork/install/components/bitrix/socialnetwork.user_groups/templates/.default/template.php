<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$bodyClass = $bodyClass ? $bodyClass." no-paddings" : "no-paddings";
$APPLICATION->SetPageProperty("BodyClass", $bodyClass);

if(strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	?><script>
	BX.message({
		SONET_C33_T_F_REQUEST_ERROR: '<?=GetMessageJS('SONET_C33_T_F_REQUEST_ERROR')?>',
		SONET_C33_T_F_SORT_ALPHA: '<?=GetMessageJS('SONET_C33_T_F_SORT_ALPHA')?>',
		SONET_C33_T_F_SORT_DATE_REQUEST: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_REQUEST')?>',
		SONET_C33_T_F_SORT_DATE_VIEW: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_VIEW')?>',
		SONET_C33_T_F_SORT_MEMBERS_COUNT: '<?=GetMessageJS('SONET_C33_T_F_SORT_MEMBERS_COUNT')?>',
		SONET_C33_T_F_SORT_DATE_ACTIVITY: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_ACTIVITY')?>',
		filterAlphaUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=alpha', array('order')))?>',
		filterDateRequestUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_request', array('order')))?>',
		filterDateViewUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_view', array('order')))?>',
		filterMembersCountUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=members_count', array('order')))?>',
		filterDateActivitytUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_activity', array('order')))?>',
		SONET_C33_T_ACT_FAVORITES_ADD: '<?=GetMessageJS("SONET_C33_T_ACT_FAVORITES_ADD")?>',
		SONET_C33_T_ACT_FAVORITES_REMOVE: '<?=GetMessageJS("SONET_C33_T_ACT_FAVORITES_REMOVE")?>'

	});
	</script><?
	?><?
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.group.iframe.popup",
		".default",
		array(
			"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
			"PATH_TO_GROUP_CREATE" => $arResult["Urls"]["GroupsAdd"],
			"ON_GROUP_ADDED" => "BX.DoNothing",
			"ON_GROUP_CHANGED" => "BX.DoNothing",
			"ON_GROUP_DELETED" => "BX.DoNothing"
		),
		null,
		array("HIDE_ICONS" => "Y")
	);
	?><?

	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if (
		$arParams["PAGE"] == "groups_list"
		|| (
			$arParams["PAGE"] == "user_groups"
			&& isset($arResult["CurrentUserPerms"])
			&& isset($arResult["CurrentUserPerms"]["IsCurrentUser"])
			&& $arResult["CurrentUserPerms"]["IsCurrentUser"]
		)
	)
	{
		?><?
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.user_groups.link.add",
			".default",
			array(
				"HREF" => $arResult["Urls"]["GroupsAdd"],
				"PATH_TO_GROUP_CREATE" => $arResult["Urls"]["GroupsAdd"],
				"ALLOW_CREATE_GROUP" => ($arResult["CurrentUserPerms"]["IsCurrentUser"] && $arResult["ALLOW_CREATE_GROUP"] ? "Y" : "N"),
				"LIST_NAV_ID" => $arResult["NAV_ID"]
			),
			null,
			array("HIDE_ICONS" => "Y")
		);
		?><?

		$arFilterKeys = array("filter_my", "filter_archive", "filter_extranet");

		$menuItems = array();
		$myTabActive = false;

		if (!$arResult["bExtranet"] && $USER->IsAuthorized())
		{
			$myTabActive = (
				$arParams["PAGE"] == 'user_groups'
				|| ($arParams["PAGE"] == 'groups_list' && $arResult["filter_my"])
			);
			$menuItems[] = array(
				"TEXT" => GetMessage("SONET_C33_T_F_MY"),
				"URL" => (strlen($arResult["WORKGROUPS_PATH"]) > 0 ? $arResult["WORKGROUPS_PATH"]."?filter_my=Y" : $APPLICATION->GetCurPageParam("filter_my=Y", $arFilterKeys, false)),
				"ID" => "workgroups_my",
				"IS_ACTIVE" => $myTabActive
			);
		}
		$menuItems[] = array(
			"TEXT" => GetMessage("SONET_C36_T_F_ALL"),
			"URL" => (strlen($arResult["WORKGROUPS_PATH"]) > 0 ? $arResult["WORKGROUPS_PATH"] : $APPLICATION->GetCurPageParam("", $arFilterKeys, false)),
			"ID" => "workgroups_all",
			"IS_ACTIVE" => (!$myTabActive && !$arResult["filter_my"] && !$arResult["filter_archive"] && !$arResult["filter_extranet"] && !$arResult["filter_tags"] && !$arResult["filter_favorites"])
		);
		if ($USER->IsAuthorized())
		{
			$menuItems[] = array(
				"TEXT" => GetMessage("SONET_C36_T_F_FAVORITES"),
				"URL" => (strlen($arResult["WORKGROUPS_PATH"]) > 0 ? $arResult["WORKGROUPS_PATH"]."?filter_favorites=Y" : $APPLICATION->GetCurPageParam("filter_favorites=Y", $arFilterKeys, false)),
				"ID" => "workgroups_favorites",
				"IS_ACTIVE" => $arResult["filter_favorites"]
			);
		}
		if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
		{
			$menuItems[] = array(
				"TEXT" => GetMessage("SONET_C33_T_F_ARCHIVE"),
				"URL" => (strlen($arResult["WORKGROUPS_PATH"]) > 0 ? $arResult["WORKGROUPS_PATH"]."?filter_archive=Y" : $APPLICATION->GetCurPageParam("filter_archive=Y", $arFilterKeys, false)),
				"ID" => "workgroups_archive",
				"IS_ACTIVE" => $arResult["filter_archive"]
			);
		}
		if (IsModuleInstalled("extranet") && !$arResult["bExtranet"])
		{
			$menuItems[] = array(
				"TEXT" => GetMessage("SONET_C33_T_F_EXTRANET"),
				"URL" => (strlen($arResult["WORKGROUPS_PATH"]) > 0 ? $arResult["WORKGROUPS_PATH"]."?filter_extranet=Y" : $APPLICATION->GetCurPageParam("filter_extranet=Y", $arFilterKeys, false)),
				"ID" => "workgroups_extranet",
				"IS_ACTIVE" => $arResult["filter_extranet"]
			);
		}
		if (
			$arParams["USE_KEYWORDS"] != "N"
			&& IsModuleInstalled("search")
		)
		{
			$menuItems[] = array(
				"TEXT" => GetMessage("SONET_C33_T_F_TAGS"),
				"URL" => (strlen($arResult["WORKGROUPS_PATH"]) > 0 ? $arResult["WORKGROUPS_PATH"]."?filter_tags=Y" : $APPLICATION->GetCurPageParam("filter_tags=Y", $arFilterKeys, false)),
				"ID" => "workgroups_tags",
				"IS_ACTIVE" => $arResult["filter_tags"]
			);
		}

		if (SITE_TEMPLATE_ID === "bitrix24")
		{
			$this->SetViewTarget("above_pagetitle", 100);
		}

		$menuId = "sonetgroups_panel_menu";

		$APPLICATION->IncludeComponent(
			"bitrix:main.interface.buttons",
			"",
			array(
				"ID" => $menuId,
				"ITEMS" => $menuItems,
			)
		);

		if (SITE_TEMPLATE_ID === "bitrix24")
		{
			$this->EndViewTarget();
		}
		?>

		<div class="sonet-groups-separator"></div><?

		if (
			$arParams["USE_KEYWORDS"] != "N"
			&& $arResult["filter_tags"] == "Y"
		)
		{
			if (IsModuleInstalled("search"))
			{
				?><div class="sonet-groups-tags-block"><?
				$arrFilterAdd = array("PARAMS" => array("entity" => "sonet_group"));
				$APPLICATION->IncludeComponent(
					"bitrix:search.tags.cloud",
					"",
					Array(
						"FONT_MAX" => (IntVal($arParams["FONT_MAX"]) >0 ? $arParams["FONT_MAX"] : 20),
						"FONT_MIN" => (IntVal($arParams["FONT_MIN"]) >0 ? $arParams["FONT_MIN"] : 10),
						"COLOR_NEW" => (strlen($arParams["COLOR_NEW"]) >0 ? $arParams["COLOR_NEW"] : "3f75a2"),
						"COLOR_OLD" => (strlen($arParams["COLOR_OLD"]) >0 ? $arParams["COLOR_OLD"] : "8D8D8D"),
						"ANGULARITY" => $arParams["ANGULARITY"],
						"PERIOD_NEW_TAGS" => $arResult["PERIOD_NEW_TAGS"],
						"SHOW_CHAIN" => "N",
						"COLOR_TYPE" => $arParams["COLOR_TYPE"],
						"WIDTH" => $arParams["WIDTH"],
						"SEARCH" => "",
						"TAGS" => "",
						"SORT" => "NAME",
						"PAGE_ELEMENTS" => "150",
						"PERIOD" => $arParams["PERIOD"],
						"URL_SEARCH" => $arResult["PATH_TO_GROUP_SEARCH"],
						"TAGS_INHERIT" => "N",
						"CHECK_DATES" => "Y",
						"FILTER_NAME" => "arrFilterAdd",
						"arrFILTER" => Array("socialnetwork"),
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600"
					),
					$component
				);
				?></div><?
			}
			else
			{
				echo "<br /><span class='errortext'>".GetMessage("SONET_C36_T_NO_SEARCH_MODULE")."</span><br /><br />";
			}
		}
	}
	?><script>
		BX.ready(function() {
			BX.bind(BX('bx-sonet-groups-sort'), 'click', function(e) {
				oSUG.showSortMenu({
					bindNode: BX('bx-sonet-groups-sort'),
					valueNode: BX('bx-sonet-groups-sort-value'),
					userId: <?=intval($arParams['USER_ID'])?>,
					showMembersCountItem: <?=($arParams["PAGE"] == 'groups_list' && !$arResult["filter_my"] ? 'true' : 'false')?>
				});
				return BX.PreventDefault(e);
			});
		});
	</script><?

	$available = (
		in_array($arParams["PAGE"], array("groups_list", "groups_subject"))
		|| (
			$arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
			&& $arResult["CurrentUserPerms"]["Operations"]["viewgroups"]
		)
	);

	$notEmptyList = ($available && $arResult["Groups"] && $arResult["Groups"]["List"]);

	?><div class="sonet-groups-content-wrap<?=(!$notEmptyList ? " no-groups" : "")?>">
		<div class="sonet-groups-content-sort-container"><?
			?><span id="bx-sonet-groups-sort"><?
				?><?=GetMessage('SONET_C33_T_F_SORT')?><?
				?><span class="sonet-groups-content-sort-btn" id="bx-sonet-groups-sort-value"><?=GetMessage('SONET_C33_T_F_SORT_'.strtoupper($arResult["ORDER_KEY"]))?></span><?
			?></span><?
			?><span class="sonet-groups-search"><?
				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.user_groups.search_form",
					".default",
					array(),
					null,
					array("HIDE_ICONS" => "Y")
				);
			?></span><?
		?></div>
		<?

		if ($available)
		{
			if ($notEmptyList)
			{
				?><div class="sonet-groups-group-block-shift">
					<div class="sonet-groups-group-block-row"><?

				/**/$i = 1;/**/
				foreach ($arResult["Groups"]["List"] as $group)
				{
					/**/if ($i > 1 && $i % 2)
					{
						?></div><div class="sonet-groups-group-block-row"><?
					}/**/

					?><div class="sonet-groups-group-block"><?
						?><span class="sonet-groups-group-img"<?=($group["GROUP_PHOTO_RESIZED_COMMON"] ? " style=\"background:#fff url('".$group["GROUP_PHOTO_RESIZED_COMMON"]["src"]."') no-repeat; background-size: cover;\"" : "")?>></span><?
						?><span class="sonet-groups-group-text"><?
							?><span class="sonet-groups-group-title<?=($group["IS_EXTRANET"] == "Y" ? " sonet-groups-group-title-extranet" : "")?>"><?
								?><span class="sonet-groups-group-title-text"><?
									?><a href="<?=$group["GROUP_URL"]?>" class="sonet-groups-group-link"><?=$group["GROUP_NAME"]?></a><?
									?><?=($group["IS_EXTRANET"] == "Y" && SITE_TEMPLATE_ID != "bitrix24" ? '<span class="sonet-groups-group-signature">'.GetMessage("SONET_C33_T_IS_EXTRANET").'</span>' : '')?><?
								?></span><?

								$isFav = (isset($group['IN_FAVORITES']) && $group['IN_FAVORITES'] == 'Y');
								?><span title="<?=GetMessage('SONET_C33_T_ACT_FAVORITES_'.($isFav ? 'REMOVE' : 'ADD'))?>" id="bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>" class="sonet-groups-group-title-favorites<?=($isFav ? ' sonet-groups-group-title-favorites-active' : '')?>"></span><?
								?><script>
									BX.bind(BX('bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>'), 'click', function(e) {
										var star = BX('bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>');
										var isActive = BX.hasClass(BX('bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>'), 'sonet-groups-group-title-favorites-active');

										oSUG.setFavorites(star, !isActive);

										oSUG.sendRequest({
											action: 'FAVORITES',
											groupId: <?=intval($group['GROUP_ID'])?>,
											value: (isActive ? 'N' : 'Y'),
											callback_success: function(data)
											{
												if (
													typeof data.NAME != 'undefined'
													&& typeof data.URL != 'undefined'
												)
												{
													BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupFavorites:onSet', [{
														id: <?=intval($group['GROUP_ID'])?>,
														name: data.NAME,
														url: data.URL,
														extranet: (typeof data.EXTRANET != 'undefined' ? data.EXTRANET : 'N')
													}, !isActive]);
												}
											},
											callback_failure: function(errorText)
											{
												oSUG.setFavorites(star, isActive);
											}
										});
										return BX.PreventDefault(e);
									});
								</script><?
							?></span><?
							?><?=(strlen($group["GROUP_DESCRIPTION_FULL"]) > 0 ? '<span class="sonet-groups-group-description">'.$group["GROUP_DESCRIPTION_FULL"].'</span>' : "")?><?
							$membersCount = $group["NUMBER_OF_MEMBERS"];
							$suffix = (
								($membersCount % 100) > 10
								&& ($membersCount % 100) < 20
									? 5
									: $membersCount % 10
							);
							?><span class="sonet-groups-group-users"><?=GetMessage('SONET_C33_T_F_MEMBERS_'.$suffix, array('#NUM#' => $membersCount))?></span><?

							if (
								$USER->isAuthorized()
								&& (!isset($group['GROUP_CLOSED']) || $group['GROUP_CLOSED'] != "Y")
							)
							{
								?><span class="sonet-groups-group-btn-container"><?

									$requestSent = (isset($group['ROLE']) && $group['ROLE'] == \Bitrix\Socialnetwork\UserToGroupTable::ROLE_REQUEST);
									?><span id="bx-sonet-groups-request-sent-<?=intval($group['GROUP_ID'])?>" class="sonet-groups-group-desc-container<?=($requestSent ? " sonet-groups-group-desc-container-active" : "")?>"><span class="sonet-groups-group-desc-check"></span><?=GetMessage('SONET_C33_T_F_DO_REQUEST_SENT')?></span><?

									if (
										isset($group['ROLE'])
										&& empty($group['ROLE'])
									)
									{
										?><span id="bx-sonet-groups-request-<?=intval($group['GROUP_ID'])?>" class="popup-window-button"><?=GetMessage('SONET_C33_T_F_DO_REQUEST')?></span><?
										?><script>
											BX.bind(BX('bx-sonet-groups-request-<?=intval($group['GROUP_ID'])?>'), 'click', function(e) {
												var button = BX('bx-sonet-groups-request-<?=intval($group['GROUP_ID'])?>');
												var requestSentNode = BX('bx-sonet-groups-request-sent-<?=intval($group['GROUP_ID'])?>');

												oSUG.showRequestWait(button);
												oSUG.sendRequest({
													action: 'REQUEST',
													groupId: <?=intval($group['GROUP_ID'])?>,
													callback_success: function(response)
													{
														oSUG.closeRequestWait(button);
														oSUG.setRequestSent(button, requestSentNode, (typeof response != 'undefined' && typeof response.ROLE != 'undefined' ? response.ROLE : null));
													},
													callback_failure: function(errorText)
													{
														oSUG.closeRequestWait(button);
														oSUG.showError(errorText);
													}
												});
												return BX.PreventDefault(e);
											});
										</script><?
									}

								?></span><?
							}

						?></span><?
					?></div><?

					/**/$i++;/**/
				}
				/**/?></div></div><?/**/

				if (StrLen($arResult["NAV_STRING"]) > 0)
				{
					?><?=$arResult["NAV_STRING"]?><br /><br /><?
				}
			}
			else
			{
				?><div class="sonet-groups-group-message"><div class="sonet-groups-group-message-text"><?=GetMessage("SONET_C36_T_NO_GROUPS");?></div></div><?
			}
		}
		else
		{
			?><div class="sonet-groups-group-message"><div class="sonet-groups-group-message-text"><?=GetMessage("SONET_C36_T_GR_UNAVAIL");?></div></div><?
		}
		?>
	</div><?
	if (
		SITE_TEMPLATE_ID === "bitrix24"
		&& !empty($arResult["SIDEBAR_GROUPS"])
	)
	{
		$this->SetViewTarget("sidebar");

		?><div class="sonet-sidebar">
			<div class="sonet-groups-sidebar-content">
				<div class="sonet-groups-sidebar-title">
					<span class="sonet-groups-sidebar-status-text"><?=GetMessage("SONET_C33_T_F_LAST_VIEW")?></span>
				</div>

				<div class="sonet-groups-sidebar-items"><?
					foreach ($arResult["SIDEBAR_GROUPS"] as $group)
					{
						?><div class="sonet-groups-group-block"><?
							?><span class="sonet-groups-group-img"<?=($group["IMAGE_RESIZED"] ? " style=\"background:#fff url('".$group["IMAGE_RESIZED"]["src"]."') no-repeat; background-size: cover;\"" : "") ?>></span><?
							?><span class="sonet-groups-group-text"><?
								?><span class="sonet-groups-group-title<?=($group["IS_EXTRANET"] == "Y" ? " sonet-groups-group-title-extranet" : "") ?>"><?
									?><a href="<?=$group["URL"]?>" class="sonet-groups-group-link"><?=$group["NAME"] ?></a><?
								?></span><?
								?><span class="sonet-groups-group-description"><?=(strlen($group["DESCRIPTION"]) > 0 ? $group["DESCRIPTION"] : "&nbsp;") ?></span><?
							?></span><?
						?></div><?
					}
				?></div>
			</div>
		</div>
		<?
		$this->EndViewTarget();
	}
}
?>