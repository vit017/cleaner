<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Socialnetwork\LogIndexTable;
use Bitrix\Main\Loader;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Disk\AttachedObject;

class LogIndex
{
	public static function getUserName($userId = 0)
	{
		$result = '';
		$userId = intval($userId);

		if ($userId <= 0)
		{
			return $result;
		}

		$code = 'U'.$userId;
		$data = self::getEntitiesName(array($code));
		if (!empty($data[$code]))
		{
			$result = $data[$code];
		}

		return $result;
	}

	public static function getEntitiesName($entityCodesList = array())
	{
		static $renderPartsUser = false;
		static $renderPartsSonetGroup = false;
		static $renderPartsDepartment = false;

		$result = array();
		if (
			!is_array($entityCodesList)
			|| empty($entityCodesList)
		)
		{
			return $result;
		}

		$renderOptions = array(
			'skipLink' => true
		);

		if ($renderPartsUser === false)
		{
			$renderPartsUser = new \Bitrix\Socialnetwork\Livefeed\RenderParts\User($renderOptions);
		}
		if ($renderPartsSonetGroup === false)
		{
			$renderPartsSonetGroup = new \Bitrix\Socialnetwork\Livefeed\RenderParts\SonetGroup($renderOptions);
		}
		if ($renderPartsDepartment === false)
		{
			$renderPartsDepartment = new \Bitrix\Socialnetwork\Livefeed\RenderParts\Department($renderOptions);
		}

		foreach($entityCodesList as $code)
		{
			$renderData = false;
			if (preg_match('/^U(\d+)$/i', $code, $matches))
			{
				$renderData = $renderPartsUser->getData($matches[1]);
			}
			elseif (preg_match('/^SG(\d+)$/i', $code, $matches))
			{
				$renderData = $renderPartsSonetGroup->getData($matches[1]);
			}
			elseif (
				preg_match('/^D(\d+)$/i', $code, $matches)
				|| preg_match('/^DR(\d+)$/i', $code, $matches)
			)
			{
				$renderData = $renderPartsDepartment->getData($matches[1]);
			}

			if (
				$renderData
				&& $renderData['name']
			)
			{
				$result[$code] = $renderData['name'];
			}
		}

		return $result;
	}

	public static function getDiskUFFileNameList($valueList = array())
	{
		$result = array();

		if (
			!empty($valueList)
			&& is_array($valueList)
			&& Loader::includeModule('disk')
		)
		{
			$attachedIdList = array();
			foreach($valueList as $value)
			{
				list($type, $realValue) = FileUserType::detectType($value);
				if($type == FileUserType::TYPE_NEW_OBJECT)
				{
					$file = \Bitrix\Disk\File::loadById($realValue, array('STORAGE'));
					$result[] = strip_tags($file->getName());
				}
				else
				{
					$attachedIdList[] = $realValue;
				}
			}

			if(!empty($attachedIdList))
			{
				$attachedObjects = AttachedObject::getModelList(array(
					'with' => array('OBJECT'),
					'filter' => array(
						'ID' => $attachedIdList
					),
				));
				foreach($attachedObjects as $attachedObject)
				{
					$file = $attachedObject->getFile();
					$result[] = strip_tags($file->getName());
				}
			}
		}

		return $result;
	}

