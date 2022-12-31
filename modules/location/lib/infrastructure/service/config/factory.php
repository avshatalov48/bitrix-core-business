<?php

namespace Bitrix\Location\Infrastructure\Service\Config;

use Bitrix\Location\Entity\Source;
use Bitrix\Location\Infrastructure\Service\DisputedAreaService;
use Bitrix\Location\Infrastructure\Service\LoggerService;
use Bitrix\Location\Infrastructure\Service\CurrentRegionFinderService;
use Bitrix\Location\Infrastructure\SourceCodePicker;
use Bitrix\Location\Repository\AddressRepository;
use	Bitrix\Location\Exception\ErrorCodes;
use Bitrix\Location\Repository\Format\DataCollection;
use Bitrix\Location\Repository\FormatRepository;
use Bitrix\Location\Repository;
use Bitrix\Location\Repository\Location\Database;
use Bitrix\Location\Service\SourceService;
use Bitrix\Location\Repository\Location\Strategy\Delete;
use Bitrix\Location\Repository\Location\Strategy\Find;
use Bitrix\Location\Repository\Location\Strategy\Save;
use Bitrix\Location\Repository\LocationRepository;
use Bitrix\Location\Service\AddressService;
use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Location\Service\FormatService;
use Bitrix\Location\Service\LocationService;
use Bitrix\Main\Config\Option;

class Factory implements IFactory
{
	/** @var IFactory */
	private static $delegate = null;

	/**
	 * @inheritDoc
	 */
	public static function createConfig(string $serviceType): Container
	{
		$result = null;

		if(self::$delegate !== null && self::$delegate instanceof IFactory)
		{
			if($result = self::$delegate::createConfig($serviceType))
			{
				return $result;
			}
		}

		return new Container(
			static::getServiceConfig($serviceType)
		);
	}

	/**
	 * @param IFactory $factory
	 */
	public static function setDelegate(IFactory $factory): void
	{
		self::$delegate = $factory;
	}

	protected static function getLogLevel(): int
	{
		return (int)Option::get('location', 'log_level', LoggerService\LogLevel::ERROR);
	}

	protected static function getServiceConfig(string $serviceType)
	{
		$result = [];

		switch ($serviceType)
		{
			case LoggerService::class:
				$result = [
					'logger' => new LoggerService\CEventLogger(),
					'logLevel'=> static::getLogLevel(),
					'eventsToLog' => []
				];
				break;

			case ErrorService::class:
				$result = [
					'logErrors' => true,
					'throwExceptionOnError' => false
				];
				break;

			case FormatService::class:
				$result = [
					'repository' => new FormatRepository([
						'dataCollection' => DataCollection::class //Format data collection
					]),
					'defaultFormatCode' => \Bitrix\Location\Infrastructure\FormatCode::getCurrent()
				];
				break;

			case AddressService::class:
				$result = [
					'repository' => new AddressRepository()
				];
				break;

			case SourceService::class:
				$result = [
					'source' => self::obtainSource()
				];
				break;

			case LocationService::class:
				$result = [
					'repository' => static::createLocationRepository(
						self::obtainSource()
					)
				];
				break;

			case CurrentRegionFinderService::class:
			case DisputedAreaService::class:
				break;

			default:
				throw new \LogicException("Unknown service type \"${serviceType}\"", ErrorCodes::SERVICE_CONFIG_FABRIC_WRONG_SERVICE);
		}

		return $result;
	}

	/**
	 * @param Source|null $source
	 * @return LocationRepository
	 */
	private static function createLocationRepository(Source $source = null): LocationRepository
	{
		$cacheTTL = 2592000; //month
		$poolSize = 30;
		$pool = new Repository\Location\Cache\Pool($poolSize);

		$cache = new Repository\Location\Cache(
			$pool,
			$cacheTTL,
			'locationRepositoryCache',
			\Bitrix\Main\Data\Cache::createInstance(),
			\Bitrix\Main\EventManager::getInstance()
		);

		$repositories = [
			$cache,
			new Database()
		];

		if($source)
		{
			$repositories[] = $source->makeRepository();
		}

		return new LocationRepository(
			new Find($repositories),
			new Save($repositories),
			new Delete($repositories)
		);
	}

	/**
	 * @return Source|null
	 */
	private static function obtainSource(): ?Source
	{
		static $result;
		if (!is_null($result))
		{
			return $result;
		}

		$result = (new Repository\SourceRepository(new Source\OrmConverter()))
			->findByCode(SourceCodePicker::getSourceCode())
		;

		return $result && $result->isAvailable() ? $result : null;
	}
}
