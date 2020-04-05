<?php
namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\AppTable;

if(!defined('REST_MP_CATEGORIES_CACHE_TTL'))
{
	define('REST_MP_CATEGORIES_CACHE_TTL', 86400);
}

class Client
{
	const CATEGORIES_CACHE_TTL = REST_MP_CATEGORIES_CACHE_TTL;

	protected static $buyLinkList = array(
		'bitrix24' => '/settings/order/make.php?limit=#NUM#&module=#CODE#',
		'ru' => 'https://marketplace.1c-bitrix.ru/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
		'en' => 'https://store.bitrix24.com/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
		'de' => 'https://store.bitrix24.de/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
		'ua' => 'https://marketplace.1c-bitrix.ua/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
	);

	private static $appTop = null;

	public static function getTop($action, $fields = array())
	{
		$allowedActions = array(
			Transport::METHOD_GET_LAST,
			Transport::METHOD_GET_DEV,
			Transport::METHOD_GET_BEST
		);

		if(in_array($action, $allowedActions))
		{
			if(!is_array(self::$appTop))
			{
				$batch = array();
				foreach($allowedActions as $method)
				{
					$batch[$method] = array($method, $fields);
				}

				self::$appTop = Transport::instance()->batch($batch);
			}

			return self::$appTop[$action];
		}
		else
		{
			return Transport::instance()->call($action);
		}
	}

	public static function getBuy($codeList)
	{
		return Transport::instance()->call(
			Transport::METHOD_GET_BUY,
			array(
				"code" => implode(",", $codeList)
			)
		);
	}

	public static function getUpdates($codeList)
	{
		$updatesList = Transport::instance()->call(
			Transport::METHOD_GET_UPDATES,
			array(
				"code" => serialize($codeList)
			)
		);

		if(is_array($updatesList) && is_array($updatesList["ITEMS"]))
		{
			static::setAvailableUpdate($updatesList["ITEMS"]);
		}

		return $updatesList;
	}

	public static function setAvailableUpdate($updateList = array())
	{
		if(!is_array($updateList) || count($updateList) <= 0)
		{
			$cnt = 0;
			$optionValue = "";
		}
		else
		{
			$cnt = count($updateList);
			$optionValue = array();

			foreach($updateList as $update)
			{
				if(is_array($update['VERSIONS']) && count($update['VERSIONS']) > 0)
				{
					$optionValue[$update["CODE"]] = max(array_keys($update["VERSIONS"]));
				}
			}

			$optionValue = serialize($optionValue);
		}

		Option::set("rest", "mp_num_updates", $cnt);
		Option::set("rest", "mp_updates", $optionValue);
	}

	public static function getAvailableUpdate($code = false)
	{
		$updates = Option::get("rest", "mp_updates", "");
		$updates = $updates == "" ? array() : unserialize($updates);

		if($code !== false)
		{
			return array_key_exists($code, $updates) ? $updates[$code] : false;
		}
		else
		{
			return $updates;
		}
	}

	public static function getAvailableUpdateNum()
	{
		return intval(Option::get("rest", "mp_num_updates", 0));
	}

	public static function getCategories($forceReload = false)
	{
		$managedCache = Application::getInstance()->getManagedCache();

		$cacheId = 'rest|marketplace|categories|'.LANGUAGE_ID;

		if(
			$forceReload === false
			&& static::CATEGORIES_CACHE_TTL > 0
			&& $managedCache->read(static::CATEGORIES_CACHE_TTL, $cacheId)
		)
		{
			$categoriesList = $managedCache->get($cacheId);
		}
		else
		{
			$categoriesList = Transport::instance()->call(Transport::METHOD_GET_CATEGORIES);
			if($categoriesList)
			{
				$categoriesList = $categoriesList["ITEMS"];
			}

			if(static::CATEGORIES_CACHE_TTL > 0)
			{
				$managedCache->set($cacheId, $categoriesList);
			}
		}

		return $categoriesList;
	}

	public static function getCategory($code, $page = false, $pageSize = false)
	{
		$queryFields = Array(
			"code" => $code
		);
		$page = intval($page);
		$pageSize = intval($pageSize);
		if($page > 0)
		{
			$queryFields["page"] = $page;
		}
		if($pageSize > 0)
		{
			$queryFields["onPageSize"] = $pageSize;
		}

		return Transport::instance()->call(
			Transport::METHOD_GET_CATEGORY,
			$queryFields
		);
	}

