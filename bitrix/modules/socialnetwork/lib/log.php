<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

class LogTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log';
	}

	public static function getUfId()
	{
		return 'SONET_LOG';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'EVENT_ID' => array(
				'data_type' => 'string',
			),
			'TITLE' => array(
				'data_type' => 'string',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			),
			'URL' => array(
				'data_type' => 'string',
			),
			'PARAMS' => array(
				'data_type' => 'text',
			),
			'SOURCE_ID' => array(
				'data_type' => 'integer',
			),
		);

		return $fieldsMap;
	}
}
