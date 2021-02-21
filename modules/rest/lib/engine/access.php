<?php

namespace Bitrix\Rest\Engine;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Preset\IntegrationTable;
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
	public const ENTITY_TYPE_WEBHOOK = 'webhook';
	public const ENTITY_COUNT = 'count';
	private const MODULE_ID = 'rest';
	private const OPTION_APP_USAGE_LIST = 'app_usage_list';
	private const OPTION_ACCESS_ACTIVE = 'access_active';
	private const OPTION_HOLD_CHECK_COUNT_APP = '~hold_check_count_app';

	private static $availableApp = [];
	private static $availableAppCount = [];

	/**
	 * @return bool
	 */
	public static function isFeatureEnabled()
	{
		return !Loader::includeModule('bitrix24') || \Bitrix\Bitrix24\Feature::isFeatureEnabled('rest_access');
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
			if (!Loader::includeModule('bitrix24'))
			{
				static::$availableApp[$app] = true;
			}
			elseif (Client::isSubscriptionAvailable())
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
			$maxCount = static::getAvailableCount();
			if ($maxCount >= 0)
			{
				if ($entityType === static::ENTITY_TYPE_APP)
				{
					$appInfo = AppTable::getByClientId($entity);
					if ($appInfo['CODE'])
					{
						$entity = $appInfo['CODE'];
					}
				}
				else
				{
					$entity = (int) $entity;
				}

				$entityList = static::getActiveEntity();
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
						(
							$entityType === static::ENTITY_TYPE_APP
							&& in_array($entity, Immune::getList(), true)
						)
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
		if (
			!$subscriptionActive
			&& Loader::includeModule('bitrix24')
			&& \CBitrix24::getLicensePrefix() === 'ru'
		)
		{
			$count = (int) \Bitrix\Bitrix24\Feature::getVariable('rest_no_subscribe_access_limit');
			if ($count >= 0)
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
			static::ENTITY_TYPE_INTEGRATION => [],
			static::ENTITY_TYPE_WEBHOOK => [],
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

		$res = IntegrationTable::getList(
			[
				'select' => [
					'ID',
					'PASSWORD_ID',
				],
			]
		);
		while ($item = $res->fetch())
		{
			if ( (int) $item['PASSWORD_ID'] > 0)
			{
				$result[static::ENTITY_TYPE_WEBHOOK][] = (int) $item['PASSWORD_ID'];
			}
			$result[static::ENTITY_TYPE_INTEGRATION][] = (int) $item['ID'];
			$result[static::ENTITY_COUNT]++;
		}

		return $result;
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getHelperCode() : string
	{
		if (!static::isActiveRules())
		{
			return '';
		}
		$code = '';
		$dateFinish = Client::getSubscriptionFinalDate();
		$entity = static::getActiveEntity();
		$maxCount = static::getAvailableCount();
		if (!static::isAvailable())
		{
			if (
				$maxCount >= 0
				&& (
					$entity[static::ENTITY_COUNT] > $maxCount
					|| $entity[static::ENTITY_TYPE_APP_STATUS][AppTable::STATUS_PAID] > 0
					|| $entity[static::ENTITY_TYPE_APP_STATUS][AppTable::STATUS_SUBSCRIPTION] > 0
				)
			)
			{
				$code = 'limit_subscription_market_tarifwithmarket';
			}
			else
			{
				if ($dateFinish && $dateFinish < (new Date()))
				{
					$code = 'limit_subscription_market_demomarket_end';
				}
				else
				{
					$code = 'limit_subscription_market_access';
				}
			}
		}
		elseif (Loader::includeModule('bitrix24'))
		{
			if (\CBitrix24::getLicenseFamily() === "demo")
			{
				if ($maxCount >= 0 && $entity[static::ENTITY_COUNT] >= $maxCount)
				{
					$code = 'limit_subscription_market_tarifwithmarket';
				}
				elseif ($dateFinish && $dateFinish < (new Date()))
				{
					$code = 'limit_subscription_market_marketpaid_trialend';
				}
			}
			elseif ($dateFinish && $dateFinish < (new Date()))
			{
				$code = 'limit_subscription_market_marketpaid_trialend';
			}
			elseif (!Client::isSubscriptionAvailable())
			{
				$code = 'limit_subscription_market_marketpaid';
			}
		}

		return $code;
	}

	/**
	 * @return bool
	 */
	public static function resetToFree()
	{
		Option::delete(static::MODULE_ID, ['name' => static::OPTION_HOLD_CHECK_COUNT_APP]);
		\Bitrix\Rest\Marketplace\Notification::setLastCheckTimestamp(time());

		return true;
	}

	/**
	 * @param $licenseType string
	 */
	public static function onBitrix24LicenseChange($licenseType)
	{
		if (
			!static::isActiveRules()
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
		return Option::get(static::MODULE_ID, static::OPTION_ACCESS_ACTIVE);
	}
}