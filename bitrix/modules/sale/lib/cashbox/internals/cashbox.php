<?php
namespace Bitrix\Sale\Cashbox\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use	Bitrix\Main\Entity\DataManager;
use	Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Cashbox\Cashbox1C;
use Bitrix\Sale\Cashbox\Manager;

class CashboxTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'HANDLER' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime()
			),
			'DATE_LAST_CHECK' => array(
				'data_type' => 'datetime',
			),
			'KKM_ID' => array(
				'data_type' => 'integer',
			),
			'OFD' => array(
				'data_type' => 'string',
			),
			'NUMBER_KKM' => array(
				'data_type' => 'string',
			),
			'SETTINGS' => array(
				'data_type' => 'string',
				'serialized' => true
			),
			'USE_OFFLINE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'OFD_TEST_MODE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'ENABLED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
		);
	}

}
