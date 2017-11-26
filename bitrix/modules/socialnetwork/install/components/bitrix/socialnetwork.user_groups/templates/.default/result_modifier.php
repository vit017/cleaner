<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

$arResult['SIDEBAR_GROUPS'] = array();

if (
	SITE_TEMPLATE_ID === "bitrix24"
	&& $arParams["USER_ID"] == $USER->getId()
	&& (
		!\Bitrix\Main\Loader::includeModule('extranet')
		|| !CExtranet::IsExtranetSite()
	)
)
{
	$count = 10;

	$lastViewCache = \Bitrix\Main\Data\Cache::createInstance();
	$cacheTtl = 60*60*24*365;
	$cacheId = 'user_groups_date_view'.SITE_ID.'_'.$arParams["USER_ID"].$count;
	$cacheDir = '/sonet/user_group_date_view/'.SITE_ID.'/'.$arParams["USER_ID"];

	$lastViewGroupsList = array();

	if($lastViewCache->startDataCache($cacheTtl, $cacheId, $cacheDir))
	{
		$query = new \Bitrix\Main\Entity\Query(\Bitrix\Socialnetwork\WorkgroupTable::getEntity());

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('UG',
				\Bitrix\Socialnetwork\UserToGroupTable::getEntity(),
				array(
					'=ref.GROUP_ID' => 'this.ID',
					'=ref.USER_ID' =>  new \Bitrix\Main\DB\SqlExpression($arParams["USER_ID"])
				),
				array('join_type' => 'LEFT')
			)
		);
		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('GV',
				\Bitrix\Socialnetwork\WorkgroupViewTable::getEntity(),
				array(
					'=ref.GROUP_ID' => 'this.ID',
					'=ref.USER_ID' =>  new \Bitrix\Main\DB\SqlExpression($arParams["USER_ID"])
				),
				array('join_type' => 'INNER')
			)
		);
		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('GS',
				\Bitrix\Socialnetwork\WorkgroupSiteTable::getEntity(),
				array(
					'=ref.GROUP_ID' => 'this.ID'
				),
				array('join_type' => 'INNER')
			)
		);
		$query->addOrder('GV.DATE_VIEW', 'DESC');

		$query->addFilter('=GS.SITE_ID', SITE_ID);
		$query->addFilter(null, array(
			'LOGIC' => 'OR',
			'=VISIBLE' => 'Y',
			'<=UG.ROLE' => \Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER
		));

		$query->addSelect('ID');
		$query->addSelect('NAME');
		$query->addSelect('DESCRIPTION');
		$query->addSelect('IMAGE_ID');

		$query->countTotal(false);
		$query->setOffset(0);
		$query->setLimit($count);

		$res = $query->exec();

		if ($res)
		{
			$groupIdList = array();
			while ($group = $res->fetch())
			{
				$groupIdList[] = $group['ID'];

				$group["NAME"] = htmlspecialcharsEx($group["NAME"]);
				$group['URL'] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $group["ID"]));
				$group["DESCRIPTION"] = (strlen($group["DESCRIPTION"]) > 47 ? substr($group["DESCRIPTION"], 0, 47)."..." : $group["DESCRIPTION"]);
				$group["DESCRIPTION"] = htmlspecialcharsEx($group["DESCRIPTION"]);

				$imageResized = false;

				if (intval($group["IMAGE_ID"]) > 0)
				{
					$imageFile = \CFile::getFileArray($group["IMAGE_ID"]);
					if ($imageFile !== false)
					{
						$imageResized = \CFile::resizeImageGet(
							$imageFile,
							array("width" => $arParams["THUMBNAIL_SIZE_COMMON"], "height" => $arParams["THUMBNAIL_SIZE_COMMON"]),
							BX_RESIZE_IMAGE_EXACT
						);
					}
				}
				$group['IMAGE_RESIZED'] = $imageResized;

				$lastViewGroupsList[] = $group;
			}

			// get extranet info
			if (
				!empty($groupIdList)
				&& Bitrix\Main\Loader::includeModule('extranet')
				&& ($extranetSiteId = CExtranet::getExtranetSiteID())
			)
			{
				$groupSiteList = array();
				$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList(array(
					'filter' => array(
						'@GROUP_ID' => $groupIdList
					),
					'select' => array('GROUP_ID', 'SITE_ID')
				));
				while ($groupSite = $resSite->fetch())
				{
					if (!isset($groupSiteList[$groupSite['GROUP_ID']]))
					{
						$groupSiteList[$groupSite['GROUP_ID']] = array();
					}
					$groupSiteList[$groupSite['GROUP_ID']][] = $groupSite['SITE_ID'];
				}

				foreach($lastViewGroupsList as $key => $group)
				{
					$lastViewGroupsList[$key]['IS_EXTRANET'] = (in_array($extranetSiteId, $groupSiteList[$group['ID']]) ? 'Y' : 'N');
				}
			}
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->startTagCache($cacheDir);
			$CACHE_MANAGER->registerTag("sonet_group_view_U".$arParams["USER_ID"]);
			$CACHE_MANAGER->registerTag("sonet_user2group_U".$arParams["USER_ID"]);
			$CACHE_MANAGER->registerTag("sonet_group");
			$CACHE_MANAGER->endTagCache();
		}

		$lastViewCache->endDataCache(array("SIDEBAR_GROUPS" => $lastViewGroupsList));
	}
	else
	{
		$cacheResult = $lastViewCache->getVars();
		$lastViewGroupsList = $cacheResult['SIDEBAR_GROUPS'];
	}

	$arResult['SIDEBAR_GROUPS'] = $lastViewGroupsList;
}

?>