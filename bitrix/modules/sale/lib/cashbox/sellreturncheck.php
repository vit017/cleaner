<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SellReturnCheck
 * @package Bitrix\Sale\Cashbox
 */
class SellReturnCheck extends SellCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'sellreturn';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Loc::getMessage('SALE_CASHBOX_SELL_RETURN_NAME');
	}

	/**
	 * @return array
	 */
	public function getDataForCheck()
	{
		$result = parent::getDataForCheck();

		if (isset($result['payments']))
		{
			foreach ($result['payments'] as $i => $payment)
				$result['payments'][$i]['is_cash'] = 'N';

		}

		return $result;
	}
}