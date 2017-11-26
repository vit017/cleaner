<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Converters;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class DataConverter
{
	protected $exportId;
	
	abstract public function convert($data);
	
	/**
	 * VK don't understand specialchars-quotes. Change them to the yolochki
	 *
	 * @param $str
	 * @return mixed
	 */
	protected static function convertQuotes($str)
	{
		if(strlen($str) > 0)
			return preg_replace(
				array('#"(.*?)"#', '#&quot;(.*?)&quot;#'),
				Loc::getMessage("SALE_VK_PRODUCT_LAQUO").'$1'.Loc::getMessage("SALE_VK_PRODUCT_RAQUO"), $str
			);
		else
			return $str;
	}
}


