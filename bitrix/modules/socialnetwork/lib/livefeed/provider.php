<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class Provider
{
	const DATA_RESULT_TYPE_SOURCE = 'SOURCE';

	const DATA_ENTITY_TYPE_BLOG_POST = 'BLOG_POST';
	const DATA_ENTITY_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';

	const PERMISSION_DENY = 'D';
	const PERMISSION_READ = 'I';
	const PERMISSION_FULL = 'W';

	protected $entityId = 0;
	protected $logId = 0;
	protected $sourceFields = array();
	protected $siteId = false;

	protected $cloneDiskObjects = false;
	protected $sourceDescription = '';
	protected $sourceTitle = '';
	protected $sourceAttachedDiskObjects = array();
	protected $sourceDiskObjects = array();
	protected $diskObjectsCloned = array();
	protected $attachedDiskObjectsCloned = array();

	/**
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return get_called_class();
	}

	public function setSiteId($siteId)
	{
		$this->siteId = $siteId;
	}

	public function getSiteId()
	{
		return $this->siteId;
	}

	public static function getId()
	{
		return 'BASE';
	}

	public function getEventId()
	{
		return false;
	}

	public function getType()
	{
		return false;
	}

	final private static function getProvider($entityType)
	{
		switch ($entityType)
		{
			case self::DATA_ENTITY_TYPE_BLOG_POST:
				$provider = new \Bitrix\Socialnetwork\Livefeed\BlogPost();
				break;
			case self::DATA_ENTITY_TYPE_BLOG_COMMENT:
				$provider = new \Bitrix\Socialnetwork\Livefeed\BlogComment();
				break;
			default:
				$provider = false;
		}

		return $provider;
	}

	public static function init(array $params)
	{
		$provider = self::getProvider($params['ENTITY_TYPE']);
		if ($provider)
		{
			$provider->setEntityId($params['ENTITY_ID']);
			$provider->setSiteId(isset($params['SITE_ID']) ? $params['SITE_ID'] : SITE_ID);
			if (
				isset($params['CLONE_DISK_OBJECTS'])
				&& $params['CLONE_DISK_OBJECTS'] === true
			)
			{
				$provider->cloneDiskObjects = true;
			}
		}

		return $provider;
	}

	public function initSourceFields()
	{
	}

	public static function canRead($params)
	{
		return false;
	}

	protected function getPermissions(array $entity)
	{
		return self::PERMISSION_DENY;
	}

	protected function getLogId()
	{
		$result = false;

		if (intval($this->logId) > 0)
		{
			$result = intval($this->logId);
		}
		else
		{
			$eventId = $this->getEventId();

			if (!empty($eventId))
			{
				if ($this->getType() == 'entry')
				{
					$res = \CSocNetLog::getList(
						array(),
						array(
							'SOURCE_ID' => $this->entityId,
							'EVENT_ID' => $eventId
						),
						false,
						array('nTopCount' => 1),
						array('ID')
					);

					if (
						($logEntry = $res->fetch())
						&& (intval($logEntry['ID']) > 0)
					)
					{
						$result = $this->logId = intval($logEntry['ID']);
					}
				}
				elseif ($this->getType() == 'comment')
				{
					$res = \CSocNetLogComments::getList(
						array(),
						array(
							'SOURCE_ID' => $this->entityId,
							'EVENT_ID' => $eventId
						),
						false,
						array('nTopCount' => 1),
						array('ID', 'LOG_ID')
					);

					if (
						($logEntry = $res->fetch())
						&& (intval($logEntry['LOG_ID']) > 0)
					)
					{
						$result = $this->logId = intval($logEntry['LOG_ID']);
					}
				}
			}
		}

		return $result;
	}

	public function getSonetGroupsAvailable($feature = false, $operation = false)
	{
		global $USER;

		$result = array();

		$logRights = $this->getLogRights();
		if (
			!empty($logRights)
			&& is_array($logRights)
		)
		{
			foreach($logRights as $groupCode)
			{
				if (preg_match('/^SG(\d+)/', $groupCode, $matches))
				{
					$result[] = $matches[1];
				}
			}
		}

		if (
			!empty($result)
			&& !!$feature
			&& !!$operation
		)
		{
			$availability = \CSocNetFeaturesPerms::canPerformOperation(
				$USER->getId(),
				SONET_ENTITY_GROUP,
				$result,
				$feature,
				$operation
			);
			foreach ($result as $key => $groupId)
			{
				if (
					!isset($availability[$groupId])
					|| !$availability[$groupId]
				)
				{
					unset($result[$key]);
				}
			}
		}
		$result = array_unique($result);

		return $result;
	}

	public function getLogRights()
	{
		$result = array();
		$logId = $this->getLogId();

		if ($logId  > 0)
		{
			$result = $this->getLogRightsEntry();
		}

		return $result;
	}

	protected function getLogRightsEntry()
	{
		$result = array();

		if ($this->logId > 0)
		{
			$res = \CSocNetLogRights::getList(
				array(),
				array(
					'LOG_ID' => $this->logId
				)
			);

			while ($right = $res->fetch())
			{
				$result[] = $right['GROUP_CODE'];
			}
		}

		return $result;
	}

	final protected function setEntityId($entityId)
	{
		$this->entityId = $entityId;
	}

	final protected function getEntityId()
	{
		return $this->entityId;
	}

	final protected function setSourceFields(array $fields)
	{
		$this->sourceFields = $fields;
	}

	final protected function getSourceFields()
	{
		return $this->sourceFields;
	}

	final protected function setSourceDescription($description)
	{
		$this->sourceDescription = $description;
	}

	public function getSourceDescription()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$result = $this->sourceDescription;

		if ($this->cloneDiskObjects === true)
		{
			$this->getAttachedDiskObjects(true);
			$result = $this->processDescription($result);
		}

		return $result;
	}

	final protected  function setSourceTitle($title)
	{
		$this->sourceTitle = $title;
	}

	public  function getSourceTitle()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceTitle;
	}

	final protected function setSourceAttachedDiskObjects(array $diskAttachedObjects)
	{
		$this->sourceAttachedDiskObjects = $diskAttachedObjects;
	}

	final protected function setSourceDiskObjects(array $files)
	{
		$this->sourceDiskObjects = $files;
	}

	final public function setDiskObjectsCloned(array $values)
	{
		$this->diskObjectsCloned = $values;
	}

	final public function getDiskObjectsCloned()
	{
		return $this->diskObjectsCloned;
	}

	final public function getAttachedDiskObjectsCloned()
	{
		return $this->attachedDiskObjectsCloned;
	}

	public function getSourceAttachedDiskObjects()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceAttachedDiskObjects;
	}

	public function getSourceDiskObjects()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceDiskObjects;
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		return array();
	}

	protected static function cloneUfValues(array $values)
	{
		global $USER;

		$result = array();
		if (Loader::includeModule('disk'))
		{
			$result = \Bitrix\Disk\Driver::getInstance()->getUserFieldManager()->cloneUfValuesFromAttachedObject($values, $USER->getId());
		}

		return $result;
	}

	public function getDiskObjects($entityId, $clone = false)
	{
		$result = array();

		if ($clone)
		{
			$result = $this->getAttachedDiskObjects(true);

			if (
				empty($this->diskObjectsCloned)
				&& Loader::includeModule('disk')
			)
			{
				foreach($result as $clonedDiskObjectId)
				{
					if (
						in_array($clonedDiskObjectId, $this->attachedDiskObjectsCloned)
						&& ($attachedDiskObjectId = array_search($clonedDiskObjectId, $this->attachedDiskObjectsCloned))
					)
					{
						$attachedObject = \Bitrix\Disk\AttachedObject::loadById($attachedDiskObjectId);
						if ($attachedObject)
						{
							$this->diskObjectsCloned[\Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$attachedObject->getObjectId()] = $this->attachedDiskObjectsCloned[$attachedDiskObjectId];
						}
					}
				}
			}

			return $result;
		}
		else
		{
			$diskObjects = $this->getAttachedDiskObjects(false);

			if (
				!empty($diskObjects)
				&& Loader::includeModule('disk')
			)
			{
				foreach ($diskObjects as $attachedObjectId)
				{
					$attachedObject = \Bitrix\Disk\AttachedObject::loadById($attachedObjectId);
					if ($attachedObject)
					{
						$result[] = \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$attachedObject->getObjectId();
					}
				}
			}
		}

		return $result;
	}

	final private function processDescription($text)
	{
		$result = $text;

		$diskObjectsCloned = $this->getDiskObjectsCloned();
		$attachedDiskObjectsCloned = $this->getAttachedDiskObjectsCloned();

		if (
			!empty($diskObjectsCloned)
			&& is_array($diskObjectsCloned)
		)
		{
			$result = preg_replace_callback(
				"#\\[disk file id=(n\\d+)\\]#is".BX_UTF_PCRE_MODIFIER,
				array($this, "parseDiskObjectsCloned"),
				$result
			);
		}

		if (
			!empty($attachedDiskObjectsCloned)
			&& is_array($attachedDiskObjectsCloned)
		)
		{
			$result = preg_replace_callback(
				"#\\[disk file id=(\\d+)\\]#is".BX_UTF_PCRE_MODIFIER,
				array($this, "parseAttachedDiskObjectsCloned"),
				$result
			);
		}

		return $result;
	}

	final private function parseDiskObjectsCloned($matches)
	{
		$text = $matches[0];

		$diskObjectsCloned = $this->getDiskObjectsCloned();

		if (array_key_exists($matches[1], $diskObjectsCloned))
		{
			$text = str_replace($matches[1], $diskObjectsCloned[$matches[1]], $text);
		}

		return $text;
	}

	final private function parseAttachedDiskObjectsCloned($matches)
	{
		$text = $matches[0];

		$attachedDiskObjectsCloned = $this->getAttachedDiskObjectsCloned();

		if (array_key_exists($matches[1], $attachedDiskObjectsCloned))
		{
			$text = str_replace($matches[1], $attachedDiskObjectsCloned[$matches[1]], $text);
		}

		return $text;
	}

	public function getLiveFeedUrl()
	{
		return '';
	}
}