	public static function setIndex($params = array())
	{
		if (!is_array($params))
		{
			return;
		}

		$fields = (isset($params['fields']) ? $params['fields'] : array());
		$itemType = (isset($params['itemType']) ? trim($params['itemType']) : false);
		$itemId = (isset($params['itemId']) ? intval($params['itemId']) : 0);

		if (
			!is_array($fields)
			|| empty($fields)
			|| empty($itemType)
			|| !in_array($itemType, LogIndexTable::getItemTypes())
			|| $itemId <= 0
		)
		{
			return;
		}

		$eventId = (isset($fields['EVENT_ID']) ? trim($fields['EVENT_ID']) : false);
		$sourceId = (isset($fields['SOURCE_ID']) ? intval($fields['SOURCE_ID']) : 0);

		if (
			empty($eventId)
			|| $sourceId <= 0
		)
		{
			$res = false;
			if ($itemType == LogIndexTable::ITEM_TYPE_LOG)
			{
				$res = LogTable::getList(array(
					'filter' => array(
						'=ID' => $itemId
					),
					'select' => array('ID', 'EVENT_ID', 'SOURCE_ID')
				));
			}
			elseif ($itemType == LogIndexTable::ITEM_TYPE_COMMENT)
			{
				$res = LogCommentTable::getList(array(
					'filter' => array(
						'=ID' => $itemId
					),
					'select' => array('ID', 'EVENT_ID', 'SOURCE_ID')
				));
			}

			if (
				$res
				&& ($item = $res->fetch())
			)
			{
				$eventId = (isset($item['EVENT_ID']) ? trim($item['EVENT_ID']) : false);
				$sourceId = (isset($item['SOURCE_ID']) ? intval($item['SOURCE_ID']) : 0);
				$itemId = (isset($item['ID']) ? intval($item['ID']) : 0);
			}
		}

		if (
			empty($eventId)
			|| $itemId <= 0
		)
		{
			return;
		}

		$content = '';
		$event = new Main\Event(
			'socialnetwork',
			($itemType == LogIndexTable::ITEM_TYPE_COMMENT ? 'onLogCommentIndexGetContent' : 'onLogIndexGetContent'),
			array(
				'eventId' => $eventId,
				'sourceId' => $sourceId,
				'itemId' => $itemId,
			)
		);
		$event->send();

		foreach($event->getResults() as $eventResult)
		{
			if($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$eventParams = $eventResult->getParameters();

				if (
					is_array($eventParams)
					&& isset($eventParams['content'])
				)
				{
					$content = $eventParams['content'];
					if (Main\Loader::includeModule('search'))
					{
						$content = \CSearch::killTags($content);
					}
					$content = trim(str_replace(
						array("\r", "\n", "\t"),
						" ",
						$content
					));

					$content = self::prepareToken($content);
				}
				break;
			}
		}

		if (!empty($content))
		{
			$logId = 0;

			if ($itemType == LogIndexTable::ITEM_TYPE_LOG)
			{
				$logId = $itemId;
			}
			elseif ($itemType == LogIndexTable::ITEM_TYPE_COMMENT)
			{
				$logId = (isset($fields['LOG_ID']) ? intval($fields['LOG_ID']) : 0);
				if ($logId <= 0)
				{
					$res = LogCommentTable::getList(array(
						'filter' => array(
							'=ID' => $itemId
						),
						'select' => array('ID', 'LOG_ID')
					));
					if ($comment = $res->fetch())
					{
						$logId = intval($comment['LOG_ID']);
					}
				}
			}

			if ($logId > 0)
			{
				LogIndexTable::set(array(
					'itemType' => $itemType,
					'itemId' => $itemId,
					'logId' => $logId,
					'content' => $content,
				));
			}
		}
	}

	public static function deleteIndex($params = array())
	{
		if (!is_array($params))
		{
			return;
		}

		$itemType = (isset($params['itemType']) ? trim($params['itemType']) : false);
		$itemId = (isset($params['itemId']) ? intval($params['itemId']) : 0);

		if (
			empty($itemType)
			|| !in_array($itemType, LogIndexTable::getItemTypes())
			|| $itemId <= 0
		)
		{
			return;
		}

		if ($itemType == LogIndexTable::ITEM_TYPE_LOG) // delete all comments
		{
			$connection = Main\Application::getConnection();
			$query = "DELETE FROM ".LogIndexTable::getTableName()." WHERE LOG_ID = ".$itemId;
			$connection->queryExecute($query);
		}

		LogIndexTable::delete(array(
			'ITEM_TYPE' => $itemType,
			'ITEM_ID' => $itemId
		));
	}

	public static function prepareToken($str)
	{
		return str_rot13($str);
	}
}
