<?php

namespace Bitrix\Rest\Engine;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\Marketplace\Immune;

/**
 * Class Access
 * @package Bitrix\Rest\Engine
 */
class Access
{
	public const ENTITY_TYPE_APP = 'app';
	public const ENTITY_TYPE_APP_STATUS = 'status';
	public const ENTITY_TYPE_INTEGRATION = 'integration';
	public const ENTITY_TYPE_AP_CONNECT = 'ap_connect';
	public const ENTITY_TYPE_WEBHOOK = 'webhook';
	public const ENTITY_COUNT = 'count';

	public const ACTION_INSTALL = 'install';
	public const ACTION_OPEN = 'open';
	public const ACTION_BUY = 'buy';

	public const MODULE_ID = 'rest';
	public const OPTION_ACCESS_ACTIVE = 'access_active';
	public const OPTION_AVAILABLE_COUNT = 'app_available_count';
	public const OPTION_SUBSCRIPTION_AVAILABLE = 'subscription_available';
	private const OPTION_APP_USAGE_LIST = 'app_usage_list';
	private const OPTION_REST_UNLIMITED_FINISH = 'rest_unlimited_finish';
	private const OPTION_HOLD_CHECK_COUNT_APP = '~hold_check_count_app';
	private const DEFAULT_AVAILABLE_COUNT = -1;
	private const DEFAULT_AVAILABLE_COUNT_DEMO = 10;

	private static $availableApp = [];
	private static $availableAppCount = [];

	/**
	 * @return bool
	 */
	public static function isFeatureEnabled()
	{
		return
			!ModuleManager::isModuleInstalled('bitrix24')
			|| (
				Loader::includeModule('bitrix24')
				&& \Bitrix\Bitrix24\Feature::isFeatureEnabled('rest_access')
			)
		;
	}

	/**
	 * Check available rest api.
	 *
	 * @param string $app
	 *
	 * @return bool
	 */
	public static function isAvailable($app = '') : bool
	{
		if (!static::isActiveRules())
		{
			return true;
		}

		if (!array_key_exists($app, static::$availableApp))
		{
			static::$availableApp[$app] = false;
			if (Client::isSubscriptionAvailable())
			{
				static::$availableApp[$app] = true;
			}
			elseif (static::isFeatureEnabled())
			{
				static::$availableApp[$app] = true;
			}
			elseif ($app !== '')
			{
				if (in_array($app, Immune::getList(), true))
				{
					static::$availableApp[$app] = true;
				}
				else
				{
					$appInfo = AppTable::getByClientId($app);
					if ($appInfo['CODE'] && in_array($appInfo['CODE'], Immune::getList(), true))
					{
						static::$availableApp[$app] = true;
					}
				}
			}
		}

		return static::$availableApp[$app];
	}

