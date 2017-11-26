<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Blog;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Data\Cache;

Loc::loadMessages(__FILE__);

class BlogUser
{
	const CACHE_ID = 'BLOG_USERS';
	private $blogId = NULL;

//	this values was be hardcoded in components. If we want customization - need add settings and remake
	private static $avatarSizes = array(
		'COMMENT' => array('WIDTH' => 30, 'HEIGHT' => 30),
		'POST' => array('WIDTH' => 100, 'HEIGHT' => 100),
	);
	
	private $cacheTime = 0;
	
	
	/**
	 * Blog users can be saved in cache or get from DB. To cached - set cacheTime
	 *
	 * BlogUser constructor.
	 * @param int $cacheTime - integer - value of cache TTL
	 */
	public function __construct($cacheTime = 0)
	{
		if ($cacheTime > 0)
			$this->cacheTime = intval($cacheTime);
	}
	
	/**
	 * Blog ID using to separate cache for different blogs
	 * @param $id
	 */
	public function setBlogId($id)
	{
		$this->blogId = intval($id);
	}
	
	public function setCacheTime($cacheTime)
	{
		if ($cacheTime > 0)
			$this->cacheTime = $cacheTime;
	}
	
	
	/**
	 * Get users from cache (if set cache time) or from DB.
	 *
	 * @param array $ids - array of users ids
	 * @return array|bool
	 */
	public function getUsers($ids = array())
	{
		if(empty($ids))
			return array();
		
		if ($this->cacheTime > 0)
		{
			$result = $this->getUsersFromCache($ids);
		}
		else
		{
			$result = $this->getUsersFromDB($ids);
		}
		
		return $result;
	}
	
	
	/**
	 * Get blog users data from cache. If them not exist on cache - get new data from DB and write on cache.
	 * False if error.
	 *
	 * @return array|bool
	 */
	private function getUsersFromCache($ids)
	{
		$cache = Cache::createInstance();
		if ($cache->initCache($this->cacheTime, self::createCacheId($ids), self::createCacheDir($this->blogId)))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$result = self::getUsersFromDB($ids);
			$cache->endDataCache($result);
		}
		else
		{
			$result = false;
		}
		
