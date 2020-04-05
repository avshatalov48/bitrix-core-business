<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Iblock\SectionElementTable;
use Bitrix\Sale\TradingPlatform;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class SectionsList
 * Work with iblock sections - get export settings, get list of saving sections, formatted settings to save etc
 *
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class SectionsList
{
	private static $sections;
	private $mappedSections;
	private $mappedAlbums;
	private $exportId;
	private $iblocksIds = array();
	private $currSectionSettings = array();
	
	const CACHE_DIR = '/sale/vkexport/';
	const CACHE_TTL = 86400;
	const CACHE_ID_PREFIX = "vk_sectionslist_cache";
	const CACHE_ID_SECTIONS = "iblock_sections";
	const CACHE_ID_MAPPED_SECTIONS = "mapped_sections";
	const CACHE_ID_MAPPED_SECTIONS_LIST = "mapped_sections_list";
	
	const VK_ICON = '<img src="/bitrix/images/sale/vk/vk_icon.png" style="height:16px; width:16px; margin-right: 1em;" />';
	const VK_ICON_EMPTY = '<span style="width:16px; margin-right: 1em; display:inline-block;"></span>';
	
	/**
	 * SectionsList constructor.
	 * @param $exportId
	 */
	public function __construct($exportId)
	{
		$this->exportId = intval($exportId);

//		save mapped sections in cache
		$cacheManager = Application::getInstance()->getManagedCache();
		if ($cacheManager->read(self::CACHE_TTL, $this->createCacheIdMappedSections()))
		{
			$mappedSections = $cacheManager->get($this->createCacheIdMappedSections());
		}
		else
		{
			$mappedSections = Map::getMappedSections($exportId);
			$cacheManager->set($this->createCacheIdMappedSections(), $mappedSections);
		}
		$this->mappedSections = $mappedSections;
		
		if (!Loader::includeModule('iblock'))
			throw new SystemException("Can't include module \"IBlock\"! " . __METHOD__);
	}
	
	/**
	 * @return string
	 * Create name for cache
	 */
	private function createCacheId($cacheName = NULL)
	{
		$cacheId = self::CACHE_ID_PREFIX . '__' . $this->exportId;
		
		return $cacheName ? $cacheId . '__' . $cacheName : self::CACHE_ID_PREFIX;
	}
	
	/**
	 * Create cache ID for iblock sections
	 * @return string
	 */
	public function createCacheIdSections()
	{
		return $this->createCacheId(self::CACHE_ID_SECTIONS);
	}
	
	/**
	 * Create cache ID for mapped sections list
	 * @return string
	 */
	public function createCacheIdMappedSections()
	{
		return $this->createCacheId(self::CACHE_ID_MAPPED_SECTIONS);
	}
	
	/**
	 * Create cache ID for mapped sections list
	 * @return string
	 */
	public function createCacheIdMappedSectionsList()
	{
		return $this->createCacheId(self::CACHE_ID_MAPPED_SECTIONS_LIST);
	}
	
	
	/**
	 * Clean all caches for class. Run in moment of changed sections settings
	 */
	public function clearCaches()
	{
		$cacheManager = Application::getInstance()->getManagedCache();
		$cacheManager->clean($this->createCacheIdSections());
		$cacheManager->clean($this->createCacheIdMappedSections());
		$cacheManager->clean($this->createCacheIdMappedSectionsList());
	}
	
	
	/**
	 * Return list of iblock sections. At the first run saving list in cache.
	 *
	 * @param bool $tree - is true - list will be converted to tree
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSections($tree = false)
	{
//		We can save data in cache.Cache must be reload only if sections settings will be changed.
		$cacheManager = Application::getInstance()->getManagedCache();
		$sections = array();
		
		if ($cacheManager->read(self::CACHE_TTL, $this->createCacheIdSections()))
		{
			$sections = $cacheManager->get($this->createCacheIdSections());
		}
		else
		{
//			IBLOCK IDS for getting catalog sections
			$iblocks = $this->getMappedIblocks();
			$iblocksIds = array();
			foreach ($iblocks as $id => $value)
				$iblocksIds[] = $id;
			
			$filter = array("IBLOCK_ID" => $iblocksIds, "ELEMENT_SUBSECTIONS" => "N");

//			calculate all products or just active
			$vk = Vk::getInstance();
			$vkSettings = $vk->getSettings($this->exportId);
			if (isset($vkSettings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"]) && !$vkSettings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"])
				$filter["CNT_ACTIVE"] = "N";
			else
				$filter["CNT_ACTIVE"] = "Y";

//			get ALL sections for ALL catalog iblocks
			$resSections = \CIBlockSection::GetList(
				array("LEFT_MARGIN" => "asc"),
				$filter,
				true,
				array("IBLOCK_ID", "IBLOCK_SECTION_ID", "ID", "DEPTH_LEVEL", "NAME", "LEFT_MARGIN", "RIGHT_MARGIN", "ELEMENT_CNT")
			);
			
			while ($currSection = $resSections->Fetch())
			{
//				output format - list or tree separated on iblocks
				$sections[$currSection["IBLOCK_ID"]][$currSection["ID"]] = $currSection;
			}
			
			$cacheManager->set($this->createCacheIdSections(), $sections);
		}

//		if not a tree - formatted to list
		if (!$tree)
		{
			$sectionsList = array();
			foreach ($sections as $iblock)
				$sectionsList += $iblock;
			
			return $sectionsList;
		}
		
		return $sections;
	}
	
	
	/**
	 * Collect IBLOCK for all mapped sections. Need at creation FEEDs for product export
	 *
	 * @return array of iblocks IDs
	 */
	public function getMappedIblocks()
	{
//		todo: maybe need cache to?
//		get iblocks if they not set yet
		if (empty($this->iblocksIds))
		{
			foreach ($this->mappedSections as $mappedSection)
			{
				$id = $mappedSection["PARAMS"]["IBLOCK"];
				$this->iblocksIds[$id] = $id;
			}
		}
		
		return $this->iblocksIds;
	}
	
	
	/**
	 * Find and converted all section to product export.
	 * Check inhering of parameters and define which sections will be exported
	 *
	 * @return array - array of sections ID
	 */
	public function getSectionsToProductExport()
	{
		$sectionsToExport = array();
		foreach ($this->mappedSections as $mappedSection)
		{
			$params = $mappedSection["PARAMS"];
			$parentParams = $params["PARENT_SETTINGS"];

//			put current section to export, if them enabled and not inherit, or if parent section enabled
			if (
				(!$params["INHERIT"] && $params['ENABLE']) ||
				($params["INHERIT"] && $parentParams && $parentParams["ENABLE"])
			)
				$sectionsToExport[$params["IBLOCK"]][$mappedSection["BX_ID"]] = $mappedSection["BX_ID"];
		}
		
		return $sectionsToExport;
	}
	
	
	private function getListMappedSections()
	{
		$sectionsToExport = array();
		$sectionsAliases = array();
		$result = array();
		
		foreach ($this->mappedSections as $mappedSection)
		{
			$params = $mappedSection["PARAMS"];
			$parentParams = $params["PARENT_SETTINGS"];

//			not inherit - check pur params
			if (!$params["INHERIT"] && $params["ENABLE"])
			{
				$result[$mappedSection["BX_ID"]] = array(
					"TO_ALBUM" => $params["TO_ALBUM"],
					"BX_ID" => $mappedSection["BX_ID"],
					"VK_CATEGORY" => $params["VK_CATEGORY"],
					"IBLOCK" => $params["IBLOCK"],
				);
//				alias get from settings. If not set - do nothing (will be used default name)
				if ($params["TO_ALBUM_ALIAS"])
					$result[$mappedSection["BX_ID"]]["TO_ALBUM_ALIAS"] = $params["TO_ALBUM_ALIAS"];
			}

//			inherit - get params from parent. If not include child - import in self album
			elseif ($params["INHERIT"] && $parentParams && $parentParams["ENABLE"] && !$parentParams["INCLUDE_CHILDS"])
			{
				$result[$mappedSection["BX_ID"]] = array(
					"TO_ALBUM" => $mappedSection["BX_ID"],
					"BX_ID" => $mappedSection["BX_ID"],
					"VK_CATEGORY" => $parentParams["VK_CATEGORY"],
					"IBLOCK" => $params["IBLOCK"],
				);
//				alias get from settings. If not set - do nothing (will be used default name)
//				not get aliases from parent settings - it should take place in category itself
//				if ($parentParams["TO_ALBUM_ALIAS"])
//					$sectionsAliases[$parentParams["TO_ALBUM"]] = $parentParams["TO_ALBUM_ALIAS"];
			}

//			if INHERIT and parent section included childs - put section to parent to_album
			elseif ($params["INHERIT"] && $parentParams && $parentParams["ENABLE"] && $parentParams["INCLUDE_CHILDS"])
			{
				$result[$mappedSection["BX_ID"]] = array(
					"TO_ALBUM" => $parentParams["TO_ALBUM"],
					"BX_ID" => $mappedSection["BX_ID"],
					"VK_CATEGORY" => $params["VK_CATEGORY"],
					"IBLOCK" => $params["IBLOCK"],
				);

//				alias get from settings. If not set - do nothing (will be used default name)
				if ($parentParams["TO_ALBUM_ALIAS"])
					$result[$mappedSection["BX_ID"]]["TO_ALBUM_ALIAS"] = $parentParams["TO_ALBUM_ALIAS"];
			}
		}
		
		return $result;
	}
	
	
	/**
	 * Find and converted all section to albums export.
	 * Check inhering of parameters and define which sections will be exported
	 *
	 * @return array - in field "SECTIONS" - array of sections ID. In field "ALIASES" - aliases for sections.
	 */
	public function getSectionsToAlbumsExport()
	{
		$sectionsToExport = array();
		$sectionsAliases = array();
		
		foreach ($this->mappedSections as $mappedSection)
		{
			$params = $mappedSection["PARAMS"];
			$parentParams = $params["PARENT_SETTINGS"];

//			not inherit - check pur params
			if (!$params["INHERIT"] && $params["ENABLE"])
			{
				$sectionsToExport[$params["TO_ALBUM"]] = $params["TO_ALBUM"];
//				alias get from settings. If not set - do nothing (will be used default name)
				if ($params["TO_ALBUM_ALIAS"])
					$sectionsAliases[$params["TO_ALBUM"]] = $params["TO_ALBUM_ALIAS"];
			}

//			inherit - get params from parent. If not include child - import in self album
			elseif ($params["INHERIT"] && $parentParams && $parentParams["ENABLE"] && !$parentParams["INCLUDE_CHILDS"])
			{
				$sectionsToExport[$mappedSection["BX_ID"]] = $mappedSection["BX_ID"];
//				alias get from settings. If not set - do nothing (will be used default name)
				if ($parentParams["TO_ALBUM_ALIAS"])
					$sectionsAliases[$parentParams["TO_ALBUM"]] = $parentParams["TO_ALBUM_ALIAS"];
			}

//			if INHERIT and parent section included childs - put section to parent to_album
			elseif ($params["INHERIT"] && $parentParams && $parentParams["ENABLE"] && $parentParams["INCLUDE_CHILDS"])
			{
				$sectionsToExport[$parentParams["TO_ALBUM"]] = $parentParams["TO_ALBUM"];
//					alias get from settings. If not set - do nothing (will be used default name)
				if ($parentParams["TO_ALBUM_ALIAS"])
					$sectionsAliases[$parentParams["TO_ALBUM"]] = $parentParams["TO_ALBUM_ALIAS"];
			}
		}
		
		return array("SECTIONS" => $sectionsToExport, "ALIASES" => $sectionsAliases);
	}
	
	
	public function getMultiSectionsToProduct($pdoructsIds)
	{
		$sections = SectionElementTable::getList(array(
			"filter" => array(
				"IBLOCK_ELEMENT_ID" => $pdoructsIds,
				"ADDITIONAL_PROPERTY_ID" => NULL,
			),
		));
		
		$result = array();
		while ($section = $sections->fetch())
		{
			$result[$section["IBLOCK_ELEMENT_ID"]][] = $section["IBLOCK_SECTION_ID"];
		}
		
		return $result;
	}
	
	
	/**
	 * Prepare mapped sections to print export map
	 */
	public function getSectionMapToPrint()
	{
		$mappedSectionGroupped = $this->getSectionsToMap();
		$result = '<table class="internal">';
		$result .= '
				<tr class="heading">
					<td class="internal-left">' . Loc::getMessage("VK_EXPORT_MAP__ALBUM_NAME") . '</td>
					<td style="text-align: right !important;">' . Loc::getMessage("VK_EXPORT_MAP__ELEMENT_CNT") . '</td>
					<td class="internal-right">' . Loc::getMessage("VK_EXPORT_MAP__SECTIONS_NAME") . '</td>
				</tr>
			';
		
		foreach ($mappedSectionGroupped as $currAlbum)
		{
			$result .= '<tr>';
			$result .= '<td>';
			$result .= isset($currAlbum["ALBUM_VK_URL"]) ?
				'<a href="' . $currAlbum["ALBUM_VK_URL"] . '">' . self::VK_ICON . '</a>' . $currAlbum["TO_ALBUM_NAME"] :
				self::VK_ICON_EMPTY . $currAlbum["TO_ALBUM_NAME"];
			$result .= '</td>';
			$result .= '<td class="bx-digit-cell" >' . $currAlbum["ELEMENT_CNT"] . '</td>';

//			collect SECTIONS
			if (count($currAlbum["ITEMS"]) > 0)
			{
				$items = '';
				foreach ($currAlbum["ITEMS"] as $currSection)
				{
					$items .= '<div style = "margin-bottom:4px;">' . $currSection["NAME"];
					$items .= ' <i>(' . Loc::getMessage("VK_EXPORT_MAP__ELEMENT_CNT_2") . ': ' . $currSection["ELEMENT_CNT"] . ')</i>';
					$items .= isset($currSection["SECTION_URL"]) ?
						' <a href="' . $currSection["SECTION_URL"] . '">' . Loc::getMessage("VK_EXPORT_MAP__SECTION_SETTINGS") . '</a> ' :
						'';
					$items .= '</div>';
				}
				$result .= '<td>' . $items . '</td>';
			}
			else
			{
				$result .= '<td>' . Loc::getMessage("VK_EXPORT_MAP__NO_SECTIONS") . '</td>';
			}
			$result .= '</tr>';
		}
		$result .= '</table>';
		
		return $result;
	}
	
	
	/**
	 * Get extend sections array - group sections by VK-album, add URL to VK album, add URL to section edit page,
	 * add count of element
	 *
	 * @return array
	 */
	public function getSectionsToMap()
	{
		if (empty($this->mappedAlbums))
			$this->mappedAlbums = Map::getMappedAlbums($this->exportId);
		if (empty($this->mappedSections))
			$this->mappedSections = Map::getMappedSections($this->exportId);
		
		$sectionsUnformatted = $this->getListMappedSections();
		$sectionsFormatted = array();

//		Empty settings = empty result. It's law
		if (empty($this->mappedSections))
			return array();
		
		$sections = $this->getSections(true);
		$vkCategories = new VkCategories($this->exportId);
		$vkCategoriesList = $vkCategories->getList(false);
		
		foreach ($sectionsUnformatted as $sectionUnformatted)
		{
			$currSection = $sections[$sectionUnformatted["IBLOCK"]][$sectionUnformatted["BX_ID"]];

//			processing only not empty sections
//			if ($currSection["ELEMENT_CNT"] <= 0)
//				continue;

//			if first item to this vk-album
			if (!array_key_exists($sectionUnformatted["TO_ALBUM"], $sectionsFormatted))
			{
				$sectionsFormatted[$sectionUnformatted["TO_ALBUM"]] = array(
					"TO_ALBUM" => $sectionUnformatted["TO_ALBUM"],
					"ITEMS" => array(),
					"ELEMENT_CNT" => 0,
				);
				
				if (array_key_exists($sectionUnformatted["TO_ALBUM"], $this->mappedAlbums))
				{
					$albumVkId = $this->mappedAlbums[$sectionUnformatted["TO_ALBUM"]]["ALBUM_VK_ID"];
					$sectionsFormatted[$sectionUnformatted["TO_ALBUM"]]["ALBUM_VK_ID"] = $albumVkId;
					$sectionsFormatted[$sectionUnformatted["TO_ALBUM"]]["ALBUM_VK_URL"] = $this->createVkAlbumLink($albumVkId);
				}
			}
			
//			create toAlbum name only from section, then root for this album. Take alias if exist, or just name
			if($sectionUnformatted["TO_ALBUM"] == $sectionUnformatted["BX_ID"])
			{
				$sectionsFormatted[$sectionUnformatted["TO_ALBUM"]]["TO_ALBUM_NAME"] = $sectionUnformatted["TO_ALBUM_ALIAS"] ?
					$sectionUnformatted["TO_ALBUM_ALIAS"] :
					trim($currSection["NAME"]);
			}

//			format current item
			$vkCategoryName = $vkCategoriesList[$sectionUnformatted["VK_CATEGORY"]]['NAME'];
			$sectionName = $currSection["NAME"];
			$sectionElementCnt = $currSection["ELEMENT_CNT"];
			
			$item = array(
				"BX_ID" => $sectionUnformatted["BX_ID"],
				"NAME" => $sectionName,
				"VK_CATEGORY_ID" => $sectionUnformatted["VK_CATEGORY"],
				"VK_CATEGORY_NAME" => $vkCategoryName,
				"ELEMENT_CNT" => $sectionElementCnt,
				"IBLOCK" => $sectionUnformatted["IBLOCK"],
				"LEFT_MARGIN" => $currSection["LEFT_MARGIN"],
				"DEPTH_LEVEL" => $currSection["DEPTH_LEVEL"],
			);
			
			if (isset($item["BX_ID"]) && isset($item["IBLOCK"]))
				$item["SECTION_URL"] = $this->createSectionLink($item["IBLOCK"], $item["BX_ID"]);
			
			$sectionsFormatted[$sectionUnformatted["TO_ALBUM"]]["ITEMS"][$item["LEFT_MARGIN"]] = $item;
			$sectionsFormatted[$sectionUnformatted["TO_ALBUM"]]["ELEMENT_CNT"] += $sectionElementCnt;
		}

//		sorted sections for each album by level, add tabs to names
		$sectionsFormatted = self::sortMapElementItems($sectionsFormatted);
		
		return $sectionsFormatted;
	}
	
	private static function sortMapElementItems($sectionsFormatted)
	{
		$sectionsSorted = array();
		foreach ($sectionsFormatted as $albumKey => $album)
		{
			if (!empty($album["ITEMS"]))
			{
				ksort($album["ITEMS"]);
//				add tabs to name, by depth level. Can't use base DEPTH_LEVEL, need rematch
				$prevDepthLevel = false;
				$tabsCount = 0;
				foreach ($album["ITEMS"] as &$item)
				{
					if (!$prevDepthLevel)
						$prevDepthLevel = $item["DEPTH_LEVEL"];	//first item
					if ($item["DEPTH_LEVEL"] > $prevDepthLevel)
					{
						$tabsCount++;
						$prevDepthLevel = $item["DEPTH_LEVEL"];
					}
					elseif ($item["DEPTH_LEVEL"] < $prevDepthLevel)
					{
						$tabsCount--;
						$prevDepthLevel = $item["DEPTH_LEVEL"];
					}
					$item["NAME"] = str_repeat('- ', $tabsCount) . $item["NAME"];
				}
			}
			
			$sectionsSorted[$albumKey] = $album;
		}
		
		return $sectionsSorted;
	}
	
	
	/**
	 * Create URL to VK album by VK album ID
	 * @param $albumVkId
	 * @return bool|string
	 */
	private function createVkAlbumLink($albumVkId)
	{
		$vk = Vk::getInstance();
		$groupId = str_replace('-', '', $vk->getGroupId($this->exportId));
		
		if ($groupId)
			return Vk::VK_URL . Vk::VK_URL__MARKET_PREFIX . $groupId . Vk::VK_URL__ALBUM_PREFIX . $albumVkId;
		else
			return false;
	}
	
	
	/**
	 * Create URL to section setting page, to trading platform settings TAB, by iblock ID and section ID
	 * @param $iblockId
	 * @param $sectionId
	 * @return string
	 */
	private function createSectionLink($iblockId, $sectionId)
	{
		$sectionTabControlName = 'form_section_' . $iblockId . '_active_tab';
		
		return \CAllIBlock::GetAdminSectionEditLink($iblockId, $sectionId, array(
			$sectionTabControlName => "SALE_TRADING_PLATFORM_edit_trading_platforms"
		));
	}
	
	
	/**
	 * Find parameter TO_ALBUM to section. Check INHERING and parent settings
	 *
	 * @param $sectionId
	 * @return int ID of section to adding
	 */
	public function getToAlbumBySection($sectionId)
	{
		$mappedSection = $this->mappedSections[$sectionId];
		$params = $mappedSection["PARAMS"];
		$parentParams = $params["PARENT_SETTINGS"];

//		get params from current section if them enabled
//		or from parent, if them enabled and not INCLUDE_CHILDS
		if (
			(!$params["INHERIT"] && $params["ENABLE"]) ||
			($params["INHERIT"] && $parentParams && $parentParams["ENABLE"] && !$parentParams["INCLUDE_CHILDS"])
		)
		{
			if (isset($params["TO_ALBUM"]) && $params["TO_ALBUM"])
//				get param TO_ALBUM
				return $params["TO_ALBUM"];
			else
//				or add in current album
				return $sectionId;
		}

//		if INHERIT and parent section included childs - put section to parent to_album
		elseif ($params["INHERIT"] && $parentParams && $parentParams["ENABLE"] && $parentParams["INCLUDE_CHILDS"])
		{
			return $parentParams["TO_ALBUM"];
		}
		
		else
		{
			return 0;
		}
	}
	
	
	/**
	 * Create selector for HTML. Not create <select> tag, only inner <options>
	 *
	 * @param null $checkedSection - ID of section. If not NULL - this option will be checked
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSectionsSelector($checkedSection = NULL)
	{
		$iblockIds = $this->getMappedIblocks();
		$sectionsTree = $this->getSections(true);
		
		$result = '';
		$result .= '<option value="0">' . Loc::getMessage("SALE_CATALOG_VK_MAIN_ALBUM") . '</option>';
		foreach ($iblockIds as $iblock)
		{
//			todo: why strtoupper dont work ? encode problem ?
			$result .= '<option disabled value="-1">' . strtoupper($iblock["NAME"]) . '</option>';

//			parse ITEMS for current iblock
			foreach ($sectionsTree[$iblock["IBLOCK_ID"]] as $bxCategory)
			{
				$selected = $checkedSection == $bxCategory["ID"] ? ' selected' : '';
				$result .=
					'<option' . $selected . ' value="' . $bxCategory["ID"] . '">' .
					str_repeat('. ', $bxCategory["DEPTH_LEVEL"]) . $bxCategory["NAME"] .
					'</option>';
			}
		}
		
		return $result;
	}
	
	
	/**
	 * Return vk default category from settings.
	 *
	 * @param $sectionId
	 * @return int
	 */
	public function getVkCategory($sectionId)
	{
//		get category from mapped
//		if not set - get from VK-settings (if set '-1' = default)
		$vkCategory = $this->mappedSections[$sectionId]['VK_ID'];
//		todo: or get parent category 
		if (!isset($vkCategory) || $vkCategory <= 0)
		{
			$vk = Vk::getInstance();
			$settings = $vk->getSettings($this->exportId);
			
			if (isset($settings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"]))
				$vkCategory = $settings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"];
			else
				$vkCategory = Vk::VERY_DEFAULT_VK_CATEGORY;    //hardcoooooooooooode
		}
		
		return $vkCategory;
	}
	
	
	/**
	 * Get saved values for selected section and export ID and format them.
	 * Check settings dependence, adjust them if needed or set default values.
	 * Set visibility for controls according by settings
	 *
	 * @param $sectionId
	 * @return array
	 */
	public function prepareSectionToShow($sectionId)
	{
		$sections = $this->getSections();
		$section = $sections[$sectionId];
		
		$currParams = $this->mappedSections[$sectionId]['PARAMS'];
		$parentParams = $currParams["PARENT_SETTINGS"];

//		for root section inherit always false
		if (!$section["IBLOCK_SECTION_ID"])
			$currParams['INHERIT'] = false;

//		if not INHERIT - get own settings, Else - find parents
		if (isset($currParams['INHERIT']) && !$currParams['INHERIT'])
		{
			$currParams = $currParams + $this->getDefaultExportParams($sectionId);
		}
		else
		{
//			prepared for correct show to_album and album_alias
			if (!empty($parentParams))
				$currParams = $this->prepareParentSettingToShow($parentParams, $section);
//			if parent not set - get default
			else
				$currParams = $this->getDefaultExportParams($sectionId);

//			override parent setting
			$currParams['INHERIT'] = true;
		}

//		add hidden fields for saving PARENTS settings
		$hiddenParentParams = !empty($parentParams) ?
			$this->prepareParentSettingToShow($parentParams, $section) :
			$hiddenParentParams = $this->getDefaultExportParams($sectionId);
		foreach ($hiddenParentParams as $key => $param)
			$currParams[$key . '__PARENT'] = $param;
		
		return $currParams;
	}
	
	/**
	 * Set visibility for controls according by settings. Return settings array
	 *
	 * @param $params
	 * @param $section
	 * @return mixed
	 */
	public function prepareSettingsVisibility($params, $sectionId)
	{
		$sections = $this->getSections();
		$section = $sections[$sectionId];
//		always hide inherit for root sections
		$params["INHERIT__DISPLAY"] = $section["IBLOCK_SECTION_ID"] ? '' : ' disabled ';

//		default
		$params["ENABLE__DISPLAY"] = ' disabled ';
		$params["TO_ALBUM__DISPLAY"] = ' disabled ';
		$params["TO_ALBUM_ALIAS__DISPLAY"] = ' disabled ';
		$params["INCLUDE_CHILDS__DISPLAY"] = " disabled ";
		$params["VK_CATEGORY__DISPLAY"] = " disabled ";

//		show params only if NOT inherit
		if (isset($params["INHERIT"]) && !$params["INHERIT"])
		{
			$params["ENABLE__DISPLAY"] = '';
//			if not enable - not params
			if (isset($params["ENABLE"]) && $params["ENABLE"])
			{
				$params["VK_CATEGORY__DISPLAY"] = '';
				$params["TO_ALBUM__DISPLAY"] = '';

//				if not common album
//				ALIAS can be showed only if checked TO ALBUM selector
				if (isset($params["TO_ALBUM"]) && $params["TO_ALBUM"] > 0 && $params["TO_ALBUM"] == $sectionId)
				{
					$params["TO_ALBUM_ALIAS__DISPLAY"] = '';
				}
				
				$params["INCLUDE_CHILDS__DISPLAY"] = $params["TO_ALBUM"] > 0 ? "" : " disabled ";
			}
		}

//		change bool values to CHECKED
		foreach ($params as $key => $param)
		{
			if ($param === true)
				$params[$key] = ' checked ';
			if ($param === false)
				$params[$key] = ' ';
		}
		
		return $params;
		
	}
	
	/**
	 * Prepare setting of parent settings to showing inhering in current section
	 *
	 * @param $settings - array of parent settings
	 * @param $section - array of section values
	 * @return array - preparing settins
	 */
	private function prepareParentSettingToShow($settings, $section)
	{
		$preparedSettings = array();
		
		$preparedSettings["ENABLE"] = $settings["ENABLE"];
		$preparedSettings["INCLUDE_CHILDS"] = false;    // always false, tak pravilno
		$preparedSettings["VK_CATEGORY"] = $settings["VK_CATEGORY"] > 0 ? $settings["VK_CATEGORY"] : Vk::VK_CATEGORY_TO_CHANGE;
		if ($settings["INCLUDE_CHILDS"])
		{
			$preparedSettings["TO_ALBUM"] = $settings["TO_ALBUM"];
			$preparedSettings["TO_ALBUM_ALIAS"] = $settings["TO_ALBUM_ALIAS"];
		}
		else
		{
			$preparedSettings["TO_ALBUM"] = $section["ID"];
			$preparedSettings["TO_ALBUM_ALIAS"] = $section["NAME"];
		}
		
		return $preparedSettings;
	}
	
	
	/**
	 * Default params for current section - need if have not cirrent and parent params
	 *
	 * @param $sectionId
	 * @return array
	 */
	private function getDefaultExportParams($sectionId)
	{
		$sections = $this->getSections();
		
		$vk = Vk::getInstance();
		$vkSettings = $vk->getSettings($this->exportId);
		$vkCategory = $vkSettings['EXPORT_SETTINGS']['CATEGORY_DEFAULT'];
		
		return array(
			"INHERIT" => true,
			"ENABLE" => false,
			"TO_ALBUM" => $sectionId,
			"TO_ALBUM_ALIAS" => $sections[$sectionId]["NAME"],
			"INCLUDE_CHILDS" => false,
			"VK_CATEGORY" => $vkCategory ? $vkCategory : Vk::VK_CATEGORY_TO_CHANGE,
		);
	}
	
	
	/**
	 * @param $settings array
	 */
	public function setCurrSectionSettings($settings)
	{
//		todo: there we must clear sectionsettings cache
		if (is_array($settings) && !empty($settings))
		{
			$this->currSectionSettings = $settings;
		}
	}
	
	/**
	 * Validate settings before saving
	 * Check settings dependence, adjust them if neede or set default values.
	 * Return array to mappng
	 *
	 * @param $sectionId
	 * @return array
	 */
	public function prepareSectionToSave($sectionId)
	{
		$settings = $this->currSectionSettings;
		if (empty($settings))
			return false;
		$sections = $this->getSections();
		$iblockId = $sections[$sectionId]["IBLOCK_ID"];
		$currParentSettings = $this->mappedSections[$sectionId]['PARAMS']['PARENT_SETTINGS'];
		
		$dataToDelete = array();
		$settingsToSave = array();

//		common params to save
		$settingsToSave["IBLOCK"] = $iblockId;
		$settingsToSave["PARENT_SETTINGS"] = $currParentSettings;

//		if INHERIT - only one flag, no need settings
// 		or if ROOT sections (newer have parent) and not enable - delete them from mapping
		if (
			(isset($settings["INHERIT"]) && $settings["INHERIT"]) ||
			(!$sections[$sectionId]["IBLOCK_SECTION_ID"] && !$settings["ENABLE"])
		)
		{
//			if INHERIT and have not parent settings - delete this item
			if (!$currParentSettings)
				$dataToDelete = array(
					"VALUE_EXTERNAL" => $settings["VK_CATEGORY"] ? $settings["VK_CATEGORY"] : Vk::VERY_DEFAULT_VK_CATEGORY,
					"VALUE_INTERNAL" => $sectionId,
				);
			else
				$settingsToSave["INHERIT"] = true;
		}
		else
		{
			$settingsToSave["INHERIT"] = false;

//			if disable - only flag
			if (!$settings["ENABLE"])
			{
				$settingsToSave["ENABLE"] = false;
			}

//			ENABLE and not INHERIT
			else
			{
				$settingsToSave["ENABLE"] = true;
				$settingsToSave["INCLUDE_CHILDS"] = $settings["INCLUDE_CHILDS"] ? true : false;
				$settingsToSave["VK_CATEGORY"] = $settings["VK_CATEGORY"] != 0 ? $settings["VK_CATEGORY"] : Vk::VK_CATEGORY_TO_CHANGE;
				if (isset($settings["TO_ALBUM"]) && $settings["TO_ALBUM"] > 0)
				{
					$settingsToSave["TO_ALBUM"] = $settings["TO_ALBUM"];
					if (
						isset($settings["TO_ALBUM_ALIAS"]) && $settings["TO_ALBUM_ALIAS"] &&
						$settings["TO_ALBUM"] == $sectionId
					)
						$settingsToSave["TO_ALBUM_ALIAS"] = $settings["TO_ALBUM_ALIAS"];
					else
						$settingsToSave["TO_ALBUM_ALIAS"] = NULL;
				}
				else
				{
					$settingsToSave["TO_ALBUM"] = 0;
					$settingsToSave["TO_ALBUM_ALIAS"] = NULL;
				}
			}
		}

//		VALUE_EXTERNAL is required, but we might be have not this value. Get some default
		
		if (!empty($dataToDelete))
			return array(
				"TO_DELETE" => array(
					$sectionId => $dataToDelete,
				),
			);
		
		else
			return array(
				"TO_SAVE" => array(
					$sectionId => array(
						"VALUE_EXTERNAL" => $settings["VK_CATEGORY"] ? $settings["VK_CATEGORY"] : Vk::VERY_DEFAULT_VK_CATEGORY,
						"VALUE_INTERNAL" => $sectionId,
						"PARAMS" => $settingsToSave,
					),
				),
			);
	}
	
	
	/**
	 * Create settings for childs.
	 * For childs set only INHERIT option and VK CATEGORY. Other settings will be getting in export
	 *
	 * @param $sectionId
	 * @return array
	 */
	public function prepareChildsToSave($sectionId)
	{
		$settings = $this->currSectionSettings;
		if (empty($settings))
			return false;
		
		$sections = $this->getSections();
		$currParentSettings = $this->mappedSections[$sectionId]['PARAMS']['PARENT_SETTINGS'];
		$iblockId = $sections[$sectionId]["IBLOCK_ID"];
		
		$currLeftMargin = intval($sections[$sectionId]["LEFT_MARGIN"]);
		$currRightMargin = intval($sections[$sectionId]["RIGHT_MARGIN"]);
		
		$dataToSave = array();
		$dataToDelete = array();

//		if INHERIT changed to true - set parent settings instead settings of current section
//		or if ROOT section, then not enable - delete mapping childs
		$needDelete = false;
		if ($settings["INHERIT"] || (!$sections[$sectionId]["IBLOCK_SECTION_ID"] && !$settings["ENABLE"]))
		{
//			if have not parent settings - needed delete childs
			if (!$currParentSettings)
				$needDelete = true;
			else
				$settings = $currParentSettings;
		}
		
		
		foreach ($sections as $section)
		{
//			find CHILDS
			if (
				intval($section["LEFT_MARGIN"]) > $currLeftMargin &&
				intval($section["RIGHT_MARGIN"]) < $currRightMargin &&
				$section["IBLOCK_ID"] == $iblockId
			)
			{
//				only if childs inherit or empty settings
				if ($this->mappedSections[$section["ID"]]["PARAMS"]["INHERIT"] !== false)
				{
//					get childs to delete
					if ($needDelete)
						$dataToDelete[$section["ID"]] = array(
							'VALUE_EXTERNAL' => $settings["VK_CATEGORY"] ? $settings["VK_CATEGORY"] : Vk::VK_CATEGORY_TO_CHANGE,
							'VALUE_INTERNAL' => $section["ID"],
						);

//					get childs to add to mapping
					else
						$dataToSave[$section["ID"]] = array(
							"VALUE_EXTERNAL" => $settings["VK_CATEGORY"] ? $settings["VK_CATEGORY"] : Vk::VK_CATEGORY_TO_CHANGE,
							"VALUE_INTERNAL" => $section["ID"],
							"PARAMS" => array(
								"INHERIT" => true,
								"IBLOCK" => $iblockId,
//								save parent settings for get inherit params in future
								"PARENT_SETTINGS" => array(
									"INHERIT" => false,
									"ENABLE" => $settings["ENABLE"] ? true : false,
									"TO_ALBUM" => $settings["TO_ALBUM"],
									"TO_ALBUM_ALIAS" => $settings["TO_ALBUM_ALIAS"],
									"VK_CATEGORY" => $settings["VK_CATEGORY"],
									"INCLUDE_CHILDS" => $settings["INCLUDE_CHILDS"] ? true : false,
								),
							),
						);
				}
				else
				{
//					if we catch first not inherit child - put parent setting (save other values) to them and end cycle
					$dataToSave[$section["ID"]] = array(
						"VALUE_EXTERNAL" => $this->mappedSections[$section["ID"]]["VK_ID"],
						"VALUE_INTERNAL" => $this->mappedSections[$section["ID"]]["BX_ID"],
						"PARAMS" => $this->mappedSections[$section["ID"]]["PARAMS"],
					);
//					add parent settings to child params if parent section not deleted
					if (!$needDelete)
						$dataToSave[$section["ID"]]["PARAMS"]["PARENT_SETTINGS"] = array(
							"INHERIT" => false,
							"ENABLE" => $settings["ENABLE"] ? true : false,
							"TO_ALBUM" => $settings["TO_ALBUM"],
							"TO_ALBUM_ALIAS" => $settings["TO_ALBUM_ALIAS"],
							"VK_CATEGORY" => $settings["VK_CATEGORY"],
							"INCLUDE_CHILDS" => $settings["INCLUDE_CHILDS"] ? true : false,
						);
					else
						unset($dataToSave[$section["ID"]]["PARAMS"]["PARENT_SETTINGS"]);
				}
			}
		}
		
		$result = array();
		if (!empty($dataToSave))
			$result['TO_SAVE'] = $dataToSave;
		
		if (!empty($dataToDelete))
			$result['TO_DELETE'] = $dataToDelete;
		
		return $result;
	}
}