	/**
	 * @param $entityType string static::ENTITY_TYPE_APP | static::ENTITY_TYPE_INTEGRATION
	 * @param $entity mixed app code or integration id
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isAvailableCount(string $entityType, $entity = 0) : bool
	{
		if (!static::isActiveRules())
		{
			return true;
		}

		$key = $entityType . $entity;
		if (!array_key_exists($key, static::$availableAppCount))
		{
			static::$availableAppCount[$key] = true;
			if ($entityType === static::ENTITY_TYPE_APP)
			{
				$maxCount = static::getAvailableCount();
				if ($maxCount >= 0)
				{
					$appInfo = AppTable::getByClientId($entity);
					if (!isset($appInfo['STATUS']) || $appInfo['STATUS'] !== AppTable::STATUS_LOCAL)
					{
						if (isset($appInfo['CODE']) && $appInfo['CODE'])
						{
							$entity = $appInfo['CODE'];
						}

						$entityList = static::getActiveEntity(true);
						if ($entityList[static::ENTITY_COUNT] > $maxCount)
						{
							static::$availableAppCount[$key] = false;
						}
						elseif (
							$entityList[static::ENTITY_COUNT] === $maxCount
							&& !in_array($entity, $entityList[$entityType], true)
						)
						{
							static::$availableAppCount[$key] = false;
						}

						if (
							static::$availableAppCount[$key] === false
							&& (
								in_array($entity, Immune::getList(), true)
								|| (
									!static::needCheckCount()
									&& in_array($entity, $entityList[$entityType], true)
								)
							)
						)
						{
							static::$availableAppCount[$key] = true;
						}
					}
				}
			}
		}

		return static::$availableAppCount[$key];
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getAvailableCount() : int
	{
		$result = -1;
		$subscriptionActive = Client::isSubscriptionAvailable();
		if (!$subscriptionActive)
		{
			$restUnlimitedFinish = false;
			$count = static::DEFAULT_AVAILABLE_COUNT;
			if (Loader::includeModule('bitrix24'))
			{
				if (Client::isSubscriptionAccess())
				{
					$restUnlimitedFinish = Option::get(static::MODULE_ID, static::OPTION_REST_UNLIMITED_FINISH, null);
					$count = (int) \Bitrix\Bitrix24\Feature::getVariable('rest_no_subscribe_access_limit');
					if (\CBitrix24::getLicensePrefix() === 'ua')
					{
						$count = -1;
					}
				}
			}
			else
			{
				$count = (int) Option::get(
					static::MODULE_ID,
					static::OPTION_AVAILABLE_COUNT,
					static::DEFAULT_AVAILABLE_COUNT
				);
			}
			if (
				(!$restUnlimitedFinish || $restUnlimitedFinish < time())
				&& $count >= 0
			)
			{
				$result = $count;
			}
		}

		return $result;
	}

	public static function getActiveEntity($force = false)
	{
		$option = Option::get(static::MODULE_ID, static::OPTION_APP_USAGE_LIST, null);
		if ($force || is_null($option))
		{
			$result = static::calcUsageEntity();
			Option::set(static::MODULE_ID, static::OPTION_APP_USAGE_LIST, Json::encode($result));
		}
		else
		{
			try
			{
				$result = Json::decode($option);
			}
			catch (\Exception $exception)
			{
				$result = [];
			}
			if (!is_array($result))
			{
				$result = [];
			}
		}

		return $result;
	}

	private static function calcUsageEntity()
	{
		$result = [
			static::ENTITY_TYPE_APP => [],
			static::ENTITY_TYPE_APP_STATUS => [],
			static::ENTITY_COUNT => 0
		];
		$immuneList = Immune::getList();

		$res = AppTable::getList(
			[
				'filter' => [
					'=ACTIVE' => AppTable::ACTIVE,
				],
				'select' => [
					'CODE',
					'STATUS',
				],
			]
		);
		while ($item = $res->fetch())
		{
			if (!in_array($item['CODE'], $immuneList, true))
			{
				if (!isset($result[static::ENTITY_TYPE_APP_STATUS][$item['STATUS']]))
				{
					$result[static::ENTITY_TYPE_APP_STATUS][$item['STATUS']] = 0;
				}
				$result[static::ENTITY_TYPE_APP_STATUS][$item['STATUS']]++;

				if ($item['STATUS'] === AppTable::STATUS_LOCAL)
				{
					$result[static::ENTITY_TYPE_APP][] = $item['CODE'];
				}

				if ($item['STATUS'] === AppTable::STATUS_FREE)
				{
					$result[static::ENTITY_TYPE_APP][] = $item['CODE'];
					$result[static::ENTITY_COUNT]++;
				}
			}
		}

		return $result;
	}

	/**
	 * @param $action string
	 * @param $entityType string
	 * @param $entityData mixed
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getHelperCode($action = '', $entityType = '', $entityData = []) : string
	{
		if ($action === static::ACTION_BUY)
		{
			return 'limit_subscription_market_trial_access';
		}

		if ($entityType === static::ENTITY_TYPE_APP && !is_array($entityData))
		{
			$entityData = AppTable::getByClientId($entityData);
		}

		$code = '';
		$dateFinish = Client::getSubscriptionFinalDate();
		$entity = static::getActiveEntity();
		$maxCount = static::getAvailableCount();
		$isB24 = ModuleManager::isModuleInstalled('bitrix24') && Loader::includeModule('bitrix24');

		$isSubscriptionFinished = $dateFinish && $dateFinish < (new Date());
		$isSubscriptionAccess = Client::isSubscriptionAccess();
		$isSubscriptionDemoAvailable = Client::isSubscriptionDemoAvailable() && !$dateFinish;
		$isSubscriptionAvailable = Client::isSubscriptionAvailable();
		$canBuySubscription = Client::canBuySubscription();
		$isDemoSubscription = Client::isSubscriptionDemo();
		$isCanInstallInDemo = true;
		if (
			!empty($entityData['HOLD_INSTALL_BY_TRIAL'])
			&& $entityData['HOLD_INSTALL_BY_TRIAL'] === 'Y'
		)
		{
			$isCanInstallInDemo = false;
		}

		$license = $isB24 ? \CBitrix24::getLicenseFamily() : '';
		$isDemo = $license === 'demo';
		$isMinLicense = $isB24 && mb_strpos($license, 'project') === 0;
		$isMaxLicense = $isB24 && ($license === 'ent' || $license === 'pro' || mb_strpos($license, 'company') === 0);

		$isMaxApplication = false;
		if ($maxCount >= 0 && $entity[static::ENTITY_COUNT] >= $maxCount)
		{
			$isMaxApplication = true;
		}

		$isMaxApplicationDemo = false;
		if ($entity[static::ENTITY_COUNT] >= static::DEFAULT_AVAILABLE_COUNT_DEMO)
		{
			$isMaxApplicationDemo = true;
		}

		$hasPaidApplication = false;
		if (
			$entity[static::ENTITY_TYPE_APP_STATUS][AppTable::STATUS_PAID] > 0
			|| $entity[static::ENTITY_TYPE_APP_STATUS][AppTable::STATUS_SUBSCRIPTION] > 0
		)
		{
			$hasPaidApplication = true;
		}

		$isFreeEntity = false;
		if ($entityType === static::ENTITY_TYPE_INTEGRATION || $entityType === static::ENTITY_TYPE_AP_CONNECT)
		{
			$isFreeEntity = true;
		}
		elseif (!empty($entityData))
		{
			if (
				$entityData['ID'] > 0
				&& (isset($entityData['ACTIVE']) && $entityData['ACTIVE'])
				&& (
					$entityData['STATUS'] === AppTable::STATUS_FREE
					|| $entityData['STATUS'] === AppTable::STATUS_LOCAL
				)
			)
			{
				$isFreeEntity = true;
			}
			elseif (
				(
				!isset($entityData['ACTIVE'])
				|| !$entityData['ACTIVE']
				)
				&& !(
					$entityData['BY_SUBSCRIPTION'] === 'Y'
					|| ($entityData['FREE'] === 'N' && !empty($entityData['PRICE']))
				)
			)
			{
				$isFreeEntity = true;
			}
		}

		$isUsedDemoLicense = false;
		if ($isB24 && (int) Option::get('bitrix24', 'DEMO_START', 0) > 0)
		{
			$isUsedDemoLicense = true;
		}

		if (!static::isActiveRules())
		{
			if (
				!empty($entityData)
				&& (
					$entityData['BY_SUBSCRIPTION'] === 'Y'
					|| ($entityData['ID'] > 0 && $entityData['STATUS'] === AppTable::STATUS_SUBSCRIPTION)
				)
			)
			{
				if ($isSubscriptionDemoAvailable)
				{
					// activate demo subscription
					$code = 'limit_subscription_market_marketpaid';
				}
				elseif ($isB24 && $isDemo)
				{
					// choose license with subscription
					$code = 'limit_subscription_market_tarifwithmarket';
				}
				else
				{
					// choose subscription
					$code = 'limit_subscription_market_marketpaid';
				}
			}
		}
		elseif (!$isSubscriptionAccess)
		{
			if ($isMinLicense)
			{
				if ($isUsedDemoLicense)
				{
					$code = 'limit_free_rest_hold_no_demo';
				}
				elseif ($entityType === static::ENTITY_TYPE_AP_CONNECT)
				{
					$code = 'limit_market_bus';
				}
				else
				{
					$code = 'limit_free_rest_hold';
				}
			}
		}
		elseif (!static::isAvailable())
		{
			if ($hasPaidApplication || !$isFreeEntity)
			{
				if ($isSubscriptionDemoAvailable)
				{
					// activate demo subscription
					$code = 'limit_subscription_market_access';
				}
				elseif (!$isB24)
				{
					// choose subscription
					$code = 'plus_need_trial';
				}
				else
				{
					// choose license with subscription
					$code = 'limit_subscription_market_tarifwithmarket';
					if ($action === static::ACTION_OPEN)
					{
						$code = 'installed_plus_buy_license_with_plus';
					}
				}
			}
			elseif ($isB24 && !$isUsedDemoLicense)
			{
				// activate demo license
				if ($entityType === static::ENTITY_TYPE_AP_CONNECT)
				{
					$code = 'limit_market_bus';
				}
				else
				{
					$code = 'limit_free_rest_hold';
				}
			}
			elseif ($isB24 && !$isMaxApplicationDemo)
			{
				// choose license
				$code = 'limit_free_rest_hold_no_demo';
			}
			elseif ($isSubscriptionDemoAvailable)
			{
				// activate demo subscription
				$code = 'limit_subscription_market_marketpaid';
			}
			elseif ($isB24 && $isSubscriptionAccess)
			{
				// choose license with subscription
				$code = 'limit_subscription_market_tarifwithmarket';
				if ($action === static::ACTION_OPEN)
				{
					$code = 'limit_free_apps_buy_license_with_plus';
				}
			}
			elseif (!$isB24 && $isSubscriptionAccess)
			{
				// choose subscription
				$code = 'plus_need_trial';
			}
		}
		elseif ($isB24 && !$isDemo && $isMaxApplication && $isFreeEntity && !$isMaxLicense)
		{
			if (!$isUsedDemoLicense)
			{
				// activate demo license
				if ($entityType === static::ENTITY_TYPE_AP_CONNECT)
				{
					$code = 'limit_market_bus';
				}
				else
				{
					$code = 'limit_free_apps_need_demo';
				}
			}
			else
			{
				// choose license
				$code = 'limit_free_apps_buy_license';
			}
		}
		elseif (
			$isSubscriptionDemoAvailable
			&& $isCanInstallInDemo
			&& ($hasPaidApplication || $isMaxApplication || !$isFreeEntity)
		)
		{
			if (!$isFreeEntity)
			{
				// activate demo subscription
				$code = 'limit_subscription_market_access_buy_marketplus';
			}
			elseif ($isB24 && $isDemo)
			{
				// activate demo subscription
				$code = 'limit_subscription_market_marketpaid';
			}
			else
			{
				// activate demo subscription
				$code = 'limit_subscription_market_marketpaid';
			}
		}
		elseif ($isDemoSubscription && !$isCanInstallInDemo)
		{
			// only paid subscription app
			$code = 'subscription_market_paid_access';
		}
		elseif ($canBuySubscription)
		{
			if ($isSubscriptionFinished)
			{
				// choose subscription
				$code = 'limit_subscription_market_access_buy_marketplus';
			}
			else
			{
				// choose new subscription
				$code = 'plus_need_trial';
			}
		}
		elseif ($isB24 && $isDemo)
		{
			if (!$isSubscriptionDemoAvailable)
			{
				// choose license with subscription
				$code = 'limit_subscription_market_tarifwithmarket';
			}
			else
			{
				// activate demo subscription
				$code = 'limit_subscription_market_access';
			}
		}
		elseif (!$isSubscriptionAvailable)
		{
			$code = 'limit_free_rest_hold_no_demo';
		}

		return $code;
	}

	/**
	 * @return bool
	 */
	public static function resetToFree()
	{
		static::reset();
		Option::delete(static::MODULE_ID, ['name' => static::OPTION_HOLD_CHECK_COUNT_APP]);
		\Bitrix\Rest\Marketplace\Notification::setLastCheckTimestamp(time());

		return true;
	}

