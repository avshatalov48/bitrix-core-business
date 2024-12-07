<?php
namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\Date;
use Bitrix\Market\Subscription;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Engine\Access;
use Bitrix\Bitrix24\Feature;

if(!defined('REST_MP_CATEGORIES_CACHE_TTL'))
{
	define('REST_MP_CATEGORIES_CACHE_TTL', 86400);
}

class Client
{
	const CATEGORIES_CACHE_TTL = REST_MP_CATEGORIES_CACHE_TTL;
	private const SUBSCRIPTION_REGION = [
		'ru',
		'ua',
		'by',
	];
	private const SUBSCRIPTION_DEFAULT_START_TIME = [
		'ua' => 1625090400,
		'by' => 1660514400,
	];

	protected static $buyLinkList = array(
		'bitrix24' => '/settings/order/make.php?limit=#NUM#&module=#CODE#',
		'ru' => 'https://marketplace.1c-bitrix.ru/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
		'en' => 'https://store.bitrix24.com/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
		'de' => 'https://store.bitrix24.de/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
		'ua' => 'https://marketplace.1c-bitrix.ua/tobasket.php?ID=#CODE#&limit=#NUM#&b24=y',
	);

	private static $appTop = null;
	private static $isPayApplicationAvailable;

	public static function getTop($action, $fields = array())
	{
		$allowedActions = array(
			Transport::METHOD_GET_LAST,
			Transport::METHOD_GET_DEV,
			Transport::METHOD_GET_BEST,
			Transport::METHOD_GET_SALE_OUT
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

	public static function getImmuneApp()
	{
		return Transport::instance()->call(
			Transport::METHOD_GET_IMMUNE
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
		$updates = $updates == "" ? array() : unserialize($updates, ['allowed_classes' => false]);

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

	/**
	 * Return marketplace category query result
	 * @param bool $forceReload
	 *
	 * @return array
	 */
	public static function getCategoriesFull($forceReload = false)
	{
		$managedCache = Application::getInstance()->getManagedCache();

		$cacheId = 'rest|marketplace|categories|full|'.LANGUAGE_ID;

		$requestNeeded = true;

		if (
			$forceReload === false
			&& static::CATEGORIES_CACHE_TTL > 0
			&& $managedCache->read(static::CATEGORIES_CACHE_TTL, $cacheId)
		)
		{
			$result = $managedCache->get($cacheId);
			if (is_array($result))
			{
				$requestNeeded = false;
			}
			elseif (intval($result) > time())
			{
				$requestNeeded = false;
				$result = [];
			}
		}

		if ($requestNeeded)
		{
			$result = Transport::instance()->call(Transport::METHOD_GET_CATEGORIES);
			if (!is_array($result))
			{
				$result = time() + 300;
			}

			if (static::CATEGORIES_CACHE_TTL > 0)
			{
				$managedCache->set($cacheId, $result);
			}
		}

		return (is_array($result) ? $result : []);
	}

	/**
	 * Return marketplace category items
	 * @param bool $forceReload
	 *
	 * @return array
	 */
	public static function getCategories($forceReload = false)
	{
		$categories = static::getCategoriesFull($forceReload);
		return (is_array($categories['ITEMS']) ? $categories['ITEMS'] : []);
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

	public static function getByTag($tag, $page = false, $pageSize = false)
	{
		$queryFields = Array(
			"tag" => $tag
		);
		$page = intval($page);
		if($page > 0)
		{
			$queryFields["page"] = $page;
		}

		if($pageSize > 0)
		{
			$queryFields["onPageSize"] = $pageSize;
		}

		return Transport::instance()->call(
			Transport::METHOD_GET_TAG,
			$queryFields
		);
	}

	public static function getLastByTag($tag, $page = false, $pageSize = false)
	{
		$queryFields = Array(
			"tag" => $tag,
			"sort" => "date_public"
		);

		$page = intval($page);
		if($page > 0)
		{
			$queryFields["page"] = $page;
		}

		if($pageSize > 0)
		{
			$queryFields["onPageSize"] = $pageSize;
		}

		return Transport::instance()->call(Transport::METHOD_GET_TAG, $queryFields);
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

	/**
	 * Returns site by id.
	 * @param $id
	 *
	 * @return array|false|mixed
	 */
	public static function getSite($id)
	{
		$query = [
			'site_id' => $id
		];

		return Transport::instance()->call(
			Transport::METHOD_GET_SITE_ITEM,
			$query
		);
	}

	/**
	 * Returns list of sites.
	 *
	 * @param array $query
	 *
	 * @return array|false|mixed
	 */
	public static function getSiteList(array $query = [])
	{
		$query['onPageSize'] = (int)($query['pageSize'] ?? 50);
		$query['page'] = (int)($query['page'] ?? 1);

		return Transport::instance()->call(
			Transport::METHOD_GET_SITE_LIST,
			$query
		);
	}

	public static function getAppPublic($code, $version = false, $checkHash = false, $installHash = false)
	{
		$queryFields = [
			"code" => $code
		];

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
			Transport::METHOD_GET_APP_PUBLIC,
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

			if (is_array($updateList) && isset($updateList['ITEMS']))
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
		if($placement <> '')
		{
			if(mb_substr($placement, 0, 4) === 'CRM_' || $placement === \Bitrix\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE)
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
			elseif(mb_substr($placement, 0, 5) === 'CALL_')
			{
				$tag[] = 'placement';
				$tag[] = 'telephony';
			}
		}

		$tag[] = $placement;

		return $tag;
	}

	/**
	 * @return bool
	 */
	public static function isSubscriptionAvailable()
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$status = Option::get('bitrix24', '~mp24_paid', 'N');
		}
		else
		{
			$status = Option::get('main', '~mp24_paid', 'N');
			if ($status === 'T' && Option::get('main', '~mp24_used_trial', 'N') !== 'Y')
			{
				Option::set('main', '~mp24_used_trial', 'Y');
			}
		}

		$result = ($status === 'Y' || $status === 'T');

		if (
			$status === 'Y'
			&& ModuleManager::isModuleInstalled('bitrix24')
			&& Loader::includeModule('bitrix24')
			&& \CBitrix24::getLicenseFamily() === 'project'
			&& Option::get('rest', 'can_use_subscription_project', 'N') === 'N'
		)
		{
			$result = false;
		}
		elseif($result)
		{
			$date = static::getSubscriptionFinalDate();
			if ($date)
			{
				$now = new \Bitrix\Main\Type\Date();
				if ($date < $now)
				{
					$result = false;
				}
			}
		}

		return $result;
	}

	public static function isStartDemoSubscription(): bool
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			return Option::get('bitrix24', '~mp24_paid', 'N') === 'T'
				&& Option::get('bitrix24', '~mp24_used_trial', 'N') === 'Y';
		}

		return false;
	}

	public static function getSubscriptionFinalDate(): ?Date
	{
		$result = null;

		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$timestamp = (int)Option::get('bitrix24', '~mp24_paid_date');
		}
		else
		{
			$timestamp = (int)Option::get('main', '~mp24_paid_date');
		}

		if ($timestamp > 0)
		{
			$result = Date::createFromTimestamp($timestamp);
		}

		return $result;
	}

	/**
	 * Checks subscriptions demo status
	 *
	 * @return bool
	 */
	public static function isSubscriptionDemo(): bool
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$status = Option::get('bitrix24', '~mp24_paid', 'N');
		}
		else
		{
			$status = Option::get('main', '~mp24_paid', 'N');
		}

