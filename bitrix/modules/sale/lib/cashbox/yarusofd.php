<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class YarusOfd
 * @package Bitrix\Sale\Cashbox
 */
class YarusOfd extends Ofd
{
	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_YARUS_OFD_NAME');
	}

}