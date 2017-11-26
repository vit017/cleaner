<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

class FirstOfd extends Ofd
{
	const ACTIVE_URL = 'http://consumer.1-ofd.ru/v1?';
	const TEST_URL = 'http://test-consumer.1-ofd.ru/v1?';

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return $this->testMode ? static::TEST_URL : static::ACTIVE_URL;
	}

	protected function getLinkParamsMap()
	{
		return array(
			't' => Check::PARAM_DOC_TIME,
			's' => Check::PARAM_DOC_SUM,
			'fn' => Check::PARAM_FN_NUMBER,
			'i' => Check::PARAM_FISCAL_DOC_NUMBER,
			'fp' => Check::PARAM_FISCAL_DOC_ATTR,
			'n' => Check::PARAM_CALCULATION_ATTR
		);
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_FIRST_OFD_NAME');
	}

}