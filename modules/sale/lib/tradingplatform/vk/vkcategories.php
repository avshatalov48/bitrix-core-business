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
	private int $exportId;

	/**
	 * VkCategories constructor.
	 * @param $exportId - int, ID of export profile
	 */
	public function __construct(int $exportId)
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
	public function deleteAgent()
	{
//		not change cache - they will self dropped after ttl
//		dropped agent
		$dbRes = \CAgent::GetList(
			array(),
			array(
				'NAME' => self::createAgentName($this->exportId),
			)
		);

		if ($agent = $dbRes->Fetch())
			\CAgent::Delete($agent["ID"]);
	}


	/**
	 * Remove agents for ALL export IDs
	 */
	public function deleteAllAgents()
	{
		$vk = Vk::getInstance();
		$settings = $vk->getSettings();

		foreach ($settings as $id => $value)
		{
			$this->deleteAgent();
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
	 * @param $exportId
	 * @return string
	 * Create name for agent
	 */
	private static function createAgentName(int $exportId): string
	{
		return 'Bitrix\Sale\TradingPlatform\Vk\VkCategories::updateVkCategoriesAgent(' . $exportId . ');';
	}

	/**
	 * If cache exist - get values from it.
	 * Else - download categories via API
	 *
	 * @param bool $isTree
	 * @return array|bool
	 */
	public function getList($isTree = true)
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
		{
			$result = self::convertVkCategoriesToTree($result);
		}
		else
		{
			$result = self::convertVkCategoriesToList($result);
		}

		return $result;
	}


	/**
	 * Load vk-categories from VK and save them to cache.
	 *
	 * @param $exportId
	 * @return array of VkCategories or null if error
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
			return null;
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
					"ID" => $category["section"]["id"],
					"NAME" => $category["section"]["name"],
					"ITEMS" => array(),
				);
			}

//			put data in exist tree item
			$categoriesTree[$category["section"]["id"]]["ITEMS"][$category["id"]] = array(
				"ID" => $category["id"],
				"NAME" => $category["name"],
			);
		}

		return $categoriesTree;
	}


	/**
	 * Convert category list from VK to correct list
	 *
	 * @param $categoriesList
	 * @return array
	 */
	private static function convertVkCategoriesToList($categoriesList)
	{
		$categoriesListFormatted = array();
		foreach ($categoriesList as $category)
		{
			$categoriesListFormatted[$category["id"]]= array(
				"ID" => $category["id"],
				"NAME" => $category["name"],
			);
		}

		return $categoriesListFormatted;
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
		$vkCategory = $this->getList();

//		todo: why upper case dont work?
		$defaultItemText = $defaultItemText <> '' ? $defaultItemText : Loc::getMessage("SALE_CATALOG_CHANGE_VK_CATEGORY");
		$strSelect = '<option value="-1">[' . $defaultItemText . ']</option>';

		foreach ($vkCategory as $vkTreeItem)
		{
			$strSelect .= '<option disabled value="0">'.mb_strtoupper($vkTreeItem["NAME"]) . '</option>';

			foreach ($vkTreeItem["ITEMS"] as $sectionItem)
			{
				$selected = '';
				if ($catVkSelected && ($sectionItem["ID"] == $catVkSelected))
					$selected = " selected";

				$strSelect .= '<option' . $selected . ' value="' . $sectionItem["ID"] . '">- ' . $sectionItem["NAME"] . '</option>';
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
	public static function updateVkCategoriesAgent(int $exportId): string
	{
		if (self::updateDataToCache($exportId))
		{
			return self::createAgentName($exportId);
		}
		else return '';
	}
}