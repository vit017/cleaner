<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

class OrderArchiveTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_order_archive';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'ID',
				array(
					'autocomplete' => true,
					'primary' => true,
				)
			),
			new Main\Entity\IntegerField(
				'LID',
				array(
					'required' => true,
				)
			),
			new Main\Entity\IntegerField(
				'ORDER_ID',
				array(
					'required' => true,
				)
			),
			new Main\Entity\StringField(
				'ACCOUNT_NUMBER',
				array(
					'size' => 100,
					'required' => true,
				)
			),			
			new Main\Entity\StringField(
				'USER_ID',
				array(
					'required' => true,
				)
			),
			new Main\Entity\StringField(
				'PERSON_TYPE_ID',
				array(
					'required' => true,
				)
			),

			new Main\Entity\StringField('STATUS_ID'),

			new Main\Entity\ReferenceField(
				'STATUS',
				'Bitrix\Sale\Internals\StatusLang',
				array(
					'=this.STATUS_ID' => 'ref.STATUS_ID',
					'=ref.LID' => array('?', LANGUAGE_ID)
				)
			),

			new Main\Entity\BooleanField(
				'PAYED',
				array(
					'values' => array('N', 'Y')
				)
			),

			new Main\Entity\BooleanField(
				'DEDUCTED',
				array(
					'values' => array('N','Y')
				)
			),

			new Main\Entity\BooleanField(
				'CANCELED',
				array(
					'values' => array('N', 'Y')
				)
			),

			new Main\Entity\FloatField(
				'PRICE',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\StringField(
				'CURRENCY',
				array(
					'required' => true,
					'size' => 3
				)
			),

			new Main\Entity\IntegerField(
				'VERSION',
				array(
					'required' => true,
				)
			),

			new Main\Entity\IntegerField('XML_ID'),

			new Main\Entity\IntegerField('ID_1C'),

			new Main\Entity\DatetimeField('DATE_ARCHIVED'),
			
			new Main\Entity\DatetimeField('DATE_INSERT'),

			new Main\Entity\IntegerField('RESPONSIBLE_ID'),
			
			new Main\Entity\IntegerField('COMPANY_ID'),
			
			new Main\Entity\StringField('ORDER_DATA')
		);
	}
}