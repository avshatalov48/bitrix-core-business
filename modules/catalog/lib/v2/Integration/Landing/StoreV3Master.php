<?php

namespace Bitrix\Catalog\v2\Integration\Landing;

use Bitrix\Crm\Order\TradeBindingCollection;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

final class StoreV3Master
{
	private const STORE_ID = 'store_v3';
	private const MINIMAL_ORDERS_LIMIT = 5;

	private static $storeId;

	public static function hasStore(): bool
	{
		return self::getStoreId() !== null;
	}

	public static function getStoreId(): ?int
	{
		if (self::$storeId === null)
		{
			if (!Loader::includeModule('landing'))
			{
				return null;
			}

			Rights::setGlobalOff();

			$result = Site::getList([
				'select' => ['ID'],
				'filter' => ['=TPL_CODE' => self::STORE_ID],
				'order' => ['ID' => 'desc'],
				'limit' => 1,
			]);
			if ($store = $result->fetch())
			{
				self::$storeId = (int)$store['ID'];
			}

			Rights::setGlobalOn();
		}

		return self::$storeId;
	}

	public static function getStoreUrl(): ?string
	{
		if (!self::hasStore())
		{
			return null;
		}

		Rights::setGlobalOff();
		$storeUrl = Site::getPublicUrl(self::getStoreId());
		Rights::setGlobalOn();

		return $storeUrl;
	}

	public static function addNewStore(): Result
	{
		$result = new Result();

		if (!Loader::includeModule('landing'))
		{
			return $result->addError(new Error('Failed to load module \'landing\'.'));
		}

		if (self::limitExceeded())
		{
			if (!self::hasUnusedStores())
			{
				return $result->addError(new Error('Store limit exceeded.'));
			}

			$res = self::deactivateUnusedStore();
			if (!$res->isSuccess())
			{
				return $result->addErrors($res->getErrors());
			}

			$result->setData($res->getData());
		}

		self::createStore();

		if (!self::hasStore())
		{
			$result->addError(new Error('Failed to create a new store.'));
		}

		return $result;
	}

	public static function canCreate(): bool
	{
		if (!Loader::includeModule('landing'))
		{
			return false;
		}

		if (!self::limitExceeded())
		{
			return true;
		}

		return self::hasUnusedStores();
	}

	private static function limitExceeded(): bool
	{
		return !Manager::checkFeature(
			Manager::FEATURE_PUBLICATION_SITE,
			['type' => 'STORE']
		);
	}

	private static function hasUnusedStores(): bool
	{
		// return !empty(self::getUnusedStoresList());
		return false;
	}

	private static function deactivateUnusedStore(): Result
	{
		$result = new Result();

		if (!self::hasUnusedStores())
		{
			return $result->addError(
				new Error('There are no unused stores on the portal.')
			);
		}

		Rights::setGlobalOff();
		$deactivatedStore = null;

		foreach (self::getUnusedStoresList() as $storeId)
		{
			$result = Site::update($storeId, ['ACTIVE' => 'N']);
			if ($result->isSuccess())
			{
				$deactivatedStore = Site::getList([
					'select' => ['ID', 'TITLE'],
					'filter' => ['=ID' => $storeId],
				])
					->fetch()
				;
				if ($deactivatedStore)
				{
					$result->setData(compact('deactivatedStore'));
				}

				break;
			}
		}

		Rights::setGlobalOn();
		if (!$deactivatedStore)
		{
			$result->addError(new Error('Failed to deactivate unused stores.'));
		}

		return $result;
	}

	private static function getUnusedStoresList(): array
	{
		static $result = null;

		if ($result === null)
		{
			if (!Loader::includeModule('landing') || !Loader::includeModule('crm'))
			{
				return [];
			}

			Rights::setGlobalOff();

			$result = [];
			$activeStoreIds = [];
			$res = Site::getList([
				'select' => ['ID'],
				'filter' => [
					'=TYPE' => 'STORE',
					'=ACTIVE' => 'Y',
				],
			]);
			while ($row = $res->fetch())
			{
				$activeStoreIds[] = $row['ID'];
			}

			if (!empty($activeStoreIds))
			{
				$storesInUse = [];
				$filter = [
					'>CNT' => self::MINIMAL_ORDERS_LIMIT,
					'=TRADING_PLATFORM.CODE' => [],
				];
				foreach ($activeStoreIds as $siteId)
				{
					$filter['=TRADING_PLATFORM.CODE'][] = 'landing_' . $siteId;
				}
				$res = TradeBindingCollection::getList([
					'select' => [
						'CNT',
						'TRADING_PLATFORM_CODE' => 'TRADING_PLATFORM.CODE',
					],
					'filter' => $filter,
					'group' => 'TRADING_PLATFORM.CODE',
					'runtime' => [new ExpressionField('CNT', 'COUNT(*)')],
				]);
				while ($row = $res->fetch())
				{
					if ($row['TRADING_PLATFORM_CODE'])
					{
						[, $siteId] = explode('_', $row['TRADING_PLATFORM_CODE']);
						$storesInUse[] = $siteId;
					}
				}

				$result = array_diff($activeStoreIds, $storesInUse);
			}

			Rights::setGlobalOn();
		}

		return $result;
	}

	private static function createStore(): void
	{
		$componentName = 'bitrix:landing.site_master';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$siteMaster = new $className;
		/** @var \LandingSiteMasterComponent $siteMaster */
		$siteMaster->initComponent($componentName);
		$siteMaster->actionCreate(self::STORE_ID);

		self::clearCache();
	}

	private static function clearCache(): void
	{
		self::$storeId = null;
	}
}
