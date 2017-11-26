<?php
namespace Bitrix\Im;

class User
{
	private static $instance = Array();
	private $userId = 0;
	private $userData = null;
	
	const PHONE_ANY = 'PHONE_ANY';
	const PHONE_WORK = 'WORK_PHONE';
	const PHONE_PERSONAL = 'PERSONAL_PHONE';
	const PHONE_MOBILE = 'PERSONAL_MOBILE';
	const PHONE_INNER = 'UF_PHONE_INNER';

	function __construct($userId = null)
	{
		global $USER;

		$this->userId = (int)$userId;
		if ($this->userId <= 0 && is_object($USER) && $USER->GetID() > 0)
		{
			$this->userId = (int)$USER->GetID();
		}
	}

	/**
	 * @param null $userId
	 * @return User
	 */
	public static function getInstance($userId = null)
	{
		global $USER;

		$userId = (int)$userId;
		if ($userId <= 0 && is_object($USER) && $USER->GetID() > 0)
		{
			$userId = (int)$USER->GetID();
		}

		if (!isset(self::$instance[$userId]))
		{
			self::$instance[$userId] = new self($userId);
		}

		return self::$instance[$userId];
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getFullName($safe = true)
	{
		$fields = $this->getFields();
		if (!$fields)
			return '';
		
		return $safe? $fields['name']: htmlspecialcharsback($fields['name']);
	}

	/**
	 * @return string
	 */
	public function getName($safe = true)
	{
		$fields = $this->getFields();
		if (!$fields)
			return '';
		
		return $safe? $fields['firstName']: htmlspecialcharsback($fields['firstName']);
	}

	/**
	 * @return string
	 */
	public function getLastName($safe = true)
	{
		$fields = $this->getFields();
		if (!$fields)
			return '';
		
		return $safe? $fields['lastName']: htmlspecialcharsback($fields['lastName']);
	}

	/**
	 * @return string
	 */
	public function getAvatar()
	{
		$fields = $this->getFields();

		return $fields? $fields['avatar']: '';
	}

	/**
	 * @return string
	 */
	public function getAvatarId()
	{
		$fields = $this->getFields();

		return $fields? $fields['avatarId']: 0;
	}

	/**
	 * @return string
	 */
	public function getWorkPosition()
	{
		$fields = $this->getFields();

		return $fields? $fields['workPosition']: '';
	}

	/**
	 * @return string
	 */
	public function getGender()
	{
		$fields = $this->getFields();

		return $fields? $fields['gender']: '';
	}

	/**
	 * @return string
	 */
	public function getWebsite()
	{
		$fields = $this->getFields();

		return $fields? $fields['website']: '';
	}
	
	/**
	 * @return string
	 */
	public function getEmail()
	{
		$fields = $this->getFields();

		return $fields? $fields['email']: '';
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public function getPhone($type = self::PHONE_ANY)
	{
		$fields = $this->getPhones();

		$result = '';
		if ($type == self::PHONE_ANY)
		{
			if (isset($fields[self::PHONE_MOBILE]))
			{
				$result = $fields[self::PHONE_MOBILE];
			}
			else if (isset($fields[self::PHONE_PERSONAL]))
			{
				$result = $fields[self::PHONE_PERSONAL];
			}
			else if (isset($fields[self::PHONE_WORK]))
			{
				$result = $fields[self::PHONE_WORK];
			}
		}
		else if (isset($fields[$type]))
		{
			$result = $fields[$type];
		}
		
		return $result;
	}
	
	/**
	 * @return string
	 */
	public function getColor()
	{
		$fields = $this->getFields();

		return $fields? $fields['color']: '';
	}

	/**
	 * @return string
	 */
	public function getTzOffset()
	{
		$fields = $this->getFields();

		return $fields? $fields['tzOffset']: '';
	}

	/**
	 * @return bool
	 */
	public function isOnline()
	{
		$fields = $this->getFields();

		return $fields? $fields['status'] != 'offline': false;
	}
	/**
	 * @return bool
	 */
	public function isExtranet()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['extranet']: null;
	}
	
	/**
	 * @return bool
	 */
	public function isActive()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['active']: null;
	}

	/**
	 * @return bool
	 */
	public function isNetwork()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['network']: null;
	}

	/**
	 * @return bool
	 */
	public function isBot()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['bot']: null;
	}

	/**
	 * @return bool
	 */
	public function isConnector()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['connector']: null;
	}

	/**
	 * @return bool
	 */
	public function isExists()
	{
		$fields = $this->getFields();

		return $fields? true: false;
	}

	/**
	 * @return array|null
	 */
	public function getFields()
	{
		$params = $this->getParams();

		return $params? $params['user']: null;
	}
	
	/**
	 * @return array|null
	 */
	public function getPhones()
	{
		$params = $this->getParams();

		return $params? $params['phones']: null;
	}

	/**
	 * @return array|null
	 */
	private function getParams()
	{
		if (is_null($this->userData))
		{
			$userData = \CIMContactList::GetUserData(Array(
				'ID' => self::getId(),
				'PHONES' => 'Y',
				'EXTRA_FIELDS' => 'Y'
			));
			if (isset($userData['users'][self::getId()]))
			{
				$this->userData['user'] = $userData['users'][self::getId()];
				$this->userData['phones'] = $userData['phones'][self::getId()];
			}
		}
		return $this->userData;
	}

	public static function uploadAvatar($avatarUrl = '')
	{
		if (strlen($avatarUrl) <= 4)
			return '';

		if (!in_array(\GetFileExtension($avatarUrl), Array('png', 'jpg', 'gif')))
			return '';

		$orm = \Bitrix\Im\Model\ExternalAvatarTable::getList(Array(
			'filter' => Array('=LINK_MD5' => md5($avatarUrl))
		));
		if ($cache = $orm->fetch())
		{
			return $cache['AVATAR_ID'];
		}

		$recordFile = \CFile::MakeFileArray($avatarUrl);
		if (!\CFile::IsImage($recordFile['name'], $recordFile['type']))
			return '';

		if (is_array($recordFile) && $recordFile['size'] && $recordFile['size'] > 0 && $recordFile['size'] < 1000000)
		{
			$recordFile = array_merge($recordFile, array('MODULE_ID' => 'imbot'));
		}
		else
		{
			$recordFile = 0;
		}

		if ($recordFile)
		{
			$recordFile = \CFile::SaveFile($recordFile, 'botcontroller');
		}
		
		if ($recordFile > 0)
		{
			\Bitrix\Im\Model\ExternalAvatarTable::add(Array(
				'LINK_MD5' => md5($avatarUrl),
				'AVATAR_ID' => intval($recordFile)
			));
		}

		return $recordFile;
	}

	/**
	 * @return bool
	 */
	public static function clearStaticCache()
	{
		self::$instance = Array();
		return true;
	}
}