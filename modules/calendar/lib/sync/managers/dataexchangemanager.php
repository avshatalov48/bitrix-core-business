<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Sync\Factories\FactoryBase;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Factories\SyncSectionFactory;
use Bitrix\Calendar\Sync\Icloud;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\Office365;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class DataExchangeManager
{
	private const COUNT_CONNECTIONS_FOR_REGULAR_SYNC = 10;
	protected SyncSectionFactory $syncSectionFactory;
	private FactoriesCollection $factories;

	/**
	 * @param FactoriesCollection $factories
	 */
	public function __construct(FactoriesCollection $factories)
	{
		$this->factories = $factories;
	}

	/**
	 * @param Connection $connection
	 * @return void
	 * @throws Core\Base\BaseException
	 * @throws ObjectNotFoundException
	 */
	public static function markDeletedFailedConnection(Connection $connection): void
	{
		/** @var Core\Mappers\Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$mapperFactory
			->getConnection()
			->patch($connection, ['IS_DELETED' => Core\Mappers\Mapper::POSITIVE_ANSWER])
		;
	}

	/**
	 * @return Result
	 * @throws Core\Base\BaseException
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function exchange(): Result
	{
		/** @var FactoryBase $factory */
		foreach ($this->factories as $factory)
		{
			if (!$factory)
			{
				continue;
			}

			$exchangeManager = new VendorDataExchangeManager($factory, self::getSyncSectionMap($factory));
			$exchangeManager->exchange();
		}

		return new Result();
	}

	/**
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function import(): Result
	{
		/** @var FactoryBase $factory */
		foreach ($this->factories as $factory)
		{
			$exchangeManager = new VendorDataExchangeManager($factory, self::getSyncSectionMap($factory));
			$exchangeManager
				->importSections()
				->importEvents()
				->updateConnection($factory->getConnection())
				->clearCache()
			;

			try
			{
				$exchangeManager->renewSubscription($factory->getConnection());
			}
			catch (\Exception $e)
			{
			}
		}

		return new Result();
	}

	/**
	 * @return string
	 *
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function importAgent(): string
	{
		if (!Loader::includeModule('calendar') || !Loader::includeModule('dav'))
		{
			return "\\Bitrix\\Calendar\\Sync\\Managers\\DataExchangeManager::importAgent();";
		}

		$connections = self::getConnections();
		/** @var Connection $connection */
		while ($connection = $connections->fetch())
		{
			if ($connection->getOwner() === null)
			{
				self::markDeletedFailedConnection($connection);
				continue;
			}

			try
			{
				/** @var FactoryBase $factory */
				$factory = FactoriesCollection::createByConnection($connection)->fetch();

				if (!$factory)
				{
					continue;
				}

				$exchangeManager = new VendorDataExchangeManager($factory, self::getSyncSectionMap($factory));
				$exchangeManager
					->importSections()
					->importEvents()
					->updateConnection($factory->getConnection())
					->clearCache();
			}
			catch (RemoteAccountException $e)
			{
				self::markDeletedFailedConnection($connection);
			}
			catch (\Exception $e)
			{
				$connection->setSyncStatus(Dictionary::SYNC_STATUS['failed']);
			}
		}

		return "\\Bitrix\\Calendar\\Sync\\Managers\\DataExchangeManager::importAgent();";
	}

	/**
	 * @return Core\Base\Map
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private static function getConnections(): Core\Base\Map
	{
		/** @var Core\Mappers\Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		return $mapperFactory->getConnection()->getMap(
			[
				'=ACCOUNT_TYPE' => [
					Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
					Office365\Helper::ACCOUNT_TYPE
				],
				'=ENTITY_TYPE' => Core\Role\User::TYPE,
				'=IS_DELETED' => 'N',
			],
			self::COUNT_CONNECTIONS_FOR_REGULAR_SYNC,
			['SYNCHRONIZED' => 'ASC']
		);
	}

	/**
	 * @param FactoryBase $factory
	 *
	 * @return \Bitrix\Calendar\Sync\Entities\SyncSectionMap
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private static function getSyncSectionMap(FactoryBase $factory): SyncSectionMap
	{
		return (new SyncSectionFactory())->getSyncSectionMapByFactory($factory);
	}
}
