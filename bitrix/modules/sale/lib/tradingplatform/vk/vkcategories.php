<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Sale\TradingPlatform\Vk\Api\ApiHelper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

/**
 * Class VkCategories
 * Download categories from vk, save them in cache, get from cache
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class VkCategories
{
	const CACHE_DIR = '/sale/vkexport/';
	const CACHE_TTL = 86400;
	const CACHE_ID_PREFIX = "vkcategory_cache";
	private $exportId;

	/**
	 * VkCategories constructor.
	 * @param $exportId - int, ID of export profile
	 */
	public function __construct($exportId)
	{
		$this->exportId = $exportId;
	}
	
	
	/**
	 * Create agent for pereodical update vk-categories in values (main function)
	 *
	 * @return array|bool|false|mixed|null - ID of created or existing agent
	 */
	public function createAgent()
	{
//		CREATE agent if not exist
		if (!$agent = $this->getAgentId())
		{
			$ttl = self::CACHE_TTL;
			$timeToStart = ConvertTimeStamp(strtotime(date('Y-m-d H:i:s', time() + $ttl)), 'FULL');
			
			$resultAgentAdd = \CAgent::AddAgent(
				self::createAgentName($this->exportId),
				'sale',
				"N",
				$ttl,
				$timeToStart,
				"Y",
				$timeToStart
			);
			
			return $resultAgentAdd;
		}
		
		else
		{
			return $agent;
		}
	}
	
	
	/**
	 * @return array|bool|false|mixed|null
	 * Check if exist agent for update vk-categories.
	 * Return agent ID
	 */
	private function getAgentId()
	{
		$dbRes = \CAgent::GetList(
			array(),
			array(
				'NAME' => self::createAgentName($this->exportId),
			)
		);
		
		if ($agent = $dbRes->Fetch())
			return $agent;
		else
			return false;
	}
	
	
	/**
	 * @param $exportid
	 * Remove agent for current export ID
	 */
	public static function deleteAgent($exportid)
	{
//		not change cache - they will self dropped after ttl
//		dropped agent
		$dbRes = \CAgent::GetList(
			array(),
			array(
				'NAME' => self::createAgentName($exportid),
			)
		);
		
		if ($agent = $dbRes->Fetch())
			\CAgent::Delete($agent["ID"]);
	}
	
	
	/**
	 * Remove agents for ALL export IDs
	 */
	public static function deleteAllAgents()
	{
		$vk = Vk::getInstance();
		$settings = $vk->getSettings();
		
		foreach ($settings as $id => $value)
		{
			self::deleteAgent($id);
		}
	}
	
	
	/**
	 * @return string
	 * Create name for cache
	 */
	private static function createCacheId()
	{
//		we need only one cache for all exports => no needed export ID for cache ID
		return self::CACHE_ID_PREFIX;
	}
	
	/**
	 * @param $exportid
	 * @return string
	 * Create name for agent
	 */
	private static function createAgentName($exportid)
	{
		return 'Bitrix\Sale\TradingPlatform\Vk\VkCategories::updateVkCategoriesAgent("' . $exportid . '");';
	}
	
	/**
	 * If cache exist - get values from it.
	 * Else - download categories via API
	 *
	 * @param bool $isTree
	 * @return array|bool
	 */
	private function getData($isTree = true)
	{
		$cacheManager = Application::getInstance()->getManagedCache();
		$result = NULL;
		
		if ($cacheManager->read(self::CACHE_TTL, self::createCacheId()))
		{
			$result = $cacheManager->get(self::createCacheId());
		}
		else
		{
			$result = self::updateDataToCache($this->exportId);
		}
		
		if ($isTree)
			$result = self::convertVkCategoriesToTree($result);
		
		return $result;
	}
	
	
	/**
	 * Load vk-categories from VK and save them to cache.
	 *
	 * @param $exportId
	 * @return bool - array of VkCategories or false if error
	 */
	private static function updateDataToCache($exportId)
	{
		$vkCategories = self::getDataFromVk($exportId);
		
		if (is_array($vkCategories))
		{
			$cacheManager = Application::getInstance()->getManagedCache();
			$cacheManager->set(self::createCacheId(), $vkCategories);
			
			return $vkCategories;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	 * get vk categories from vk-api
	 *
	 * @param $exportId
	 * @return array
	 */
	private static function getDataFromVk($exportId)
	{
		$apiHelper = new ApiHelper($exportId);
		
		return $apiHelper->getVkCategories();
	}
	
	
	/**
	 * Convert category list to tree
	 *
	 * @param $categoriesList
	 * @return array
	 */
	private static function convertVkCategoriesToTree($categoriesList)
	{
		$categoriesTree = array();
		foreach ($categoriesList as $category)
		{
			if (!isset($categoriesTree[$category["section"]["id"]]))
			{
//				create NEW tree-item
				$categoriesTree[$category["section"]["id"]] = array(
					"id" => $category["section"]["id"],
					"name" => $category["section"]["name"],
					"items" => array(),
				);
			}

//			put data in exist tree item
			$categoriesTree[$category["section"]["id"]]["items"][$category["id"]] = array(
				"id" => $category["id"],
				"name" => $category["name"],
			);
		}
		
		return $categoriesTree;
	}
	
	
	/**
	 * Formmatted selector to HTML. Not create <select> tag. only inner options.
	 *
	 * @param null $catVkSelected - ID of item, which it is necessary to ckecked
	 * @param string $defaultItemText - if set - rename first element. Default - 'Check category'
	 * @return string
	 */
	public function getVkCategorySelector($catVkSelected = NULL, $defaultItemText = '')
	{
		$vkCategory = $this->getData();

//		todo: why upper case dont work?
		$defaultItemText = strlen($defaultItemText) > 0 ? $defaultItemText : Loc::getMessage("SALE_CATALOG_CHANGE_VK_CATEGORY");
		$strSelect = '<option value="-1">[' . $defaultItemText . ']</option>';
		
		foreach ($vkCategory as $vkTreeItem)
		{
			$strSelect .= '<option disabled value="0">' . strtoupper($vkTreeItem["name"]) . '</option>';
			
			foreach ($vkTreeItem["items"] as $sectionItem)
			{
				$selected = '';
				if ($catVkSelected && ($sectionItem["id"] == $catVkSelected))
					$selected = " selected";
				
				$strSelect .= '<option' . $selected . ' value="' . $sectionItem["id"] . '">- ' . $sectionItem["name"] . '</option>';
			}
		}
		
		return $strSelect;
	}
	
	
	/**
	 * Agent wrap-method for update cache
	 *
	 * @param $exportId
	 * @return string
	 */
	public function updateVkCategoriesAgent($exportId)
	{
		if (self::updateDataToCache($exportId))
			return self::createAgentName($exportId);
		else return '';
	}
}