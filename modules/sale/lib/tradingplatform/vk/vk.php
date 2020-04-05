<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform\Platform;
use Bitrix\Sale\TradingPlatformTable;
use Bitrix\Sale\TradingPlatform;
use Bitrix\Sale\TradingPlatform\Vk\Api\Api;
use Bitrix\Sale\TradingPlatform\Vk\Api\Executer;

Loc::loadMessages(__FILE__);

/**
 * Class Vk - manage VKontakte object - create, delete, install, uninstall, get and save settings etc.
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class Vk extends Platform
{
	const TRADING_PLATFORM_CODE = "vk";
	private $accessToken;
	private $api = array();
	private $executer = array();
	
	const OAUTH_URL = "https://oauth.vk.com/authorize";
	const TOKEN_URL = "https://oauth.vk.com/access_token";
	const VK_URL = 'https://vk.com/';
	const VK_URL__MARKET_PREFIX = 'market-';
	const VK_URL__ALBUM_PREFIX = '?section=album_';
	
	const GROUP_GET_STEP = 1000;
	const MAX_EXECUTION_ITEMS = 25;
	const MAX_ALBUMS = 100;
	const MAX_PRODUCTS = 15000;
	const MAX_PRODUCTS_IN_ALBUM = 1000;
	const PRODUCTS_GET_STEP = 200;
	const MAX_PHOTOS_IN_PRODUCT = 4;
	const MAX_PHOTOS_IN_ALBUM = 1;
	const MAX_VK_CATEGORIES = 1000;
	
	const MIN_ALBUM_PHOTO_WIDTH = 1280;
	const MIN_ALBUM_PHOTO_HEIGHT = 720;
	const MAX_ALBUM_PHOTO_SIZES_SUM = 14000;        //sum height and width
	const MAX_ALBUM_PHOTO_SIZE = 52428800;    //Bites
	const MAX_ALBUM_RATIO_V = 0.25;	// width / height
	const MAX_ALBUM_RATIO_H = 3;
	
	const MIN_PRODUCT_PHOTO_WIDTH = 400;
	const MIN_PRODUCT_PHOTO_HEIGHT = 400;
	const MAX_PRODUCT_PHOTO_SIZES_SUM = 14000;        //sum height and width
	const MAX_PRODUCT_PHOTO_SIZE = 52428800;    //Bites
	const MAX_PRODUCT_RATIO_V = 0.1;	// width / height
	const MAX_PRODUCT_RATIO_H = 10;
	
	const DEFAULT_TIMELIMIT = 40;    //seconds
	const DEFAULT_EXECUTION_ITEMS = 6;
	
	const VERY_DEFAULT_VK_CATEGORY = 1;    //very very default, not true, but only for preserve errors
	const VK_CATEGORY_TO_CHANGE = -1;    //category "change category"
	
	/**
	 * Return singltone object of VK
	 *
	 * @return Vk
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getInstance()
	{
		return parent::getInstanceByCode(self::TRADING_PLATFORM_CODE);
	}
	
	/**
	 * Get settings from profiles table. If passed esportId - return only one item
	 *
	 * @param null $exportId
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getSettings($exportId = NULL)
	{
		$filter = array();
		if ($exportId)
			$filter["=ID"] = $exportId;
		
		$settings = array();
		$profiles = ExportProfileTable::getList(array('filter' => $filter));
		while ($profile = $profiles->fetch())
		{
			$settings[$profile["ID"]] = array(
				'DESCRIPTION' => $profile['DESCRIPTION'],
				'VK_SETTINGS' => $profile['VK_SETTINGS'],
				'EXPORT_SETTINGS' => $profile['EXPORT_SETTINGS'],
				'OAUTH' => $profile['OAUTH'],
				'PROCESS' => $profile['PROCESS'],
			);
		}

//		only one profile if required
		if ($exportId)
			$settings = $settings[$exportId];
		
		return $settings;
	}
	
	/**
	 * Formatted export profile settings and save them in own table.
	 *
	 * @param array $settings
	 * @param null $exportId
	 * @return bool|int
	 * @throws \Exception
	 */
	public function saveSettings(array $settings)
	{
		$exportId = isset($settings['EXPORT_ID']) ? $settings['EXPORT_ID'] : NULL;
		$settings = $settings['SETTINGS'];
		
		$settingsToSave = array();
		if (isset($settings["DESCRIPTION"]))
			$settingsToSave["DESCRIPTION"] = $settings["DESCRIPTION"];
		
		if (isset($settings["VK_SETTINGS"]))
			$settingsToSave["VK_SETTINGS"] = $settings["VK_SETTINGS"];
		
		if (isset($settings["EXPORT_SETTINGS"]))
			$settingsToSave["EXPORT_SETTINGS"] = $settings["EXPORT_SETTINGS"];
		
		if (isset($settings["OAUTH"]))
			$settingsToSave["OAUTH"] = $settings["OAUTH"];
		
		if (isset($settings["PROCESS"]))
			$settingsToSave["PROCESS"] = $settings["PROCESS"];

//		check UPDATE or ADD
		$settingsExists = $this->getSettings();
		if ($exportId && array_key_exists($exportId, $settingsExists))
		{
			$resUpdate = ExportProfileTable::update($exportId, $settingsToSave);
			
			return $resUpdate->isSuccess() && $resUpdate->getAffectedRowsCount();
		}
		else
		{
			$settingsToSave["PLATFORM_ID"] = $this->id;
			$resAdd = ExportProfileTable::add($settingsToSave);
			if ($resAdd->isSuccess())
				return $resAdd->getId();
			else
				return false;
		}
		
		
	}
	
	
	/**
	 * Sets Vk active.
	 * @return bool
	 * @throws \Bitrix\Main\SystemException
	 */
	public function setActive()
	{
		if ($this->isActive())
			return true;
		
		return parent::setActive();
	}
	
	/**
	 * Sets Vk inactive.
	 * @return bool
	 */
	public function unsetActive()
	{
		if (!$this->isActive())
			return true;
		
		return parent::unsetActive();
	}
	
	/**
	 * Installs all necessary stuff for Vk.
	 * @return bool
	 */
	public function install()
	{
		RegisterModuleDependences('main', 'OnEventLogGetAuditTypes', 'sale', '\Bitrix\Sale\TradingPlatform\Vk\Vk', 'OnEventLogGetAuditTypes');

//		adding new PLATFORM
		$tptAddRes = TradingPlatformTable::add(array(
			"CODE" => $this->getCode(),
			"ACTIVE" => "N",
			"NAME" => Loc::getMessage("SALE_VK_NAME"),
			"DESCRIPTION" => Loc::getMessage("SALE_VK_DESC"),
			"CATALOG_SECTION_TAB_CLASS_NAME" => '\Bitrix\Sale\TradingPlatform\Vk\CatalogSectionTabHandler',
			"CLASS" => '\Bitrix\Sale\TradingPlatform\Vk\Vk',
		));

//		force set flag install
		if ($tptAddRes->isSuccess())
		{
			$this->isInstalled = true;
			$this->id = $tptAddRes->getId();
		}

//		adding VK IMAGE PROPERTY for all catalogs iblocks
		$iblockIds = TradingPlatform\Helper::getIblocksIds(true);
		foreach ($iblockIds as $iblock)
		{
			self::createSectionFieldVkImage($iblock["IBLOCK_ID"]);
		}
		
		return $tptAddRes->isSuccess();
	}
	
	/**
	 * Clear all items related with VK - settings, agents, mapping
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public function uninstall()
	{
//		delete vk image field for all iblocks
		$iblockIds = TradingPlatform\Helper::getIblocksIds(true);
		foreach ($iblockIds as $iblock)
		{
			self::deleteSectionFieldVkImage($iblock["IBLOCK_ID"]);
		}

//		todo: del profiles
		Map::deleteAllMapping();

//		unset active, delete from TP table, unset instance
		parent::uninstall();
	}
	
	
	/**
	 * Remove one export profile. If it last profile - uninstall all VK-platform
	 *
	 * @param $exportId
	 * @return \Bitrix\Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	public function removeProfile($exportId)
	{
		$resDel = ExportProfileTable::delete($exportId);

//		uninstall vk-categories update agent
		$vkCategories = new VkCategories($exportId);
		$vkCategories->deleteAgent($exportId);

//		clear log
		$logger = new Logger($exportId);
		$logger->clearLog();

//		uninstall platform if deleted last exportId
		$settings = $this->getSettings();
		if (empty($settings))
		{
			$this->uninstall();
		}
		
		return $resDel;
	}
	
	
	/**
	 * Create user field in catalog iblock
	 *
	 * @param $props
	 * @return array
	 */
	private function createCatalogField($props)
	{
		$result = array('RESULT' => true, 'VALUE' => '');

//		find existing props to exclude duplicates
		if ($existPropId = self::checkExistingCatalogField(
			$props['IBLOCK_ID'],
			self::createCodeForSectionFieldVkImage($props['IBLOCK_ID'])
		)
		)
		{
			$result['VALUE'] = $existPropId;
		}

//		if not exist - create new
		else
		{
			$ibp = new \CIBlockProperty();
			$result['VALUE'] = (int)$ibp->Add($props);
			if ($result['VALUE'] <= 0)
			{
				$result['RESULT'] = false;
				$result['VALUE'] = $ibp->LAST_ERROR;
			}
		}
		
		return $result;
	}
	
	
	/**
	 * Check existing user field in section by code
	 *
	 * @param $iblockId
	 * @return bool
	 */
	private function checkExistingCatalogField($iblockId, $code)
	{
		$existingProps = \CIBlockProperty::GetList(
			array(),
			array(
				'IBLOCK_ID' => $iblockId,
				'CODE' => $code,
			)
		);
		
		if ($existPropId = $existingProps->Fetch())
			return $existPropId['ID'];
		else
			return false;
	}
	
	
	/**
	 * Delete field for VK-image from sections
	 *
	 * @param $iblockId
	 * @return bool|\CDBResult
	 */
	private function deleteSectionFieldVkImage($iblockId)
	{
//		delete property with values
		if ($propertyId = self::checkExistingCatalogField($iblockId, self::createCodeForSectionFieldVkImage($iblockId)))
			return \CIBlockProperty::Delete($propertyId);
		
		else
			return false;
	}
	
	
	/**
	 * Create field for VK-image from sections
	 *
	 * @param $iblockId
	 * @return array
	 */
	private function createSectionFieldVkImage($iblockId)
	{
		$properties = array(
//			'ID' => 0,
			'NAME' => Loc::getMessage('PROP_VK_IMAGES__NAME'),
			'CODE' => self::createCodeForSectionFieldVkImage($iblockId),
			'IBLOCK_ID' => $iblockId,
			'ACTIVE' => 'Y',
			'PROPERTY_TYPE' => 'F',
			'MULTIPLE' => 'Y',
			'HINT' => Loc::getMessage('PROP_VK_IMAGES__HINT'),
			'FILE_TYPE' => 'jpg, jpeg, bmp, gif, png',
		);
		
		return self::createCatalogField($properties);
	}
	
	
	/**
	 * Create code for field for VK-image from sections
	 *
	 * @param $iblockId
	 * @return string
	 */
	private function createCodeForSectionFieldVkImage($iblockId)
	{
		return 'PHOTOS_FOR_VK_' . $iblockId;
	}
	
	
	/**
	 * Get VK-group ID from settings
	 *
	 * @param $exportId
	 * @return bool
	 */
	public function getGroupId($exportId)
	{
		$settings = $this->getSettings($exportId);
		$groupId = false;
		
		if (isset($settings["VK_SETTINGS"]["GROUP_ID"]))
		{
			$groupId = $settings["VK_SETTINGS"]["GROUP_ID"];
			$groupId = substr($groupId,0,1) == '-' ? $groupId : '-'.$groupId;
		}
		
		return $groupId;
	}
	
	
	/**
	 * Return param Agressive_export
	 *
	 * @param $exportId
	 * @return bool
	 */
	public function isAgressiveExport($exportId)
	{
		$settings = $this->getSettings($exportId);
		
		if (isset($settings["EXPORT_SETTINGS"]["AGRESSIVE"]) && $settings["EXPORT_SETTINGS"]["AGRESSIVE"])
			return true;
		else
			return false;
		
	}
	
	
	/**
	 * Return ore create new API object
	 *
	 * @param $exportId
	 * @return Api
	 */
	public function getApi($exportId)
	{
		if (!isset($this->api[$exportId]))
			$this->api[$exportId] = new Api($this->getAccessToken($exportId), $exportId);
		
		return $this->api[$exportId];
	}
	
	
	/**
	 * Return ore create new executer object
	 *
	 * @param $exportId
	 * @return Executer
	 */
	public function getExecuter($exportId)
	{
		if (!isset($this->executer[$exportId]))
			$this->executer[$exportId] = new Executer($this->getApi($exportId));
		
		return $this->executer[$exportId];
	}
	
	
	/**
	 * Return access token from settings
	 *
	 * @param $exportId
	 * @return bool
	 */
	private function getAccessToken($exportId)
	{
		if (!isset($this->accessToken[$exportId]))
		{
			$settings = $this->getSettings();
			
			if (isset($settings[$exportId]["OAUTH"]["ACCESS_TOKEN"]))
				$this->accessToken[$exportId] = $settings[$exportId]["OAUTH"]["ACCESS_TOKEN"];
			else
				throw new ArgumentNullException('accessToken');
		}
		
		return $this->accessToken[$exportId];
	}
	
	
	/**
	 * Return list of all possible exports type
	 *
	 * @return array
	 */
	public static function getExportTypes()
	{
		return array(
			'PRODUCTS',
			'PRODUCTS_DELETE',
			'PRODUCTS_DELETE_ALL',
			'ALBUMS',
			'ALBUMS_DELETE',
			'ALBUMS_DELETE_ALL',
			'ALL',
		);
	}
	
	
	/**
	 * Log events to system log & sends error to email.
	 * @param int $level Log level of event.
	 * @param string $type Event type.
	 * @param string $itemId Item id.
	 * @param string $description Event description.
	 * @return bool
	 */