	public static function getByTag($tag, $page = false)
	{
		$queryFields = Array(
			"tag" => $tag
		);
		$page = intval($page);
		if($page > 0)
		{
			$queryFields["page"] = $page;
		}

		return Transport::instance()->call(
			Transport::METHOD_GET_TAG,
			$queryFields
		);
	}

	public static function getApp($code, $version = false, $checkHash = false, $installHash = false)
	{
		$queryFields = Array(
			"code" => $code
		);

		$version = intval($version);
		if($version > 0)
		{
			$queryFields["ver"] = $version;
		}

		if($checkHash !== false)
		{
			$queryFields["check_hash"] = $checkHash;
			$queryFields["install_hash"] = $installHash;
		}

		return Transport::instance()->call(
			Transport::METHOD_GET_APP,
			$queryFields
		);
	}

	public static function filterApp($fields, $page = false)
	{
		if (!is_array($fields))
			$fields = array($fields);

		$queryFields = $fields;

		$page = intval($page);
		if($page > 0)
		{
			$queryFields["page"] = $page;
		}

		return Transport::instance()->call(
			Transport::METHOD_FILTER_APP,
			$queryFields
		);
	}

	public static function searchApp($q, $page = false)
	{
		$q = trim($q);

		$queryFields = Array(
			"q" => $q
		);

		$page = intval($page);
		if($page > 0)
		{
			$queryFields["page"] = $page;
		}

		return Transport::instance()->call(
			Transport::METHOD_SEARCH_APP,
			$queryFields
		);
	}

	public static function getInstall($code, $version = false, $checkHash = false, $installHash = false)
	{
		$queryFields = Array(
			"code" => $code,
			"encode_key" => "Y",
			"member_id" => \CRestUtil::getMemberId(),
		);

		$version = intval($version);
		if($version > 0)
		{
			$queryFields["ver"] = $version;
		}

		if($checkHash !== false)
		{
			$queryFields["check_hash"] = $checkHash;
			$queryFields["install_hash"] = $installHash;
		}

		return Transport::instance()->call(Transport::METHOD_GET_INSTALL, $queryFields);
	}

	public static function getBuyLink($num, $appCode)
	{
		$linkTpl = static::$buyLinkList['en'];

		if(\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$linkTpl = static::$buyLinkList['bitrix24'];
		}
		else
		{
			if(array_key_exists(LANGUAGE_ID, static::$buyLinkList))
			{
				$linkTpl = static::$buyLinkList[LANGUAGE_ID];
			}
			elseif(array_key_exists(Loc::getDefaultLang(LANGUAGE_ID), static::$buyLinkList))
			{
				$linkTpl = static::$buyLinkList[Loc::getDefaultLang(LANGUAGE_ID)];
			}
		}

		return str_replace(
			array('#NUM#', '#CODE#'),
			array(intval($num), urlencode($appCode)),
			$linkTpl
		);
	}

	public static function getNumUpdates()
	{
		$appCodes = array();
		$dbApps = AppTable::getList(array(
			'filter' => array(
				"=ACTIVE" => AppTable::ACTIVE,
				"!=STATUS" => AppTable::STATUS_LOCAL,
			),
			'select' => array('CODE', 'VERSION')
		));
		while($app = $dbApps->fetch())
		{
			$appCodes[$app["CODE"]] = $app["VERSION"];
		}

		if(!empty($appCodes))
		{
			$updateList = static::getUpdates($appCodes);

			if($updateList)
			{
				self::setAvailableUpdate($updateList['ITEMS']);
			}
			else
			{
				self::setAvailableUpdate();
			}
		}

		return __CLASS__."::getNumUpdates();";
	}

	public static function getTagByPlacement($placement)
	{
		$tag = array();
		if(strlen($placement) > 0)
		{
			if(substr($placement, 0, 4) === 'CRM_' || $placement === \Bitrix\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE)
			{
				if($placement !== 'CRM_ROBOT_TRIGGERS')
				{
					$tag[] = 'placement';
				}
				else
				{
					$tag[] = 'automation';
				}

				$tag[] = 'crm';
			}
			elseif(substr($placement, 0, 5) === 'CALL_')
			{
				$tag[] = 'placement';
				$tag[] = 'telephony';
			}
		}

		$tag[] = $placement;

		return $tag;
	}

}