		return $status === 'T';
	}

	private static function checkSubscriptionAccessStart($region): bool
	{
		$canStart = true;
		if (!empty(static::SUBSCRIPTION_DEFAULT_START_TIME[$region]))
		{
			$time = Option::get(
				'rest',
				'subscription_region_start_time_' . $region,
				static::SUBSCRIPTION_DEFAULT_START_TIME[$region]
			);
			$canStart =  $time < time();
		}

		return $canStart && in_array($region, static::SUBSCRIPTION_REGION, true);
	}

	public static function isSubscriptionAccess()
	{
		if (ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24'))
		{
			$result = static::checkSubscriptionAccessStart(\CBitrix24::getLicensePrefix());
		}
		else
		{
			$result = Option::get(Access::MODULE_ID, Access::OPTION_SUBSCRIPTION_AVAILABLE, 'N') === 'Y';
		}

		return $result;
	}

	public static function canBuySubscription()
	{
		$result = false;
		if (
			static::isSubscriptionAccess()
			&& Access::isFeatureEnabled()
			&& !(
				ModuleManager::isModuleInstalled('bitrix24')
				&& Loader::includeModule('bitrix24')
				&& !Feature::isFeatureEnabled('rest_can_buy_subscription')
			)
		)
		{
			$result = true;
		}

		return $result;
	}

	public static function isSubscriptionDemoAvailable()
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$used = Option::get('bitrix24', '~mp24_used_trial', 'N') === 'Y';
		}
		else
		{
			$used = Option::get('main', '~mp24_used_trial', 'N') === 'Y';
		}

		return !$used && static::isSubscriptionAccess();
	}

	/**
	 * Returns available pay application
	 * @return bool
	 */
	public static function isPayApplicationAvailable() : bool
	{
		if (is_null(static::$isPayApplicationAvailable))
		{
			static::$isPayApplicationAvailable = true;
			$time = (int) Option::get('rest', 'time_pay_application_off', 1621029600);
			if (time() > $time)
			{
				if (Loader::includeModule('bitrix24'))
				{
					$region = \CBitrix24::getLicensePrefix();
				}
				else
				{
					$region = Option::get('main', '~PARAM_CLIENT_LANG', '');
				}

				if ($region === 'ru')
				{
					static::$isPayApplicationAvailable = false;
				}
			}
		}

		return static::$isPayApplicationAvailable;
	}

	/**
	 * @param Event $event
	 */
	public static function onChangeSubscriptionDate(Event $event): void
	{
		if (static::isSubscriptionAvailable())
		{
			$event = new Event(
				'rest',
				'onSubscriptionRenew',
			);

			EventManager::getInstance()->send($event);
		}
	}
}