//	todo: need a use only one logger, tp or vk
	public function log($level, $type, $itemId, $description)
	{
		//hardcode, because we not use other levels
		$logLevel = TradingPlatform\Logger::LOG_LEVEL_DEBUG;
		$this->logger->setLevel($logLevel);

//		todo: maybe we need email reporting of fatal errors
		
		return $this->addLogRecord($level, $type, $itemId, $description);
	}
	
	
	/**
	 * Change params and set ACTIVE flag to one export profile
	 *
	 * @param $exportId
	 */
	public function changeActiveById($exportId)
	{
		$settings = $this->getSettings($exportId);
		
		if (
			(isset($settings["VK_SETTINGS"]["GROUP_ID"]) && !empty($settings["VK_SETTINGS"]["GROUP_ID"])) &&
			(isset($settings["VK_SETTINGS"]["APP_ID"]) && $settings["VK_SETTINGS"]["APP_ID"]) &&
			(isset($settings["VK_SETTINGS"]["SECRET"]) && !empty($settings["VK_SETTINGS"]["SECRET"])) &&
			(isset($settings["OAUTH"]["ACCESS_TOKEN"]) && $settings["OAUTH"]["ACCESS_TOKEN"]) &&
			(isset($settings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"]) && $settings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"] > 0)
		)
			$this->setActiveById($exportId);
		else
			$this->unsetActiveById($exportId);
		
	}
	
	
	/**
	 * Set ACTIVE flag to one export profile
	 *
	 * @param $exportId
	 * @return bool|int
	 */
	private function setActiveById($exportId)
	{
		$settings = $this->getSettings($exportId);
		if (isset($settings) && is_array($settings))
			$settings["EXPORT_SETTINGS"]["ACTIVE"] = "Y";

//		if we set active to one profile - all platform is active
		if (!parent::isActive())
			parent::setActive();
		
		return $this->saveSettings(array('SETTINGS' => $settings, 'EXPORT_ID' => $exportId));
	}
	
	
	/**
	 * Unset ACTIVE flag to one export profile
	 *
	 * @param $exportId
	 * @return bool|int
	 */
	public function unsetActiveById($exportId)
	{
		$settings = $this->getSettings();
		if (isset($settings[$exportId]) && is_array($settings[$exportId]))
			$settings[$exportId]["EXPORT_SETTINGS"]["ACTIVE"] = "N";

//		check all profiles for active
		$bActiveAll = false;
		foreach ($settings as $id => $exportSettings)
		{
			if ($this->isActiveById($id))
				$bActiveAll = true;
		}

//		if no one profile is active - unactive all platform
		if (!$bActiveAll)
			$this->unsetActive();
		
		if (isset($settings[$exportId]) && is_array($settings[$exportId]))
			return $this->saveSettings(array('SETTINGS' => $settings[$exportId], 'EXPORT_ID' => $exportId));
		
		else
			return false;
	}
	
	
	/**
	 * Return value of ACTIVE flag to one export profile
	 *
	 * @param $exportId
	 * @return bool
	 */
	public function isActiveById($exportId)
	{
		$settings = $this->getSettings($exportId);
		
		if (isset($settings["EXPORT_SETTINGS"]["ACTIVE"]) && $settings["EXPORT_SETTINGS"]["ACTIVE"] == "Y")
			return true;
		else
			return false;
	}
	
	
	/**
	 * Create URL for link to authorize in VK Oauth server
	 *
	 * @param $exportId
	 * @param $redirectUrl
	 * @return bool|string
	 */
	public function getAuthUrl($exportId, $redirectUrl)
	{
		$settings = $this->getSettings();
		$urlParams = array();
		
		if (isset($settings[$exportId]["VK_SETTINGS"]["APP_ID"]) && !empty($settings[$exportId]["VK_SETTINGS"]["APP_ID"]))
			$urlParams['client_id'] = $settings[$exportId]["VK_SETTINGS"]["APP_ID"];
		else return false;
		
		if (!empty($redirectUrl))
			$urlParams['redirect_uri'] = self::formatRedirectUrl($redirectUrl);
		else return false;
		
		$urlParams["display"] = "page";
		$urlParams["scope"] = self::getScope(array("market", "photos", "offline", "wall", "docs", "groups"));
		$urlParams["response_type"] = "code";
		$urlParams["v"] = Api::$apiVersion;
		
		return self::OAUTH_URL . "?" . http_build_query($urlParams);
	}
	
	
	/**
	 * Create link to getting access token
	 *
	 * @param $exportId
	 * @param $redirectUrl
	 * @param $code
	 * @return bool|string
	 */
	public function getTokenUrl($exportId, $redirectUrl, $code)
	{
		$settings = $this->getSettings($exportId);
		$urlParams = array();
		
		if (isset($settings["VK_SETTINGS"]["APP_ID"]) && !empty($settings["VK_SETTINGS"]["APP_ID"]))
			$urlParams['client_id'] = $settings["VK_SETTINGS"]["APP_ID"];
		else return false;
		
		if (isset($settings["VK_SETTINGS"]["SECRET"]) && !empty($settings["VK_SETTINGS"]["SECRET"]))
			$urlParams['client_secret'] = $settings["VK_SETTINGS"]["SECRET"];
		else return false;
		
		if (!empty($redirectUrl))
			$urlParams['redirect_uri'] = self::formatRedirectUrl($redirectUrl);
		else return false;
		
		if (!empty($code))
			$urlParams['code'] = $code;
		else return false;
		
		return self::TOKEN_URL . "?" . http_build_query($urlParams);
	}
	
	
	/**
	 * Decoding url and adding protocol
	 *
	 * @param $redirectUrl
	 * @return string
	 */
	private static function formatRedirectUrl($redirectUrl)
	{
		$protocol = \CMain::IsHTTPS() ? "https://" : "http://";
		$redirectUrl = $protocol . urldecode($redirectUrl);
		
		return $redirectUrl;
	}
	
	
	/**
	 * Return array of permissions to authorize in VK
	 *
	 * @param $params - array of needed permissions names
	 * @return int|mixed
	 */
	public static function getScope($params)
	{
		$scopes = array(
			"notify" => 1,
			"friends" => 2,
			"photos" => 4,
			"audio" => 8,
			"video" => 16,
			"docs" => 131072,
			"notes" => 2048,
			"pages" => 128,
			"status" => 1024,
			"offers" => 32,
			"questions" => 64,
			"wall" => 8192,
			"groups" => 262144,
			"messages" => 4096,
			"email" => 4194304,
			"notifications" => 524288,
			"stats" => 1048576,
			"ads" => 32768,
			"market" => 134217728,
			"offline" => 65536,
		);
		$scope = 0;
		
		foreach ($params as $param)
		{
			if (isset($scopes[$param]))
				$scope += $scopes[$param];
		}
		
		return $scope;
	}
	
	/**
	 * Get timelimit from settings
	 * @param $exportId
	 * @return mixed - timelimit or false
	 */
	public function getTimelimit($exportId)
	{
		$settings = $this->getSettings($exportId);
		
		if (isset($settings["EXPORT_SETTINGS"]["TIMELIMIT"]))
			return $settings["EXPORT_SETTINGS"]["TIMELIMIT"];
		else
			return false;
	}
	
	/**
	 * Get max item count to export
	 * @param $exportId
	 * @return mixed - max item count to export or false
	 */
	public function getExecutionItemsLimit($exportId)
	{
		$settings = $this->getSettings();
		$settings = $settings[$exportId];
		
		if (isset($settings["EXPORT_SETTINGS"]["COUNT_ITEMS"]))
			return $settings["EXPORT_SETTINGS"]["COUNT_ITEMS"];
		else
			return false;
	}
	
	/**
	 * Return true if set option "Use rich log". Else return false.
	 * Rich log white more information about export, need for debug unknown errors.
	 *
	 * @param $exportId
	 * @return bool
	 */
	public function getRichLog($exportId)
	{
		$settings = $this->getSettings($exportId);
		
		if (isset($settings["EXPORT_SETTINGS"]["RICH_LOG"]) && $settings["EXPORT_SETTINGS"]["RICH_LOG"])
			return true;
		else
			return false;
	}
	
	
	public function getAvailableFlag($exportId)
	{
		$settings = $this->getSettings($exportId);
		
		if (isset($settings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"]) && !$settings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"])
			return false;
		else
			return true;
	}
	
	
	/**
	 * Return array of existings profiles IDs
	 *
	 * @$onlyActive - if true - return only active profiles. If false - return all
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getExportProfilesList($onlyActive = true)
	{
		$exportIds = array();
		$resExports = ExportProfileTable::getList(array(
				'filter' => array('PLATFORM_ID' => $this->getId()),
				'select' => array('ID', 'DESCRIPTION', 'EXPORT_SETTINGS'),
			)
		);
		
		while ($export = $resExports->fetch())
		{
			if ($onlyActive && $export["EXPORT_SETTINGS"]["ACTIVE"] != "Y")
				continue;
			else
				$exportIds[$export["ID"]] = array(
					'ID' => $export["ID"],
					'DESC' => $export['DESCRIPTION'],
				);
		}
		
		return $exportIds;
	}
	
	
	/**
	 * Error types for event log
	 *
	 * @return array
	 */
	public static function OnEventLogGetAuditTypes()
	{
		$prefix = 'VK: ';
		
		$result = array(
			"VK_PROCESS__START" => Loc::getMessage("SALE_VK_PROCESS__START"),
			"VK_PROCESS__TIMELIMIT" => Loc::getMessage("SALE_VK_PROCESS__TIMELIMIT"),
			"VK_PROCESS__ERRORS" => Loc::getMessage("SALE_VK_PROCESS__ERRORS"),
			"VK_PROCESS__FINISH" => Loc::getMessage("SALE_VK_PROCESS__FINISH"),
			"VK_FEED__CREATED" => Loc::getMessage("SALE_VK_FEED_CREATED"),
			"VK_FEED__FEED_ERRORS" => Loc::getMessage("SALE_VK_FEED_ERRORS"),
			"VK_FEED__FEED_TIMELIMIT" => Loc::getMessage("SALE_VK_FEED_TIMELIMIT"),
			"VK_FEED__FEED_FINISH" => Loc::getMessage("SALE_VK_FEED_FINISH"),
			"VK_FEED__FEED_FINISH_OK" => Loc::getMessage("SALE_VK_FEED_FINISH_OK"),
			"VK_FEED__FEED_ALBUM_PART_FINISH" => Loc::getMessage("SALE_VK_FEED_ALBUM_PART_FINISH"),
		);
		
		array_walk($result, function (&$value, $key, $prefix)
		{
			$value = $prefix . $value;
		},
			$prefix
		);
		
		return $result;
	}
}