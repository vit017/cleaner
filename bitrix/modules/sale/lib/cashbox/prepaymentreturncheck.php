<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PrePaymentReturnCheck
 * @package Bitrix\Sale\Cashbox
 */
class PrePaymentReturnCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'prepaymentreturn';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Loc::getMessage('SALE_CASHBOX_PREPAYMENT_RETURN_NAME');
	}

	/**
	 * @return array
	 */
	public function getDataForCheck()
	{
		return array();
	}

}