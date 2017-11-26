<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists("getProductByProps"))
{
	function getProductByProps($iblockID, $arSkuProps)
	{
		$result = false;
		$arOfFilter = array(
			"IBLOCK_ID" => $iblockID,
		);

		$rsProps = CIBlockProperty::GetList(
			array('SORT' => 'ASC', 'ID' => 'ASC'),
			array('IBLOCK_ID' => $iblockID, 'ACTIVE' => 'Y')
		);
		while ($arProp = $rsProps->Fetch())
		{
			if (!$arProp['CODE'])
				$arProp['CODE'] = $arProp['ID'];
			if (isset($arSkuProps[$arProp["CODE"]]))
			{
				if ($arProp["CODE"] == "CML2_LINK" || $arProp['PROPERTY_TYPE'] == 'E' || ($arProp['PROPERTY_TYPE'] == 'S' && $arProp['USER_TYPE'] == 'directory'))
				{
					$arOfFilter["PROPERTY_".$arProp["CODE"]] = $arSkuProps[$arProp["CODE"]];
				}
				elseif ($arProp["PROPERTY_TYPE"] == "L")
				{
					$arOfFilter["PROPERTY_".$arProp["CODE"]."_VALUE"] = $arSkuProps[$arProp["CODE"]];
				}
			}
		}

		$rsOffers = CIBlockElement::GetList(
			array(),
			$arOfFilter,
			false,
			false,
			array('ID')
		);
		if ($arOffer = $rsOffers->Fetch())
			$result = $arOffer['ID'];

		return $result;
	}
}
?>