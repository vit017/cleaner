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

class BasketArchiveTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_basket_archive';
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
				'ARCHIVE_ID',
				array(
					'required' => true,
				)
			),

			new Main\Entity\IntegerField(
				'PRODUCT_ID',
				array(
					'required' => true,
				)
			),

			new Main\Entity\IntegerField('PRODUCT_PRICE_ID'),

			new Main\Entity\StringField(
				'NAME',
				array(
					'size' => 255,
					'required' => true,
				)
			),


			new Main\Entity\FloatField(
				'PRICE',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\StringField('MODULE'),

			new Main\Entity\FloatField(
				'QUANTITY',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\FloatField(
				'WEIGHT',
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

			new Main\Entity\StringField(
				'PRODUCT_XML_ID',
				array(
					'size' => 100
				)
			),

			new Main\Entity\StringField(
				'MEASURE_NAME',
				array(
					'size' => 50
				)
			),

			new Main\Entity\IntegerField('TYPE'),

			new Main\Entity\IntegerField('SET_PARENT_ID'),

			new Main\Entity\IntegerField('MEASURE_CODE'),

			new Main\Entity\DatetimeField('DATE_INSERT'),
			
			new Main\Entity\StringField('BASKET_DATA')
		);
	}
}