	/**
	 * @param $licenseType string
	 */
	public static function onBitrix24LicenseChange($licenseType)
	{
		static::reset();
		if (
			!Client::isSubscriptionAccess()
			&& Loader::includeModule('bitrix24')
			&& in_array($licenseType, \CBitrix24::PAID_EDITIONS, true)
		)
		{
			Option::set(static::MODULE_ID, static::OPTION_HOLD_CHECK_COUNT_APP, 'Y');
		}
	}

	/**
	 * @return bool
	 */
	public static function needCheckCount()
	{
		if (!static::isActiveRules())
		{
			return false;
		}

		return Option::get(static::MODULE_ID, static::OPTION_HOLD_CHECK_COUNT_APP, 'N') === 'N';
	}

	/**
	 * @return string
	 */
	public static function isActiveRules()
	{
		return
			ModuleManager::isModuleInstalled('bitrix24')
			|| Option::get(static::MODULE_ID, static::OPTION_ACCESS_ACTIVE, 'N') === 'Y'
		;
	}

	/**
	 * Agent calculates usage entities
	 *
	 * @param bool $period
	 *
	 * @return string
	 */
	public static function calcUsageEntityAgent($period = false)
	{
		static::getActiveEntity(true);
		return $period === true ? '\Bitrix\Rest\Engine\Access::calcUsageEntityAgent(true);' : '';
	}

	/**
	 * Reset saved data
	 * @return bool
	 */
	public static function reset() : bool
	{
		static::$availableApp = [];
		static::$availableAppCount = [];

		return true;
	}
}