		return $result;
	}
	
	
	/**
	 * Delete all data from blog users cache
	 *
	 * @return mixed
	 */
	public static function cleanCache($blogId = NULL)
	{
		$cache = Cache::createInstance();
		
		return $cache->cleanDir(self::CACHE_ID, self::createCacheDir($blogId));
	}
	
	
	private static function createCacheDir($blogId = NULL)
	{
		$dir = '/' . SITE_ID;
		$dir .= $blogId ? '/BLOG_ID_' . $blogId : '/BLOGS_ALL';
		
		return $dir;
	}
	
	/**
	 * Get only unique IDs, sorting them and glue in string. It is will unique string for this chunk of users
	 *
	 * @param $ids
	 * @return string
	 */
	private static function createCacheId($ids)
	{
		$ids = array_unique($ids);
		asort($ids);
		return self::CACHE_ID . '_' . implode('_', $ids);
	}
	
	
	/**
	 * Catch data from CUser, CBlogUser and formatted them to array to save in cache
	 */
	private function getUsersFromDB($ids = array())
	{
		$result = array();

//		BLOG users
		$filter = array();
		if (!empty($ids))
			$filter["=USER_ID"] = $ids;
		$resBlogUsers = Internals\BlogUserTable::getList(array(
			'select' => array('ID', 'USER_ID', 'ALIAS', 'AVATAR', 'USER.PERSONAL_PHOTO', 'USER.LOGIN', 'USER.NAME', 'USER.LAST_NAME'),
			'filter' => $filter,
		));

//		find Users then not exists as BlogUser
		if (is_array($ids) && !empty($ids))
			$notExistingUsersIds = array_combine($ids, $ids);    // set keys = value in new array
		
		while ($row = $resBlogUsers->fetch())
		{
			unset($notExistingUsersIds[$row["USER_ID"]]);
			
			$row["BLOG_USER_ID"] = $row["ID"];    // rename for understandability
			$row["BlogUser"] = array(
				"ALIAS" => $row["ALIAS"],
				"~ALIAS" => htmlspecialcharsex($row["ALIAS"]),
			);

//			Avatars for post and for comments
			$row["BlogUser"]["AVATAR_file"] = intval($row["AVATAR"]) > 0 ?
				\CFile::GetFileArray($row["AVATAR"]) :
				\CFile::GetFileArray($row["BLOG_INTERNALS_BLOG_USER_USER_PERSONAL_PHOTO"]);
			if ($row["BlogUser"]["AVATAR_file"] !== false)
			{
				foreach (self::$avatarSizes as $key => $avatarSize)
				{
					$row["BlogUser"]["Avatar_resized"][$avatarSize['WIDTH'] . '_' . $avatarSize['HEIGHT']] = \CFile::ResizeImageGet(
						$row["BlogUser"]["AVATAR_file"],
						array("width" => $avatarSize['WIDTH'], "height" => $avatarSize['HEIGHT']),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$row["BlogUser"]["AVATAR_img"][$avatarSize['WIDTH'] . '_' . $avatarSize['HEIGHT']] = \CFile::ShowImage(
						$row["BlogUser"]["Avatar_resized"][$avatarSize['WIDTH'] . '_' . $avatarSize['HEIGHT']]["src"],
						$avatarSize['WIDTH'],
						$avatarSize['HEIGHT'],
						"border=0 align='right'"
					);
				}
			}

//			create correct name from alias, login and names
			$row["AUTHOR_NAME"] = self::GetUserName(
				$row["ALIAS"],
				$row["BLOG_INTERNALS_BLOG_USER_USER_NAME"],
				$row["BLOG_INTERNALS_BLOG_USER_USER_LAST_NAME"],
				$row["BLOG_INTERNALS_BLOG_USER_USER_LOGIN"]
			);
			$row["~AUTHOR_NAME"] = htmlspecialcharsex($row["AUTHOR_NAME"]);

//			array for User data
			$row["arUser"] = array(
				"ID" => $row["USER_ID"],
				"NAME" => $row["BLOG_INTERNALS_BLOG_USER_USER_NAME"],
				"LAST_NAME" => $row["BLOG_INTERNALS_BLOG_USER_USER_LAST_NAME"],
				"LOGIN" => $row["BLOG_INTERNALS_BLOG_USER_USER_LOGIN"],
			);
			$row["arUser"] = \CBlogTools::htmlspecialcharsExArray($row["arUser"]);

//			we need work with main user ID - is may be not equal blog user ID
			$result[$row["arUser"]['ID']] = $row;
		}

//		create new empty BlogUsers for not existing Users
		if (!empty($notExistingUsersIds))
			$result = $result + $this->addNotExistingUsers($notExistingUsersIds);
		
		return $result;
	}
	
	
	private function addNotExistingUsers($ids = array())
	{
//		get Users data
		$rsUsers = \CUser::GetList(
			$by = 'id',
			$order = 'asc',
			array('ID' => implode('|', $ids)),
			array('FIELDS' => array('ID', 'DATE_REGISTER'/*, 'NAME', 'LAST_NAME', 'LOGIN'*/))
		);
		
		while ($user = $rsUsers->Fetch())
		{
//			todo: use new BlogUser class, when finish them
			\CBlogUser::Add(
				array(
					'USER_ID' => $user['ID'],
					'DATE_REG' => $user['DATE_REGISTER'],
				)
			);
		}

//		get created BlogUsers from DB
		return $this->getUsersFromDB($ids);
	}
	
	
	/**
	 * Return users ids of post author and comments authors (for this post)
	 *
	 * @param $postId
	 */
	public static function getCommentAuthorsIdsByPostId($postId)
	{
		if (!$postId)
			throw new ArgumentNullException('post ID');
		$postId = intval($postId);
		$result = array();
		
		$resComment = \CBlogComment::GetList(array(), array("POST_ID" => $postId), false, false, array("AUTHOR_ID"));
		while ($comment = $resComment->Fetch())
		{
			if ($comment["AUTHOR_ID"])
				$result[$comment["AUTHOR_ID"]] = $comment["AUTHOR_ID"];
		}
		
		return $result;
	}
	
	/**
	 * Return users ids of blog posts
	 * @param $blogId
	 */
	public static function getPostAuthorsIdsByBlogId($blogId)
	{
		if (!$blogId)
			throw new ArgumentNullException('blog ID');
		$blogId = intval($blogId);
		$result = array();
		
		$resPost = \CBlogPost::GetList(array(), array("BLOG_ID" => $blogId), false, false, array("AUTHOR_ID"));
		while ($post = $resPost->Fetch())
			if ($post["AUTHOR_ID"])
				$result[$post["AUTHOR_ID"]] = $post["AUTHOR_ID"];
		
		return $result;
	}
	
	
	/**
	 * Creat correctly blog user name from name, alias and login
	 *
	 * @param $alias
	 * @param $name
	 * @param $lastName
	 * @param $login
	 * @param string $secondName
	 * @return string
	 *
	 */
	public static function GetUserName($alias, $name, $lastName, $login, $secondName = "")
	{
		$result = "";
		
		$canUseAlias = \COption::GetOptionString("blog", "allow_alias", "Y");
		if ($canUseAlias == "Y")
			$result = $alias;
		
		if (strlen($result) <= 0)
		{
			$result = \CUser::FormatName(
				\CSite::GetNameFormat(false),
				array("NAME" => $name,
					"LAST_NAME" => $lastName,
					"SECOND_NAME" => $secondName,
					"LOGIN" => $login),
				true,
				false
			);
		}
		
		return $result;
	}
	
	public static function GetUserNameEx($user, $blogUser, $params)
	{
		$result = "";
		if (!$params["bSoNet"])
		{
			$canUseAlias = \COption::GetOptionString("blog", "allow_alias", "Y");
			if ($canUseAlias == "Y")
				$result = $blogUser["ALIAS"];
		}
		
		if (strlen($result) <= 0)
		{
			$params["NAME_TEMPLATE"] = $params["NAME_TEMPLATE"] ? $params["NAME_TEMPLATE"] : \CSite::GetNameFormat();
			$params["NAME_TEMPLATE"] = str_replace(
				array("#NOBR#", "#/NOBR#"),
				array("", ""),
				$params["NAME_TEMPLATE"]
			);
			$isUseLogin = $params["SHOW_LOGIN"] != "N" ? true : false;
			
			$result = \CUser::FormatName(
				$params["NAME_TEMPLATE"],
				$user,
				$isUseLogin,
				false
			);
		}
		
		return $result;
	}
}
